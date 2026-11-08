<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\Hotels;
use App\Models\Branch;
use App\Models\BookingLogs;
use App\Models\TicketTypeSystem;
use App\Models\Tickets;
use App\Models\Partner;
use App\Models\BookingCustomer;
use Carbon\Carbon;
use App\Models\Location;
use App\User;
use App\Models\Settings;
use Helper, File, Session, Auth, Image, Hash;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{

    private $_minDateKey = 0;
    private $_maxDateKey = 1;

    public function congnoTour(Request $request)
    {
        $month_do = date('m');
        $userLogin = Auth::user();
        if($userLogin->id == 519){
            return redirect()->route('booking-bbc.index');
        }
        $arrSearch['id_search'] = $id_search = $request->id_search ? $request->id_search : null;
        $arrSearch['status'] = $status = $request->status ? $request->status : [1,2,4];
        $arrSearch['da_thu'] = $da_thu = $request->da_thu > -1 ? $request->da_thu : -1;
        $arrSearch['xe_4t'] = $xe_4t = $request->xe_4t ?? null;
        $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;
        $arrSearch['branch_id'] = $branch_id = $request->branch_id ? $request->branch_id : null;
        $arrSearch['branch_ids'] = $branch_ids = $request->branch_ids ? $request->branch_ids : [];      
        $arrSearch['nguoi_tu_van'] = $nguoi_tu_van = $request->nguoi_tu_van ?? null;
        $arrSearch['partner_id'] = $partner_id = $request->partner_id ?? null;
        $arrSearch['hdv_id'] = $hdv_id = $request->hdv_id ?? null;
        $arrSearch['phone'] = $phone = $request->phone ? $request->phone : null;
        $arrSearch['not_john'] = $not_john = $request->not_john ?? null;
        $arrSearch['per_com'] = $per_com = $request->per_com ? $request->per_com : null;

        $arrSearch['sort_by'] = $sort_by = $request->sort_by ? $request->sort_by : 'created_at';

        $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ? $request->nguoi_thu_tien : null;
        $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc = $request->nguoi_thu_coc ? $request->nguoi_thu_coc : null;
        if($userLogin->role == 3){
            $defaultTimeType = 1;
        }else{
            $defaultTimeType = 3;
        }
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : $defaultTimeType;
        $arrSearch['search_by'] = $search_by = $request->search_by ? $request->search_by : 2;

        $use_df_default = $userLogin->id == 151 ? date('d/m/Y', strtotime('yesterday')) : date('d/m/Y', time());
        $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
        $arrSearch['keyword'] = $keyword = $request->keyword ? $request->keyword : null;

        $arrSearch['created_at'] = $created_at = $request->created_at ? $request->created_at :  null;
        
        
        $query = Booking::where('city_id', 1);             

        if($keyword){
            if(strlen($keyword) <= 8){
                $id_search = $keyword;
            }else{
                $phone = $keyword;
            }
        }
        $arrSearch['month'] = $month = $request->month ?? date('m');
        $arrSearch['is_drink'] = $is_drink = $request->is_drink ?? null;
        $arrSearch['year'] = $year = $request->year ?? date('Y'); ;
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        // if($ko_cap_treo > -1){
        //     $query->where('ko_cap_treo', $ko_cap_treo);
        // }
        $use_date_from_format = $use_date_to_format = null;
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
        }elseif($phone){
            $arrSearch['phone'] = $phone;
            $query->where('phone', $phone);
        }else{
           
            if($status){
                $query->whereIn('status', $status);
            }
            
          
            // if($branch_id){
            //     $query->where('branch_id', $branch_id);
            // }
            if($partner_id){
                $query->where('partner_id', $partner_id);
            }
            if($userLogin->branch_id == 1 || $userLogin->id == 494){
                $branch_ids = [7];
            }
            if($branch_ids){
              $query->whereIn('branch_id', $branch_ids);
            }

            if($nguoi_thu_tien){
                $query->where('nguoi_thu_tien', $nguoi_thu_tien);
            }
            if($hdv_id){
                $query->where('hdv_id', $hdv_id);
            }
            if($nguoi_thu_coc){
                $query->where('nguoi_thu_coc', $nguoi_thu_coc);
            }
            if($is_drink == 1){
                $query->whereHas('details', function ($q) {
                    $q->where('is_drink', 1);
                });
            }
            

            if($userLogin->role == 1 || $userLogin->role == 2){
                if($user_id && $user_id > 0){
                    $arrSearch['user_id'] = $user_id;
                    $query->where('user_id', $user_id);
                }
            }elseif($userLogin->role == 4){
                $arrSearch['branch_id'] = $branch_id = Auth::user()->branch_id;
                $query->where('branch_id', $branch_id);

            }else{
                $arrSearch['user_id'] = Auth::user()->id;
                $query->where(function ($query) {
                    $query->where('user_id', '=', Auth::user()->id)
                          ->orWhere('partner_id', '=', Auth::user()->id);
                });
            }

            if($created_at){
                $tmpDate = explode('/', $created_at);
                $created_at_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                $query->where('created_at','>=', $created_at_format." 00:00:00");
                $query->where('created_at','<=', $created_at_format." 23:59:59");
            }else{
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

                    $tmpDate = explode('/', $use_date_from);
                    $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                    $query->where('use_date','=', $use_date_from_format);
                    $day = $tmpDate[0];
                    $month_do = $tmpDate[1];
                   
                }
            }
        }//end else
        // lay danh sach doi tac book phong


        $query->orderBy('use_date', 'asc');      

        $branchArr = [];
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        foreach($branchList as $beach){
            $branchArr[$beach->id] = $beach->name;
        }
        $collecterNameArr = Helper::getCollecterNameArr();
        $items = $query->get();
       // dd($items);
         $i = 0;
         if(Auth::user()->role == 1){
            
            // export tour
            $contents[] = [
                'NO.' => 'NO.',
                'BRANCH' => 'BRANCH',
                'DATE' => 'DATE',
                'SUBTOTAL' => 'SUBTOTAL',                   
                'DISCOUNT' => 'DISCOUNT',
                'TOTAL' => 'TOTAL',      
                'RUPEES' => 'RUPEES',                    
                'RECEIVER' => 'RECEIVER',
                'NOTES' => 'NOTES'

            ];
            foreach ($items as $item) {
                $i++;              
                
                $notes = "";
                $notes .= $item->notes; 

                $contents[] = [
                    'NO.' => $i,
                    'BRANCH' => $branchArr[$item->branch_id],
                    'DATE' => date('d/m', strtotime($item->use_date)),
                    'SUBTOTAL' => $item->total_price,                   
                    'DISCOUNT' => $item->discount,
                    'TOTAL' => $item->con_lai,
                    'RUPEES' => $item->rupees,
                    'RECEIVER' => $collecterNameArr[$item->nguoi_thu_tien],
                    'NOTES' => $notes
                ];

            


        }//else
        }


       // dd($contents);
        if(count($contents) > 0){
            try{

                $str_file_name = 'Sandip_booking';

                Excel::create($str_file_name, function ($excel) use ($contents) {
                  //  dd('111');
                // Set sheets
                $excel->sheet('KH', function ($sheet) use ($contents) {
                    $sheet->fromArray($contents, null, 'A1', false, false);
                });
            })->download('xlsx');
            }catch(\Exception $ex){
                dd($ex->getMessage());
            }

        }

    }
   
    

    
}
