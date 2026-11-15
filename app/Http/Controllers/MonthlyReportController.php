<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AppSetting;
use App\Services\Perm;
use Auth, DB;

/**
 * Thống kê doanh thu – chi phí – lợi nhuận (làm lại 14/11/2026, thêm xem theo ngày / tháng / khoảng ngày 15/11/2026)
 * - Doanh thu = tổng bill − giảm giá (chưa VAT), bill trạng thái 1, 2, 4 (khớp danh sách bill).
 * - Chi phí = cost status > 0, city_id <> 3 (như cũ); hoa hồng đối tác đã chi nằm trong chi phí.
 * - Lọc chi nhánh + áp quyền chi nhánh. Xem theo tháng trước 06/2024 giữ trang cũ (ReportController@doanhthuthang).
 * - Khoảng ≤ 62 ngày: bảng theo từng ngày; dài hơn: gom theo tháng.
 */
class MonthlyReportController extends Controller
{
    const STATUSES = [1, 2, 4];
    const MAX_DAYS = 400;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || (!Perm::active() && Auth::user()->role >= 3)) abort(403, __('Không có quyền'));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $ymd = function ($v) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) && strtotime($v) ? $v : null; };
        $mode = in_array($request->mode, ['day', 'month', 'range'], true) ? $request->mode : 'month';
        $today = date('Y-m-d');

        $month = (int) ($request->month ?: date('m'));
        $year = (int) ($request->year ?: date('Y'));
        if ($month < 1 || $month > 12) $month = (int) date('m');
        if ($year < 2020 || $year > (int) date('Y') + 1) $year = (int) date('Y');

        if ($mode == 'day') {
            $from = $to = $ymd($request->date) ?: $today;
        } elseif ($mode == 'range') {
            $from = $ymd($request->from) ?: date('Y-m-01');
            $to = $ymd($request->to) ?: $today;
            if ($from > $to) { $t = $from; $from = $to; $to = $t; }
            if ((strtotime($to) - strtotime($from)) / 86400 >= self::MAX_DAYS) $from = date('Y-m-d', strtotime($to . ' -' . (self::MAX_DAYS - 1) . ' days'));
        } else {
            if ($year < 2024 || ($year == 2024 && $month <= 5)) { // dữ liệu cũ: giữ cách tính cũ
                $request->merge(['month' => str_pad($month, 2, '0', STR_PAD_LEFT), 'year' => $year]);
                return app(ReportController::class)->doanhthuthang($request);
            }
            $from = sprintf('%04d-%02d-01', $year, $month);
            $to = date('Y-m-t', strtotime($from));
        }
        $month = (int) date('m', strtotime($from)); $year = (int) date('Y', strtotime($from));

        $branchId = $request->branch_id ? (int) $request->branch_id : null;
        $allowed = Perm::branchIds();
        if ($allowed !== null && $branchId && !in_array($branchId, $allowed)) $branchId = null;

        $data = $this->collect($from, $to, $branchId, $allowed);

        // kỳ so sánh: tháng trước (cùng số ngày nếu đang xem tháng hiện tại) / ngày trước / khoảng liền trước cùng độ dài
        if ($mode == 'month') {
            $pFrom = date('Y-m-01', strtotime($from . ' -1 month'));
            $pTo = date('Y-m-t', strtotime($pFrom));
            if ($to > $today && $from <= $today) $pTo = min($pTo, date('Y-m-d', strtotime($pFrom . ' +' . ((int) date('d') - 1) . ' days')));
        } else {
            $len = (int) round((strtotime($to) - strtotime($from)) / 86400) + 1;
            $pTo = date('Y-m-d', strtotime($from . ' -1 day'));
            $pFrom = date('Y-m-d', strtotime($pTo . ' -' . ($len - 1) . ' days'));
        }
        $prev = $this->collect($pFrom, $pTo, $branchId, $allowed)['total'];

        // nhóm dòng: theo ngày (≤ 62 ngày) hoặc theo tháng
        $groupBy = (strtotime($to) - strtotime($from)) / 86400 < 62 ? 'day' : 'month';
        $rows = $data['days'];
        if ($groupBy == 'month') {
            $rows = [];
            foreach ($data['days'] as $d => $r) {
                $k = substr($d, 0, 7);
                if (!isset($rows[$k])) $rows[$k] = array_fill_keys(array_keys($r), 0);
                foreach ($r as $f => $v) $rows[$k][$f] += $v;
            }
        }

        $branches = \App\Models\Branch::where('status', 1)->orderBy('display_order')->pluck('name', 'id');
        $cateNames = DB::table('cost_type')->pluck('name', 'id');
        $years = range((int) date('Y'), 2023);
        return view('report.thong-ke-thang', $data + compact('mode', 'month', 'year', 'from', 'to', 'branchId', 'branches', 'cateNames', 'prev', 'pFrom', 'pTo', 'years', 'groupBy', 'rows'));
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
        $byBranch = [];
        foreach ($bq->get(['use_date', 'branch_id', 'total_price', 'discount', 'no_vat']) as $b) {
            $net = $b->total_price - $b->discount;
            $vat = AppSetting::vatOf($b->total_price, $b->discount, $b->no_vat, $b->branch_id);
            $r = &$days[$b->use_date];
            $r['n']++; $r['gross'] += $b->total_price; $r['discount'] += $b->discount; $r['revenue'] += $net; $r['vat'] += $vat;
            unset($r);
            if (!isset($byBranch[$b->branch_id])) $byBranch[$b->branch_id] = $blank;
            $byBranch[$b->branch_id]['n']++; $byBranch[$b->branch_id]['revenue'] += $net; $byBranch[$b->branch_id]['discount'] += $b->discount; $byBranch[$b->branch_id]['vat'] += $vat;
        }
        $cates = []; $cateDays = [];
        foreach ($cq->get(['date_use', 'branch_id', 'cate_id', 'type', 'total_money']) as $c) {
            $m = (int) $c->total_money;
            $r = &$days[$c->date_use];
            $r['cost'] += $m;
            if ($c->type == 1) $r['cost_vh'] += $m; else $r['cost_khac'] += $m;
            unset($r);
            if (!isset($byBranch[$c->branch_id])) $byBranch[$c->branch_id] = $blank;
            $byBranch[$c->branch_id]['cost'] += $m;
            $cates[$c->cate_id] = ($cates[$c->cate_id] ?? 0) + $m;
            $cateDays[$c->cate_id][$c->date_use] = ($cateDays[$c->cate_id][$c->date_use] ?? 0) + $m;
        }
        arsort($cates);
        foreach ($byBranch as &$r) $r['profit'] = $r['revenue'] - $r['cost'];
        unset($r);
        ksort($byBranch);
        $total = $blank;
        foreach ($days as $d => &$r) {
            $r['profit'] = $r['revenue'] - $r['cost'];
            foreach ($total as $k => $v) $total[$k] += $r[$k];
        }
        unset($r);
        return compact('days', 'total', 'cates', 'cateDays', 'byBranch');
    }
}
