<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Booking;
use App\Models\Account;
use App\Models\Partner;
use App\Models\Ctv;
use App\Models\TourPrice;
use App\User;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use Helper, File, Session, Auth;

class DebtController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = Debt::find($id);   
        

        $model->update([$column => $value]);
    }
    public function report(Request $request)
    {
        //$rs = Helper::calTourPrice(1, 1, 2, 5, 5, '2022-07-21');

        $day = date('d');
        $month_do = date('m');
        $arrSearch['type'] = $type = $request->type ? $request->type : 1;  
        $arrSearch['user_id_manage'] = $arrSearch['user_id_manage'] = $user_id_manage = $request->user_id_manage ? $request->user_id_manage : null;   
        $arrSearch['id_search'] = $id_search = $request->id_search ? $request->id_search : null;                
        $arrSearch['level'] = $level = $request->level ? $request->level : null; 
        $arrSearch['sales'] = $sales = $request->sales ? $request->sales : null;        
        $arrSearch['status'] = $status = $request->status ? $request->status : [1,2];        
        $arrSearch['tour_id'] = $tour_id = $request->tour_id ? $request->tour_id : null;
        $arrSearch['tour_cate'] = $tour_cate = $request->tour_cate ?? null;
        $arrSearch['tour_type'] = $tour_type = $request->tour_type ?? [1,2,3];
        $arrSearch['user_id'] = $user_id = $request->user_id ?? null;        
        $arrSearch['ctv_id'] = $ctv_id = $request->ctv_id ?? null;        
        $arrSearch['sort_by'] = $sort_by = $request->sort_by ? $request->sort_by : 'booking.created_at';
        
        $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ?? 4;
        $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc = $request->nguoi_thu_coc ? $request->nguoi_thu_coc : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;
        $arrSearch['search_by'] = $search_by = $request->search_by ? $request->search_by : 2;
        $arrSearch['debt_type'] = $debt_type = $request->debt_type ?? null;
        if($type == 1){
            $use_df_default = Auth::user()->id == 151 ? date('d/m/Y', strtotime('yesterday')) : date('d/m/Y', time());
            $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
                 
        }
        $arrSearch['created_at'] = $created_at = $request->created_at ? $request->created_at :  null;

        $arrSearch['book_date'] = $book_date = $request->book_date ? $request->book_date :  null;
        $arrSearch['book_date_from'] = $book_date_from = $request->book_date_from ? $request->book_date_from :  null;
        
        $arrSearch['book_date_to'] = $book_date_to = $request->book_date_to ? $request->book_date_to : null;
        if($type == 2){
            $arrSearch['checkin_from'] = $checkin_from = $request->checkin_from ? $request->checkin_from : null;
            $arrSearch['checkin_to'] = $checkin_to = $request->checkin_to ? $request->checkin_to : $checkin_from;  

            $arrSearch['checkout_from'] = $checkout_from = $request->checkout_from ? $request->checkout_from : null;
            $arrSearch['checkout_to'] = $checkout_to = $request->checkout_to ? $request->checkout_to : null;    
        }
        
        $query = Booking::where('type', $type);
        $query->where('booking.city_id', 1);
      
        $arrSearch['month'] = $month = $request->month ?? date('m');        
        $arrSearch['year'] = $year = $request->year ?? date('Y'); ;
        $mindate = "$year-$month-01";        
        $maxdate = date("Y-m-t", strtotime($mindate));
        
        if($id_search){
           //  dd($id_search);
            $id_search = strtolower($id_search);
            $id_search = str_replace("ptt", "", $id_search);
            $id_search = str_replace("pth", "", $id_search);
            $id_search = str_replace("ptv", "", $id_search);
            $id_search = str_replace("ptx", "", $id_search);
            $id_search = str_replace("ptc", "", $id_search);
            $arrSearch['id_search'] = $id_search;
            $query->where('id', $id_search);            
        }else{
            if($debt_type){
                $arrSearch['debt_type'] = $debt_type;
                $query->join('users', 'users.id', '=', 'booking.user_id')  
                ->where('users.debt_type', $debt_type);
            }
            if($status){

                $arrSearch['status'] = $status;
                $query->whereIn('booking.status', $status);            
            }
          
            if($tour_id){
                $arrSearch['tour_id'] = $tour_id;
                $query->where('tour_id', $tour_id);            
            }
            if($tour_cate){
                $arrSearch['tour_cate'] = $tour_cate;
                $query->where('tour_cate', $tour_cate);
            }
            if($tour_type && $type == 1){
                $arrSearch['tour_type'] = $tour_type;
                $query->whereIn('tour_type', $tour_type);            
            }
            
            if($user_id_manage){             
                $query->where('booking.user_id_manage', $user_id_manage);            
            }       
            
            if($nguoi_thu_tien){
                $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien;
                $query->where('nguoi_thu_tien', $nguoi_thu_tien);
            }
            if($nguoi_thu_coc){
                $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc;
                $query->where('nguoi_thu_coc', $nguoi_thu_coc);
            }
            if($level && $type == 1){
                $arrSearch['level'] = $level;
                if(!$debt_type){
                    $query->join('users', 'users.id', '=', 'booking.user_id') 
                    ->where('users.level', $level);
                }else{
                    $query->where('users.level', $level);
                }
            }       
            if(Auth::user()->id == 333){
                $level = 7;
                $arrSearch['level'] = $level;
                $query->where('level', $level);
            }        
          
            if($user_id && $user_id > 0){
                $arrSearch['user_id'] = $user_id;
                $query->where('user_id', $user_id);
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
                $day = $tmpDate[0];
                $month_do = $tmpDate[1];
            }
                        
           
            if($ctv_id){
                $query->where('ctv_id', $ctv_id);
            }
            

        
        }    
        
        $query->orderBy($sort_by, 'desc');
        $allList = $query->select('booking.*', 'booking.id as booking_id')->get();        
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
              
        $items  = $query->paginate(300); 
       // dd($items);
        $tong_hoa_hong_cty = $tong_hoa_hong_sales = $tong_so_nguoi = $tong_phan_an = $tong_coc = $tong_phan_an_te = 0 ;
        $tong_thuc_thu = $tong_hoa_hong_chup =  0; 
        $cap_nl = $cap_te = $tong_te =  0;
       
        $tong_hdv_thu = $tong_thao_thu = 0;

        $listUser = User::whereIn('level', [2,7])->where('status', 1)->get();
       
        $agent = new Agent();
        if($level){
            $listUser = User::where('level', $level)->where('status', 1)->get();
        }
        $arrUser = [];
        foreach($listUser as $u){
            $arrUser[$u->id] = $u;
        } 
      
        $userArr = [];
        $ghep = $vip = $thue = $tong_vip= 0;
        if($allList->count() > 0){
            
            foreach($allList as $bk){
                if($bk->tour_type == 1){
                    $ghep += $bk->adults;
                }elseif($bk->tour_type == 2){
                    $vip++;
                    $tong_vip += $bk->adults;
                }elseif($bk->tour_type == 3){
                    $thue++;
                }
                $userArr[$bk->user_id] = $bk->user_id;            
                if($bk->status < 3){
                                      
                    $tong_so_nguoi += $bk->adults;
                    $tong_te += $bk->childs;
                    if($bk->nguoi_thu_coc == 1){                       
                        $tong_coc += $bk->tien_coc; 
                    }
                    $tong_phan_an += $bk->meals;
                    $tong_phan_an_te += $bk->meals_te;
                    $tong_thuc_thu += $bk->tien_thuc_thu;
                    if($bk->nguoi_thu_tien == 3){
                        $tong_hdv_thu += $bk->tien_thuc_thu;    
                    }elseif($bk->nguoi_thu_tien == 5){
                        $tong_thao_thu += $bk->tien_thuc_thu;
                    }
                    $cap_nl += $bk->cap_nl;
                    $cap_te += $bk->cap_te; 
                    $tong_hoa_hong_cty += $bk->hoa_hong_cty;
                    $tong_hoa_hong_sales += $bk->hoa_hong_sales;                    
                    $tong_hoa_hong_chup += $bk->hoa_hong_chup;               
                }                  
                
                //update level                
                if(isset($arrUser[$bk->user_id]) && $arrUser[$bk->user_id]->level != $bk->level){
                    $bk->update(['level' => $arrUser[$bk->user_id]->level]);    
                }                
            }    
        }            
    
                  
            if($agent->isMobile()){
                $view = 'debt.m-report';
            }else{
               
                $view = 'debt.report';    
               
                
            }
            $listHDV = User::where('hdv', 1)->where('status', 1)->get(); 
          
            $canoList = Partner::where('cano', 1)->get();                     
            // cal doanh so doi tac
            $arrDs = [];
            if($time_type == 1){
                foreach($items as $item){
                    if(in_array($item->tour_type, [1, 2]) && !in_array($item->level, [1, 5]) ){
                        if(!isset($arrDs[$item->user_id])){
                            $arrDs[$item->user_id] = $item->adults;
                        }else{
                            $arrDs[$item->user_id] += $item->adults;
                        }
                    }
                }                
            }
            
            return view($view, compact( 'items', 'arrSearch', 'type', 'listUser', 'tong_so_nguoi', 'tong_hoa_hong_sales', 'tong_hoa_hong_cty', 'tong_phan_an', 'tong_coc', 'tong_thuc_thu', 'level', 'cap_nl', 'cap_te', 'tong_te', 'arrUser', 'listHDV', 'tong_phan_an_te', 'tong_hdv_thu', 'canoList', 'time_type', 'month', 'year', 'arrDs', 'day', 'tong_thao_thu','month_do', 'ctvList', 'ghep', 'vip', 'thue', 'tong_vip', 'debt_type'));
        
    }
    public function export(Request $request)
    {
        //$rs = Helper::calTourPrice(1, 1, 2, 5, 5, '2022-07-21');

        $day = date('d');
        $month_do = date('m');
        $arrSearch['type'] = $type = $request->type ? $request->type : 1;  
        $arrSearch['user_id_manage'] = $arrSearch['user_id_manage'] = $user_id_manage = $request->user_id_manage ? $request->user_id_manage : null;   
        $arrSearch['id_search'] = $id_search = $request->id_search ? $request->id_search : null;                
        $arrSearch['level'] = $level = $request->level ? $request->level : null; 
        $arrSearch['sales'] = $sales = $request->sales ? $request->sales : null;        
        $arrSearch['status'] = $status = $request->status ? $request->status : [1,2];        
        $arrSearch['tour_id'] = $tour_id = $request->tour_id ? $request->tour_id : null;
        $arrSearch['tour_cate'] = $tour_cate = $request->tour_cate ?? null;
        $arrSearch['tour_type'] = $tour_type = $request->tour_type ?? [1,2,3];
        $arrSearch['user_id'] = $user_id = $request->user_id ?? null;        
        $arrSearch['ctv_id'] = $ctv_id = $request->ctv_id ?? null;        
        $arrSearch['sort_by'] = $sort_by = $request->sort_by ? $request->sort_by : 'booking.created_at';
        
        $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ?? 4;
        $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc = $request->nguoi_thu_coc ? $request->nguoi_thu_coc : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;
        $arrSearch['search_by'] = $search_by = $request->search_by ? $request->search_by : 2;
        $arrSearch['debt_type'] = $debt_type = $request->debt_type ?? null;
        if($type == 1){
            $use_df_default = Auth::user()->id == 151 ? date('d/m/Y', strtotime('yesterday')) : date('d/m/Y', time());
            $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
                 
        }
        $arrSearch['created_at'] = $created_at = $request->created_at ? $request->created_at :  null;

        $arrSearch['book_date'] = $book_date = $request->book_date ? $request->book_date :  null;
        $arrSearch['book_date_from'] = $book_date_from = $request->book_date_from ? $request->book_date_from :  null;
        
        $arrSearch['book_date_to'] = $book_date_to = $request->book_date_to ? $request->book_date_to : null;
        if($type == 2){
            $arrSearch['checkin_from'] = $checkin_from = $request->checkin_from ? $request->checkin_from : null;
            $arrSearch['checkin_to'] = $checkin_to = $request->checkin_to ? $request->checkin_to : $checkin_from;  

            $arrSearch['checkout_from'] = $checkout_from = $request->checkout_from ? $request->checkout_from : null;
            $arrSearch['checkout_to'] = $checkout_to = $request->checkout_to ? $request->checkout_to : null;    
        }
        
        $query = Booking::where('type', $type);
        $query->where('booking.city_id', 1);
      
        $arrSearch['month'] = $month = $request->month ?? date('m');        
        $arrSearch['year'] = $year = $request->year ?? date('Y'); ;
        $mindate = "$year-$month-01";        
        $maxdate = date("Y-m-t", strtotime($mindate));
        
        if($id_search){
           //  dd($id_search);
            $id_search = strtolower($id_search);
            $id_search = str_replace("ptt", "", $id_search);
            $id_search = str_replace("pth", "", $id_search);
            $id_search = str_replace("ptv", "", $id_search);
            $id_search = str_replace("ptx", "", $id_search);
            $id_search = str_replace("ptc", "", $id_search);
            $arrSearch['id_search'] = $id_search;
            $query->where('id', $id_search);            
        }else{
            if($debt_type){
                $arrSearch['debt_type'] = $debt_type;
                $query->join('users', 'users.id', '=', 'booking.user_id')  
                ->where('users.debt_type', $debt_type);
            }
            if($status){

                $arrSearch['status'] = $status;
                $query->whereIn('booking.status', $status);            
            }
          
            if($tour_id){
                $arrSearch['tour_id'] = $tour_id;
                $query->where('tour_id', $tour_id);            
            }
            if($tour_cate){
                $arrSearch['tour_cate'] = $tour_cate;
                $query->where('tour_cate', $tour_cate);
            }
            if($tour_type && $type == 1){
                $arrSearch['tour_type'] = $tour_type;
                $query->whereIn('tour_type', $tour_type);            
            }
            
            if($user_id_manage){             
                $query->where('booking.user_id_manage', $user_id_manage);            
            }       
            
            if($nguoi_thu_tien){
                $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien;
                $query->where('nguoi_thu_tien', $nguoi_thu_tien);
            }
            if($nguoi_thu_coc){
                $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc;
                $query->where('nguoi_thu_coc', $nguoi_thu_coc);
            }
            if($level && $type == 1){
                $arrSearch['level'] = $level;
                if(!$debt_type){
                    $query->join('users', 'users.id', '=', 'booking.user_id') 
                    ->where('users.level', $level);
                }else{
                    $query->where('users.level', $level);
                }
            }       
            if(Auth::user()->id == 333){
                $level = 7;
                $arrSearch['level'] = $level;
                $query->where('level', $level);
            }        
          
            if($user_id && $user_id > 0){
                $arrSearch['user_id'] = $user_id;
                $query->where('user_id', $user_id);
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
                $day = $tmpDate[0];
                $month_do = $tmpDate[1];
            }
                        
           
            if($ctv_id){
                $query->where('ctv_id', $ctv_id);
            }
            

        
        }    
        
        $query->orderBy('tour_id', 'asc')->orderBy('tour_type', 'desc');
               
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
              
        $items  = $query->get(); 

        $listUser = User::whereIn('level', [2,7])->where('status', 1)->get();
       
        $agent = new Agent();
        if($level){
            $listUser = User::where('level', $level)->where('status', 1)->get();
        }
        $arrUser = [];
        foreach($listUser as $u){
            $arrUser[$u->id] = $u;
        } 
      
        $userArr = [];

        $listHDV = User::where('hdv', 1)->where('status', 1)->get(); 
      
        $canoList = Partner::where('cano', 1)->get();                     
        // cal doanh so doi tac
        $arrDs = [];
        if($time_type == 1){
            foreach($items as $item){
                if(in_array($item->tour_type, [1, 2]) && !in_array($item->level, [1, 5]) ){
                    if(!isset($arrDs[$item->user_id])){
                        $arrDs[$item->user_id] = $item->adults;
                    }else{
                        $arrDs[$item->user_id] += $item->adults;
                    }
                }
            }                
        }
        
        $contents[] = [
            'STT' => 'STT',
            'CODE' => 'CODE',
            'Tên KH' => 'Tên KH',
            'Ngày đi' => 'Ngày đi',            
            'NL/TE' => 'NL/TE',           
            'ĂN NL/TE' => 'ĂN NL/TE ',
            'Cáp NL/TE' => 'Cáp NL/TE',
            'Cọc' => 'Cọc' ,
            'Tổng tiền' => 'Tổng tiền',
            'Phụ thu' => 'Phụ thu',
            'Giảm' => 'Giảm',           
            'Còn lại' => 'Còn lại'
            
        ]; 
        $i = $tong_cong_no = 0;
        $total_adults = $total_childs = $total_meals = $total_meals_te = $total_cap_nl = $total_cap_te = 0;
        $total_extra_fee = $total_discount = $total_coc = $total_tong_tien = 0;
        foreach ($items as $item) {
            $i++;
            $tong_tien = $cong_no = 0;
          $rsPrice = Helper::calTourPrice($item->tour_id, $item->tour_type, $item->level, $item->adults, $item->childs, $item->use_date);
         
          if(!empty($rsPrice)){
            if($item->tour_type == 3){
                $tong_tien = $rsPrice['price'] + $item->meals*$rsPrice['meals'] + $item->meals_te*$rsPrice['meals_te'] 
                + $item->cap_nl*$rsPrice['cap_nl'] + $item->cap_te*$rsPrice['cap_te'] + $rsPrice['extra_fee'];
                $cong_no = $tong_tien + $item->extra_fee - $item->discount;
            }else{
                $tong_tien =  $item->adults*$rsPrice['price'] + $item->childs*$rsPrice['price_child'] + $item->meals*$rsPrice['meals'] + $item->meals_te*$rsPrice['meals_te'] 
                + $item->cap_nl*$rsPrice['cap_nl'] + $item->cap_te*$rsPrice['cap_te'] ;
                $cong_no = $tong_tien + $item->extra_fee - $item->discount;
            }
          }  
          $tong_cong_no += $cong_no;
          $total_adults += $item->adults;
          $total_childs += $item->childs;
          $total_meals += $item->meals;
          $total_meals_te += $item->meals_te;
          $total_cap_nl += $item->cap_nl;
          $total_cap_te += $item->cap_te;
          $total_coc += $item->tien_coc;
          $total_tong_tien += $tong_tien;
          $total_extra_fee += $item->extra_fee;
          $total_discount += $item->discount;
            $contents[] = [
                'STT' => $i,
                'CODE' => 'PTT'.$item->id,
                'Tên KH' => $item->name,
                'Ngày đi' => date('d/m', strtotime($item->use_date)),               
                'NL/TE' => $item->adults.'/'.$item->childs,               
                'Ăn NL/TE' => $item->meals.'/'.$item->meals_te,
                'Cáp NL/TE' => $item->cap_nl.'/'.$item->cap_te,
                'Cọc' => $item->tien_coc ? number_format($item->tien_coc) : "-",
                'Tổng tiền' => number_format($tong_tien),
                'Phụ thu' => $item->extra_fee ? number_format($item->extra_fee) : "-",
                'Giảm giá' => $item->discount ? number_format($item->discount) : "-",
                'Còn lại' => $cong_no ? number_format($cong_no) : '-'
            ];   
                       
        }
        $contents[] = [
                'STT' => '',
                'CODE' => '',
                'Tên KH' => '',
                'Ngày đi' => '',             
                'NL/TE' => $total_adults.'/'.$total_childs,         
                'Ăn NL/TE' => $total_meals.'/'.$total_meals_te,
                'Cáp NL/TE' => $total_cap_nl.'/'.$total_cap_te,
                'Cọc' => $total_coc ? number_format($total_coc) : "-",
                'Tổng tiền' => $total_tong_tien ? number_format($total_tong_tien) : "-",
                'Phụ thu' => $total_extra_fee ? number_format($total_extra_fee) : "-",
                'Giảm giá' => $total_discount ? number_format($total_discount) : "-",
                'Còn lại' => number_format($tong_cong_no)
            ];  
        if(!empty($contents)){
            try{
                $filename = 'PTT-Cong-No-'.date('dmhis', time());
                Excel::create($filename, function ($excel) use ($contents, $filename) {
                    // Set sheets
                    $excel->sheet($filename, function ($sheet) use ($contents) {
                        $sheet->fromArray($contents, null, 'A1', false, false);
                    });
                })->download('xls');
            }catch(\Exception $ex){
                dd($ex);
            }
        }
        
    }
    public function index(Request $request)
    {     
        
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y')-1;
        $type = $request->type ?? 1;        
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        $content = $request->content ?? null;
        $nguoi_thu_tien = $request->nguoi_thu_tien ?? null;       
        $city_id = $request->city_id ? $request->city_id : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;
        $query = Debt::where('status', 1);

        if($nguoi_thu_tien){
            $query->where('nguoi_thu_tien', $nguoi_thu_tien);
        }
        if($city_id){
            $query->where('city_id', $city_id);
        }
        if($content){
            $query->where('content', 'LIKE', '%'.$content.'%');
        }
        if($time_type == 1){
            $arrSearch['pay_date_from'] = $pay_date_from = $date_use = date('d/m/Y', strtotime($mindate));
            $arrSearch['pay_date_to'] = $pay_date_to = date('d/m/Y', strtotime($maxdate));
                      
            $query->where('pay_date','>=', $mindate);                   
            $query->where('pay_date', '<=', $maxdate);
        }elseif($time_type == 2){
            $arrSearch['pay_date_from'] = $pay_date_from = $date_use = $request->pay_date_from ? $request->pay_date_from : date('d/m/Y', time());
            $arrSearch['pay_date_to'] = $pay_date_to = $request->pay_date_to ? $request->pay_date_to : $pay_date_from;

            if($pay_date_from){
                $arrSearch['pay_date_from'] = $pay_date_from;
                $tmpDate = explode('/', $pay_date_from);
                $pay_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('pay_date','>=', $pay_date_from_format);
            }
            if($pay_date_to){
                $arrSearch['pay_date_to'] = $pay_date_to;
                $tmpDate = explode('/', $pay_date_to);
                $pay_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($pay_date_to_format < $pay_date_from_format){
                    $arrSearch['pay_date_to'] = $pay_date_from;
                    $pay_date_to_format = $pay_date_from_format;   
                }        
                $query->where('pay_date', '<=', $pay_date_to_format);
            }
        }else{
            $arrSearch['pay_date_from'] = $pay_date_from = $arrSearch['pay_date_to'] = $pay_date_to = $date_use = $request->pay_date_from ? $request->pay_date_from : date('d/m/Y', time());
            
            $arrSearch['pay_date_from'] = $pay_date_from;
            $tmpDate = explode('/', $pay_date_from);
            $pay_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('pay_date','=', $pay_date_from_format);

        }
       
        $items = $query->orderBy('id', 'desc')->paginate(50);
        $totalDebt = 0;
        foreach($items as $item){
            $totalDebt+= $item->amount;
        }
       
        return view('debt.index', compact( 'items', 'content', 'nguoi_thu_tien', 'arrSearch', 'month', 'city_id', 'totalDebt', 'time_type', 'year'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $nguoi_thu_tien = $request->nguoi_thu_tien ?? null;     
        $back_url = $request->back_url ?? null;
        return view('debt.create', compact('nguoi_thu_tien', 'back_url'));
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  Request  $request
    * @return Response
    */
    public function store(Request $request)
    {
        $dataArr = $request->all();        
       
        $this->validate($request,[   
            'amount' => 'required',
            //'nguoi_thu_tien' => 'required',
            'pay_date' => 'required',

        ],
        [  
            'amount.required' => 'Bạn chưa nhập số tiền',
            //'nguoi_thu_tien.required' => 'Bạn chưa chọn người thu tiền',
            'pay_date.required' => 'Bạn chưa nhập ngày chuyển tiền',
        ]);       
        $pay_date = explode('/', $dataArr['pay_date']);
        $dataArr['pay_date'] = $pay_date[2]."-".$pay_date[1]."-".$pay_date[0];
        $dataArr['amount'] = str_replace(",", "", $dataArr['amount']);

        if($dataArr['image_url'] && $dataArr['image_name']){
            
            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);
            
            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);
            
            $dataArr['image_url'] = $destionation;
        }        

        $rs = Debt::create($dataArr);

        Session::flash('message', 'Tạo mới thành công');
        $month = date('m', strtotime($dataArr['pay_date']));
        return redirect()->route('debt.index', [ 'city_id' => $dataArr['city_id'], 'month' => $month]);
    }

   
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return Response
    */
    public function show($id)
    {
    //
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return Response
    */
    public function edit($id)
    {        
        $detail = Debt::find($id);
        return view('debt.edit', compact( 'detail'));
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  Request  $request
    * @param  int  $id
    * @return Response
    */
    public function update(Request $request)
    {
        $dataArr = $request->all();
        
         $this->validate($request,[   
            'amount' => 'required',
            //'nguoi_thu_tien' => 'required',
            'pay_date' => 'required',

        ],
        [  
            'amount.required' => 'Bạn chưa nhập số tiền',
            //'nguoi_thu_tien.required' => 'Bạn chưa chọn người thu tiền',
            'pay_date.required' => 'Bạn chưa nhập ngày',
        ]);       
        $pay_date = explode('/', $dataArr['pay_date']);
        $dataArr['pay_date'] = $pay_date[2]."-".$pay_date[1]."-".$pay_date[0];
        $dataArr['amount'] = str_replace(",", "", $dataArr['amount']);

        if($dataArr['image_url'] && $dataArr['image_name']){
            
            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);
            
            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);
            
            $dataArr['image_url'] = $destionation;
        }
        
        $model = Debt::find($dataArr['id']);  

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');
        $month = date('m', strtotime($dataArr['pay_date']));
        return redirect()->route('debt.index', [ 'city_id' => $dataArr['city_id'], 'month' => $month]);
    }    
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    public function destroy($id)
    {
        // delete
        $model = Debt::find($id);
        $model->update(['status' => 0]);

        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('debt.index', ['nguoi_thu_tien' => $model->nguoi_thu_tien]);
    }
}