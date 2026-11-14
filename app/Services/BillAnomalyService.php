<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Phát hiện bill bất thường (14/11/2026).
 * Loại cảnh báo:
 *  - discount : giảm giá > 15% tổng bill
 *  - cancel   : bill bị hủy / xóa
 *  - reduce   : sau khi bill đã Hoàn tất mà bị sửa giảm tiền / tăng giảm giá / bớt món
 *  - receiver : sau khi bill đã Hoàn tất mà đổi người thu tiền
 *  - old      : bill của ngày trước bị sửa / xóa
 *  - many     : bill bị sửa từ 3 lần trở lên
 * Các loại reduce / receiver / old / many dựa vào bảng activity_log (chỉ có dữ liệu từ khi bật lịch sử thao tác).
 */
class BillAnomalyService
{
    const DISCOUNT_PCT = 15;   // > 15%
    const MANY_EDITS = 3;      // >= 3 lần
    const STATUS_DONE = 2;

    public static $typeNames = [
        'discount' => 'Giảm giá cao',
        'cancel' => 'Bill bị hủy / xóa',
        'reduce' => 'Sửa giảm tiền sau khi hoàn tất',
        'receiver' => 'Đổi người thu sau khi hoàn tất',
        'old' => 'Sửa / xóa bill ngày cũ',
        'many' => 'Sửa nhiều lần',
    ];

