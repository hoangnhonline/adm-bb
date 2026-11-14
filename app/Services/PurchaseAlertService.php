<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Cảnh báo nguyên liệu nhập tăng bất thường so với kỳ trước / trung bình 3 kỳ trước.
 * Nguồn dữ liệu: chi tiết hàng của chi phí (cost_detail) – giống trang Thống kê nhập hàng.
 *  - Số lượng chỉ cộng các dòng cùng ĐVT chuẩn của nguyên liệu.
 *  - Mặc định bỏ dòng "cần kiểm tra" (need_review = 1).
 *  - Kỳ đang chạy dở (có hôm nay) được so "cùng số ngày" để không lệch (vd 1–14/11 so với 1–14/10).
 */
class PurchaseAlertService
{
    const SAME_UNIT = "(d.unit IS NULL OR d.unit = '' OR d.unit = i.unit)";

    public static function defaults()
    {
        return [
            'period' => 'month',       // week | month | year | custom
            'date' => null,            // ngày bất kỳ trong kỳ (Y-m-d), mặc định hôm nay
            'from' => null, 'to' => null, // cho custom (Y-m-d)
            'compare' => 'prev',       // prev = kỳ trước | avg3 = trung bình 3 kỳ trước
            'same_days' => 1,          // kỳ đang chạy dở: so cùng số ngày
            'pct' => 30,               // ngưỡng tăng (%)
            'min_amount' => 300000,    // bỏ qua nguyên liệu có tiền nhập kỳ này nhỏ hơn mức này
            'basis' => 'any',          // qty | amount | any
            'branch_ids' => [],
            'cate_id' => null,
            'ncc_id' => null,
            'include_review' => 0,
            'include_new' => 1,        // cảnh báo cả nguyên liệu kỳ trước không nhập
        ];
    }

    /** Khoảng ngày của kỳ thứ $back trước kỳ chứa $anchor (back = 0: kỳ hiện tại). */
    public static function periodRange($period, $anchor, $back = 0, $from = null, $to = null)
    {
        $ts = strtotime($anchor);
        if ($period == 'week') {
            $f = date('Y-m-d', strtotime('monday this week', $ts));
            $f = date('Y-m-d', strtotime($f . ' -' . (7 * $back) . ' days'));
            return [$f, date('Y-m-d', strtotime($f . ' +6 days'))];
        }
        if ($period == 'year') {
            $y = (int) date('Y', $ts) - $back;
            return ["$y-01-01", "$y-12-31"];
        }
        if ($period == 'custom' && $from && $to) {
            $days = (int) round((strtotime($to) - strtotime($from)) / 86400) + 1;
            $f = date('Y-m-d', strtotime($from . ' -' . ($days * $back) . ' days'));
            return [$f, date('Y-m-d', strtotime($f . ' +' . ($days - 1) . ' days'))];
        }
        $f = date('Y-m-01', strtotime(date('Y-m-01', $ts) . ' -' . $back . ' month'));
        return [$f, date('Y-m-t', strtotime($f))];
    }

