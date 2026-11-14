<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\AppSetting;
use App\Services\Perm;
use Auth, DB, Session;

/**
 * Báo cáo VAT & hoa hồng đối tác (14/11/2026)
 * - VAT = (tổng bill − giảm giá) × thuế suất (config bombay.vat_rate), bill tick "KHÔNG VAT" = 0.
 * - Hoa hồng = booking.com_amount (tính tự động khi lưu bill có đối tác + tick "Có chiết khấu").
 * - Chi nhánh trong config bombay.no_vat_branches (Vui Fest, bill đồng bộ từ Sapo) không tính VAT.
 * - Bill lẻ khác không VAT: đánh dấu hàng loạt "KHÔNG VAT" tại trang này.
 */
class VatReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || (!Perm::active() && Auth::user()->role != 1)) abort(403, __('Không có quyền'));
            return $next($request);
        });
    }

    protected function filters(Request $r)
    {
        $ymd = function ($v, $def) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? $v : $def; };
        return [
            'from' => $ymd($r->from, date('Y-m-01')),
            'to' => $ymd($r->to, date('Y-m-d')),
            'branch_id' => $r->branch_id ? (int) $r->branch_id : null,
            'vat' => in_array($r->vat, ['1', '0'], true) ? $r->vat : '',
            'doi_tac' => $r->doi_tac === null || $r->doi_tac === '' ? '' : (int) $r->doi_tac, // 0 = khách lẻ
            'com' => in_array($r->com, ['1', '0'], true) ? $r->com : '',
            'q' => trim((string) $r->q),
        ];
    }

    protected function query(array $f)
    {
        $q = DB::table('booking as b')->whereBetween('b.use_date', [$f['from'], $f['to']])->whereNotIn('b.status', [0, 3]);
        $ids = Perm::branchIds();
        if ($ids !== null) $q->whereIn('b.branch_id', $ids ?: [0]);
        if ($f['branch_id']) $q->where('b.branch_id', $f['branch_id']);
        $nvb = AppSetting::noVatBranches() ?: [0];
        if ($f['vat'] === '1') $q->where(function ($w) { $w->whereNull('b.no_vat')->orWhere('b.no_vat', 0); })->whereNotIn('b.branch_id', $nvb);
        if ($f['vat'] === '0') $q->where(function ($w) use ($nvb) { $w->where('b.no_vat', 1)->orWhereIn('b.branch_id', $nvb); });
        if ($f['doi_tac'] === 0) $q->where(function ($w) { $w->whereNull('b.doi_tac_id')->orWhere('b.doi_tac_id', 0); });
        elseif ($f['doi_tac'] !== '') $q->where('b.doi_tac_id', $f['doi_tac']);
        if ($f['com'] === '1') $q->where('b.com_amount', '>', 0);
        if ($f['com'] === '0') $q->where(function ($w) { $w->whereNull('b.com_amount')->orWhere('b.com_amount', 0); });
        if ($f['q'] !== '') {
            $kw = $f['q'];
            $q->where(function ($w) use ($kw) {
                $w->where('b.notes', 'like', '%' . $kw . '%')->orWhere('b.table_no', $kw)->orWhere('b.bill_no', $kw);
                if (ctype_digit($kw)) $w->orWhere('b.id', (int) $kw);
            });
        }
        return $q;
    }

    protected function rows(array $f)
    {
        $rows = $this->query($f)->orderBy('b.use_date')->orderBy('b.id')
            ->get(['b.id', 'b.use_date', 'b.branch_id', 'b.table_no', 'b.bill_no', 'b.total_price', 'b.discount', 'b.no_vat',
                'b.doi_tac_id', 'b.co_chiet_khau', 'b.bill_type', 'b.pax', 'b.com_amount', 'b.com_pct', 'b.notes', 'b.status']);
        foreach ($rows as $r) {
            $r->no_vat = AppSetting::isNoVat($r->no_vat, $r->branch_id) ? 1 : 0; // chi nhánh không VAT (Vui Fest) tính như tick KHÔNG VAT
            $r->net = (int) $r->total_price - (int) $r->discount;
            $r->vat = AppSetting::vatOf($r->total_price, $r->discount, $r->no_vat);
            $r->pay = $r->net + $r->vat;
            $r->com = (int) $r->com_amount;
        }
        return $rows;
    }

    public function index(Request $request)
    {
        $f = $this->filters($request);
        $rows = $this->rows($f);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('display_order')->pluck('name', 'id');
        $partners = DB::table('partners')->where('doi_tac', 1)->orderBy('name')->pluck('name', 'id');
        if ($request->export) return $this->export($rows, $branches, $partners, $f);

        $sum = function ($list) {
            $s = ['n' => 0, 'n_vat' => 0, 'n_novat' => 0, 'total' => 0, 'discount' => 0, 'net' => 0, 'net_vat' => 0, 'net_novat' => 0, 'vat' => 0, 'pay' => 0, 'com' => 0, 'n_com' => 0];
            foreach ($list as $r) {
                $s['n']++; $s['total'] += $r->total_price; $s['discount'] += $r->discount; $s['net'] += $r->net;
                $s['vat'] += $r->vat; $s['pay'] += $r->pay; $s['com'] += $r->com;
                if ($r->com > 0) $s['n_com']++;
                if ($r->no_vat) { $s['n_novat']++; $s['net_novat'] += $r->net; } else { $s['n_vat']++; $s['net_vat'] += $r->net; }
            }
            return $s;
        };
        $total = $sum($rows);
        $byBranch = []; foreach ($rows->groupBy('branch_id') as $bid => $list) $byBranch[$bid] = $sum($list);
        $byDay = []; foreach ($rows->groupBy('use_date') as $d => $list) $byDay[$d] = $sum($list);
        $byPartner = []; foreach ($rows->filter(function ($r) { return $r->doi_tac_id; })->groupBy('doi_tac_id') as $pid => $list) $byPartner[$pid] = $sum($list);
        arsort($byPartner);
        $canEdit = Auth::user()->id == 1 || (Perm::active() ? Perm::can('bill.edit') : Auth::user()->role == 1);
        $vatRate = AppSetting::vatRate();
        $view = in_array($request->view, ['day', 'detail'], true) ? $request->view : 'detail';
        return view('report.vat', compact('f', 'rows', 'total', 'byBranch', 'byDay', 'byPartner', 'branches', 'partners', 'canEdit', 'vatRate', 'view'));
    }

    protected function export($rows, $branches, $partners, $f)
    {
        $name = 'vat-hoa-hong_' . $f['from'] . '_' . $f['to'] . '.csv';
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [__('Mã bill'), __('Ngày'), __('Chi nhánh'), __('Bàn'), __('Đối tác'), __('Loại bill'), __('Số khách'),
            __('Tổng tiền'), __('Giảm giá'), __('Chưa VAT'), 'VAT', __('Tổng thanh toán'), __('KHÔNG VAT'), __('% hoa hồng'), __('Hoa hồng'), __('Ghi chú')]);
        foreach ($rows as $r) {
            fputcsv($out, [$r->id, date('d/m/Y', strtotime($r->use_date)), $branches[$r->branch_id] ?? $r->branch_id, $r->table_no,
                $r->doi_tac_id ? ($partners[$r->doi_tac_id] ?? $r->doi_tac_id) : __('Khách lẻ'),
                $r->bill_type == 1 ? 'Buffet' : ($r->bill_type == 2 ? 'Alacarte' : ''), $r->pax,
                (int) $r->total_price, (int) $r->discount, $r->net, $r->vat, $r->pay, $r->no_vat ? 'x' : '',
                $r->com_pct !== null ? (float) $r->com_pct : '', $r->com, $r->notes]);
        }
        rewind($out); $csv = stream_get_contents($out); fclose($out);
        return new \Illuminate\Http\Response($csv, 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $name . '"']);
    }

    /** Đánh dấu / bỏ đánh dấu "KHÔNG VAT" hàng loạt (bill đồng bộ từ Sapo...) */
    public function setNoVat(Request $request)
    {
        $canEdit = Auth::user()->id == 1 || (Perm::active() ? Perm::can('bill.edit') : Auth::user()->role == 1);
        if (!$canEdit) abort(403, __('Không có quyền'));
        $ids = array_slice(array_values(array_filter(array_map('intval', (array) $request->input('ids', [])))), 0, 1000);
        $val = $request->value ? 1 : 0;
        $n = 0;
        if ($ids) {
            $q = Booking::whereIn('id', $ids);
            $bIds = Perm::branchIds();
            if ($bIds !== null) $q->whereIn('branch_id', $bIds ?: [0]);
            foreach ($q->get() as $b) { // từng bill để ghi lịch sử thao tác
                if ((int) $b->no_vat === $val) continue;
                $b->no_vat = $val; $b->save(); $n++;
            }
        }
        Session::flash('message', ($val ? __('Đã đánh dấu KHÔNG VAT') : __('Đã bỏ đánh dấu KHÔNG VAT')) . ': ' . $n . ' ' . __('bill'));
        return redirect()->back();
    }
}
