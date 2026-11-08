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
use App\Models\NhaCungCap;

use Maatwebsite\Excel\Facades\Excel;
use App\User;
use Helper, File, Session, Auth, Image, Hash;

class CostController extends Controller
{

    public function cal(){
       $all = Cost::all();
       foreach($all as $a){
            $date_use = $a->date_use;
            foreach($a->details as $b){
                $b->update(['date_use' => $date_use]);
            }
       }
    }
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
        $arrSearch['xe_4t'] = $xe_4t = $request->xe_4t ?? null;
        $arrSearch['city_id'] = $city_id = 1;
        $arrSearch['partner_id'] = $partner_id = $request->partner_id ? $request->partner_id : null;
        $arrSearch['nguoi_chi'] = $nguoi_chi = $request->nguoi_chi ? $request->nguoi_chi : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $arrSearch['is_fixed'] = $is_fixed = $request->is_fixed ?? null;
        $arrSearch['tren_2'] = $tren_2 = $request->tren_2 ?? null;
        
        $arrSearch['hoang_the'] = $hoang_the = $request->hoang_the ?? null;
        $content = $request->content ? $request->content : null;
        $arrSearch['status'] = $status = $request->status ?? null;
        $arrSearch['id_search'] = $id_search = $request->id_search ?? null;
        $arrSearch['ncc_id'] = $ncc_id = $request->ncc_id ?? null;
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
        if($tren_2){
            $query->where('total_money','>=', 5000000);
        }
        if($xe_4t){
            $query->where('xe_4t', $xe_4t);
        }
        if($status){
                $query->where('status', $status);
            }
        if($type > 0){
            $query->where('type', $type);
        }
        if($partner_id){
            $query->where('partner_id', $partner_id);
        }
        if($ncc_id){
            $query->where('ncc_id', $ncc_id);
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

        $items = $query->orderBy('date_use', 'asc')->orderBy('nguoi_chi')->paginate(10000);
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
            $view = 'cost.m-index';
        }else{
            $view = 'cost.index';
        }
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $beachArr = [];
        foreach($branchList as $beach){
            $beachArr[$beach->id] = $beach->name;
        }
        $nccList = NhaCungCap::get();
        return view($view, compact( 'items', 'content', 'cate_id', 'arrSearch', 'date_use', 'total_actual_amount', 'cateList', 'nguoi_chi', 'partnerList', 'partner_id', 'total_quantity', 'month', 'city_id', 'time_type','year', 'is_fixed', 'type', 'branch_id', 'branchList', 'beachArr', 'arrReport', 'cateArr', 'nccList'));
    }
    public function export(Request $request)
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
        $arrSearch['xe_4t'] = $xe_4t = $request->xe_4t ?? null;
        $arrSearch['city_id'] = $city_id = 1;
        $arrSearch['partner_id'] = $partner_id = $request->partner_id ? $request->partner_id : null;
        $arrSearch['nguoi_chi'] = $nguoi_chi = $request->nguoi_chi ? $request->nguoi_chi : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $arrSearch['is_fixed'] = $is_fixed = $request->is_fixed ?? null;
        $arrSearch['tren_2'] = $tren_2 = $request->tren_2 ?? null;
        
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
        if($tren_2){
            $query->where('total_money','>=', 5000000);
        }
        if($xe_4t){
            $query->where('xe_4t', $xe_4t);
        }
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
        
        }

        $items = $query->orderBy('date_use', 'asc')->orderBy('nguoi_chi')->get();
        //dd($items);

        $cateList = CostType::pluck('name', 'id');
        $partnerList = Partner::pluck('name', 'id');
        $collecterNameArr = Helper::getCollecterNameArr();
        $i = 0;
        $contents[] = [
                '#' => '#',
                'Date' => 'Date',
                'Content' => 'Content',              
                'Price' => 'Price',
                'Amount' => 'Amount',
                'Total Money' => 'Total Money',
                'Payer' => 'Payer',
                'Notes'=> 'Notes'
            ];
            $total  = $totalAmount = 0;
           // dd($items);
        foreach ($items as $item) {
            $total += $item->total_money;
            $totalAmount += $item->amount;
            $i++;
            $contents[] = [
                '#' => $i,
                'Date' => date('d/m', strtotime($item->date_use)),
                'Content' => $item->cate_id > 0 && isset( $cateList[$item->cate_id]) ? $cateList[$item->cate_id] : "",
                
                'Price' => number_format($item->price),
                'Amount' => ($item->amount),
                'Total Money' => number_format($item->total_money),
                'Payer' => $collecterNameArr[$item->nguoi_chi],
                'Notes'=> $item->notes
            ];

        }
        $contents[] = [
                '#' => '',
                'Date' => '',
                'Content' => '',               
                'Price' => '',
                'Amount' => number_format($totalAmount),
                'Total Money' => number_format($total),
                'Payer' => '',
                'Notes'=> ''
            ];
        if(!empty($contents)){
            try{
                $filename = 'Cost-'.date('dmhis', time());
                Excel::create($filename, function ($excel) use ($contents, $filename) {
                    // Set sheets
                    $excel->sheet($filename, function ($sheet) use ($contents) {
                        $sheet->fromArray($contents, null, 'A1', false, false);
                    });
                })->download('xls');
            }catch(\Exception $ex){
              throw $ex;
            }
        }
    }
    public function ajaxDoiTac(Request $request){
        $cate_id = $request->cate_id;
        $cate_id = $cate_id == 15 ? 14 : $cate_id;
        $partnerList = Partner::where('cost_type_id', $cate_id)->get();
        return view('cost.doi-tac', compact( 'partnerList'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {

        $cate_id = $request->cate_id ? $request->cate_id : null;
        $date_use = $request->date_use ? $request->date_use : null;
        $cateList = CostType::orderBy('display_order')->get();
        $cateListShow = CostType::where('is_show', 1)->orderBy('display_order')->get();
        
        $nccList = NhaCungCap::get();
        
        $partnerList = null;
        if($cate_id){
            $partnerList = Partner::where('cost_type_id', $cate_id)->get();
        }

        $month = $request->month ?? null;

        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view('cost.create', compact('cate_id', 'date_use', 'cateList', 'month', 'partnerList', 'bankInfoList', 'vietNameBanks', 'branchList', 'nccList', 'cateListShow'));
    }
    public function sms(Request $request)
    {
        return view('cost.sms');
    }
    public function parseSms(Request $request){
        $dataArr['body'] = $request->sms;
        Helper::smsParser($dataArr);
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
            'date_use' => 'required',
            'nguoi_chi' => 'required'
        ],
        [
            'date_use.required' => 'Bạn chưa nhập ngày',
            'nguoi_chi.required' => 'Bạn chưa chọn người chi tiền',
        ]);


        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);

        $date_use = $dataArr['date_use'];
        $tmpDate = explode('/', $dataArr['date_use']);
        $dataArr['date_use'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        if($dataArr['image_url'] && $dataArr['image_name']){

            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);

            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);

            $dataArr['image_url'] = $destionation;
        }
        $is_fixed = isset($dataArr['is_fixed']) ? 1 : 0;
        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);
        $dataArr['price'] = (int) str_replace(',', '', $dataArr['price']);
        $dataArr['partner_id'] =  $dataArr['partner_id'] ?? null;
        $dataArr['city_id'] = $dataArr['type'] = 1;
        $dataArr['xe_4t'] = isset($dataArr['xe_4t']) ? 1 : 0;
        //dd($arr);
        if($dataArr['nguoi_chi'] == 1){ // tien mat thi trang thai Da Thanh Toan
            $dataArr['status'] = 2;
        }
        $arrData['created_user'] = $arrData['updated_user'] = Auth::user()->id;
        if(isset($dataArr['image_tmp_url'])){
            $dataArr['image_list'] = json_encode($dataArr['image_tmp_url']);
        }
        $rs = Cost::create($dataArr);

        Session::flash('message', 'Tạo mới thành công');
        $use_date_from = date('d/m/Y', strtotime($dataArr['date_use']));
        return redirect()->route('cost.index', ['use_date_from' => $use_date_from, 'time_type' => 3]);
    }
    public function update(Request $request)
    {
        $dataArr = $request->all();
        $cost_id = $dataArr['id'];

        $model= Cost::findOrFail($cost_id);
        $this->validate($request,[
            'date_use' => 'required',
            'nguoi_chi' => 'required'
        ],
        [
            'date_use.required' => 'Bạn chưa nhập ngày',
            'nguoi_chi.required' => 'Bạn chưa chọn người chi tiền',
        ]);
        if($dataArr['image_url'] && $dataArr['image_name']){

            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);

            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);

            $dataArr['image_url'] = $destionation;
        }
        //dd($dataArr);
        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);
        $dataArr['price'] = (int) str_replace(',', '', $dataArr['price']);
        $date_use = $dataArr['date_use'];
        $tmpDate = explode('/', $dataArr['date_use']);
        $dataArr['date_use'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        $dataArr['is_fixed'] = isset($dataArr['is_fixed']) ? 1 : 0;
        $dataArr['xe_4t'] = isset($dataArr['xe_4t']) ? 1 : 0;
        if($dataArr['nguoi_chi'] == 1){ // tien mat thi trang thai Da Thanh Toan
            $dataArr['status'] = 2;
        }
        $arrData['updated_user'] = Auth::user()->id;
        if(isset($dataArr['old_image'])){
            $dataArr['image_list'] = $dataArr['old_image'];
        }
        $old_image = isset($dataArr['old_image']) ? $dataArr['old_image'] : [];
        
        $new_image = isset($dataArr['image_tmp_url']) ? $dataArr['image_tmp_url'] : [];
        $image_list = array_merge($old_image, $new_image);
        $dataArr['image_list'] = json_encode($image_list);
        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('cost.index', ['use_date_from' => $date_use, 'time_type' => 3]);
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

        $detail = Cost::find($id);
        $nccList = NhaCungCap::get();
        //$cateList = CostType::orderBy('display_order')->get();
        $partnerList = Partner::where('cost_type_id', $detail->cate_id)->get();
        $cateList = CostType::orderBy('display_order')->get();

        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view('cost.edit', compact( 'detail', 'cateList', 'partnerList', 'bankInfoList', 'vietNameBanks', 'branchList', 'nccList'));
    }
    public function copy($id)
    {

        $detail = Cost::find($id);
        $cateList = CostType::orderBy('display_order')->get();
        $partnerList = Partner::where('cost_type_id', $detail->cate_id)->get();
        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        return view('cost.copy', compact( 'detail', 'cateList', 'partnerList', 'bankInfoList', 'vietNameBanks'));
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
        $model = Cost::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=>0]);
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('cost.index');
    }
}
