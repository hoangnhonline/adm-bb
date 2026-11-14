<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Hoa hồng & công nợ đối tác.
 *
 * Quy tắc (theo yêu cầu anh Hoàng 14/11/2026):
 *  - Bill chỉ tính hoa hồng khi có đối tác VÀ tick "Có chiết khấu" (co_chiet_khau = 1). Mặc định không.
 *  - Bill có món tên bắt đầu bằng "BUFFET" => bill buffet: hoa hồng = tổng SL món BUFFET × mức/khách của đối tác (mặc định 20.000).
 *  - Ngược lại => alacarte: hoa hồng = (tổng bill − giảm giá) × % của đối tác (mặc định 10%).
 *  - Bill "Ghi nợ" => đối tác nợ lại (tổng bill − giảm giá).
 *  - Bill hợp lệ: status khác 0 (xóa) và 3 (hủy).
 *
 * Số dư:
 *  - Phải thu  = Σ bill ghi nợ − Σ đối tác thanh toán (type 1) − Σ bù trừ (type 3)
 *  - Phải trả  = Σ hoa hồng    − Σ chi hoa hồng (type 2)       − Σ bù trừ (type 3)
 */
class PartnerDebtService
{
    const INVALID_STATUS = [0, 3];
    const BUFFET_PREFIX = 'BUFFET';

    /** Tính lại bill_type, pax, com_amount cho 1 bill và lưu. */
    public static function applyToBooking($bookingId)
    {
        $b = DB::table('booking')->where('id', $bookingId)->first();
        if (!$b) return null;

        $rows = DB::table('booking_detail as bd')
            ->leftJoin('dish as d', 'd.id', '=', 'bd.dish_id')
            ->where('bd.booking_id', $bookingId)
            ->get(['bd.amount', 'bd.total_price', 'd.name']);

        $pax = 0;
        foreach ($rows as $r) {
            if (stripos(ltrim((string) $r->name), self::BUFFET_PREFIX) === 0) {
                $pax += (int) round($r->amount);
            }
        }
        $billType = $pax > 0 ? 1 : 2;
        $net = max(0, (int) $b->total_price - (int) $b->discount);

        $com = 0;
        if ($b->doi_tac_id && $b->co_chiet_khau) {
            $p = Partner::find($b->doi_tac_id);
            if ($p) {
                $com = $billType == 1
                    ? $pax * (int) $p->com_buffet_per_pax
                    : (int) round($net * (float) $p->com_alacarte_pct / 100);
            }
        }
        $upd = ['bill_type' => $billType, 'pax' => $pax, 'com_amount' => $com];
        if (!$b->doi_tac_id) {
            // khách lẻ: không có chiết khấu / ghi nợ đối tác
            $upd['co_chiet_khau'] = 0;
            $upd['ghi_no'] = 0;
            $upd['com_amount'] = 0;
        }
        DB::table('booking')->where('id', $bookingId)->update($upd);
        return $upd;
    }

    /** Tính lại toàn bộ bill của 1 đối tác (khi đổi mức hoa hồng). */
    public static function recalcPartner($partnerId)
    {
        $ids = DB::table('booking')->where('doi_tac_id', $partnerId)->pluck('id');
        foreach ($ids as $id) self::applyToBooking($id);
        return count($ids);
    }

    /** Truy vấn bill hợp lệ của đối tác (có thể lọc kỳ). */
    public static function billQuery($partnerId = null, $from = null, $to = null)
    {
        $q = DB::table('booking')->whereNotNull('doi_tac_id')->whereNotIn('status', self::INVALID_STATUS);
        if ($partnerId) $q->where('doi_tac_id', $partnerId);
        if ($from) $q->where('use_date', '>=', $from);
        if ($to) $q->where('use_date', '<=', $to);
        return $q;
    }

