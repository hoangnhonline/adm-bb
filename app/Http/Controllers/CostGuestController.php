<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Models\CostDetail;
use App\Models\CostType;
use Jenssegers\Agent\Agent;
use App\Models\Partner;
use App\Models\BankInfo;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;
use App\User;
use Helper, File, Session, Auth, Image, Hash;

class CostGuestController extends Controller
{

    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = Cost::find($id);
        $model->update([$column => $value]);
    }
    public function ajaxGetCostType(Request $request){
        $type = $request->type;
        $list = CostType::where('type', $type)->get();
        return view('cost.ajax-cost-type', compact('list'));
    }
    public function index(Request $request)
    {

        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));

        $arrSearch['type'] = $type = $request->type ?? -1;

        $arrSearch['cate_id'] = $cate_id = $request->cate_id ? $request->cate_id : null;
        $arrSearch['branch_ids'] = $branch_ids = $request->branch_ids ? $request->branch_ids : [];
        $arrSearch['xe_4t'] = $xe_4t = 0;
        $arrSearch['city_id'] = $city_id = 1;
        $arrSearch['partner_id'] = $partner_id = $request->partner_id ? $request->partner_id : null;
        $arrSearch['nguoi_chi'] = $nguoi_chi = $request->nguoi_chi ? $request->nguoi_chi : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $arrSearch['is_fixed'] = $is_fixed = $request->is_fixed ?? null;
        $arrSearch['hoang_the'] = $hoang_the = $request->hoang_the ?? null;
        $content = $request->content ? $request->content : null;
        $arrSearch['status'] = $status = $request->status ?? null;
        $arrSearch['id_search'] = $id_search = $request->id_search ?? null;
        $arrSearch['branch_id'] = $branch_id = $request->branch_id ? $request->branch_id : null;
        $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
        $query = Cost::where('status', '>', 0);
        $partnerList = (object) [];
        if($id_search){
           //  dd($id_search);
            $id_search = strtolower($id_search);
            $id_search = str_replace("cp", "", $id_search);
            $arrSearch['id_search'] = $id_search;
            $query->where('id', $id_search);
        }else{
            if($nguoi_chi){
            $query->where('nguoi_chi', $nguoi_chi);
        }
        if($city_id){
            $query->where('city_id', $city_id);
        }
        
            $query->where('xe_4t', 0);
        
        if($status){
                $query->where('status', $status);
            }
        if($type > 0){
            $query->where('type', $type);
        }
        if($partner_id){
            $query->where('partner_id', $partner_id);
        }
         // if($branch_id){
         //        $arrSearch['branch_id'] = $branch_id;
         //        $query->where('branch_id', $branch_id);
         //    }
        if($branch_ids){
          $query->whereIn('branch_id', $branch_ids);
        }
        if($cate_id){
            $query->where('cate_id', $cate_id);
            $partnerList = Partner::where('cost_type_id', $cate_id)->get();
        }


        if($time_type == 1){
            $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
            $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));

            $query->where('date_use','>=', $mindate);
            $query->where('date_use', '<=', $maxdate);
        }elseif($time_type == 2){
            $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

            if($use_date_from){
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                $query->where('date_use','>=', $use_date_from_format);
            }
            if($use_date_to){
                $arrSearch['use_date_to'] = $use_date_to;
                $tmpDate = explode('/', $use_date_to);
                $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                if($use_date_to_format < $use_date_from_format){
                    $arrSearch['use_date_to'] = $use_date_from;
                    $use_date_to_format = $use_date_from_format;
                }
                $query->where('date_use', '<=', $use_date_to_format);
            }
        }else{
            $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());

            $arrSearch['use_date_from'] = $use_date_from;
            $tmpDate = explode('/', $use_date_from);
            $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
            $query->where('date_use','=', $use_date_from_format);

        }
        if($is_fixed == 1){
                $query->where('is_fixed', 1);
            }
        if($hoang_the == 1){
            $query->where('hoang_the', 1);
        }
        }

        $items = $query->orderBy('date_use', 'asc')->paginate(10000);
        $total_actual_amount = $total_quantity = 0;
        $arrReport = [];
        foreach($items as $o){
            if(!isset($arrReport[$o->cate_id])) $arrReport[$o->cate_id] = 0;
            $arrReport[$o->cate_id] += $o->total_money;
            $total_actual_amount+= $o->total_money;
            $total_quantity += $o->amount;
        }
        
        $cateList = CostType::orderBy('display_order')->get();
        $cateArr = [];
        foreach($cateList as $cate){
            $cateArr[$cate->id] = $cate->name;
        }
        $agent = new Agent();
        if($agent->isMobile()){
            $view = 'cost-guest.m-index';
        }else{
            $view = 'cost-guest.index';
        }
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $beachArr = [];
        foreach($branchList as $beach){
            $beachArr[$beach->id] = $beach->name;
        }
        return view($view, compact( 'items', 'content', 'cate_id', 'arrSearch', 'date_use', 'total_actual_amount', 'cateList', 'nguoi_chi', 'partnerList', 'partner_id', 'total_quantity', 'month', 'city_id', 'time_type','year', 'is_fixed', 'type', 'branch_id', 'branchList', 'beachArr', 'arrReport', 'cateArr'));
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

    
    
}
