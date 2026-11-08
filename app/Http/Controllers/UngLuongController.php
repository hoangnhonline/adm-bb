<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UngLuong;
use Jenssegers\Agent\Agent;
use App\Models\Partner;
use App\Models\BankInfo;

use Maatwebsite\Excel\Facades\Excel;
use App\User;
use Helper, File, Session, Auth, Image, Hash;

class UngLuongController  extends Controller
{

    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = UngLuong::find($id);
        $model->update([$column => $value]);
    }
    public function index(Request $request)
    {

        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
       
        $maxDay = date('d', strtotime($maxdate));

        $arrSearch['partner_id'] = $partner_id = $request->partner_id ? $request->partner_id : null;
        $arrSearch['nguoi_chi'] = $nguoi_chi = $request->nguoi_chi ? $request->nguoi_chi : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;        
        $arrSearch['status'] = $status = $request->status ? $request->status : null; 
        $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
        $query = UngLuong::where('status', '>', 0);     
        
        if($status){
            $query->where('status', $status);
        } 
        if($nguoi_chi){
            $query->where('nguoi_chi', $nguoi_chi);
        }        
      
        if($partner_id){
            $query->where('partner_id', $partner_id);
        }       
    
        $partnerList = Partner::where('cost_type_id', 1)->get();
       

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

        $items = $query->orderBy('date_use', 'asc')->paginate(10000);
        $total_actual_amount = $total_quantity = 0;
 
        foreach($items as $o){
    
            $total_actual_amount+= $o->total_money;
            $total_quantity += $o->amount;
        }
        $agent = new Agent();
        if($agent->isMobile()){
            $view = 'ung-luong.m-index';
        }else{
            $view = 'ung-luong.index';
        }
        
        return view($view, compact( 'items', 'arrSearch', 'date_use', 'total_actual_amount', 'nguoi_chi', 'partnerList', 'partner_id', 'total_quantity', 'month', 'time_type','year', 'status'));
    }
    public function ajaxDoiTac(Request $request){        
        $partnerList = Partner::where('cost_type_id', 1)->get();
        return view('ung-luong.doi-tac', compact( 'partnerList'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {       
        $date_use = $request->date_use ? $request->date_use : null;         
        $partnerList = Partner::where('cost_type_id', 1)->get();
        $month = $request->month ?? null;
        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
    
        return view('ung-luong.create', compact('date_use', 'month', 'partnerList', 'bankInfoList', 'vietNameBanks'));
    }
    public function sms(Request $request)
    {
        return view('ung-luong.sms');
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
     

        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);
    
        $dataArr['partner_id'] =  $dataArr['partner_id'] ?? null;
        $dataArr['city_id'] = $dataArr['type'] = 1;
        $dataArr['status'] = $dataArr['status'];
        
        $arrData['created_user'] = $arrData['updated_user'] = Auth::user()->id;  
        $rs = UngLuong::create($dataArr);

        Session::flash('message', 'Tạo mới thành công');
        $use_date_from = date('d/m/Y', strtotime($dataArr['date_use']));
        return redirect()->route('ung-luong.index', ['month' => $tmpDate[1], 'year' => $tmpDate[2],'time_type' => 1]);
    }
    public function update(Request $request)
    {
        $dataArr = $request->all();
        $cost_id = $dataArr['id'];
        $model= UngLuong::findOrFail($cost_id);
        $this->validate($request,[
            'date_use' => 'required',
            'nguoi_chi' => 'required'
        ],
        [
            'date_use.required' => 'Bạn chưa nhập ngày',
            'nguoi_chi.required' => 'Bạn chưa chọn người chi tiền',
        ]);
      
        //dd($dataArr);
        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);
        
        $date_use = $dataArr['date_use'];
        $tmpDate = explode('/', $dataArr['date_use']);
        $dataArr['date_use'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        $dataArr['status'] = $dataArr['status'];
        $arrData['updated_user'] = Auth::user()->id;  
        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('ung-luong.index', ['month' => $tmpDate[1], 'year' => $tmpDate[2],'time_type' => 1]);
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

        $detail = UngLuong::find($id);
        $partnerList = Partner::where('cost_type_id', 1)->get();
        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
      
        return view('ung-luong.edit', compact( 'detail', 'partnerList', 'bankInfoList', 'vietNameBanks'));
    }
    public function copy($id)
    {
        $detail = UngLuong::find($id);
        $partnerList = Partner::where('cost_type_id',1)->get();
        $bankInfoList = BankInfo::all();
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        return view('ung-luong.copy', compact( 'detail', 'partnerList', 'bankInfoList', 'vietNameBanks'));
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
        $model = UngLuong::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=> 0]);
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('ung-luong.index');
    }
}
