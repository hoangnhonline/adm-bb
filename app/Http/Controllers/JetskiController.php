<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Jetski;
use App\Models\JetskiHistory;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use App\User;
use Helper, File, Session, Auth, Image, Hash;

class JetskiController extends Controller
{

   
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

        $historyList = JetskiHistory::where('status', 1)->orderBy('time_end', 'asc')->get();
        $view = 'jetski.index';
        
        $jetskiList = Jetski::where('status', 1)->get();
        
        return view($view, compact( 'historyList', 'jetskiList'));
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
            'jetski_id' => 'required',
            'no_minutes' => 'required'
        ],
        [
            'jetski_id.required' => 'Bạn chưa chọn xe ',
            'no_minutes.required' => 'Bạn chưa chọn thời gian'
        ]);
        if($dataArr['time_start'] == '' && $dataArr['time_end'] == ''){
            $dataArr['time_start'] = date('H:i', strtotime(now()));
            $no_minutes = $dataArr['no_minutes'];
            $dataArr['time_end'] = date('H:i', strtotime("+{$no_minutes} minutes"));
        }
        
        $rs = JetskiHistory::create($dataArr);

        Session::flash('message', 'Tạo mới thành công');
        return redirect()->route('jetski.index');
    }
    public function update(Request $request)
    {
        $dataArr = $request->all();
        $cost_id = $dataArr['id'];
        $model= JetskiHistory::findOrFail($cost_id);
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

        $detail = JetskiHistory::find($id);
        //$cateList = CostType::orderBy('display_order')->get();
        $partnerList = Partner::where('cost_type_id', $detail->cate_id)->get();
        $cateList = CostType::orderBy('display_order')->get();

        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view('cost.edit', compact( 'detail', 'cateList', 'partnerList', 'bankInfoList', 'vietNameBanks', 'branchList'));
    }
    public function copy($id)
    {

        $detail = JetskiHistory::find($id);
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
        $model = JetskiHistory::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=>0]);
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('cost.index');
    }
}