    /**
     * Số dư của nhiều đối tác. Trả về [partner_id => [...]].
     * $to: tính đến ngày (null = tất cả)
     */
    public static function balances(array $partnerIds = null, $to = null)
    {
        $bq = self::billQuery(null, null, $to)
            ->groupBy('doi_tac_id')
            ->select('doi_tac_id',
                DB::raw('COUNT(*) as so_bill'),
                DB::raw('SUM(total_price - discount) as doanh_thu'),
                DB::raw('SUM(CASE WHEN bill_type = 1 THEN pax ELSE 0 END) as pax'),
                DB::raw('SUM(CASE WHEN ghi_no = 1 THEN total_price - discount ELSE 0 END) as no_phat_sinh'),
                DB::raw('SUM(com_amount) as hh_phat_sinh'));
        if ($partnerIds !== null) $bq->whereIn('doi_tac_id', $partnerIds ?: [0]);
        $bills = $bq->get()->keyBy('doi_tac_id');

        $tq = DB::table('partner_transactions')->where('status', 1)
            ->groupBy('partner_id')
            ->select('partner_id',
                DB::raw('SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as da_thu'),
                DB::raw('SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as da_chi'),
                DB::raw('SUM(CASE WHEN type = 3 THEN amount ELSE 0 END) as bu_tru'));
        if ($to) $tq->where('date', '<=', $to);
        if ($partnerIds !== null) $tq->whereIn('partner_id', $partnerIds ?: [0]);
        $trans = $tq->get()->keyBy('partner_id');

        $ids = $partnerIds !== null ? $partnerIds : $bills->keys()->merge($trans->keys())->unique()->all();
        $out = [];
        foreach ($ids as $id) {
            $b = $bills->get($id);
            $t = $trans->get($id);
            $r = [
                'so_bill' => (int) ($b->so_bill ?? 0),
                'doanh_thu' => (int) ($b->doanh_thu ?? 0),
                'pax' => (int) ($b->pax ?? 0),
                'no_phat_sinh' => (int) ($b->no_phat_sinh ?? 0),
                'hh_phat_sinh' => (int) ($b->hh_phat_sinh ?? 0),
                'da_thu' => (int) ($t->da_thu ?? 0),
                'da_chi' => (int) ($t->da_chi ?? 0),
                'bu_tru' => (int) ($t->bu_tru ?? 0),
            ];
            $r['phai_thu'] = $r['no_phat_sinh'] - $r['da_thu'] - $r['bu_tru'];
            $r['phai_tra'] = $r['hh_phat_sinh'] - $r['da_chi'] - $r['bu_tru'];
            $r['rong'] = $r['phai_thu'] - $r['phai_tra']; // >0: đối tác còn nợ mình; <0: mình còn nợ đối tác
            $out[$id] = $r;
        }
        return $out;
    }

    /**
     * Tuổi nợ phải thu theo FIFO: tiền thu + bù trừ trừ dần vào bill ghi nợ cũ nhất.
     * Trả về danh sách bill còn nợ + tổng theo nhóm tuổi.
     */
    public static function aging($partnerId, $payTermDays = 0, $today = null)
    {
        $today = $today ?: date('Y-m-d');
        $bills = self::billQuery($partnerId)->where('ghi_no', 1)
            ->orderBy('use_date')->orderBy('id')
            ->get(['id', 'use_date', 'table_no', 'bill_no', 'total_price', 'discount']);
        $credit = (int) DB::table('partner_transactions')->where('partner_id', $partnerId)
            ->where('status', 1)->whereIn('type', [1, 3])->sum('amount');

        $open = [];
        $buckets = ['0_30' => 0, '31_60' => 0, '61_90' => 0, 'tren_90' => 0, 'qua_han' => 0];
        foreach ($bills as $b) {
            $amt = (int) $b->total_price - (int) $b->discount;
            $use = min($credit, $amt);
            $credit -= $use;
            $remain = $amt - $use;
            if ($remain <= 0) continue;
            $age = (int) floor((strtotime($today) - strtotime($b->use_date)) / 86400);
            $due = date('Y-m-d', strtotime($b->use_date . ' +' . (int) $payTermDays . ' days'));
            $overdue = $today > $due;
            $key = $age <= 30 ? '0_30' : ($age <= 60 ? '31_60' : ($age <= 90 ? '61_90' : 'tren_90'));
            $buckets[$key] += $remain;
            if ($overdue) $buckets['qua_han'] += $remain;
            $open[] = (object) ['id' => $b->id, 'use_date' => $b->use_date, 'table_no' => $b->table_no,
                'bill_no' => $b->bill_no, 'amount' => $amt, 'remain' => $remain, 'age' => $age,
                'due' => $due, 'overdue' => $overdue];
        }
        return ['open' => $open, 'buckets' => $buckets, 'du_tien' => $credit];
    }
}
