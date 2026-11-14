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
use App\Models\Ingredient;
use App\Services\CostDetailService;

use Maatwebsite\Excel\Facades\Excel;
use App\User;
use Helper, File, Session, Auth, Image, Hash, DB;

class CostController extends Controller
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
        $arrSearch['detail_check'] = $detail_check = $request->detail_check ?? null;
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
        if($detail_check == 1){
            $query->where('detail_check', 1);
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
        $cateList = CostType::where('status', 1)->orderBy('display_order')->get();
        $cateListShow = CostType::where('is_show', 1)->where('status', 1)->orderBy('display_order')->get();
        
        $nccList = NhaCungCap::where('status', 1)->orderBy('display_order')->orderBy('name')->get();
        $ingredientList = Ingredient::active()->orderBy('name')->get();
        
        $partnerList = null;
        if($cate_id){
            $partnerList = Partner::where('cost_type_id', $cate_id)->get();
        }

        $month = $request->month ?? null;

        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view('cost.create', compact('cate_id', 'date_use', 'cateList', 'month', 'partnerList', 'bankInfoList', 'vietNameBanks', 'branchList', 'nccList', 'cateListShow', 'ingredientList'));
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
        // (sửa lỗi cũ: trước đây gán vào $arrData nên created_user không bao giờ được lưu)
        $dataArr['created_user'] = $dataArr['updated_user'] = Auth::user()->id;
        if(isset($dataArr['image_tmp_url'])){
            $dataArr['image_list'] = json_encode($dataArr['image_tmp_url']);
        }
        unset($dataArr['detail_total'], $dataArr['detail_check']);
        $details = $request->input('details', []);
        $rs = DB::transaction(function () use ($dataArr, $details) {
            $cost = Cost::create($dataArr);
            $this->saveDetails($cost, $details);
            return $cost;
        });

        Session::flash('message', __('Tạo mới thành công'));
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
        $dataArr['updated_user'] = Auth::user()->id;
        if(isset($dataArr['old_image'])){
            $dataArr['image_list'] = $dataArr['old_image'];
        }
        $old_image = isset($dataArr['old_image']) ? $dataArr['old_image'] : [];
        
        $new_image = isset($dataArr['image_tmp_url']) ? $dataArr['image_tmp_url'] : [];
        $image_list = array_merge($old_image, $new_image);
        $dataArr['image_list'] = json_encode($image_list);
        unset($dataArr['detail_total'], $dataArr['detail_check'], $dataArr['created_user']);
        $details = $request->input('details', []);
        DB::transaction(function () use ($model, $dataArr, $details) {
            $model->update($dataArr);
            $this->saveDetails($model, $details);
        });

        Session::flash('message', __('Cập nhật thành công'));

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
        $nccList = NhaCungCap::where('status', 1)->orWhere('id', $detail->ncc_id)->orderBy('display_order')->orderBy('name')->get();
        //$cateList = CostType::orderBy('display_order')->get();
        $partnerList = Partner::where('cost_type_id', $detail->cate_id)->get();
        $cateList = CostType::where('status', 1)->orWhere('id', $detail->cate_id)->orderBy('display_order')->get();
        $ingredientList = Ingredient::active()->orderBy('name')->get();
        $details = $detail->details()->with('ingredient')->orderBy('id')->get();

        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view('cost.edit', compact( 'detail', 'cateList', 'partnerList', 'bankInfoList', 'vietNameBanks', 'branchList', 'nccList', 'ingredientList', 'details'));
    }
    public function copy($id)
    {

        $detail = Cost::find($id);
        $cateList = CostType::where('status', 1)->orderBy('display_order')->get();
        $partnerList = Partner::where('cost_type_id', $detail->cate_id)->get();
        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $nccList = NhaCungCap::where('status', 1)->orderBy('display_order')->orderBy('name')->get();
        $ingredientList = Ingredient::active()->orderBy('name')->get();
        // Copy bill -> copy luôn dòng chi tiết (bỏ id để lưu thành dòng mới)
        $details = $detail->details()->orderBy('id')->get()->map(function ($d) {
            return ['ingredient_id' => $d->ingredient_id, 'quantity' => $d->quantity, 'unit' => $d->unit,
                    'unit_price' => $d->unit_price, 'amount' => $d->amount, 'notes' => $d->notes, 'need_review' => 0];
        });
        return view('cost.copy', compact( 'detail', 'cateList', 'partnerList', 'bankInfoList', 'vietNameBanks', 'branchList', 'nccList', 'ingredientList', 'details'));
    }
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    /**
    * Lưu các dòng hàng (cost_detail) của 1 bill.
    * - Chỉ xử lý khi form có gửi bảng chi tiết (details_submitted=1) để không xóa nhầm dữ liệu từ form khác.
    * - Dòng có id -> cập nhật, dòng mới -> thêm, dòng bị xóa trên form -> xóa.
    * - detail_total = tổng tiền các dòng; detail_check = 1 khi tổng dòng khác tổng bill (vẫn cho lưu).
    */
    private function saveDetails(Cost $cost, $rows)
    {
        if(request()->input('details_submitted') != 1){
            // Form không có bảng chi tiết: giữ nguyên dòng, chỉ đối chiếu lại với Total mới
            if($cost->detail_total !== null){
                $cost->detail_check = ((int) $cost->detail_total != (int) $cost->total_money) ? 1 : 0;
                $cost->save();
            }
            return;
        }
        CostDetailService::saveRows($cost, $rows, Auth::user()->id, 1);
    }

    private function toNumber($value, $decimal = false)
    {
        return CostDetailService::toNumber($value, $decimal);
    }

    /**
    * Báo cáo nhập hàng theo nguyên liệu (cost_detail).
    */
    public function nhapHang(Request $request)
    {
        // Kỳ thống kê: week | month | year | custom (custom = ô từ ngày - đến ngày)
        $period = in_array($request->period, ['week', 'month', 'year', 'custom']) ? $request->period
            : ($request->use_date_from || $request->use_date_to ? 'custom' : 'month');
        $anchor = $request->date && strtotime($request->date) ? strtotime($request->date) : time();
        $prevAnchor = $nextAnchor = null;
        if($period == 'week'){
            $fromDate = date('Y-m-d', strtotime('monday this week', $anchor));
            $toDate = date('Y-m-d', strtotime($fromDate.' +6 days'));
            $prevFrom = date('Y-m-d', strtotime($fromDate.' -7 days'));
            $prevTo = date('Y-m-d', strtotime($fromDate.' -1 day'));
            $periodLabel = __('Tuần').' '.date('d/m', strtotime($fromDate)).' - '.date('d/m/Y', strtotime($toDate));
            $prevAnchor = $prevFrom;
            $nextAnchor = date('Y-m-d', strtotime($fromDate.' +7 days'));
        }elseif($period == 'month'){
            $fromDate = date('Y-m-01', $anchor);
            $toDate = date('Y-m-t', $anchor);
            $prevFrom = date('Y-m-01', strtotime($fromDate.' -1 month'));
            $prevTo = date('Y-m-t', strtotime($prevFrom));
            $periodLabel = __('Tháng').' '.date('m/Y', $anchor);
            $prevAnchor = $prevFrom;
            $nextAnchor = date('Y-m-01', strtotime($fromDate.' +1 month'));
        }elseif($period == 'year'){
            $fromDate = date('Y-01-01', $anchor);
            $toDate = date('Y-12-31', $anchor);
            $prevFrom = date('Y-01-01', strtotime($fromDate.' -1 year'));
            $prevTo = date('Y-12-31', strtotime($prevFrom));
            $periodLabel = __('Năm').' '.date('Y', $anchor);
            $prevAnchor = $prevFrom;
            $nextAnchor = date('Y-01-01', strtotime($fromDate.' +1 year'));
        }else{
            $fromDate = $this->toSqlDate($request->use_date_from ?: date('01/m/Y')) ?: date('Y-m-01');
            $toDate = $this->toSqlDate($request->use_date_to ?: date('t/m/Y')) ?: date('Y-m-t');
            if($toDate < $fromDate){
                $toDate = $fromDate;
            }
            $days = (int) round((strtotime($toDate) - strtotime($fromDate)) / 86400) + 1;
            $prevFrom = date('Y-m-d', strtotime($fromDate.' -'.$days.' days'));
            $prevTo = date('Y-m-d', strtotime($fromDate.' -1 day'));
            $periodLabel = date('d/m/Y', strtotime($fromDate)).' - '.date('d/m/Y', strtotime($toDate));
        }
        $from = date('d/m/Y', strtotime($fromDate));
        $to = date('d/m/Y', strtotime($toDate));
        $sort = $request->sort == 'qty' ? 'qty' : 'amount';
        $arrSearch = [
            'period' => $period,
            'sort' => $sort,
            'use_date_from' => $from,
            'use_date_to' => $to,
            'branch_ids' => $request->branch_ids ? (array) $request->branch_ids : [],
            'ncc_id' => $request->ncc_id ?? null,
            'cate_id' => $request->cate_id ?? null,
            'ingredient_id' => $request->ingredient_id ?? null,
            'include_review' => $request->include_review ? 1 : 0,
        ];

        $makeBase = function ($f, $t) use ($arrSearch) {
            $q = DB::table('cost_detail as d')
                ->join('cost as c', 'c.id', '=', 'd.cost_id')
                ->leftJoin('ingredient as i', 'i.id', '=', 'd.ingredient_id')
                ->where('c.status', '>', 0)
                ->where('c.date_use', '>=', $f)
                ->where('c.date_use', '<=', $t);
            if($arrSearch['branch_ids']){
                $q->whereIn('c.branch_id', $arrSearch['branch_ids']);
            }
            if($arrSearch['ncc_id']){
                $q->where('c.ncc_id', $arrSearch['ncc_id']);
            }
            if($arrSearch['cate_id']){
                $q->where('i.cost_type_id', $arrSearch['cate_id']);
            }
            return $q;
        };
        $base = $makeBase($fromDate, $toDate);
        $reviewRows = (clone $base)->where('d.need_review', 1)->count();
        if(!$arrSearch['include_review']){
            $base->where('d.need_review', 0);
        }

        // Tổng hợp theo nguyên liệu. Số lượng chỉ cộng các dòng cùng ĐVT chuẩn của nguyên liệu.
        $sameUnit = "(d.unit IS NULL OR d.unit = '' OR d.unit = i.unit)";
        $items = (clone $base)
            ->select(
                'd.ingredient_id', 'i.name', 'i.unit', 'i.cost_type_id',
                DB::raw("SUM(CASE WHEN $sameUnit THEN d.quantity END) as qty"),
                DB::raw('SUM(d.amount) as amount'),
                DB::raw("SUM(CASE WHEN $sameUnit AND d.quantity > 0 AND d.amount > 0 THEN d.amount END) as amount_has_qty"),
                DB::raw("SUM(CASE WHEN $sameUnit AND d.quantity > 0 AND d.amount > 0 THEN d.quantity END) as qty_has_amount"),
                DB::raw('COUNT(DISTINCT d.cost_id) as bills'),
                DB::raw('COUNT(*) as line_count'),
                DB::raw('SUM(d.need_review) as review_lines')
            )
            ->groupBy('d.ingredient_id', 'i.name', 'i.unit', 'i.cost_type_id')
            ->orderBy('amount', 'desc')
            ->get();
        if($sort == 'qty'){
            $items = $items->sortByDesc(function ($it) { return [(float) $it->qty, (float) $it->amount]; })->values();
        }
        $totalAmount = $items->sum('amount');

        // Kỳ trước (cùng bộ lọc) để so sánh
        $prevBase = $makeBase($prevFrom, $prevTo);
        if(!$arrSearch['include_review']){
            $prevBase->where('d.need_review', 0);
        }
        $prevMap = [];
        foreach($prevBase->select('d.ingredient_id',
                DB::raw("SUM(CASE WHEN $sameUnit THEN d.quantity END) as qty"),
                DB::raw('SUM(d.amount) as amount'))
            ->groupBy('d.ingredient_id')->get() as $p){
            $prevMap[$p->ingredient_id ?: 'none'] = $p;
        }
        $prevTotal = 0;
        foreach($prevMap as $p){ $prevTotal += (float) $p->amount; }

        // Chi tiết từng dòng khi chọn 1 nguyên liệu ('none' = dòng chưa gắn nguyên liệu)
        $lines = collect();
        if($arrSearch['ingredient_id']){
            $q = (clone $base)->select('d.*', 'c.date_use', 'c.branch_id', 'c.ncc_id', 'c.total_money', 'c.detail_check', 'i.name as ingredient_name', 'i.unit as ingredient_unit');
            if($arrSearch['ingredient_id'] == 'none'){
                $q->whereNull('d.ingredient_id');
            }else{
                $q->where('d.ingredient_id', $arrSearch['ingredient_id']);
            }
            $lines = $q->orderBy('c.date_use')->orderBy('d.cost_id')->get();
        }

        // Độ phủ dữ liệu: bill nhóm "Giá vốn" trong kỳ có / chưa có chi tiết
        $cover = DB::table('cost as c')
            ->join('cost_type as t', 't.id', '=', 'c.cate_id')
            ->where('c.status', '>', 0)
            ->where('t.group_name', 'like', 'Giá vốn%')
            ->where('c.date_use', '>=', $fromDate)
            ->where('c.date_use', '<=', $toDate);
        if($arrSearch['branch_ids']){
            $cover->whereIn('c.branch_id', $arrSearch['branch_ids']);
        }
        if($arrSearch['ncc_id']){
            $cover->where('c.ncc_id', $arrSearch['ncc_id']);
        }
        $coverage = $cover->select(
                DB::raw('COUNT(*) as bills'),
                DB::raw('SUM(c.total_money) as money'),
                DB::raw('SUM(CASE WHEN EXISTS (SELECT 1 FROM cost_detail x WHERE x.cost_id = c.id) THEN 1 ELSE 0 END) as bills_detail'),
                DB::raw('SUM(CASE WHEN EXISTS (SELECT 1 FROM cost_detail x WHERE x.cost_id = c.id) THEN c.total_money ELSE 0 END) as money_detail'),
                DB::raw('SUM(c.detail_check) as bills_check')
            )->first();

        $cateList = CostType::where('status', 1)->orderBy('display_order')->get();
        $cateArr = $cateList->pluck('name', 'id');
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $beachArr = $branchList->pluck('name', 'id');
        $nccList = NhaCungCap::where('status', 1)->orderBy('display_order')->orderBy('name')->get();
        $nccArr = NhaCungCap::pluck('name', 'id');
        $ingredientList = Ingredient::orderBy('name')->get();

        return view('cost.nhap-hang', compact('items', 'lines', 'totalAmount', 'reviewRows', 'coverage', 'arrSearch',
            'cateList', 'cateArr', 'branchList', 'beachArr', 'nccList', 'nccArr', 'ingredientList',
            'period', 'periodLabel', 'prevAnchor', 'nextAnchor', 'prevFrom', 'prevTo', 'prevMap', 'prevTotal', 'sort', 'fromDate'));
    }

    /**
     * Biểu đồ giá nguyên liệu: giá theo từng lần nhập (mỗi bill = 1 điểm) hoặc bình quân theo ngày.
     * Đơn giá = thành tiền / số lượng, chỉ lấy dòng có SL > 0, tiền > 0 và đúng ĐVT chuẩn của nguyên liệu.
     */
    public function bieuDoGia(Request $request)
    {
        $from = $request->use_date_from ? $request->use_date_from : date('d/m/Y', strtotime('-6 months'));
        $to = $request->use_date_to ? $request->use_date_to : date('d/m/Y');
        $fromDate = $this->toSqlDate($from) ?: date('Y-m-d', strtotime('-6 months'));
        $toDate = $this->toSqlDate($to) ?: date('Y-m-d');
        if($toDate < $fromDate){
            $toDate = $fromDate;
            $to = $from;
        }
        $ingredientIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->ingredient_ids))));
        $ingredientIds = array_slice($ingredientIds, 0, 5);
        $arrSearch = [
            'use_date_from' => $from,
            'use_date_to' => $to,
            'ingredient_ids' => $ingredientIds,
            'branch_ids' => $request->branch_ids ? array_map('intval', (array) $request->branch_ids) : [],
            'ncc_id' => $request->ncc_id ? (int) $request->ncc_id : null,
            'include_review' => $request->include_review ? 1 : 0,
            'mode' => $request->mode == 'ngay' ? 'ngay' : 'lan',
            'by_ncc' => ($request->by_ncc && count($ingredientIds) == 1) ? 1 : 0,
        ];

        $ingredientList = Ingredient::orderBy('name')->get();
        $ingArr = $ingredientList->keyBy('id');
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $beachArr = $branchList->pluck('name', 'id');
        $nccList = NhaCungCap::where('status', 1)->orderBy('display_order')->orderBy('name')->get();
        $nccArr = NhaCungCap::pluck('name', 'id');

        $series = [];
        $history = [];
        $summary = [];
        $skipped = ['unit' => 0, 'zero' => 0, 'review' => 0];

        if($ingredientIds){
            $base = DB::table('cost_detail as d')
                ->join('cost as c', 'c.id', '=', 'd.cost_id')
                ->join('ingredient as i', 'i.id', '=', 'd.ingredient_id')
                ->where('c.status', '>', 0)
                ->whereIn('d.ingredient_id', $ingredientIds)
                ->where('c.date_use', '>=', $fromDate)
                ->where('c.date_use', '<=', $toDate);
            if($arrSearch['branch_ids']){
                $base->whereIn('c.branch_id', $arrSearch['branch_ids']);
            }
            if($arrSearch['ncc_id']){
                $base->where('c.ncc_id', $arrSearch['ncc_id']);
            }

            $sameUnit = "(d.unit IS NULL OR d.unit = '' OR d.unit = i.unit)";
            $skipped['review'] = $arrSearch['include_review'] ? 0 : (clone $base)->where('d.need_review', 1)->count();
            if(!$arrSearch['include_review']){
                $base->where('d.need_review', 0);
            }
            $skipped['unit'] = (clone $base)->whereRaw("NOT $sameUnit")->count();
            $skipped['zero'] = (clone $base)->whereRaw($sameUnit)
                ->where(function($q){
                    $q->whereNull('d.quantity')->orWhere('d.quantity', '<=', 0)
                      ->orWhereNull('d.amount')->orWhere('d.amount', '<=', 0);
                })->count();

            // Gộp theo (bill, nguyên liệu): 1 bill có nhiều dòng cùng nguyên liệu (vd tỏi xay + tỏi lột) = 1 lần nhập
            $rows = (clone $base)
                ->whereRaw($sameUnit)
                ->where('d.quantity', '>', 0)
                ->where('d.amount', '>', 0)
                ->select(
                    'd.cost_id', 'd.ingredient_id', 'c.date_use', 'c.branch_id', 'c.ncc_id',
                    DB::raw('SUM(d.quantity) as qty'),
                    DB::raw('SUM(d.amount) as amount'),
                    DB::raw('MAX(d.need_review) as need_review')
                )
                ->groupBy('d.cost_id', 'd.ingredient_id', 'c.date_use', 'c.branch_id', 'c.ncc_id')
                ->orderBy('c.date_use')->orderBy('d.cost_id')
                ->get();

            // Lịch sử từng lần nhập + chênh lệch so với lần trước (cùng nguyên liệu)
            $prev = [];
            foreach($rows as $r){
                $price = (float) $r->amount / (float) $r->qty;
                $ingId = (int) $r->ingredient_id;
                $p = $prev[$ingId] ?? null;
                $history[] = [
                    'cost_id' => (int) $r->cost_id,
                    'ingredient_id' => $ingId,
                    'date' => $r->date_use,
                    'branch' => $beachArr[$r->branch_id] ?? '',
                    'ncc_id' => (int) $r->ncc_id,
                    'ncc' => $nccArr[$r->ncc_id] ?? '(Chưa có NCC)',
                    'qty' => (float) $r->qty,
                    'amount' => (int) $r->amount,
                    'price' => $price,
                    'diff' => $p !== null ? $price - $p : null,
                    'diff_pct' => ($p !== null && $p > 0) ? ($price - $p) / $p * 100 : null,
                    'need_review' => (int) $r->need_review,
                ];
                $prev[$ingId] = $price;
            }

            // Tóm tắt theo nguyên liệu
            foreach($ingredientIds as $ingId){
                $list = array_values(array_filter($history, function($h) use ($ingId){ return $h['ingredient_id'] == $ingId; }));
                if(!$list){
                    $summary[$ingId] = null;
                    continue;
                }
                $prices = array_column($list, 'price');
                $sumAmount = array_sum(array_column($list, 'amount'));
                $sumQty = array_sum(array_column($list, 'qty'));
                $first = $list[0];
                $last = $list[count($list) - 1];
                $minIdx = array_keys($prices, min($prices))[0];
                $maxIdx = array_keys($prices, max($prices))[0];
                $summary[$ingId] = [
                    'count' => count($list),
                    'min' => $list[$minIdx],
                    'max' => $list[$maxIdx],
                    'avg' => $sumQty > 0 ? $sumAmount / $sumQty : null,
                    'first' => $first,
                    'last' => $last,
                    'change_pct' => $first['price'] > 0 ? ($last['price'] - $first['price']) / $first['price'] * 100 : null,
                    'qty' => $sumQty,
                    'amount' => $sumAmount,
                ];
            }

            // Dữ liệu biểu đồ
            $groups = [];
            foreach($history as $h){
                if($arrSearch['by_ncc']){
                    $key = 'n' . $h['ncc_id'];
                    $name = $h['ncc'];
                }else{
                    $key = 'i' . $h['ingredient_id'];
                    $name = optional($ingArr->get($h['ingredient_id']))->name;
                }
                if(!isset($groups[$key])){
                    $groups[$key] = ['name' => $name, 'unit' => optional($ingArr->get($h['ingredient_id']))->unit, 'items' => []];
                }
                $groups[$key]['items'][] = $h;
            }
            foreach($groups as $g){
                $data = [];
                if($arrSearch['mode'] == 'ngay'){
                    $byDay = [];
                    foreach($g['items'] as $h){
                        $d = $h['date'];
                        if(!isset($byDay[$d])) $byDay[$d] = ['qty' => 0, 'amount' => 0, 'bills' => [], 'ncc' => []];
                        $byDay[$d]['qty'] += $h['qty'];
                        $byDay[$d]['amount'] += $h['amount'];
                        $byDay[$d]['bills'][] = 'CP' . $h['cost_id'];
                        $byDay[$d]['ncc'][$h['ncc']] = 1;
                    }
                    foreach($byDay as $d => $v){
                        $data[] = [
                            'x' => strtotime($d . ' UTC') * 1000,
                            'y' => round($v['amount'] / $v['qty']),
                            'qty' => round($v['qty'], 3),
                            'amount' => $v['amount'],
                            'bill' => implode(', ', $v['bills']),
                            'ncc' => implode(', ', array_keys($v['ncc'])),
                            'branch' => '',
                            'cost_id' => count($v['bills']) == 1 ? (int) substr($v['bills'][0], 2) : null,
                        ];
                    }
                }else{
                    foreach($g['items'] as $h){
                        $data[] = [
                            'x' => strtotime($h['date'] . ' UTC') * 1000,
                            'y' => round($h['price']),
                            'qty' => round($h['qty'], 3),
                            'amount' => $h['amount'],
                            'bill' => 'CP' . $h['cost_id'],
                            'ncc' => $h['ncc'],
                            'branch' => $h['branch'],
                            'cost_id' => $h['cost_id'],
                        ];
                    }
                }
                $series[] = ['name' => $g['name'], 'unit' => $g['unit'], 'data' => $data];
            }
        }

        // Lịch sử hiển thị mới nhất trước
        $history = array_reverse($history);

        return view('cost.bieu-do-gia', compact('arrSearch', 'ingredientList', 'ingArr', 'branchList', 'nccList',
            'series', 'history', 'summary', 'skipped'));
    }

    private function toSqlDate($dmy)
    {
        $tmp = explode('/', (string) $dmy);
        if(count($tmp) != 3 || !checkdate((int) $tmp[1], (int) $tmp[0], (int) $tmp[2])) return null;
        return sprintf('%04d-%02d-%02d', $tmp[2], $tmp[1], $tmp[0]);
    }

    public function destroy($id)
    {
        // delete
        $model = Cost::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=>0]);
        // redirect
        Session::flash('message', __('Xóa thành công'));
        return redirect()->route('cost.index');
    }
}
