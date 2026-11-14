<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AppSetting;
use App\Services\Perm;
use Auth, DB;

/**
 * Thống kê tháng: doanh thu – chi phí – lợi nhuận theo ngày (làm lại 14/11/2026)
 * - Doanh thu = tổng bill − giảm giá (chưa VAT), bill trạng thái 1, 2, 4 (khớp danh sách bill).
 * - Chi phí = cost status > 0, city_id <> 3 (như cũ); hoa hồng đối tác đã chi nằm trong chi phí.
 * - Lọc chi nhánh + áp quyền chi nhánh. Tháng trước 06/2024 giữ trang cũ (ReportController@doanhthuthang).
 */
class MonthlyReportController extends Controller
{
    const STATUSES = [1, 2, 4];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || (!Perm::active() && Auth::user()->role >= 3)) abort(403, __('Không có quyền'));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $month = (int) ($request->month ?: date('m'));
        $year = (int) ($request->year ?: date('Y'));
        if ($month < 1 || $month > 12) $month = (int) date('m');
        if ($year < 2020 || $year > (int) date('Y') + 1) $year = (int) date('Y');
        if ($year < 2024 || ($year == 2024 && $month <= 5)) { // dữ liệu cũ: giữ cách tính cũ
            $request->merge(['month' => str_pad($month, 2, '0', STR_PAD_LEFT), 'year' => $year]);
            return app(ReportController::class)->doanhthuthang($request);
        }
        $branchId = $request->branch_id ? (int) $request->branch_id : null;
        $allowed = Perm::branchIds();
        if ($allowed !== null && $branchId && !in_array($branchId, $allowed)) $branchId = null;

        $from = sprintf('%04d-%02d-01', $year, $month);
        $to = date('Y-m-t', strtotime($from));
        $data = $this->collect($from, $to, $branchId, $allowed);

        $pFrom = date('Y-m-01', strtotime($from . ' -1 month'));
        $pTo = date('Y-m-t', strtotime($pFrom));
        // so sánh cùng số ngày nếu đang xem tháng hiện tại
        if ($to > date('Y-m-d') && $from <= date('Y-m-d')) {
            $pTo = min($pTo, date('Y-m-d', strtotime($pFrom . ' +' . ((int) date('d') - 1) . ' days')));
        }
        $prev = $this->collect($pFrom, $pTo, $branchId, $allowed)['total'];

        $branches = \App\Models\Branch::where('status', 1)->orderBy('display_order')->pluck('name', 'id');
        $cateNames = DB::table('cost_type')->pluck('name', 'id');
        $years = range((int) date('Y'), 2023);
        return view('report.thong-ke-thang', $data + compact('month', 'year', 'from', 'to', 'branchId', 'branches', 'cateNames', 'prev', 'pFrom', 'pTo', 'years'));
    }

    protected function collect($from, $to, $branchId, $allowed)
    {
        $bq = DB::table('booking')->where('city_id', 1)->whereIn('status', self::STATUSES)->whereBetween('use_date', [$from, $to]);
        $cq = DB::table('cost')->where('status', '>', 0)->where('city_id', '<>', 3)->whereBetween('date_use', [$from, $to]);
        if ($allowed !== null) { $bq->whereIn('branch_id', $allowed ?: [0]); $cq->whereIn('branch_id', $allowed ?: [0]); }
        if ($branchId) { $bq->where('branch_id', $branchId); $cq->where('branch_id', $branchId); }

        $days = [];
        $blank = ['n' => 0, 'gross' => 0, 'discount' => 0, 'revenue' => 0, 'vat' => 0, 'cost' => 0, 'cost_vh' => 0, 'cost_khac' => 0, 'profit' => 0];
        for ($d = $from; $d <= $to; $d = date('Y-m-d', strtotime($d . ' +1 day'))) $days[$d] = $blank;
        foreach ($bq->get(['use_date', 'branch_id', 'total_price', 'discount', 'no_vat']) as $b) {
            $r = &$days[$b->use_date];
            $r['n']++; $r['gross'] += $b->total_price; $r['discount'] += $b->discount;
            $r['revenue'] += $b->total_price - $b->discount;
            $r['vat'] += AppSetting::vatOf($b->total_price, $b->discount, $b->no_vat, $b->branch_id);
            unset($r);
        }
        $cates = []; $cateDays = [];
        foreach ($cq->get(['date_use', 'cate_id', 'type', 'total_money']) as $c) {
            $r = &$days[$c->date_use];
            $m = (int) $c->total_money;
            $r['cost'] += $m;
            if ($c->type == 1) $r['cost_vh'] += $m; else $r['cost_khac'] += $m;
            unset($r);
            $cates[$c->cate_id] = ($cates[$c->cate_id] ?? 0) + $m;
            $cateDays[$c->cate_id][$c->date_use] = ($cateDays[$c->cate_id][$c->date_use] ?? 0) + $m;
        }
        arsort($cates);
        $total = $blank;
        foreach ($days as $d => &$r) {
            $r['profit'] = $r['revenue'] - $r['cost'];
            foreach ($total as $k => $v) $total[$k] += $r[$k];
        }
        unset($r);
        return compact('days', 'total', 'cates', 'cateDays');
    }
}
