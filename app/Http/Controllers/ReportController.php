<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\Partner;
use App\Models\CostType;
use App\Models\Cost;
use App\Models\Branch;

use App\User;
use App\Models\Settings;
use Helper, File, Session, Auth, Image, Hash;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UserNotification;
use App\Models\BookingLogs;
use DB;
class ReportController extends Controller
{
    /**
     * Thống kê món bán chạy theo Tuần / Tháng / Năm / Tùy chọn.
     * - Gộp các món trùng tên (cùng món ở nhiều menu/chi nhánh).
     * - Chỉ tính bill hợp lệ (booking.status 1, 2), bỏ bill hủy.
     * - So sánh với kỳ trước cùng độ dài.
     */
    public function dishReport(Request $request)
    {
        $period = in_array($request->period, ['week', 'month', 'year', 'custom']) ? $request->period : 'week';
        $anchor = $request->date ? strtotime($request->date) : time();
        if(!$anchor){ $anchor = time(); }
        $branch_id = $request->branch_id ? (int) $request->branch_id : null;
        $group = in_array($request->group, ['all', 'main', 'bread', 'drink']) ? $request->group : 'all';
        $sort = $request->sort == 'money' ? 'money' : 'qty';

        if($period == 'custom' && $request->from && $request->to && strtotime($request->from) && strtotime($request->to)){
            $from = date('Y-m-d', strtotime($request->from));
            $to = date('Y-m-d', strtotime($request->to));
            if($from > $to){ list($from, $to) = [$to, $from]; }
            $days = (int) round((strtotime($to) - strtotime($from)) / 86400) + 1;
            $prevFrom = date('Y-m-d', strtotime($from.' -'.$days.' days'));
            $prevTo = date('Y-m-d', strtotime($from.' -1 day'));
            $label = date('d/m/Y', strtotime($from)).' - '.date('d/m/Y', strtotime($to));
            $prevAnchor = $nextAnchor = null;
        }else{
            if($period == 'custom'){ $period = 'week'; }
            if($period == 'week'){
                $from = date('Y-m-d', strtotime('monday this week', $anchor));
                $to = date('Y-m-d', strtotime($from.' +6 days'));
                $prevFrom = date('Y-m-d', strtotime($from.' -7 days'));
                $prevTo = date('Y-m-d', strtotime($from.' -1 day'));
                $label = 'Tuần '.date('d/m', strtotime($from)).' - '.date('d/m/Y', strtotime($to));
                $prevAnchor = $prevFrom;
                $nextAnchor = date('Y-m-d', strtotime($from.' +7 days'));
            }elseif($period == 'month'){
                $from = date('Y-m-01', $anchor);
                $to = date('Y-m-t', $anchor);
                $prevFrom = date('Y-m-01', strtotime($from.' -1 month'));
                $prevTo = date('Y-m-t', strtotime($prevFrom));
                $label = 'Tháng '.date('m/Y', $anchor);
                $prevAnchor = $prevFrom;
                $nextAnchor = date('Y-m-01', strtotime($from.' +1 month'));
            }else{
                $from = date('Y-01-01', $anchor);
                $to = date('Y-12-31', $anchor);
                $prevFrom = date('Y-01-01', strtotime($from.' -1 year'));
                $prevTo = date('Y-12-31', strtotime($prevFrom));
                $label = 'Năm '.date('Y', $anchor);
                $prevAnchor = $prevFrom;
                $nextAnchor = date('Y-01-01', strtotime($from.' +1 year'));
            }
        }

        $query = function ($f, $t) use ($branch_id, $group) {
            $q = DB::table('booking_detail as bd')
                ->join('booking as b', 'b.id', '=', 'bd.booking_id')
                ->join('dish as d', 'd.id', '=', 'bd.dish_id')
                ->whereIn('b.status', [1, 2])
                ->whereBetween('b.use_date', [$f, $t]);
            if($branch_id){
                $q->where('b.branch_id', $branch_id);
            }
            if($group == 'bread'){
                $q->where('d.is_bread', 1);
            }elseif($group == 'drink'){
                $q->where('d.is_drink', 1);
            }elseif($group == 'main'){
                $q->where(function ($w) { $w->whereNull('d.is_bread')->orWhere('d.is_bread', 0); })
                  ->where(function ($w) { $w->whereNull('d.is_drink')->orWhere('d.is_drink', 0); });
            }
            return $q;
        };

        $rows = $query($from, $to)
            ->select(DB::raw('TRIM(d.name) as name'),
                DB::raw('SUM(bd.amount) as total_qty'),
                DB::raw('SUM(bd.total_price) as total_money'),
                DB::raw('MAX(d.is_bread) as is_bread'),
                DB::raw('MAX(d.is_drink) as is_drink'))
            ->groupBy(DB::raw('TRIM(d.name)'))
            ->get();

        $prevRows = $query($prevFrom, $prevTo)
            ->select(DB::raw('TRIM(d.name) as name'),
                DB::raw('SUM(bd.amount) as total_qty'),
                DB::raw('SUM(bd.total_price) as total_money'))
            ->groupBy(DB::raw('TRIM(d.name)'))
            ->get();
        $prevMap = [];
        foreach($prevRows as $p){
            $prevMap[mb_strtolower($p->name)] = $p;
        }

        $sumQty = $sumMoney = 0;
        $totalBread = $totalDrink = $totalOther = 0;
        $reports = [];
        foreach($rows as $r){
            $qty = (float) $r->total_qty;
            $money = (float) $r->total_money;
            $sumQty += $qty;
            $sumMoney += $money;
            if($r->is_bread == 1){
                $totalBread += $money;
            }elseif($r->is_drink == 1){
                $totalDrink += $money;
            }else{
                $totalOther += $money;
            }
            $p = $prevMap[mb_strtolower($r->name)] ?? null;
            $prevQty = $p ? (float) $p->total_qty : 0;
            $reports[] = [
                'name' => $r->name,
                'qty' => $qty,
                'money' => $money,
                'type' => $r->is_bread == 1 ? 'Bánh' : ($r->is_drink == 1 ? 'Đồ uống' : 'Món chính'),
                'prev_qty' => $prevQty,
                'prev_money' => $p ? (float) $p->total_money : 0,
                'diff_pct' => $prevQty > 0 ? round(($qty - $prevQty) / $prevQty * 100, 1) : null,
            ];
        }
        usort($reports, function ($a, $b) use ($sort) {
            $k = $sort == 'money' ? 'money' : 'qty';
            if($a[$k] == $b[$k]){ return $b[$k == 'qty' ? 'money' : 'qty'] <=> $a[$k == 'qty' ? 'money' : 'qty']; }
            return $b[$k] <=> $a[$k];
        });

        $prevSumMoney = 0;
        foreach($prevRows as $p){ $prevSumMoney += (float) $p->total_money; }

        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();

        return view('reports.dish', compact('reports', 'period', 'from', 'to', 'prevFrom', 'prevTo', 'label',
            'prevAnchor', 'nextAnchor', 'branch_id', 'group', 'sort', 'branchList', 'sumQty', 'sumMoney',
            'prevSumMoney', 'totalBread', 'totalDrink', 'totalOther'));
    }
    public function chiphi(Request $request){
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        //dd($mindate, $maxdate);
        $all = Booking::where('use_date', '>=', $mindate)->where('use_date', '<=', $maxdate)->whereIn('status',[1, 2])->get();
       
        $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3)->get();
       