    public static function compute(array $p)
    {
        $p = array_merge(self::defaults(), array_filter($p, function ($v) { return $v !== null && $v !== ''; }));
        $today = date('Y-m-d');
        $anchor = $p['date'] ?: ($p['period'] == 'custom' && $p['from'] ? $p['from'] : $today);
        if ($p['period'] == 'custom' && (!$p['from'] || !$p['to'])) {
            $p['period'] = 'month';
        }
        list($from, $to) = self::periodRange($p['period'], $anchor, 0, $p['from'], $p['to']);

        // Kỳ đang chạy dở: chỉ tính đến hôm nay, kỳ so sánh cũng cắt cùng số ngày
        $elapsed = null;
        $partial = $p['same_days'] && $from <= $today && $today < $to;
        if ($partial) {
            $to = $today;
            $elapsed = (int) round((strtotime($today) - strtotime($from)) / 86400) + 1;
        }

        $nBase = $p['compare'] == 'avg3' ? 3 : 1;
        $baseRanges = [];
        for ($k = 1; $k <= $nBase; $k++) {
            list($bf, $bt) = self::periodRange($p['period'], $anchor, $k, $p['from'], $p['to']);
            if ($elapsed) {
                $cut = date('Y-m-d', strtotime($bf . ' +' . ($elapsed - 1) . ' days'));
                if ($cut < $bt) $bt = $cut;
            }
            $baseRanges[] = [$bf, $bt];
        }

        $cur = self::aggregate($from, $to, $p);
        $baseSum = [];
        foreach ($baseRanges as $r) {
            foreach (self::aggregate($r[0], $r[1], $p) as $id => $row) {
                if (!isset($baseSum[$id])) $baseSum[$id] = ['qty' => 0, 'amount' => 0, 'pq_amount' => 0, 'pq_qty' => 0, 'bills' => 0];
                foreach ($baseSum[$id] as $k => $v) $baseSum[$id][$k] += $row[$k];
            }
        }

        $rows = [];
        foreach ($cur as $id => $c) {
            if ($c['amount'] < $p['min_amount']) continue;
            $b = $baseSum[$id] ?? ['qty' => 0, 'amount' => 0, 'pq_amount' => 0, 'pq_qty' => 0, 'bills' => 0];
            $bQty = $b['qty'] / $nBase;
            $bAmount = $b['amount'] / $nBase;
            $qtyPct = $bQty > 0 ? ($c['qty'] - $bQty) / $bQty * 100 : null;
            $amtPct = $bAmount > 0 ? ($c['amount'] - $bAmount) / $bAmount * 100 : null;
            $curPrice = $c['pq_qty'] > 0 ? $c['pq_amount'] / $c['pq_qty'] : null;
            $basePrice = $b['pq_qty'] > 0 ? $b['pq_amount'] / $b['pq_qty'] : null;
            $pricePct = ($curPrice && $basePrice) ? ($curPrice - $basePrice) / $basePrice * 100 : null;
            $isNew = $bAmount <= 0 && $bQty <= 0;

            $reasons = [];
            if ($isNew) {
                if (!$p['include_new']) continue;
                $reasons[] = 'new';
            } else {
                if (in_array($p['basis'], ['qty', 'any']) && $qtyPct !== null && $qtyPct >= $p['pct']) $reasons[] = 'qty';
                if (in_array($p['basis'], ['amount', 'any']) && $amtPct !== null && $amtPct >= $p['pct']) $reasons[] = 'amount';
                if (!$reasons) continue;
                if ($pricePct !== null && $pricePct >= $p['pct']) $reasons[] = 'price';
            }
            $maxPct = max($qtyPct ?? 0, $amtPct ?? 0);
            $level = $isNew ? 'new' : ($maxPct >= 100 ? 'high' : ($maxPct >= 50 ? 'medium' : 'low'));
            $rows[] = (object) array_merge($c, [
                'id' => $id, 'base_qty' => $bQty, 'base_amount' => $bAmount, 'base_bills' => $b['bills'] / $nBase,
                'qty_pct' => $qtyPct, 'amount_pct' => $amtPct, 'price' => $curPrice, 'base_price' => $basePrice,
                'price_pct' => $pricePct, 'extra_amount' => $c['amount'] - $bAmount, 'reasons' => $reasons, 'level' => $level,
            ]);
        }
        usort($rows, function ($a, $b) { return $b->extra_amount <=> $a->extra_amount; });

        return [
            'params' => $p, 'from' => $from, 'to' => $to, 'partial' => $partial, 'elapsed' => $elapsed,
            'base_ranges' => $baseRanges, 'rows' => $rows,
            'total_cur' => array_sum(array_column($cur, 'amount')),
            'total_base' => array_sum(array_column($baseSum, 'amount')) / $nBase,
        ];
    }

    /** Tổng hợp theo nguyên liệu trong khoảng ngày. */
    protected static function aggregate($from, $to, array $p)
    {
        $su = self::SAME_UNIT;
        $q = DB::table('cost_detail as d')
            ->join('cost as c', 'c.id', '=', 'd.cost_id')
            ->join('ingredient as i', 'i.id', '=', 'd.ingredient_id')
            ->where('c.status', '>', 0)
            ->where('c.date_use', '>=', $from)
            ->where('c.date_use', '<=', $to);
        if (!$p['include_review']) $q->where('d.need_review', 0);
        if ($p['branch_ids']) $q->whereIn('c.branch_id', (array) $p['branch_ids']);
        if ($p['ncc_id']) $q->where('c.ncc_id', $p['ncc_id']);
        if ($p['cate_id']) $q->where('i.cost_type_id', $p['cate_id']);
        $out = [];
        foreach ($q->groupBy('d.ingredient_id', 'i.name', 'i.unit', 'i.cost_type_id')
            ->select('d.ingredient_id', 'i.name', 'i.unit', 'i.cost_type_id',
                DB::raw("SUM(CASE WHEN $su THEN d.quantity ELSE 0 END) as qty"),
                DB::raw('SUM(d.amount) as amount'),
                DB::raw("SUM(CASE WHEN $su AND d.quantity > 0 AND d.amount > 0 THEN d.amount ELSE 0 END) as pq_amount"),
                DB::raw("SUM(CASE WHEN $su AND d.quantity > 0 AND d.amount > 0 THEN d.quantity ELSE 0 END) as pq_qty"),
                DB::raw('COUNT(DISTINCT d.cost_id) as bills'))
            ->get() as $r) {
            $out[$r->ingredient_id] = ['name' => $r->name, 'unit' => $r->unit, 'cost_type_id' => $r->cost_type_id,
                'qty' => (float) $r->qty, 'amount' => (float) $r->amount, 'pq_amount' => (float) $r->pq_amount,
                'pq_qty' => (float) $r->pq_qty, 'bills' => (int) $r->bills];
        }
        return $out;
    }
}
