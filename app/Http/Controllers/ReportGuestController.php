<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\Hotels;
use App\Models\BookingRooms;
use App\Models\Partner;
use App\Models\CostType;
use App\Models\Tickets;
use App\Models\Location;
use App\Models\Tour;
use App\Models\Cost;
use App\Models\CarCate;
use App\Models\Drivers;
use App\Models\Revenue;
use App\Models\Debt;
use App\Models\Ctv;

use App\User;
use App\Models\Settings;
use Helper, File, Session, Auth, Image, Hash;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UserNotification;
use App\Models\BookingLogs;

class ReportGuestController extends Controller
{
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
        
        return view('report-guest.chi-phi', compact('arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac', 'arrTotal', 'cateArr', 'percentArr'));

    }
    public function thuTien(Request $request){
        $id = $request->id;
        $detail = Booking::find($id);
        $newData = [
        'nguoi_thu_tien' => 2,
            'status' => 2
        ];
        $oldData = [
            'nguoi_thu_tien' => $detail->nguoi_thu_tien,
            'status' =>  $detail->status
        ];
        $detail->update($newData);
        $rsLog = BookingLogs::create([
                'booking_id' => $id,
                'content' =>json_encode(['old' => $oldData, 'new' => $newData]),
                'action' => 2,
                'user_id' => Auth::user()->id
            ]);
    }
    public function detailCostByPartner(Request $request){
        $id = $request->id;
        $date_use = $request->date_use;
        $costAll = Cost::where('date_use', $date_use)
                        ->where('status', 1)
                        ->where('partner_id', $id)
                        ->get();
                        //->sum('amount');
        dd($costAll);
    }

    public function dsDoitac(Request $request){        
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));
        $arrSearch['ctv_id'] = $ctv_id = $request->ctv_id ?? null;
        $arrSearch['level'] = $level = $request->level ? $request->level : null; 
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;
        $query = Booking::where('type', 1)->where('status', '<', 3)->where('user_id', $user_id)->where('tour_id', 1)->whereIn('tour_type', [1, 2]);
        if($time_type == 1){ // theo thangs
            $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
            $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));
          
            $query->where('use_date','>=', $mindate);                   
            $query->where('use_date', '<=', $maxdate);
        }elseif($time_type == 2){ // theo khoang ngay
            $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

            if($use_date_from){
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('use_date','>=', $use_date_from_format);
            }
            if($use_date_to){
                $arrSearch['use_date_to'] = $use_date_to;
                $tmpDate = explode('/', $use_date_to);
                $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($use_date_to_format < $use_date_from_format){
                    $arrSearch['use_date_to'] = $use_date_from;
                    $use_date_to_format = $use_date_from_format;   
                }        
                $query->where('use_date', '<=', $use_date_to_format);
            }
        }else{
            $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            
            $arrSearch['use_date_from'] = $use_date_from;
            $tmpDate = explode('/', $use_date_from);
            $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('use_date','=', $use_date_from_format);
        
        }
        if($level && $type == 1){
            $arrSearch['level'] = $level;
            $query->where('level', $level);
        }    
        $items = $query->get();
        //dd($items);
        $arrResult = [];
        $listUser = User::whereIn('level', [1,2,3,4,5,6,7])->where('status', 1)->get();
        $arrUser = [];
        if($level){
            $listUser = User::where('level', $level)->where('status', 1)->get();
        }
        foreach($listUser as $u){
            $arrUser[$u->id] = $u;
        } 
        $total_adults = $total_money = 0;
        $arrByDay = [];
        if($items->count() > 0){
            $locationList = [];
            foreach($items as $bk){    
                $userArr[$bk->user_id] = $bk->user_id;
                $total_adults += $bk->adults;
                $money = 0;
                if(!$bk->location){
                     $money = $bk->adults*350000;
                    $total_money += $money;
                }else{
                    if($bk->location->is_ben == 1){
                        $money = $bk->adults*250000;
                        $total_money += $money;
                    }else{
                        $money = $bk->adults*350000;
                        $total_money += $money;
                    }
                }
                if(!isset($arrByDay[$bk->use_date])){
                    $arrByDay[$bk->use_date]['total_adults'] = 0;
                    $arrByDay[$bk->use_date]['total_money'] = 0;
                }              
                $arrByDay[$bk->use_date]['total_adults'] += $bk->adults;
                $arrByDay[$bk->use_date]['total_money'] += $money;
            }            
        }     
       if(Auth::user()->role == 1){
            $ctvList = Ctv::where('status', 1)->where('leader_id', 18)->get();
        }else{
            if(Auth::user()->id == 64){
                $leader_id = 3;
            }else{
                $leader_id = Auth::user()->id;
            }
            $ctvList = Ctv::where('status', 1)->where('leader_id', $leader_id)->get();
        }   
        return view('report-guest.doanh-so-doi-tac', compact('month', 'year', 'listUser', 'arrUser', 'time_type', 'arrSearch', 'arrResult', 'items', 'level', 'ctvList', 'ctv_id', 'arrByDay', 'total_adults', 'total_money'));
    }
    public function customerByLevel(Request $request){
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $level = $request->level ?? null;
        $id_loaitru = $request->id_loaitru ?? null;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $query = Booking::where('type', 1)->where('status', '<', 3);
        if($level){
            $query->where('level', $level);
            $query->whereIn('tour_type', [1,2]);
        }
        if($id_loaitru){
            $arrLoaiTru = explode(',', $id_loaitru);
            $query->whereNotIn('user_id', $arrLoaiTru);
        }
        if($time_type == 1){ // theo thangs
            $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
            $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));
          
            $query->where('use_date','>=', $mindate);                   
            $query->where('use_date', '<=', $maxdate);
        }elseif($time_type == 2){ // theo khoang ngay
            $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

            if($use_date_from){
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('use_date','>=', $use_date_from_format);
            }
            if($use_date_to){
                $arrSearch['use_date_to'] = $use_date_to;
                $tmpDate = explode('/', $use_date_to);
                $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($use_date_to_format < $use_date_from_format){
                    $arrSearch['use_date_to'] = $use_date_from;
                    $use_date_to_format = $use_date_from_format;   
                }        
                $query->where('use_date', '<=', $use_date_to_format);
            }
        }else{
            $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            
            $arrSearch['use_date_from'] = $use_date_from;
            $tmpDate = explode('/', $use_date_from);
            $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('use_date','=', $use_date_from_format);
        
        }
        $items = $query->get();
        $arrResult = [];
        $listUser = User::whereIn('level', [1,2,3,4,5,6,7])->where('status', 1)->get();
        $arrUser = [];
        foreach($listUser as $u){
            $arrUser[$u->id] = $u;
        } 
        $arrLevel = [];

        foreach($items as $item){
            $level = isset($arrUser[$item->user_id]) ? $arrUser[$item->user_id]->level : 0;
            if(!isset($arrResult[$item->user_id])){
                $arrResult[$item->user_id][$item->tour_type] = 0;                
            }else{
                if(!isset($arrResult[$item->user_id][$item->tour_type])){
                    $arrResult[$item->user_id][$item->tour_type] = 0;
                }                
            }
            if(!isset($arrLevel[$level])){
                if($item->tour_type == 3){
                    $arrLevel[$level] = 1;    
                }else{
                    $arrLevel[$level] = $item->adults;        
                }                
            }else{
                if($item->tour_type == 3){
                    $arrLevel[$level] += 1;    
                }else{
                    $arrLevel[$level] += $item->adults;
                }
                
            }
            if($item->tour_type == 3){
                $arrResult[$item->user_id][$item->tour_type] += 1;    
            }else{
                $arrResult[$item->user_id][$item->tour_type] += $item->adults;    
            }

        }
                         
        return view('report-guest.customer-by-level', compact('month', 'year', 'listUser', 'arrUser', 'time_type', 'arrSearch', 'arrResult', 'arrLevel', 'level', 'id_loaitru'));

    }
    public function ajaxSearchBen(Request $request){
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $arrSearch['id_search'] = $id_search = $request->id_search ? $request->id_search : null; 
         $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ? $request->nguoi_thu_tien : null;
         $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $arrSearch['tour_id'] = $tour_id = $request->tour_id ? $request->tour_id : 1;
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $query = Booking::where('type', 1)->where('status', '<', 3);
        //dd($id_search);
        if($id_search){
           //  dd($id_search);
            $id_search = strtolower($id_search);
            $id_search = str_replace("ptt", "", $id_search);
            $id_search = str_replace("pth", "", $id_search);
            $id_search = str_replace("ptv", "", $id_search);
            $arrSearch['id_search'] = $id_search;
            $items = $query->where('id', $id_search)->get();
        } 
                   
        $query->where('level', 7);
        $query->whereIn('tour_type', [1,2,3]);  

        $items = $query->paginate(1000);
        $arrResult = [];
        $listUser = User::where('level', 7)->where('status', 1)->get();
        $arrUser = [];
        foreach($listUser as $u){
            $arrUser[$u->id] = $u;
        } 
        $arrLevel = $arrTour = [];
        $tong_so_nguoi = $tong_phan_an = $tong_coc = 0;
        foreach($items as $item){
            $arrTour[$item->user_id][] = $item;
            if($item->status != 3){
                $tong_so_nguoi += $item->adults;
                if($item->nguoi_thu_coc == 1){                       
                    $tong_coc += $item->tien_coc; 
                }
                $tong_phan_an += $item->meals;
            }            

        }
     
        return view('report-guest.ajax-ben', compact('month', 'year', 'listUser', 'arrUser', 'time_type', 'arrSearch', 'arrResult', 'arrLevel', 'items', 'tong_so_nguoi', 'tong_phan_an', 'tong_coc', 'arrSearch'));

    }
    public function ben(Request $request){

        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $arrSearch['id_search'] = $id_search = $request->id_search ? $request->id_search : null; 
         $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ? $request->nguoi_thu_tien : null;
         $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $arrSearch['tour_id'] = $tour_id = $request->tour_id ? $request->tour_id : 1;
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $query = Booking::where('type', 1)->where('status', '<', 3);
        if($id_search){
           //  dd($id_search);
            $id_search = strtolower($id_search);
            $id_search = str_replace("ptt", "", $id_search);
            $id_search = str_replace("pth", "", $id_search);
            $id_search = str_replace("ptv", "", $id_search);
            $arrSearch['id_search'] = $id_search;
            $items = $query->where('id', $id_search)->get();
        } 
                        
              
        if($user_id){
            $arrSearch['user_id'] = $user_id;
            $query->where('user_id', $user_id);
        }    
        
        if($tour_id){
            $arrSearch['tour_id'] = $tour_id;
            $query->where('tour_id', $tour_id);            
        }
        if($nguoi_thu_tien){
                $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien;
                $query->where('nguoi_thu_tien', $nguoi_thu_tien);
            }
        $query->where('level', 7);
        $query->whereIn('tour_type', [1,2,3]);        
        
        if($time_type == 1){ // theo thangs
            $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
            $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));
          
            $query->where('use_date','>=', $mindate);                   
            $query->where('use_date', '<=', $maxdate);
        }elseif($time_type == 2){ // theo khoang ngay
            $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

            if($use_date_from){
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('use_date','>=', $use_date_from_format);
            }
            if($use_date_to){
                $arrSearch['use_date_to'] = $use_date_to;
                $tmpDate = explode('/', $use_date_to);
                $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($use_date_to_format < $use_date_from_format){
                    $arrSearch['use_date_to'] = $use_date_from;
                    $use_date_to_format = $use_date_from_format;   
                }        
                $query->where('use_date', '<=', $use_date_to_format);
            }
        }else{
            $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            
            $arrSearch['use_date_from'] = $use_date_from;
            $tmpDate = explode('/', $use_date_from);
            $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('use_date','=', $use_date_from_format);
        
        }

        $items = $query->paginate(1000);
        $arrResult = [];
        $listUser = User::where('level', 7)->where('status', 1)->get();
        $arrUser = [];
        foreach($listUser as $u){
            $arrUser[$u->id] = $u;
        } 
        $arrLevel = $arrTour = [];
        $tong_so_nguoi = $tong_phan_an = $tong_coc = 0;
        foreach($items as $item){
            $arrTour[$item->user_id][] = $item;
            if($item->status != 3){
                $tong_so_nguoi += $item->adults;
                if($item->nguoi_thu_coc == 1){                       
                    $tong_coc += $item->tien_coc; 
                }
                $tong_phan_an += $item->meals;
            }
        }
        $agent = new Agent();
        if($agent->isMobile()){          
           $view = 'report-guest.m-khach-ben'; 
        }else{
           $view = 'report-guest.khach-ben'; 
        }
        return view($view, compact('month', 'year', 'listUser', 'arrUser', 'time_type', 'arrSearch', 'arrResult', 'arrLevel', 'items', 'tong_so_nguoi', 'tong_phan_an', 'tong_coc', 'arrSearch'));

    }
    public function car(Request $request)
    {
       
        $arrSearch['chua_thuc_thu'] = $chua_thuc_thu = $request->chua_thuc_thu ?? null;
        $arrSearch['no_driver'] = $no_driver = $request->no_driver ? $request->no_driver : null;
        $arrSearch['sales'] = $sales = $request->sales ? $request->sales : null;   
        $arrSearch['keyword'] = $keyword = $request->keyword ? $request->keyword : null;
        $arrSearch['id_search'] = $id_search = $request->id_search ? $request->id_search : null; 
        $arrSearch['status'] = $status = $request->status ? $request->status : [1,2];
        $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;
        $arrSearch['driver_id'] = $driver_id = $request->driver_id ? $request->driver_id : null;        
        $arrSearch['email'] = $email = $request->email ? $request->email : null;
        $arrSearch['phone'] = $phone = $request->phone ? $request->phone : null;
        
        $arrSearch['sort_by'] = $sort_by = $request->sort_by ? $request->sort_by : 'created_at';
        $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ? $request->nguoi_thu_tien : null;
        $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc = $request->nguoi_thu_coc ? $request->nguoi_thu_coc : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;
        
        $use_df_default = Auth::user()->id == 151 ? date('d/m/Y', strtotime('yesterday')) : date('d/m/Y', time());
        $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
        $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
        $arrSearch['tour_id'] = $tour_id = $request->tour_id ? $request->tour_id : null;                 
        $query = Booking::where('type', 4);
        if(Auth::user()->id == 21){
            $status = 1;
        }
        if($keyword){
            $type = null;
        }
        if($keyword){
            if(strlen($keyword) <= 8){
                $id_search = $keyword;
            }else{
                $phone = $keyword;
            }
        }
        
        // if($ko_cap_treo > -1){
        //     $query->where('ko_cap_treo', $ko_cap_treo);
        // }
        if($id_search){
           //  dd($id_search);
            $id_search = strtolower($id_search);
            $id_search = str_replace("ptt", "", $id_search);
            $id_search = str_replace("pth", "", $id_search);
            $id_search = str_replace("ptv", "", $id_search);
            $arrSearch['id_search'] = $id_search;
            $query->where('id', $id_search);            
        }elseif($phone){
            $arrSearch['phone'] = $phone;
            $query->where('phone', $phone);            
        }else{
           
           
            
            
            $query->whereIn('status', [1,2]);            
            
            
            if($chua_thuc_thu == 1){                
                $query->where('tien_thuc_thu', 0);            
            }
         
            if($no_driver){
                $arrSearch['no_driver'] = $no_driver;
                $query->where('driver_id', 0);            
            }
            if($tour_id){
                $arrSearch['tour_id'] = $tour_id;
                $query->where('tour_id', $tour_id);            
            }
            
            if($phone){
                $arrSearch['phone'] = $phone;
                $query->where('phone', $phone);
            }           
           
           
            if($nguoi_thu_tien){
                $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien;
                $query->where('nguoi_thu_tien', $nguoi_thu_tien);
            }
            if($nguoi_thu_coc){
                $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc;
                $query->where('nguoi_thu_coc', $nguoi_thu_coc);
            }
           
           
            if(Auth::user()->role < 3){              
                if($user_id && $user_id > 0){
                    $arrSearch['user_id'] = $user_id;
                    $query->where('user_id', $user_id);
                }    
            }else{           
                $arrSearch['user_id'] = Auth::user()->id;
                $query->where('user_id', Auth::user()->id);
            }

          
            $month = $request->month ?? date('m');        
            $year = $request->year ?? date('Y'); 
            $mindate = "$year-$month-01";        
            $maxdate = date("Y-m-t", strtotime($mindate));
           
            if($time_type == 1){
                $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
                $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));
                          
                $query->where('use_date','>=', $mindate);                   
                $query->where('use_date', '<=', $maxdate);
            }elseif($time_type == 2){
                $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
                $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

                if($use_date_from){
                    $arrSearch['use_date_from'] = $use_date_from;
                    $tmpDate = explode('/', $use_date_from);
                    $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                    $query->where('use_date','>=', $use_date_from_format);
                }
                if($use_date_to){
                    $arrSearch['use_date_to'] = $use_date_to;
                    $tmpDate = explode('/', $use_date_to);
                    $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                    if($use_date_to_format < $use_date_from_format){
                        $arrSearch['use_date_to'] = $use_date_from;
                        $use_date_to_format = $use_date_from_format;   
                    }        
                    $query->where('use_date', '<=', $use_date_to_format);
                }
            }else{
                $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
                
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('use_date','=', $use_date_from_format);
            
            }
            
        }//end else
        
        if($driver_id){
            $query->where('driver_id', $driver_id);
        }
        
        if($sales == 1){
            $query->whereNotIn('user_id', [18,33]);
        }             
        
        
        $allList = $query->get();
              
        $items  = $query->paginate(400); 
       // dd($items);
        $tong_hoa_hong_cty = $tong_hoa_hong_sales = $tong_so_nguoi = $tong_phan_an = $tong_coc = $tong_phan_an_te = 0 ;
        $tong_thuc_thu = $tong_hoa_hong_chup = 0;
        $cap_nl = $cap_te = $tong_te =  0;       
        
        $listUser = User::whereIn('level', [1,2,3,4,5,6,7])->where('status', 1)->get();
        $hotelList = Hotels::all();        

        $agent = new Agent();
        
        

        $carCate = CarDish::all();
        
        //if(Auth::user()->id == 21){
        if($agent->isMobile()){
            $view = 'booking.m-index-car';
        }else{
            $view = 'booking.index-car';
            $view = 'report-guest.car';
        }
        
        $driverList = Drivers::where('status', 1)->get();
        $driverArrName = [];
        foreach($driverList as $dr){
            $driverArrName[$dr->id] = $dr->name;
        }
        $arrDriver = [];
        $t_chuyen = $t_tong = $t_cty = $t_sales = $t_tx = $t_dieuhanh = 0;
        foreach($items as $bk){
            if($bk->status != 3){
                $t_chuyen++;
                if(!isset($arrDriver[$bk->driver_id])){
                    $arrDriver[$bk->driver_id]['so_lan_chay'] = 0;
                    $arrDriver[$bk->driver_id]['tong_tien'] = 0;
                    $arrDriver[$bk->driver_id]['so_tien_tx_thu'] = 0;
                    $arrDriver[$bk->driver_id]['so_tien_sales_thu'] = 0;
                    $arrDriver[$bk->driver_id]['so_tien_cty_thu'] = 0;                    
                }
                $arrDriver[$bk->driver_id]['so_lan_chay']++;
                $arrDriver[$bk->driver_id]['tong_tien'] += $bk->total_price;
                $t_tong += $bk->total_price;
                if($bk->nguoi_thu_tien == 1){
                    $arrDriver[$bk->driver_id]['so_tien_sales_thu'] += $bk->total_price;
                    $t_sales += $bk->total_price;
                }elseif($bk->nguoi_thu_tien == 2){
                    $arrDriver[$bk->driver_id]['so_tien_cty_thu'] += $bk->total_price;
                    $t_cty += $bk->total_price;
                }elseif($bk->nguoi_thu_tien == 3){
                    $arrDriver[$bk->driver_id]['so_tien_tx_thu'] += $bk->total_price;
                    $t_tx += $bk->total_price;
                }elseif($bk->nguoi_thu_tien == 4){{
                    $arrDriver[$bk->driver_id]['so_tien_dieuhanh_thu'] += $bk->total_price;
                    $t_dieuhanh += $bk->total_price;
                }
            }
            
        }
        $type = 4;
        if($agent->isMobile()){
            $view = 'report-guest.m-car';
        }else{
            $view = 'report-guest.car';
        }
        return view($view, compact( 'items', 'arrSearch', 'type', 'listUser', 'carCate', 'keyword', 'tong_hoa_hong_sales', 'driverList', 'time_type', 'month', 'driverArrName', 'arrDriver', 't_chuyen', 't_tong', 't_sales', 't_tx', 't_cty', 't_dieuhanh'));
        }
        
    }
    public function cano(Request $request){
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));
        //$mindate = $maxdate = '2021-04-13';        
        $all = Booking::where('use_date', '>=', $mindate)->where('use_date', '<=', $maxdate)
        ->where('type', 1)->where('tour_id', 1)->whereIn('status', [1, 2])->get();
        $arrCanoUsed = [];
        $arrCanoCty = [9, 10, 11];
        $arrCanoCount = [];
        $countCano = [];
        foreach($all as $bk){
            $day = date('d', strtotime($bk->use_date));
            $day = str_pad($day, 2, "0", STR_PAD_LEFT);
            $key = $day.'-'.$bk->tour_type."-".$bk->cano_id."-".$bk->hdv_id;         

            if(!isset($arrCanoUsed[$day])){
                $arrCanoUsed[$day] = [];
            }
            if(!isset($arrCanoUsed[$key])){              
               // dd($bk->id, $bk->cano_id);
                $arrCanoUsed[$key] = $bk;
                if(!isset($countCano[$bk->cano_id])){                    
                    $countCano[$bk->cano_id] = 1;

                }else{
                    $countCano[$bk->cano_id]++;

                }
                $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['hdv_id'] = $bk->hdv_id;
                    $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['adults'] = $bk->adults;
                    $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['childs'] = $bk->childs;                
                    $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['cap_nl'] = $bk->cap_nl;
                    $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['cap_te'] = $bk->cap_te;
                    $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['meals'] = $bk->meals;
                    $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['meals_te'] = $bk->meals_te;
                // $arrCanoUsed[$key] = $bk;

                
            }else{
                $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['adults'] += $bk->adults;
                $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['childs'] += $bk->childs;
                $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['cap_nl'] += $bk->cap_nl;
                $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['cap_te'] += $bk->cap_te;
                $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['meals'] += $bk->meals;
                $arrCanoCount[$day][$bk->tour_type][$bk->cano_id]['meals_te'] += $bk->meals_te;                
            }
        }
        //dd($countCano);
        ksort($arrCanoCount);
       
        // chi phi
        $costAll = Cost::where('date_use', '>=', $mindate)->where('date_use', '<=', $maxdate)->where('status', 1)->whereIn('cate_id', [1, 2])->get();
        
        $arrCost = [];
        $tong_chi = 0;
        foreach($costAll as $costDay){            
            if($costDay->cate_id == 1 || $costDay->cate_id == 2 ){        
                if(!isset($arrCost[$costDay->partner_id])){
                    $arrCost[$costDay->partner_id]['amount'] = $costDay->amount;
                    $arrCost[$costDay->partner_id]['total_money'] = $costDay->total_money;
                }else{
                    $arrCost[$costDay->partner_id]['amount'] += $costDay->amount;
                    $arrCost[$costDay->partner_id]['total_money'] += $costDay->total_money;
                }            
            }
        }

       // dd($arrCost);
        $partnerArr = Partner::pluck('name', 'id');        
        $userArr = User::pluck('name', 'id');
        return view('report-guest.cano', compact('maxDay', 'arrCanoUsed', 'partnerArr', 'countCano', 'month', 'arrCost', 'year', 'arrCanoCty', 'arrCanoCount', 'userArr'));
    }
    public function canoDetail(Request $request){
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $cano_id = $request->cano_id ?? null;        
        $mindate = "$year-$month-01";
        
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));        
        // chi phi
        $costAll = Cost::where('date_use', '>=', $mindate)
                        ->where('date_use', '<=', $maxdate)
                        ->where('status', 1)
                        ->where('cate_id', 2)
                        ->where('partner_id', $cano_id)
                        ->orderBy('date_use', 'asc')
                        ->get();

        $allBooking = Booking::where('use_date', '>=', $mindate)->where('use_date', '<=', $maxdate)
        ->where('type', 1)->where('tour_id', 1)->where('status','<>',3)->where('cano_id', $cano_id)->get();
        
        foreach($allBooking as $bk){
            $day = date('d', strtotime($bk->use_date));
            $day = str_pad($day, 2, "0", STR_PAD_LEFT);
            $key = $day.'-'.$bk->tour_type."-".$bk->hdv_id;         

            if(!isset($arrCanoUsed[$day])){
                $arrCanoUsed[$day] = [];
            }
            if(!isset($arrCanoUsed[$key])){              
               // dd($bk->id, $bk->cano_id);
                $arrCanoUsed[$key] = $bk;
                if(!isset($countCano[$bk->cano_id])){                    
                    $countCano[$bk->cano_id] = 1;

                }else{
                    $countCano[$bk->cano_id]++;

                }
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['hdv_id'] = $bk->hdv_id;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['adults'] = $bk->adults;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['childs'] = $bk->childs;                
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['cap_nl'] = $bk->cap_nl;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['cap_te'] = $bk->cap_te;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['meals'] = $bk->meals;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['meals_te'] = $bk->meals_te;
                // $arrCanoUsed[$key] = $bk;

                
            }else{
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['adults'] += $bk->adults;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['childs'] += $bk->childs;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['cap_nl'] += $bk->cap_nl;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['cap_te'] += $bk->cap_te;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['meals'] += $bk->meals;
                $arrCanoCount[$day][$bk->hdv_id][$bk->tour_type]['meals_te'] += $bk->meals_te;                
            }
        }
        //dd($arrCanoCount);
        ksort($arrCanoCount);
        //dd($arrCanoCount);
        $arrCostByDay = [];
        $totalAmount = 0;
        foreach($costAll as $cost){
            //dd($cost);
            if(!isset($arrCostByDay[date('d', strtotime($cost->date_use))])){
                $arrCostByDay[date('d', strtotime($cost->date_use))] = $cost->amount;    
            }else{
                $arrCostByDay[date('d', strtotime($cost->date_use))] += $cost->amount;    
            }            
            $totalAmount += $cost->amount;
        }

       // dd($arrCostByDay);
        $arrCost = [];
        $tong_chi = 0;
        foreach($costAll as $costDay){
            if($costDay->cate_id == 1 || $costDay->cate_id == 2 ){        
                if(!isset($arrCost[$costDay->partner_id])){
                    $arrCost[$costDay->partner_id]['amount'] = $costDay->amount;
                    $arrCost[$costDay->partner_id]['total_money'] = $costDay->total_money;
                }else{
                    $arrCost[$costDay->partner_id]['amount'] += $costDay->amount;
                    $arrCost[$costDay->partner_id]['total_money'] += $costDay->total_money;
                }            
            }
        }

       // dd($arrCost);
        $partnerArr = Partner::pluck('name', 'id');        
        $canoDetail = Partner::find($cano_id);
        return view('report-guest.cano-detail', compact('maxDay', 'partnerArr', 'month', 'arrCost', 'year', 'arrCostByDay', 'totalAmount', 'canoDetail', 'arrCanoCount', 'cano_id'));
    }
    public function doanhthuthang(Request $request){       
        
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
            $tien_chi_5 = ($bk->total_price - $bk->discount - $bk->commision + $bk->tien_coc)*5/100;
            $arrDay[$key]['tong_chi_5'] += $tien_chi_5;
            $arrDay[$key]['tong_con_lai_chua_tru_chi_phi'] += $bk->total_price - $bk->discount - $chietkhau - $bk->tien_coc - $tien_chi_5;      

          
        }       
    
        $minDateFormat = date('d/m/Y', strtotime($mindate));
        $maxDateFormat = date('d/m/Y', strtotime($maxdate));
        $cateList = CostType::orderBy('display_order')->get();
        //dd($cateList);
        
        return view('report-guest.doanh-thu-thang', compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales', 'arrCost', 'tong_chi','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type', 'tong_chi_phi_van_hanh', 'tong_chi_phi_khac'));


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
        
        return view('report-guest.thong-ke-doi-tac', compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type'));


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
        
        return view('report-guest.thong-ke-bai-bien', compact('arrDay', 'tong_thuc_thu', 'tong_hoa_hong_sales','maxDay', 'minDateFormat', 'maxDateFormat', 'month', 'year', 'cateList', 'type'));


    }
}
