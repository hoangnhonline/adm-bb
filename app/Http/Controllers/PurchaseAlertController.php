<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\CostType;
use App\Models\NhaCungCap;
use App\Services\PurchaseAlertService;

/** Cảnh báo nguyên liệu nhập tăng bất thường so với kỳ trước / TB 3 kỳ trước. */
class PurchaseAlertController extends Controller
{
    public function index(Request $request)
    {
        $ymd = function ($v) {
            if (!$v) return null;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
            $t = explode('/', $v);
            return count($t) == 3 && checkdate((int) $t[1], (int) $t[0], (int) $t[2]) ? sprintf('%04d-%02d-%02d', $t[2], $t[1], $t[0]) : null;
        };
        $submitted = $request->has('f');
        $params = [
            'period' => in_array($request->period, ['week', 'month', 'year', 'custom']) ? $request->period : 'month',
            'date' => $ymd($request->date),
            'from' => $ymd($request->from),
            'to' => $ymd($request->to),
            'compare' => $request->compare == 'avg3' ? 'avg3' : 'prev',
            'same_days' => $submitted ? ($request->same_days ? 1 : 0) : 1,
            'pct' => $request->pct !== null && $request->pct !== '' ? max(1, (int) $request->pct) : 30,
            'min_amount' => $request->min_amount !== null && $request->min_amount !== '' ? max(0, (int) str_replace([',', '.'], '', $request->min_amount)) : 300000,
            'basis' => in_array($request->basis, ['qty', 'amount', 'any']) ? $request->basis : 'any',
            'branch_ids' => $request->branch_ids ? array_map('intval', (array) $request->branch_ids) : [],
            'cate_id' => $request->cate_id ?: null,
            'ncc_id' => $request->ncc_id ?: null,
            'include_review' => $request->include_review ? 1 : 0,
            'include_new' => $submitted ? ($request->include_new ? 1 : 0) : 1,
        ];
        $res = PurchaseAlertService::compute($params);
        $p = $res['params'];

        // điều hướng kỳ trước / kỳ sau
        $prevAnchor = $nextAnchor = null;
        if ($p['period'] != 'custom') {
            $prevAnchor = PurchaseAlertService::periodRange($p['period'], $res['from'], 1)[0];
            $nextAnchor = date('Y-m-d', strtotime(PurchaseAlertService::periodRange($p['period'], $res['from'], 0)[1] . ' +1 day'));
        }
        $labels = ['week' => __('Tuần'), 'month' => __('Tháng'), 'year' => __('Năm'), 'custom' => __('Tùy chọn')];

        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $cateList = CostType::where('status', 1)->orderBy('display_order')->get();
        $cateArr = $cateList->pluck('name', 'id');
        $nccList = NhaCungCap::where('status', 1)->orderBy('display_order')->orderBy('name')->get();
        return view('cost.canh-bao-nhap', compact('res', 'p', 'prevAnchor', 'nextAnchor', 'labels', 'branchList', 'cateList', 'cateArr', 'nccList'));
    }
}
