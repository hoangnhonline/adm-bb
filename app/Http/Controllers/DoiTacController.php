<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Cost;
use App\Models\Branch;
use App\Services\PartnerDebtService;
use Session, Auth, DB;

/**
 * Quản lý đối tác bán hàng (công ty tour, khách sạn, HDV, Plan To Travel, cty cano)
 * + công nợ phải thu (bill ghi nợ) / phải trả (hoa hồng) + bù trừ.
 * Dùng chung bảng partners (cờ doi_tac = 1). Không ảnh hưởng NCC chi phí cũ.
 */
class DoiTacController extends Controller
{
    const COST_TYPE_NAME = 'Hoa hồng đối tác';

    public function __construct()
    {
        // Chỉ quản trị (role 1) xem/sửa công nợ đối tác. Nhân viên chỉ chọn đối tác trên bill.
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role != 1) abort(403, 'Không có quyền');
            return $next($request);
        });
    }

    // ---------------------------------------------------------------- danh sách
    public function index(Request $request)
    {
        $type = $request->type ?? null;
        $name = $request->name ?? null;
        $query = Partner::where('doi_tac', 1)->where('status', 1);
        if ($type) $query->where('type', $type);
        if ($name) $query->where('name', 'LIKE', '%' . $name . '%');
        $items = $query->orderBy('type')->orderBy('name')->get();
        $balances = PartnerDebtService::balances($items->pluck('id')->all());

        $tong = ['phai_thu' => 0, 'phai_tra' => 0, 'rong' => 0];
        foreach ($balances as $b) {
            foreach ($tong as $k => $v) $tong[$k] += $b[$k];
        }
        $types = Partner::$doiTacTypes;
        return view('doi-tac.index', compact('items', 'balances', 'tong', 'types', 'type', 'name'));
    }

    // ---------------------------------------------------------------- thêm / sửa
    public function create()
    {
        $detail = new Partner(['com_buffet_per_pax' => 20000, 'com_alacarte_pct' => 10, 'pay_term_days' => 0, 'type' => 'cano']);
        return view('doi-tac.form', ['detail' => $detail, 'types' => Partner::$doiTacTypes,
            'branchList' => Branch::where('status', 1)->orderBy('display_order')->get()]);
    }

    public function edit($id)
    {
        $detail = Partner::where('doi_tac', 1)->findOrFail($id);
        return view('doi-tac.form', ['detail' => $detail, 'types' => Partner::$doiTacTypes,
            'branchList' => Branch::where('status', 1)->orderBy('display_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['doi_tac'] = 1;
        $data['status'] = 1;
        $data['cost_type_id'] = null;
        $p = Partner::create($data);
        Session::flash('message', 'Đã thêm đối tác ' . $p->name);
        return redirect()->route('doi-tac.index');
    }

    public function update(Request $request)
    {
        $p = Partner::where('doi_tac', 1)->findOrFail($request->id);
        $data = $this->validated($request);
        $rateChanged = (int) $p->com_buffet_per_pax != (int) $data['com_buffet_per_pax']
            || (float) $p->com_alacarte_pct != (float) $data['com_alacarte_pct'];
        $p->update($data);
        $msg = 'Cập nhật thành công';
        if ($rateChanged && $request->recalc_old) {
            $n = PartnerDebtService::recalcPartner($p->id);
            $msg .= " – đã tính lại hoa hồng $n bill cũ theo mức mới";
        }
        Session::flash('message', $msg);
        return redirect()->route('doi-tac.show', $p->id);
    }

    public function destroy($id)
    {
        $p = Partner::where('doi_tac', 1)->findOrFail($id);
        $p->update(['status' => 0]); // ẩn, không xóa dữ liệu
        Session::flash('message', 'Đã ẩn đối tác ' . $p->name);
        return redirect()->route('doi-tac.index');
    }

    private function validated(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Partner::$doiTacTypes)),
        ], [
            'name.required' => 'Bạn chưa nhập tên đối tác',
            'type.required' => 'Bạn chưa chọn loại đối tác',
        ]);
        $n = function ($v) { return (int) str_replace([',', '.'], '', (string) $v); };
        return [
            'name' => trim($request->name),
            'type' => $request->type,
            'contact_name' => $request->contact_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'tax_code' => $request->tax_code,
            'address' => $request->address,
            'bank_info' => $request->bank_info,
            'description' => $request->description,
            'pay_term_days' => max(0, (int) $request->pay_term_days),
            'com_buffet_per_pax' => max(0, $n($request->com_buffet_per_pax)),
            'com_alacarte_pct' => max(0, min(100, (float) str_replace(',', '.', (string) $request->com_alacarte_pct))),
            'branch_id' => $request->branch_id ?: null,
        ];
    }

    // ---------------------------------------------------------------- sổ công nợ 1 đối tác
    public function show($id, Request $request)
    {
        $detail = Partner::where('doi_tac', 1)->findOrFail($id);
        list($from, $to, $fromStr, $toStr) = $this->range($request, 'month');

        $bal = PartnerDebtService::balances([$id])[$id];
        $aging = PartnerDebtService::aging($id, $detail->pay_term_days);

        $bills = PartnerDebtService::billQuery($id, $from, $to)
            ->orderBy('use_date', 'desc')->orderBy('id', 'desc')
            ->get(['id', 'use_date', 'table_no', 'bill_no', 'branch_id', 'total_price', 'discount',
                'bill_type', 'pax', 'co_chiet_khau', 'ghi_no', 'com_amount', 'status']);
        $ky = ['so_bill' => $bills->count(), 'doanh_thu' => 0, 'pax' => 0, 'no' => 0, 'hh' => 0];
        foreach ($bills as $b) {
            $net = $b->total_price - $b->discount;
            $ky['doanh_thu'] += $net;
            $ky['pax'] += $b->bill_type == 1 ? $b->pax : 0;
            $ky['no'] += $b->ghi_no ? $net : 0;
            $ky['hh'] += $b->com_amount;
        }
        $trans = PartnerTransaction::where('partner_id', $id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        $branchArr = Branch::pluck('name', 'id');
        $types = Partner::$doiTacTypes;
        $transTypes = PartnerTransaction::$typeNames;
        return view('doi-tac.show', compact('detail', 'bal', 'aging', 'bills', 'ky', 'trans', 'fromStr', 'toStr',
            'branchArr', 'types', 'transTypes'));
    }

    public function storeTransaction($id, Request $request)
    {
        $p = Partner::where('doi_tac', 1)->findOrFail($id);
        $type = (int) $request->type;
        $amount = (int) str_replace([',', '.'], '', (string) $request->amount);
        $this->validate($request, ['type' => 'required|in:1,2,3', 'date' => 'required'],
            ['date.required' => 'Bạn chưa nhập ngày']);
        $date = $this->toYmd($request->date);
        if ($amount <= 0 || !$date) {
            return back()->withInput()->withErrors(['amount' => 'Số tiền / ngày không hợp lệ']);
        }
        $bal = PartnerDebtService::balances([$id])[$id];
        if ($type == 1 && $amount > $bal['phai_thu']) {
            return back()->withInput()->withErrors(['amount' => 'Số tiền thu (' . number_format($amount) . ') lớn hơn số đối tác đang nợ (' . number_format($bal['phai_thu']) . ')']);
        }
        if ($type == 2 && $amount > $bal['phai_tra']) {
            return back()->withInput()->withErrors(['amount' => 'Số tiền chi (' . number_format($amount) . ') lớn hơn hoa hồng còn phải trả (' . number_format($bal['phai_tra']) . ')']);
        }
        if ($type == 3 && $amount > min($bal['phai_thu'], $bal['phai_tra'])) {
            return back()->withInput()->withErrors(['amount' => 'Bù trừ tối đa ' . number_format(max(0, min($bal['phai_thu'], $bal['phai_tra']))) . ' (= số nhỏ hơn giữa phải thu và phải trả)']);
        }

        $userId = Auth::user()->id;
        $branchId = $request->branch_id ?: ($p->branch_id ?: 1);
        DB::transaction(function () use ($p, $type, $amount, $date, $request, $userId, $branchId) {
            $costId = null;
            if (in_array($type, [2, 3])) {
                // Hoa hồng là chi phí: ghi vào bảng chi phí (loại "Hoa hồng đối tác")
                $cateId = DB::table('cost_type')->where('name', self::COST_TYPE_NAME)->value('id');
                if (!$cateId) {
                    throw new \RuntimeException('Chưa có loại chi phí "' . self::COST_TYPE_NAME . '". Hãy chạy file SQL database/sql/2026_11_14_doi_tac_cong_no.sql');
                }
                $label = $type == 2 ? 'Chi hoa hồng' : 'Bù trừ công nợ (không chi tiền)';
                $cost = Cost::create([
                    'content' => $label . ' – ' . $p->name,
                    'date_use' => $date,
                    'cate_id' => $cateId,
                    'branch_id' => $branchId,
                    'partner_id' => $p->id,
                    'nguoi_chi' => $request->pay_method ?: 2,
                    'status' => 2,
                    'total_money' => $amount,
                    'amount' => 1,
                    'price' => $amount,
                    'city_id' => 1,
                    'notes' => mb_substr($label . ' đối tác ' . $p->name . ($request->notes ? ' – ' . $request->notes : ''), 0, 250),
                    'created_user' => $userId,
                    'updated_user' => $userId,
                ]);
                $costId = $cost->id;
            }
            PartnerTransaction::create([
                'partner_id' => $p->id,
                'branch_id' => $branchId,
                'date' => $date,
                'type' => $type,
                'amount' => $amount,
                'pay_method' => $request->pay_method ?: null,
                'cost_id' => $costId,
                'notes' => $request->notes,
                'status' => 1,
                'created_user' => $userId,
                'updated_user' => $userId,
            ]);
        });
        Session::flash('message', 'Đã ghi: ' . PartnerTransaction::$typeNames[$type] . ' ' . number_format($amount) . ' đ');
        return redirect()->route('doi-tac.show', $p->id);
    }

    public function cancelTransaction($tid)
    {
        $t = PartnerTransaction::findOrFail($tid);
        DB::transaction(function () use ($t) {
            $t->update(['status' => 0, 'updated_user' => Auth::user()->id]);
            if ($t->cost_id) {
                Cost::where('id', $t->cost_id)->update(['status' => 0]); // giống xóa chi phí thường
            }
        });
        Session::flash('message', 'Đã hủy giao dịch #' . $t->id);
        return redirect()->route('doi-tac.show', $t->partner_id);
    }

    public function recalc($id)
    {
        $p = Partner::where('doi_tac', 1)->findOrFail($id);
        $n = PartnerDebtService::recalcPartner($id);
        Session::flash('message', "Đã tính lại hoa hồng $n bill của " . $p->name);
        return redirect()->route('doi-tac.show', $id);
    }

    // ---------------------------------------------------------------- xuất đối soát (CSV, mở bằng Excel)
    public function export($id, Request $request)
    {
        $p = Partner::where('doi_tac', 1)->findOrFail($id);
        list($from, $to, $fromStr, $toStr) = $this->range($request, 'month');
        $bills = PartnerDebtService::billQuery($id, $from, $to)->orderBy('use_date')->orderBy('id')->get();
        $trans = PartnerTransaction::where('partner_id', $id)->where('status', 1)
            ->whereBetween('date', [$from, $to])->orderBy('date')->get();

        $rows = [];
        $rows[] = ['ĐỐI SOÁT CÔNG NỢ – ' . $p->name, 'Từ ' . $fromStr . ' đến ' . $toStr];
        $rows[] = [];
        $rows[] = ['Ngày', 'Bill ID', 'Bàn', 'Loại', 'Số khách buffet', 'Tổng bill', 'Giảm giá', 'Thực thu', 'Ghi nợ', 'Hoa hồng'];
        $s = ['net' => 0, 'no' => 0, 'hh' => 0, 'pax' => 0];
        foreach ($bills as $b) {
            $net = $b->total_price - $b->discount;
            $rows[] = [date('d/m/Y', strtotime($b->use_date)), $b->id, $b->table_no, $b->bill_type == 1 ? 'Buffet' : 'Alacarte',
                $b->bill_type == 1 ? $b->pax : '', $b->total_price, $b->discount, $net, $b->ghi_no ? $net : 0, $b->com_amount];
            $s['net'] += $net; $s['no'] += $b->ghi_no ? $net : 0; $s['hh'] += $b->com_amount; $s['pax'] += $b->bill_type == 1 ? $b->pax : 0;
        }
        $rows[] = ['TỔNG', count($bills), '', '', $s['pax'], '', '', $s['net'], $s['no'], $s['hh']];
        $rows[] = [];
        $rows[] = ['Ngày', 'Giao dịch', 'Số tiền', 'Ghi chú'];
        foreach ($trans as $t) {
            $rows[] = [date('d/m/Y', strtotime($t->date)), PartnerTransaction::$typeNames[$t->type], $t->amount, $t->notes];
        }
        $bal = PartnerDebtService::balances([$id], $to)[$id];
        $rows[] = [];
        $rows[] = ['Số dư đến ' . $toStr];
        $rows[] = ['Đối tác còn nợ nhà hàng', $bal['phai_thu']];
        $rows[] = ['Nhà hàng còn nợ hoa hồng', $bal['phai_tra']];
        $rows[] = ['Ròng (+ đối tác nợ / − nhà hàng nợ)', $bal['rong']];

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF");
        foreach ($rows as $r) fputcsv($fh, $r);
        rewind($fh);
        $csv = stream_get_contents($fh);
        $file = 'doi-soat-' . preg_replace('/[^a-z0-9]+/i', '-', \Illuminate\Support\Str::ascii($p->name)) . '-' . str_replace('-', '', $from) . '-' . str_replace('-', '', $to) . '.csv';
        return new \Illuminate\Http\Response($csv, 200, ['Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $file . '"']);
    }

    // ---------------------------------------------------------------- báo cáo tổng hợp
    public function report(Request $request)
    {
        list($from, $to, $fromStr, $toStr, $period, $prevAnchor, $nextAnchor, $label) = $this->range($request, 'month', true);
        $type = $request->type ?? null;
        $q = Partner::where('doi_tac', 1);
        if ($type) $q->where('type', $type);
        $partners = $q->orderBy('name')->get()->keyBy('id');
        $ids = $partners->keys()->all();

        // phát sinh trong kỳ theo đối tác
        $ky = PartnerDebtService::billQuery(null, $from, $to)->whereIn('doi_tac_id', $ids ?: [0])
            ->groupBy('doi_tac_id')
            ->select('doi_tac_id', DB::raw('COUNT(*) so_bill'), DB::raw('SUM(total_price - discount) doanh_thu'),
                DB::raw('SUM(CASE WHEN bill_type = 1 THEN pax ELSE 0 END) pax'),
                DB::raw('SUM(CASE WHEN ghi_no = 1 THEN total_price - discount ELSE 0 END) ghi_no'),
                DB::raw('SUM(com_amount) hh'))
            ->get()->keyBy('doi_tac_id');
        // so sánh khách lẻ trong kỳ
        $khachLe = DB::table('booking')->whereNull('doi_tac_id')->whereNotIn('status', PartnerDebtService::INVALID_STATUS)
            ->whereBetween('use_date', [$from, $to])
            ->select(DB::raw('COUNT(*) so_bill'), DB::raw('SUM(total_price - discount) doanh_thu'))->first();

        $bal = PartnerDebtService::balances($ids);
        $rows = [];
        $tong = ['so_bill' => 0, 'doanh_thu' => 0, 'pax' => 0, 'ghi_no' => 0, 'hh' => 0,
            'phai_thu' => 0, 'phai_tra' => 0, 'rong' => 0, '0_30' => 0, '31_60' => 0, '61_90' => 0, 'tren_90' => 0, 'qua_han' => 0];
        foreach ($partners as $id => $p) {
            $k = $ky->get($id);
            $b = $bal[$id];
            if (!$k && !$b['phai_thu'] && !$b['phai_tra'] && $p->status != 1) continue;
            $ag = $b['phai_thu'] > 0 ? PartnerDebtService::aging($id, $p->pay_term_days)['buckets']
                : ['0_30' => 0, '31_60' => 0, '61_90' => 0, 'tren_90' => 0, 'qua_han' => 0];
            $r = ['p' => $p, 'so_bill' => (int) ($k->so_bill ?? 0), 'doanh_thu' => (int) ($k->doanh_thu ?? 0),
                'pax' => (int) ($k->pax ?? 0), 'ghi_no' => (int) ($k->ghi_no ?? 0), 'hh' => (int) ($k->hh ?? 0),
                'phai_thu' => $b['phai_thu'], 'phai_tra' => $b['phai_tra'], 'rong' => $b['rong']] + $ag;
            foreach ($tong as $f => $v) $tong[$f] += $r[$f];
            $rows[] = $r;
        }
        usort($rows, function ($a, $b) { return $b['doanh_thu'] <=> $a['doanh_thu']; });
        $types = Partner::$doiTacTypes;
        return view('doi-tac.report', compact('rows', 'tong', 'khachLe', 'types', 'type', 'fromStr', 'toStr',
            'period', 'prevAnchor', 'nextAnchor', 'label'));
    }

    // ---------------------------------------------------------------- tiện ích ngày
    private function toYmd($dmy)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string) $dmy, $m)) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $dmy : null;
        }
        $t = explode('/', (string) $dmy);
        if (count($t) != 3 || !checkdate((int) $t[1], (int) $t[0], (int) $t[2])) return null;
        return sprintf('%04d-%02d-%02d', $t[2], $t[1], $t[0]);
    }

    /** period: week|month|year|custom, anchor = date (d/m/Y). */
    private function range(Request $request, $default = 'month', $full = false)
    {
        $period = $request->period ?: $default;
        $anchor = $this->toYmd($request->date) ?: date('Y-m-d');
        $ts = strtotime($anchor);
        if ($period == 'custom' && $this->toYmd($request->from) && $this->toYmd($request->to)) {
            $from = $this->toYmd($request->from);
            $to = $this->toYmd($request->to);
            $prev = $next = $anchor;
            $label = date('d/m/Y', strtotime($from)) . ' – ' . date('d/m/Y', strtotime($to));
        } elseif ($period == 'week') {
            $from = date('Y-m-d', strtotime('monday this week', $ts));
            $to = date('Y-m-d', strtotime($from . ' +6 days'));
            $prev = date('Y-m-d', strtotime($from . ' -7 days'));
            $next = date('Y-m-d', strtotime($from . ' +7 days'));
            $label = 'Tuần ' . date('d/m', strtotime($from)) . ' – ' . date('d/m/Y', strtotime($to));
        } elseif ($period == 'year') {
            $from = date('Y-01-01', $ts);
            $to = date('Y-12-31', $ts);
            $prev = date('Y-01-01', strtotime($from . ' -1 year'));
            $next = date('Y-01-01', strtotime($from . ' +1 year'));
            $label = 'Năm ' . date('Y', $ts);
        } else {
            $period = $period == 'custom' ? 'month' : $period;
            $from = date('Y-m-01', $ts);
            $to = date('Y-m-t', $ts);
            $prev = date('Y-m-d', strtotime($from . ' -1 month'));
            $next = date('Y-m-d', strtotime($from . ' +1 month'));
            $label = 'Tháng ' . date('m/Y', $ts);
        }
        $fromStr = date('d/m/Y', strtotime($from));
        $toStr = date('d/m/Y', strtotime($to));
        if (!$full) return [$from, $to, $fromStr, $toStr];
        return [$from, $to, $fromStr, $toStr, $period, date('d/m/Y', strtotime($prev)), date('d/m/Y', strtotime($next)), $label];
    }
}