        $arrCost = [];
        $arrTotal = [];
        $tong_chi = $tong_chi_phi_van_hanh = $tong_chi_phi_khac = 0;
        foreach($costAll as $costDay){            
            $key = (int) date('d', strtotime($costDay->date_use));
            if(!isset($arrTotal[$costDay->cate_id])){
                $arrTotal[$costDay->cate_id] = 0;
            }
            if(!isset($arrCost[$key])){
                $arrCost[$key]['total'] = 0;
                $arrCost[$key]['tong_chi_phi_van_hanh'] = 0;
                $arrCost[$key]['tong_chi_phi_khac'] = 0;
            }   
            if(!isset($arrCost[$key][$costDay->cate_id])){
                $arrCost[$key][$costDay->cate_id]['total'] = 0;
            }           
           // var_dump($costDay->partner_id);
            if($costDay->partner_id > 0 && !isset($arrCost[$key][$costDay->cate_id][$costDay->partner_id])){
             
                $arrCost[$key][$costDay->cate_id][$costDay->partner_id] = 0; 
             
            }                  
            
            $arrCost[$key]['total'] += $costDay->total_money;
            $arrTotal[$costDay->cate_id] += $costDay->total_money;
            $arrCateLoaiTru = [1, 8, 51, 9, 10, 12]; 

            if($costDay->type == 1){              
                $arrCost[$key]['tong_chi_phi_van_hanh'] += $costDay->total_money;    
                              
            }else{
                $arrCost[$key]['tong_chi_phi_khac'] += $costDay->total_money;
            }
            
            $arrCost[$key][$costDay->cate_id]['total'] += $costDay->total_money;
            if($costDay->partner_id > 0){
               $arrCost[$key][$costDay->cate_id][$costDay->partner_id] += $costDay->total_money;    
            }          

            $tong_chi += $costDay->total_money;
            if($costDay->type == 1){
                $tong_chi_phi_van_hanh += $costDay->total_money;
            }else{
                $tong_chi_phi_khac += $costDay->total_money;
            }
        }
        //dd($arrCost); 
        //dd($arrTotal);       
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        $cateArr = [];
        foreach($cateList as $cate){
            $cateArr[$cate->id] = $cate->name;
        }
        $percentArr = [];
        foreach($arrTotal as $cate_id => $total_by_cate){
            $percentArr[$cate_id] = round($total_by_cate*100/$tong_chi, 2, PHP_ROUND_HALF_ODD) ;
        }
        