    /**
     * @param string $from Y-m-d
     * @param string $to   Y-m-d
     * @param array|null $branchIds null = tất cả
     */
    public static function find($from, $to, $branchIds = null, array $opt = [])
    {
        $items = [];
        $bq = function ($q) use ($branchIds, $opt) {
            if ($branchIds !== null) $q->whereIn('b.branch_id', $branchIds ?: [0]);
            if (!empty($opt['branch_id'])) $q->where('b.branch_id', (int) $opt['branch_id']);
            return $q;
        };

        // ---- 1. giảm giá cao (theo ngày bill)
        $rows = $bq(DB::table('booking as b'))
            ->whereBetween('b.use_date', [$from, $to])->whereNotIn('b.status', [0, 3])
            ->where('b.total_price', '>', 0)
            ->whereRaw('b.discount * 100 > ? * b.total_price', [self::DISCOUNT_PCT])
            ->get(['b.id', 'b.use_date', 'b.branch_id', 'b.table_no', 'b.total_price', 'b.discount', 'b.updated_at', 'b.created_at', 'b.updated_user', 'b.created_user']);
        foreach ($rows as $b) {
            $items[] = self::item('discount', 'discount:' . $b->id, $b, $b->updated_user ?: $b->created_user, $b->updated_at ?: $b->created_at, [
                'total' => (int) $b->total_price, 'discount' => (int) $b->discount,
                'pct' => round($b->discount * 100 / $b->total_price, 1),
            ]);
        }

        // ---- 2. bill bị hủy / xóa (theo ngày bill)
        $rows = $bq(DB::table('booking as b'))
            ->whereBetween('b.use_date', [$from, $to])->whereIn('b.status', [0, 3])
            ->get(['b.id', 'b.use_date', 'b.branch_id', 'b.table_no', 'b.total_price', 'b.discount', 'b.status', 'b.updated_at', 'b.created_at']);
        $cancelLogs = $rows->count() ? DB::table('activity_log')->where('model', 'booking')
            ->whereIn('model_id', $rows->pluck('id')->all())->where('action', 'updated')
            ->where(function ($w) { $w->where('new_values', 'like', '%"status":3%')->orWhere('new_values', 'like', '%"status":0%')
                ->orWhere('new_values', 'like', '%"status":"3"%')->orWhere('new_values', 'like', '%"status":"0"%'); })
            ->orderBy('id')->get()->keyBy('model_id') : collect();
        foreach ($rows as $b) {
            $lg = $cancelLogs->get($b->id);
            $items[] = self::item('cancel', 'cancel:' . $b->id, $b, $lg ? $lg->user_id : null, $lg ? $lg->created_at : ($b->updated_at ?: $b->created_at), [
                'total' => (int) $b->total_price - (int) $b->discount, 'status' => (int) $b->status,
            ]);
        }

        // ---- 3. dựa vào lịch sử thao tác (theo thời điểm sửa)
        $logs = DB::table('activity_log')->whereIn('model', ['booking', 'booking_detail'])
            ->where('created_at', '>=', $from . ' 00:00:00')->where('created_at', '<=', $to . ' 23:59:59')
            ->orderBy('id')->get();
        // xác định bill của từng log
        $detailIds = [];
        foreach ($logs as $l) if ($l->model == 'booking_detail') $detailIds[] = $l->model_id;
        $detailBooking = $detailIds ? DB::table('booking_detail')->whereIn('id', array_unique($detailIds))->pluck('booking_id', 'id')->all() : [];
        $bookingOf = function ($l) use ($detailBooking) {
            if ($l->model == 'booking') return (int) $l->model_id;
            $v = (json_decode((string) $l->new_values, true) ?: []) + (json_decode((string) $l->old_values, true) ?: []);
            return (int) ($v['booking_id'] ?? ($detailBooking[$l->model_id] ?? 0));
        };
        $bIds = [];
        foreach ($logs as $l) { $bid = $bookingOf($l); if ($bid) $bIds[$bid] = 1; }
        if ($bIds) {
            $bookings = $bq(DB::table('booking as b'))->whereIn('b.id', array_keys($bIds))
                ->get(['b.id', 'b.use_date', 'b.branch_id', 'b.table_no', 'b.total_price', 'b.discount', 'b.status'])->keyBy('id');
            // toàn bộ log của các bill này (để dựng trạng thái theo thời gian, đếm số lần sửa)
            $all = DB::table('activity_log')->where(function ($w) use ($bookings) {
                    $w->where('model', 'booking')->whereIn('model_id', $bookings->keys()->all() ?: [0]);
                })->orWhere(function ($w) use ($bookings) {
                    $w->where('model', 'booking_detail')->where(function ($x) use ($bookings) {
                        foreach ($bookings->keys() as $bid) $x->orWhere('new_values', 'like', '%"booking_id":' . $bid . ',%')->orWhere('old_values', 'like', '%"booking_id":' . $bid . ',%');
                    });
                })->orderBy('id')->get();
            $byBooking = [];
            foreach ($all as $l) { $bid = $bookingOf($l); if (isset($bookings[$bid])) $byBooking[$bid][] = $l; }
            foreach ($logs as $l) { $bid = $bookingOf($l); if (isset($bookings[$bid])) $byBooking[$bid][$l->id] = $l; }

            foreach ($byBooking as $bid => $list) {
                $b = $bookings[$bid];
                $list = array_values(array_combine(array_map(function ($x) { return $x->id; }, $list), $list)); // bỏ trùng
                usort($list, function ($x, $y) { return $x->id <=> $y->id; });
                $status = null; $createUid = null; $editReqs = []; $lastEdit = null; $lastEditUser = null; $oldDays = [];
                foreach ($list as $l) {
                    $old = json_decode((string) $l->old_values, true) ?: [];
                    $new = json_decode((string) $l->new_values, true) ?: [];
                    $inRange = $l->created_at >= $from . ' 00:00:00' && $l->created_at <= $to . ' 23:59:59';
                    if ($l->model == 'booking' && $l->action == 'created') {
                        $status = isset($new['status']) ? (int) $new['status'] : $status;
                        $createUid = $l->request_uid;
                        continue;
                    }
                    if ($l->model == 'booking_detail' && $l->action == 'created' && $l->request_uid && $l->request_uid === $createUid) continue;
                    $before = array_key_exists('status', $old) ? (int) $old['status'] : $status;
                    // đếm lần sửa
                    $editReqs[$l->request_uid ?: ('log' . $l->id)] = 1;
                    $lastEdit = $l->created_at; $lastEditUser = $l->user_id;
                    if ($inRange) {
                        // sửa / xóa bill ngày cũ
                        if (substr($l->created_at, 0, 10) > $b->use_date) {
                            $day = substr($l->created_at, 0, 10);
                            $oldDays[$day] = $oldDays[$day] ?? ['user' => $l->user_id, 'at' => $l->created_at, 'n' => 0];
                            $oldDays[$day]['n']++;
                        }
                        if ($before === self::STATUS_DONE) {
                            if ($l->model == 'booking') {
                                $reduce = [];
                                if (isset($old['total_price'], $new['total_price']) && (int) $new['total_price'] < (int) $old['total_price'])
                                    $reduce['total_price'] = [(int) $old['total_price'], (int) $new['total_price']];
                                if (array_key_exists('discount', $new) && (int) $new['discount'] > (int) ($old['discount'] ?? 0))
                                    $reduce['discount'] = [(int) ($old['discount'] ?? 0), (int) $new['discount']];
                                if ($reduce) $items[] = self::item('reduce', 'reduce:' . $l->id, $b, $l->user_id, $l->created_at, $reduce + ['log_id' => $l->id, 'request_uid' => $l->request_uid]);
                                if (array_key_exists('nguoi_thu_tien', $new))
                                    $items[] = self::item('receiver', 'receiver:' . $l->id, $b, $l->user_id, $l->created_at,
                                        ['from' => $old['nguoi_thu_tien'] ?? null, 'to' => $new['nguoi_thu_tien'], 'log_id' => $l->id]);
                            } else {
                                $lessQty = $l->action == 'updated' && isset($old['amount'], $new['amount']) && (float) $new['amount'] < (float) $old['amount'];
                                if ($l->action == 'deleted' || $lessQty) {
                                    $items[] = self::item('reduce', 'reduce:' . $l->id, $b, $l->user_id, $l->created_at, [
                                        'dish_id' => $new['dish_id'] ?? ($old['dish_id'] ?? null),
                                        'item' => $l->action == 'deleted' ? 'deleted' : [(float) $old['amount'], (float) $new['amount']],
                                        'log_id' => $l->id, 'request_uid' => $l->request_uid]);
                                }
                            }
                        }
                    }
                    if ($l->model == 'booking' && array_key_exists('status', $new)) $status = (int) $new['status'];
                }
                foreach ($oldDays as $day => $o) {
                    $items[] = self::item('old', 'old:' . $bid . ':' . $day, $b, $o['user'], $o['at'], ['day' => $day, 'changes' => $o['n']]);
                }
                if (count($editReqs) >= self::MANY_EDITS && $lastEdit >= $from . ' 00:00:00') {
                    $items[] = self::item('many', 'many:' . $bid . ':' . count($editReqs), $b, $lastEditUser, $lastEdit, ['times' => count($editReqs)]);
                }
            }
        }

        // ---- đánh dấu đã xem
        $keys = array_map(function ($i) { return $i->key; }, $items);
        $reviews = $keys ? DB::table('bill_anomaly_reviews')->whereIn('anomaly_key', $keys)->get()->keyBy('anomaly_key') : collect();
        foreach ($items as $i) {
            $r = $reviews->get($i->key);
            $i->reviewed = (bool) $r;
            $i->reviewed_by = $r ? $r->reviewed_by : null;
            $i->reviewed_at = $r ? $r->reviewed_at : null;
        }
        if (!empty($opt['type'])) $items = array_values(array_filter($items, function ($i) use ($opt) { return $i->type == $opt['type']; }));
        if (!empty($opt['user_id'])) $items = array_values(array_filter($items, function ($i) use ($opt) { return (int) $i->user_id === (int) $opt['user_id']; }));
        if (isset($opt['reviewed']) && $opt['reviewed'] !== '' && $opt['reviewed'] !== null)
            $items = array_values(array_filter($items, function ($i) use ($opt) { return $i->reviewed == (bool) $opt['reviewed']; }));
        usort($items, function ($a, $b) { return strcmp((string) $b->at, (string) $a->at); });
        return $items;
    }

    protected static function item($type, $key, $b, $userId, $at, array $detail)
    {
        return (object) ['type' => $type, 'key' => $key, 'booking_id' => (int) $b->id, 'branch_id' => $b->branch_id,
            'use_date' => $b->use_date, 'table_no' => $b->table_no ?? null, 'user_id' => $userId, 'at' => $at, 'detail' => $detail];
    }

    /** Số cảnh báo chưa xem trong N ngày gần nhất (cho trang tổng quan). */
    public static function countUnreviewed($days = 7, $branchIds = null, $branchId = null)
    {
        $items = self::find(date('Y-m-d', strtotime('-' . ($days - 1) . ' days')), date('Y-m-d'), $branchIds, ['reviewed' => 0, 'branch_id' => $branchId]);
        $c = ['total' => count($items)];
        foreach ($items as $i) $c[$i->type] = ($c[$i->type] ?? 0) + 1;
        return $c;
    }
}
