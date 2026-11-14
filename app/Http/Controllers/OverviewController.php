<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Perm;
use App\Services\BillAnomalyService;
use App\Services\PurchaseAlertService;
use App\Services\PartnerDebtService;
use Auth, DB;

/**
 * Trang tổng quan. Tài khoản quyền cũ: role 1; tài khoản có nhóm quyền: quyền report.view
 * (giới hạn chi nhánh theo phân quyền).
 */
class OverviewController extends Controller
{
    const INVEST_CATE_IDS = [4, 75];
    const VALID_BILL_STATUS = [1, 2];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || (!Perm::active() && Auth::user()->role != 1)) abort(403, __('Không có quyền'));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $allowed = Perm::branchIds();                     // null = tất cả
        $branchId = $request->branch_id ? (int) $request->branch_id : null;
        $branchList = \App\Models\Branch::where('status', 1)->orderBy('display_order')->get();
        $scope = function ($q, $col = 'branch_id') use ($allowed, $branchId) {
            if ($branchId) $q->where($col, $branchId);
            elseif ($allowed !== null) $q->whereIn($col, $allowed ?: [0]);
            return $q;
        };

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $monthStart = date('Y-m-01');
        $dayOfMonth = (int) date('j');
        $prevMonthStart = date('Y-m-01', strtotime('first day of last month'));
        $prevMonthSameDay = date('Y-m-d', min(strtotime($prevMonthStart . ' +' . ($dayOfMonth - 1) . ' days'), strtotime(date('Y-m-t', strtotime($prevMonthStart)))));

        $rev = function ($from, $to) use ($scope) {
            $r = $scope(DB::table('booking'))->whereIn('status', self::VALID_BILL_STATUS)
                ->whereBetween('use_date', [$from, $to])
                ->select(DB::raw('COUNT(*) bills'), DB::raw('COALESCE(SUM(total_price - discount),0) net'))->first();
            return ['bills' => (int) $r->bills, 'net' => (float) $r->net, 'avg' => $r->bills ? $r->net / $r->bills : 0];
        };
        $kpi = [
            'today' => $rev($today, $today),
            'yesterday' => $rev($yesterday, $yesterday),
            'same_day_last_week' => $rev(date('Y-m-d', strtotime('-8 days')), date('Y-m-d', strtotime('-8 days'))),
            'month' => $rev($monthStart, $today),
            'prev_month_same' => $rev($prevMonthStart, $prevMonthSameDay),
        ];

        // Doanh thu 30 ngày theo chi nhánh
        $from30 = date('Y-m-d', strtotime('-29 days'));
        $days = [];
        for ($i = 29; $i >= 0; $i--) $days[] = date('Y-m-d', strtotime("-$i days"));
        $daily = $scope(DB::table('booking'))->whereIn('status', self::VALID_BILL_STATUS)->whereBetween('use_date', [$from30, $today])
            ->groupBy('use_date', 'branch_id')->select('use_date', 'branch_id', DB::raw('SUM(total_price - discount) net'))->get();
        $bName = $branchList->pluck('name', 'id');
        $series = [];
        foreach ($daily as $d) {
            $n = $bName[$d->branch_id] ?? ('#' . $d->branch_id);
            if (!isset($series[$n])) $series[$n] = array_fill_keys($days, 0);
            $series[$n][$d->use_date] = (float) $d->net;
        }
        $chart = ['days' => array_map(function ($d) { return date('d/m', strtotime($d)); }, $days), 'series' => []];
        foreach ($series as $n => $vals) $chart['series'][] = ['name' => $n, 'data' => array_values($vals)];

        // Chi phí tháng này
        $costRows = $scope(DB::table('cost as c'), 'c.branch_id')->leftJoin('cost_type as t', 't.id', '=', 'c.cate_id')
            ->where('c.status', '>', 0)->whereBetween('c.date_use', [$monthStart, $today])
            ->groupBy('c.cate_id', 't.name', 't.group_name')
            ->select('c.cate_id', 't.name', 't.group_name', DB::raw('SUM(c.total_money) money'))->get();
        $costTotal = 0; $food = 0; $labor = 0; $invest = 0;
        foreach ($costRows as $r) {
            if (in_array((int) $r->cate_id, self::INVEST_CATE_IDS)) { $invest += $r->money; continue; }
            $costTotal += $r->money;
            if (mb_strpos((string) $r->group_name, 'Giá vốn') === 0) $food += $r->money;
            if ($r->group_name === 'Nhân sự') $labor += $r->money;
        }
        $monthNet = $kpi['month']['net'];
        $costKpi = ['total' => $costTotal, 'invest' => $invest, 'food' => $food, 'labor' => $labor,
            'food_pct' => $monthNet ? $food / $monthNet * 100 : null, 'labor_pct' => $monthNet ? $labor / $monthNet * 100 : null,
            'cost_pct' => $monthNet ? $costTotal / $monthNet * 100 : null];
        $topCosts = $scope(DB::table('cost as c'), 'c.branch_id')->leftJoin('cost_type as t', 't.id', '=', 'c.cate_id')
            ->where('c.status', '>', 0)->whereBetween('c.date_use', [$monthStart, $today])
            ->whereNotIn('c.cate_id', self::INVEST_CATE_IDS)
            ->orderBy('c.total_money', 'desc')->limit(5)
            ->get(['c.id', 'c.date_use', 'c.total_money', 'c.content', 'c.notes', 't.name as cate_name', 'c.branch_id']);

        // Top món tuần này
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $topDishes = $scope(DB::table('booking_detail as d')->join('booking as b', 'b.id', '=', 'd.booking_id')
                ->join('dish as m', 'm.id', '=', 'd.dish_id'), 'b.branch_id')
            ->whereIn('b.status', self::VALID_BILL_STATUS)->whereBetween('b.use_date', [$weekStart, $today])
            ->groupBy(DB::raw('TRIM(m.name)'))
            ->select(DB::raw('TRIM(m.name) name'), DB::raw('SUM(d.amount) qty'), DB::raw('SUM(d.total_price) money'))
            ->orderBy('qty', 'desc')->limit(5)->get();

        // Cần chú ý
        $branchArr = $branchId ? [$branchId] : $allowed;
        $attention = [];
        if (Perm::can('bill_alert.view')) {
            try { $attention['anomaly'] = BillAnomalyService::countUnreviewed(7, $allowed, $branchId); } catch (\Throwable $e) {}
        }
        if (Perm::can('purchase.view')) {
            try { $attention['purchase'] = count(PurchaseAlertService::compute(['period' => 'month', 'branch_ids' => $branchArr ?: []])['rows']); } catch (\Throwable $e) {}
        }
        if (Perm::can('partner.view')) {
            try {
                $overdue = 0; $ids = DB::table('partners')->where('doi_tac', 1)->pluck('pay_term_days', 'id');
                foreach (PartnerDebtService::balances($ids->keys()->all()) as $pid => $b) {
                    if ($b['phai_thu'] > 0) $overdue += PartnerDebtService::aging($pid, $ids[$pid])['buckets']['qua_han'];
                }
                $attention['overdue'] = $overdue;
            } catch (\Throwable $e) {}
        }
        if (Perm::can('bill_upload.view')) {
            $dir = config('plantotravel.upload_path') . 'bill-inbox/';
            $attention['inbox'] = is_dir($dir) ? count(preg_grep('/^[\w\-]+\.(jpg|png|webp)$/', scandir($dir))) : 0;
        }

        return view('overview.index', compact('kpi', 'chart', 'costKpi', 'topCosts', 'topDishes', 'attention', 'branchList', 'branchId', 'bName'));
    }
}