        return view('report.chi-phi', compact('arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac', 'arrTotal', 'cateArr', 'percentArr'));

    }
    
    public function doanhthuthang(Request $request){       
        
        $error = $request->error ?? 0;
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        $minDay = 1;

        $all = Booking::where('use_date', '>=', $mindate)            
            ->where('use_date', '<=', $maxdate)->whereIn('status',[1, 2])->get();
        if($year < 2024 || ($month <= 5 && $year == 2024)){
            $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3)->whereNotIn('cate_id',[57, 58])->get(); // phần chi phí ko tính 5% và hoa hồng chi cano
            $view = 'report.doanh-thu-thang';
        }else{
            $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3)->get();
            $view = 'report.doanh-thu-thang-new';
        }
        $arrCost = [];
        $tong_chi = $tong_chi_phi_van_hanh = $tong_chi_phi_khac = 0;
        foreach($costAll as $costDay){            
            $key = (int) date('d', strtotime($costDay->date_use));
            if(!isset($arrCost[$key])){
                $arrCost[$key]['total'] = 0;
                $arrCost[$key]['tong_chi_phi_van_hanh'] = 0;
                $arrCost[$key]['tong_chi_phi_khac'] = 0;
            }   
            if(!isset($arrCost[$key][$costDay->cate_id])){
                $arrCost[$key][$costDay->cate_id]['total'] = 0;
            }           
           // var_dump($costDay->partner_id);
            if($costDay->partner_id > 0 && !isset($arrCost[$key][$costDay->cate_id][$costDay->partner_id])){
                //var_dump("aaaaa", $costDay->cate_id, $costDay->partner_id);
                $arrCost[$key][$costDay->cate_id][$costDay->partner_id] = 0; 
               // echo "<hr>";
            }                  
            
            $arrCost[$key]['total'] += $costDay->total_money;

            $arrCateLoaiTru = [1, 8, 51, 9, 10, 12]; 

            if($costDay->type == 1){
              
                $arrCost[$key]['tong_chi_phi_van_hanh'] += $costDay->total_money;    
                              
            }else{
                $arrCost[$key]['tong_chi_phi_khac'] += $costDay->total_money;
            }
            
            $arrCost[$key][$costDay->cate_id]['total'] += $costDay->total_money;
            if($costDay->partner_id > 0){
               $arrCost[$key][$costDay->cate_id][$costDay->partner_id] += $costDay->total_money;    
            }          

            $tong_chi += $costDay->total_money;
            if($costDay->type == 1){
                $tong_chi_phi_van_hanh += $costDay->total_money;
            }else{
                $tong_chi_phi_khac += $costDay->total_money;
            }
        }
        //dd($arrCost);        
        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];

        foreach($all as $bk){
            $tong_tien_flycam_johntour = 0;
            // tinh tổng tiền flycam
            foreach($bk->details as $detail){  
                if($detail->cate_id == 71) // cam 500 JohnTour
                {
                    $tong_tien_flycam_johntour += $detail->total_price;
                }
            }
            $key = (int) date('d', strtotime($bk->use_date));        
          
            if(!isset($arrDay[$key])){
                $arrDay[$key] = [
                                            'tong_tien' => 0, 
                                            'tong_giam' => 0, 
                                            'tong_chiet_khau' => 0, 
                                            'tong_tien_coc' => 0,
                                            'tong_chi_5' => 0,
                                            'tong_con_lai_chua_tru_chi_phi' => 0,
                                            'tong_chi_phi_van_hanh' => 0,
                                            'tong_chi_phi_khac' => 0,
                                            'tong_john' => 0,
                                            'tong_20' => 0
                                       ];
            }
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['tong_giam'] += $bk->discount;
            $chietkhau = $bk->commision;           
            

            $arrDay[$key]['tong_chiet_khau'] += $chietkhau;
            $arrDay[$key]['tong_tien_coc'] += $bk->tien_coc;
            
            if($year < 2024 || ($month <= 5 && $year == 2024)){
                if($error == 1){
                    $tien_chi_5 = ($bk->total_price - $tong_tien_flycam_johntour)*5/100;
                }else{
                    $tien_chi_5 = ($bk->total_price - $bk->discount - $bk->commision + $bk->tien_coc)*5/100;
                }             
            }else{
                $tien_chi_5 = ($bk->total_price - $bk->discount - $bk->commision + $bk->tien_coc)*5/100;
            }
            
            $arrDay[$key]['tong_chi_5'] += $tien_chi_5;
            $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += $bk->total_price - $bk->discount - $chietkhau - $bk->tien_coc - $tien_chi_5;    

          
        }       
    
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        //dd($cateList);
        
        return view($view, compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales', 'arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac', 'minDay', 'maxDay'));


    }
    public function doanhthuthang2(Request $request){       
        
        $error = $request->error ?? 0;
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        //dd($mindate, $maxdate);
        $all = Booking::where('use_date', '>=', $mindate)
            ->where('xe_4t', 0)
            ->where('use_date', '<=', $maxdate)->whereIn('status',[1, 2])->get();
        if($year < 2024 || ($month <= 5 && $year == 2024)){
            $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3)->whereNotIn('cate_id',[57, 58])->get(); // phần chi phí ko tính 5% và hoa hồng chi cano
            $view = 'report.doanh-thu-thang';
        }else{
            $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3)->get();
            $view = 'report.doanh-thu-thang-new';
        }
        $arrCost = [];
        $tong_chi = $tong_chi_phi_van_hanh = $tong_chi_phi_khac = 0;
        foreach($costAll as $costDay){            
            $key = (int) date('d', strtotime($costDay->date_use));
            if(!isset($arrCost[$key])){
                $arrCost[$key]['total'] = 0;
                $arrCost[$key]['tong_chi_phi_van_hanh'] = 0;
                $arrCost[$key]['tong_chi_phi_khac'] = 0;
            }   
            if(!isset($arrCost[$key][$costDay->cate_id])){
                $arrCost[$key][$costDay->cate_id]['total'] = 0;
            }           
           // var_dump($costDay->partner_id);
            if($costDay->partner_id > 0 && !isset($arrCost[$key][$costDay->cate_id][$costDay->partner_id])){
                //var_dump("aaaaa", $costDay->cate_id, $costDay->partner_id);
                $arrCost[$key][$costDay->cate_id][$costDay->partner_id] = 0; 
               // echo "<hr>";
            }                  
            
            $arrCost[$key]['total'] += $costDay->total_money;

            $arrCateLoaiTru = [1, 8, 51, 9, 10, 12]; 

            if($costDay->type == 1){
              
                $arrCost[$key]['tong_chi_phi_van_hanh'] += $costDay->total_money;    
                              
            }else{
                $arrCost[$key]['tong_chi_phi_khac'] += $costDay->total_money;
            }
            
            $arrCost[$key][$costDay->cate_id]['total'] += $costDay->total_money;
            if($costDay->partner_id > 0){
               $arrCost[$key][$costDay->cate_id][$costDay->partner_id] += $costDay->total_money;    
            }          

            $tong_chi += $costDay->total_money;
            if($costDay->type == 1){
                $tong_chi_phi_van_hanh += $costDay->total_money;
            }else{
                $tong_chi_phi_khac += $costDay->total_money;
            }
        }
        //dd($arrCost);        
        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];

        foreach($all as $bk){
            $tong_tien_flycam_johntour = 0;
            // tinh tổng tiền flycam
            foreach($bk->details as $detail){  
                if($detail->cate_id == 71) // cam 500 JohnTour
                {
                    $tong_tien_flycam_johntour += $detail->total_price;
                }
            }
            $key = (int) date('d', strtotime($bk->use_date));        
          
            if(!isset($arrDay[$key])){
                $arrDay[$key] = [
                                            'tong_tien' => 0, 
                                            'tong_giam' => 0, 
                                            'tong_chiet_khau' => 0, 
                                            'tong_tien_coc' => 0,
                                            'tong_chi_5' => 0,
                                            'tong_con_lai_chua_tru_chi_phi' => 0,
                                            'tong_chi_phi_van_hanh' => 0,
                                            'tong_chi_phi_khac' => 0,
                                            'tong_john' => 0,
                                            'tong_20' => 0
                                       ];
            }
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['tong_giam'] += $bk->discount;
            $chietkhau = $bk->commision;           
            

            $arrDay[$key]['tong_chiet_khau'] += $chietkhau;
            $arrDay[$key]['tong_tien_coc'] += $bk->tien_coc;
            
            if($year < 2024 || ($month <= 5 && $year == 2024)){
                if($error == 1){
                    $tien_chi_5 = ($bk->total_price - $tong_tien_flycam_johntour)*5/100;
                }else{
                    $tien_chi_5 = ($bk->total_price - $bk->discount - $bk->commision + $bk->tien_coc)*5/100;
                }             
            }else{
                $tien_chi_5 = ($bk->total_price - $bk->discount - $bk->commision + $bk->tien_coc)*5/100;
            }
            
            $arrDay[$key]['tong_chi_5'] += $tien_chi_5;
            $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += $bk->total_price - $bk->discount - $chietkhau - $bk->tien_coc - $tien_chi_5;    

          
        }       
    
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        //dd($cateList);
        
        return view($view, compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales', 'arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac'));


    }
    public function loiNhuan(Request $request){       
        
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $branch_id = $request->branch_id ?? null;
        $error = $request->error ?? 0;
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        //dd($mindate, $maxdate);
        $queryBooking = Booking::where('use_date', '>=', $mindate)->where('use_date', '<=', $maxdate)->whereIn('status',[1, 2]);
        if($branch_id){
            $queryBooking->where('branch_id', $branch_id);
        }
        $all = $queryBooking->get();
        
        $queryCost = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3);
        if($branch_id){
            $queryCost->where('branch_id', $branch_id);
        }
        $costAll = $queryCost->get();
            $view = 'report.loi-nhuan';
        
        $arrCost = [];
        $tong_chi = $tong_chi_phi_van_hanh = $tong_chi_phi_khac = 0;
        foreach($costAll as $costDay){            
            $key = (int) date('d', strtotime($costDay->date_use));
            if(!isset($arrCost[$key])){
                $arrCost[$key]['total'] = 0;
                $arrCost[$key]['tong_chi_phi_van_hanh'] = 0;
                $arrCost[$key]['tong_chi_phi_khac'] = 0;
            }   
            if(!isset($arrCost[$key][$costDay->cate_id])){
                $arrCost[$key][$costDay->cate_id]['total'] = 0;
            }           
           // var_dump($costDay->partner_id);
            if($costDay->partner_id > 0 && !isset($arrCost[$key][$costDay->cate_id][$costDay->partner_id])){
                //var_dump("aaaaa", $costDay->cate_id, $costDay->partner_id);
                $arrCost[$key][$costDay->cate_id][$costDay->partner_id] = 0; 
               // echo "<hr>";
            }                  
            
            $arrCost[$key]['total'] += $costDay->total_money;

            $arrCateLoaiTru = [1, 8, 51, 9, 10, 12]; 

            if($costDay->type == 1){
              
                $arrCost[$key]['tong_chi_phi_van_hanh'] += $costDay->total_money;    
                              
            }else{
                $arrCost[$key]['tong_chi_phi_khac'] += $costDay->total_money;
            }
            
            $arrCost[$key][$costDay->cate_id]['total'] += $costDay->total_money;
            if($costDay->partner_id > 0){
               $arrCost[$key][$costDay->cate_id][$costDay->partner_id] += $costDay->total_money;    
            }          

            $tong_chi += $costDay->total_money;
            if($costDay->type == 1){
                $tong_chi_phi_van_hanh += $costDay->total_money;
            }else{
                $tong_chi_phi_khac += $costDay->total_money;
            }
        }
             
        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];

        foreach($all as $bk){
            $tong_tien_flycam_johntour = 0;
            // tinh tổng tiền flycam
            foreach($bk->details as $detail){  
                if($detail->cate_id == 71) // cam 500 JohnTour
                {
                    $tong_tien_flycam_johntour += $detail->total_price;
                }
            }
            $key = (int) date('d', strtotime($bk->use_date));        
          
            if(!isset($arrDay[$key])){
                $arrDay[$key] = [
                                            'tong_tien' => 0, 
                                            'tong_giam' => 0, 
                                            'tong_chiet_khau' => 0, 
                                            'tong_tien_coc' => 0,
                                            'tong_chi_5' => 0,
                                            'tong_con_lai_chua_tru_chi_phi' => 0,                                          
                                            'tong_john' => 0,
                                            'tong_20' => 0,
                                            'tong_toto' => 0,
                                            'tong_tien_flycam' => 0
                                       ];
            }
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['tong_giam'] += $bk->discount;
            $arrDay[$key]['tong_tien_flycam'] += $tong_tien_flycam_johntour;
            $chietkhau = $bk->commision;           
            

            $arrDay[$key]['tong_chiet_khau'] += $chietkhau;
            $arrDay[$key]['tong_tien_coc'] += $bk->tien_coc;
            if($branch_id == 4){
                $arrDay[$key]['tong_john'] += $bk->total_price*20/100;
                $arrDay[$key]['tong_toto'] += $bk->total_price*5/100 + $tong_tien_flycam_johntour*5/100;

                $tien_chi_5 = ($bk->total_price - $tong_tien_flycam_johntour)*5/100;
                    
                
                $arrDay[$key]['tong_chi_5'] += $tien_chi_5;
                $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += $bk->total_price - $bk->discount - $bk->tien_coc;      
                               
                $chi_phi_co_dinh = 3500000;
                
                $arrDay[$key]['loi_nhuan_van_hanh'] = $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] - $chi_phi_co_dinh - $arrDay[$key]['tong_john'] - $arrDay[$key]['tong_toto'] - $arrCost[$key]['tong_chi_phi_van_hanh'];
                $arrDay[$key]['loi_nhuan'] = $arrDay[$key]['loi_nhuan_van_hanh']  - $arrCost[$key]['tong_chi_phi_khac'];
            }else{
                $arrDay[$key]['tong_john'] += $bk->total_price*20/100;              

                $tien_chi_5 = ($bk->total_price - $tong_tien_flycam_johntour)*5/100;
                    
                
                $arrDay[$key]['tong_chi_5'] += $tien_chi_5;
                $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += $bk->total_price - $bk->discount - $bk->tien_coc;      
                
                $chi_phi_co_dinh = 1650000;
                
                $arrDay[$key]['loi_nhuan_van_hanh'] = $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] - $chi_phi_co_dinh - $arrDay[$key]['tong_john'] - $arrCost[$key]['tong_chi_phi_van_hanh'];
                $arrDay[$key]['loi_nhuan'] = $arrDay[$key]['loi_nhuan_van_hanh']  - $arrCost[$key]['tong_chi_phi_khac'];
            }
            

        }        
        //dd($arrDay);
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        //dd($cateList);
        
        return view($view, compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales', 'arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac', 'branchList', 'branch_id'));


    }
    public function doanhthuthangNew(Request $request){       
        
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        //dd($mindate, $maxdate);
        $all = Booking::where('use_date', '>=', $mindate)->where('use_date', '<=', $maxdate)->whereIn('status',[1, 2])->get();
       
        $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3)->get();
       
        $arrCost = [];
        $tong_chi = $tong_chi_phi_van_hanh = $tong_chi_phi_khac = 0;
        foreach($costAll as $costDay){            
            $key = (int) date('d', strtotime($costDay->date_use));
            if(!isset($arrCost[$key])){
                $arrCost[$key]['total'] = 0;
                $arrCost[$key]['tong_chi_phi_van_hanh'] = 0;
                $arrCost[$key]['tong_chi_phi_khac'] = 0;
            }   
            if(!isset($arrCost[$key][$costDay->cate_id])){
                $arrCost[$key][$costDay->cate_id]['total'] = 0;
            }           
           // var_dump($costDay->partner_id);
            if($costDay->partner_id > 0 && !isset($arrCost[$key][$costDay->cate_id][$costDay->partner_id])){
                //var_dump("aaaaa", $costDay->cate_id, $costDay->partner_id);
                $arrCost[$key][$costDay->cate_id][$costDay->partner_id] = 0; 
               // echo "<hr>";
            }                  
            
            $arrCost[$key]['total'] += $costDay->total_money;

            $arrCateLoaiTru = [1, 8, 51, 9, 10, 12]; 

            if($costDay->type == 1){
              
                $arrCost[$key]['tong_chi_phi_van_hanh'] += $costDay->total_money;    
                              
            }else{
                $arrCost[$key]['tong_chi_phi_khac'] += $costDay->total_money;
            }
            
            $arrCost[$key][$costDay->cate_id]['total'] += $costDay->total_money;
            if($costDay->partner_id > 0){
               $arrCost[$key][$costDay->cate_id][$costDay->partner_id] += $costDay->total_money;    
            }          

            $tong_chi += $costDay->total_money;
            if($costDay->type == 1){
                $tong_chi_phi_van_hanh += $costDay->total_money;
            }else{
                $tong_chi_phi_khac += $costDay->total_money;
            }
        }
        //dd($arrCost);        
        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];

        foreach($all as $bk){
           // var_dump($bk->ko_cap_treo);
            //echo "<hr>";
            // if($bk->ko_cap_treo == 0){               
            //     $bk->update(['cap_nl' => $bk->adults, 'cap_te' => $bk->childs]);
            // }else{              
            //     $bk->update(['cap_nl' => 0, 'cap_te' => 0]);
            // }

            $key = (int) date('d', strtotime($bk->use_date));        
          
            if(!isset($arrDay[$key])){
                $arrDay[$key] = [
                                            'tong_tien' => 0, 
                                            'tong_giam' => 0, 
                                          
                                            'tong_tien_coc' => 0,
                                        
                                            'tong_con_lai_chua_tru_chi_phi' => 0,
                                            'tong_chi_phi_van_hanh' => 0,
                                            'tong_chi_phi_khac' => 0,
                                            'tong_john' => 0,
                                            'tong_20' => 0
                                       ];
            }
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['tong_giam'] += $bk->discount;         
            $arrDay[$key]['tong_tien_coc'] += $bk->tien_coc;
       
            $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += $bk->total_price - $bk->discount - $bk->tien_coc;      

          
        }       
    
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        //dd($cateList);
        
        return view('report.doanh-thu-thang-new', compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales', 'arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac'));


    }

    public function thongkedoitac(Request $request){       
        
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        //dd($mindate, $maxdate);
        $all = Booking::where('use_date', '>=', $mindate)->where('use_date', '<=', $maxdate)->where(function ($query) {
                    $query->where('user_id', '=', Auth::user()->id)
                          ->orWhere('partner_id', '=', Auth::user()->id);
                })->whereIn('status',[1, 2, 4])->get();
       
       
        //dd($arrCost);        
        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];

        foreach($all as $bk){
          
            $key = (int) date('d', strtotime($bk->use_date));        
          
            if(!isset($arrDay[$key])){
                $arrDay[$key] = [
                                            'tong_tien' => 0, 
                                            'tong_giam' => 0, 
                                            'tong_chiet_khau' => 0, 
                                       ];
            }
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['tong_giam'] += $bk->discount;
            $arrDay[$key]['tong_chiet_khau'] += ($bk->total_price - $bk->discount)*0.1;
          
        }       
    
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        //dd($cateList);
        
        return view('report.thong-ke-doi-tac', compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type'));


    }

    public function thongkebai(Request $request){       
        
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        //dd($mindate, $maxdate);
        $all = Booking::where('use_date', '>=', $mindate)->where('use_date', '<=', $maxdate)->where('branch_id', '=', Auth::user()->branch_id)->whereIn('status',[1, 2, 4])->get();       
       
        //dd($arrCost);        
        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];

        foreach($all as $bk){
          
            $key = (int) date('d', strtotime($bk->use_date));        
          
            if(!isset($arrDay[$key])){
                $arrDay[$key] = [
                                            'tong_tien' => 0, 
                                            'tong_giam' => 0, 
                                            'tong_chiet_khau' => 0, 
                                       ];
            }
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['tong_giam'] += $bk->discount;
            $arrDay[$key]['tong_chiet_khau'] += ($bk->total_price - $bk->discount)*0.1;
          
        }       
    
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        //dd($cateList);
        
        return view('report.thong-ke-bai-bien', compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type'));


    }

    public function thuTien(Request $request){       
        
        $error = $request->error ?? 0;
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        $minDay = 1;

        $all = Booking::where('use_date', '>=', $mindate)            
            ->where('use_date', '<=', $maxdate)->whereIn('status',[1, 2])->get();
        
        $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status','>', 0)->where('city_id','<>', 3)->get();
        $view = 'report.thu-tien';
        
        $arrCost = [];
        $tong_chi = $tong_chi_phi_van_hanh = $tong_chi_phi_khac = 0;        
        $totalChi = [];
        $totalChiByPerson = [];
        foreach($costAll as $costDay){
            if(!isset($totalChi[$costDay->nguoi_chi])){
                $totalChi[$costDay->nguoi_chi] = 0;
            }
            $totalChi[$costDay->nguoi_chi]+= $costDay->total_money;
            
            $key = (int) date('d', strtotime($costDay->date_use));
            if(!isset($arrCost[$key])){
                $arrCost[$key]['total'] = 0;
                $arrCost[$key]['tong_chi_phi_van_hanh'] = 0;
                $arrCost[$key]['tong_chi_phi_khac'] = 0;
                $arrCost[$key]['nguoi_chi'][$costDay->nguoi_chi] = 0;
            }   
            $arrCost[$key]['nguoi_chi'][$costDay->nguoi_chi] = $costDay->total_money;
            if(!isset($arrCost[$key][$costDay->cate_id])){
                $arrCost[$key][$costDay->cate_id]['total'] = 0;                
            }           
           // var_dump($costDay->partner_id);
            if($costDay->partner_id > 0 && !isset($arrCost[$key][$costDay->cate_id][$costDay->partner_id])){
                //var_dump("aaaaa", $costDay->cate_id, $costDay->partner_id);
                $arrCost[$key][$costDay->cate_id][$costDay->partner_id] = 0; 
               // echo "<hr>";
            }                  
            
            $arrCost[$key]['total'] += $costDay->total_money;

            $arrCateLoaiTru = [1, 8, 51, 9, 10, 12]; 

            if($costDay->type == 1){
              
                $arrCost[$key]['tong_chi_phi_van_hanh'] += $costDay->total_money;    
                              
            }else{
                $arrCost[$key]['tong_chi_phi_khac'] += $costDay->total_money;
            }
            
            $arrCost[$key][$costDay->cate_id]['total'] += $costDay->total_money;
            if($costDay->partner_id > 0){
               $arrCost[$key][$costDay->cate_id][$costDay->partner_id] += $costDay->total_money;    
            }          

            $tong_chi += $costDay->total_money;
            if($costDay->type == 1){
                $tong_chi_phi_van_hanh += $costDay->total_money;
            }else{
                $tong_chi_phi_khac += $costDay->total_money;
            }
        } 
              
        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];
        $arrNguoiThu = [];
        foreach($all as $bk){
            $tong_thuc_thu += $bk->con_lai;
            if(!isset($arrNguoiThu[$bk->nguoi_thu_tien])){
                $arrNguoiThu[$bk->nguoi_thu_tien] = 0;   
            }
            $arrNguoiThu[$bk->nguoi_thu_tien] += $bk->con_lai;
            $key = (int) date('d', strtotime($bk->use_date));        
          
            if(!isset($arrDay[$key])){
                $arrDay[$key] = [
                        'tong_tien' => 0, 
                        'tong_giam' => 0, 
                        'tong_chiet_khau' => 0, 
                        'tong_tien_coc' => 0,
                        'tong_chi_5' => 0,
                        'tong_con_lai_chua_tru_chi_phi' => 0,
                        'tong_chi_phi_van_hanh' => 0,
                        'tong_chi_phi_khac' => 0,
                        'tong_john' => 0,
                        'tong_20' => 0,
                        'con_lai' => 0
                                       ];
            }
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['con_lai'] += $bk->con_lai;
            $arrDay[$key]['tong_giam'] += $bk->discount;
            $chietkhau = $bk->commision;      
            if(!isset($arrDay[$key]['person'][$bk->nguoi_thu_tien])){
                $arrDay[$key]['person'][$bk->nguoi_thu_tien] = 0;
            }
            $arrDay[$key]['person'][$bk->nguoi_thu_tien] += $bk->con_lai;

            $arrDay[$key]['tong_chiet_khau'] += $chietkhau;
            $arrDay[$key]['tong_tien_coc'] += $bk->tien_coc;
            
            if($year < 2024 || ($month <= 5 && $year == 2024)){
                if($error == 1){
                    $tien_chi_5 = ($bk->total_price - $tong_tien_flycam_johntour)*5/100;
                }else{
                    $tien_chi_5 = ($bk->total_price - $bk->discount - $bk->commision + $bk->tien_coc)*5/100;
                }             
            }else{
                $tien_chi_5 = ($bk->total_price - $bk->discount - $bk->commision + $bk->tien_coc)*5/100;
            }
            
            $arrDay[$key]['tong_chi_5'] += $tien_chi_5;
            $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += $bk->total_price - $bk->discount - $chietkhau - $bk->tien_coc - $tien_chi_5;    

          
        }       
    
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
               
        return view($view, compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales', 'arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac', 'minDay', 'maxDay', 'arrNguoiThu', 'totalChi'));


    }
    public function doanhthuview(Request $request) {
        $error = $request->error ?? 0;
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $maxDay = date('d', strtotime($maxdate));
        $minDay = 1;

        // Lấy danh sách booking trong tháng
        $all = Booking::where('use_date', '>=', $mindate)            
            ->where('use_date', '<=', $maxdate)
            ->whereIn('status', [1, 2])
            ->where('branch_id', Auth::user()->branch_id)
            ->get();

        $tong_thuc_thu = $tong_coc = 0;        
        $tong_hoa_hong_sales = 0;
        $arrDay = [];
        $tong_nguoi_thu = []; // Lưu tổng tiền của từng người thu trong toàn bộ tháng

        foreach ($all as $bk) {
            $key = (int) date('d', strtotime($bk->use_date));        
            $nguoiThuId = $bk->nguoi_thu_tien ?? 0; // ID người thu tiền (hoặc 0 nếu null)

            if (!isset($arrDay[$key])) {
                $arrDay[$key] = [
                    'tong_tien'                     => 0, 
                    'tong_giam'                     => 0, 
                    'tong_chiet_khau'               => 0, 
                    'tong_tien_coc'                 => 0,
                    'tong_con_lai_chua_tru_chi_phi' => 0,
                    'tong_john'                     => 0,
                    'tong_20'                       => 0,
                    'nguoi_thu'                     => [] // Thống kê theo người thu từng ngày
                ];
            }

            $chietkhau = $bk->commision;

            // Cộng dồn theo từng ngày
            $arrDay[$key]['tong_tien'] += $bk->total_price;
            $arrDay[$key]['tong_giam'] += $bk->discount;
            $arrDay[$key]['tong_chiet_khau'] += $chietkhau;
            $arrDay[$key]['tong_tien_coc'] += $bk->tien_coc;
            $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += ($bk->total_price - $bk->discount - $chietkhau - $bk->tien_coc);    

            // Thống kê total_price theo người thu tiền theo từng ngày
            if (!isset($arrDay[$key]['nguoi_thu'][$nguoiThuId])) {
                $arrDay[$key]['nguoi_thu'][$nguoiThuId] = 0;
            }
            $arrDay[$key]['nguoi_thu'][$nguoiThuId] += $bk->total_price;

            // Thống kê total_price theo người thu tiền tổng cả tháng
            if (!isset($tong_nguoi_thu[$nguoiThuId])) {
                $tong_nguoi_thu[$nguoiThuId] = 0;
            }
            $tong_nguoi_thu[$nguoiThuId] += $bk->total_price;
        }        

        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        
        $view = 'report.doanh-thu-view';

        return view($view, compact(
            'arrDay', 
            'tong_nguoi_thu',
            'tong_thuc_thu', 
            'tong_hoa_hong_sales', 
            'maxDay', 
            'minDateFormat', 
            'maxDateFormat', 
            'month', 
            'year', 
            'type', 
            'minDay'
        ));
    }
}
