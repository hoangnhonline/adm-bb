<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AppSetting;
use App\Services\Perm;
use Auth, DB;

/**
 * Thống kê thu tiền hằng ngày theo người nhận tiền (14/11/2026)
 * - Tiền bill: booking.con_lai (đã trừ giảm giá, chiết khấu, tiền cọc) tính cho "Người thu tiền" (nguoi_thu_tien).
 * - Tiền cọc: booking.tien_coc tính cho "Người thu cọc" (nguoi_thu_coc).
 * - Tuỳ chọn "Gồm VAT": cộng VAT của bill vào người thu tiền (bill KHÔNG VAT / chi nhánh không VAT = 0).
 * - Bill tính như danh sách bill: trạng thái 1, 2, 4.
 */
class ThuTienReportController extends Controller
{
    const STATUSES = [1, 2, 4];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || (!Perm::active() && Auth::user()->role != 1)) abort(403, __('Không có quyền'));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $ymd = function ($v, $def) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? $v : $def; };
        $f = [
            'from' => $ymd($request->from, date('Y-m-01')),
            'to' => $ymd($request->to, date('Y-m-d')),
            'branch_id' => $request->branch_id ? (int) $request->branch_id : null,
            'vat' => $request->vat ? 1 : 0,
        ];
        if ($f['from'] > $f['to']) { $t = $f['from']; $f['from'] = $f['to']; $f['to'] = $t; }

        $q = DB::table('booking')->where('city_id', 1)->whereIn('status', self::STATUSES)
            ->whereBetween('use_date', [$f['from'], $f['to']]);
        $ids = Perm::branchIds();
        if ($ids !== null) $q->whereIn('branch_id', $ids ?: [0]);
        if ($f['branch_id']) $q->where('branch_id', $f['branch_id']);
        $rows = $q->get(['id', 'use_date', 'branch_id', 'total_price', 'discount', 'con_lai', 'tien_coc', 'nguoi_thu_tien', 'nguoi_thu_coc', 'no_vat']);

        // $days[date][person] = tiền ; $sum[person] = [n, bill, coc, vat, total]
        $days = []; $sum = []; $dayTotal = []; $branchSum = [];
        $add = function ($d, $p, $field, $amount, $bid) use (&$days, &$sum, &$dayTotal, &$branchSum) {
            $p = (int) $p;
            if (!isset($sum[$p])) $sum[$p] = ['n' => 0, 'bill' => 0, 'coc' => 0, 'vat' => 0, 'total' => 0];
            $sum[$p][$field] += $amount; $sum[$p]['total'] += $amount;
            $days[$d][$p] = ($days[$d][$p] ?? 0) + $amount;
            $dayTotal[$d] = ($dayTotal[$d] ?? 0) + $amount;
            $branchSum[$bid][$p] = ($branchSum[$bid][$p] ?? 0) + $amount;
        };
        $billCount = [];
        foreach ($rows as $b) {
            $d = $b->use_date;
            $add($d, $b->nguoi_thu_tien, 'bill', (int) $b->con_lai, $b->branch_id);
            $sum[(int) $b->nguoi_thu_tien]['n']++;
            $billCount[$d] = ($billCount[$d] ?? 0) + 1;
            if ((int) $b->tien_coc > 0) $add($d, $b->nguoi_thu_coc, 'coc', (int) $b->tien_coc, $b->branch_id);
            if ($f['vat']) {
                $vat = AppSetting::vatOf($b->total_price, $b->discount, $b->no_vat, $b->branch_id);
                if ($vat) $add($d, $b->nguoi_thu_tien, 'vat', $vat, $b->branch_id);
            }
        }
        krsort($days);

        // cột: người thu đang hoạt động + người đã xuất hiện trong dữ liệu; 0 = chưa chọn
        $collecters = DB::table('collecter')->orderBy('display_order')->get(['id', 'name', 'status']);
        $persons = [];
        foreach ($collecters as $c) if ($c->status == 1 || isset($sum[(int) $c->id])) $persons[(int) $c->id] = $c->name;
        foreach (array_keys($sum) as $p) if (!isset($persons[$p])) $persons[$p] = $p ? '#' . $p : __('Chưa chọn người thu');
        $grand = array_sum($dayTotal);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('display_order')->pluck('name', 'id');
        return view('report.thu-tien-nguoi', compact('f', 'days', 'sum', 'dayTotal', 'billCount', 'branchSum', 'persons', 'grand', 'branches'));
    }
}
