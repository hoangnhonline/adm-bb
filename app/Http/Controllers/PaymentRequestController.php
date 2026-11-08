<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use Jenssegers\Agent\Agent;
use App\Models\BankInfo;
use Maatwebsite\Excel\Facades\Excel;
use App\User;
use App\Models\BookingPayment;

use Helper, File, Session, Auth, Image, Hash;

class PaymentRequestController extends Controller
{

    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = PaymentRequest::find($id);
        $model->update([$column => $value]);
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

        $arrSearch['status'] = $status = $request->status ? $request->status : 1;
        $arrSearch['bank_info_id'] = $bank_info_id = $request->bank_info_id ? $request->bank_info_id : null;
        $arrSearch['city_id'] = $city_id = $request->city_id ? $request->city_id : null;       
        $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;       
        $arrSearch['payer'] = $payer = $request->payer ? $request->payer : null;
        $arrSearch['urgent'] = $urgent = $request->urgent ? $request->urgent : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;
        
        $content = $request->content ? $request->content : null;
        
        $query = PaymentRequest::whereRaw('1');
        if($status){
            $query->where('status', $status);
        }
        if($urgent){
            $query->where('urgent', 1);
        }
        if($payer){
            $query->where('payer', $payer);
        }
        if($city_id){
            $query->where('city_id', $city_id);
        }
        if($bank_info_id){
            $query->where('bank_info_id', $bank_info_id);
        }
        if($time_type == 1){
            $arrSearch['use_date_from'] = $use_date_from = $date_pay = date('d/m/Y', strtotime($mindate));
            $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));
                      
            $query->where('date_pay','>=', $mindate);                   
            $query->where('date_pay', '<=', $maxdate);
        }elseif($time_type == 2){
            $arrSearch['use_date_from'] = $use_date_from = $date_pay = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

            if($use_date_from){
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('date_pay','>=', $use_date_from_format);
            }
            if($use_date_to){
                $arrSearch['use_date_to'] = $use_date_to;
                $tmpDate = explode('/', $use_date_to);
                $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($use_date_to_format < $use_date_from_format){
                    $arrSearch['use_date_to'] = $use_date_from;
                    $use_date_to_format = $use_date_from_format;   
                }        
                $query->where('date_pay', '<=', $use_date_to_format);
            }
        }else{
            $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_pay = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            
            $arrSearch['use_date_from'] = $use_date_from;
            $tmpDate = explode('/', $use_date_from);
            $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('date_pay','=', $use_date_from_format);
        
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
        $items = $query->orderBy('urgent', 'desc')->orderBy('status', 'asc')->paginate(2000);     
        $total_actual_amount = $total_quantity = 0;   
        foreach($items as $o){
            $total_actual_amount+= $o->total_money;
            $total_quantity += $o->amount;
        }

        $bankInfoList = BankInfo::all();
        $agent = new Agent();
        if($agent->isMobile()){
            $view = 'payment-request.m-index';
        }else{
            $view = 'payment-request.index';
        }
        $listUser = User::whereIn('level', [1,2,3,4,5,6,7])->where('status', 1)->get();
        return view($view, compact( 'items', 'content', 'arrSearch', 'date_pay', 'total_actual_amount', 'payer', 'total_quantity', 'month', 'city_id', 'time_type','year', 'bank_info_id', 'bankInfoList', 'status', 'user_id', 'listUser', 'urgent'));
    }
    public function export(Request $request)
    {        
        $month = $request->month ?? "04";        
        $year = date('Y');
        $mindate = "$year-$month-01";        
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));
        $arrSearch['type'] = $type = $request->type ? $request->type : null;
        $arrSearch['bank_info_id'] = $bank_info_id = $request->bank_info_id ? $request->bank_info_id : null;
        $arrSearch['partner_id'] = $partner_id = $request->partner_id ? $request->partner_id : null;
        $arrSearch['payer'] = $payer = $request->payer ? $request->payer : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;
        $content = $request->content ? $request->content : null;
        
        $query = PaymentRequest::where('status', 1);
        if($payer){
            $query->where('payer', $payer);
        }
        if($type){
            $query->where('type', $type);
        }
        if($partner_id){
            $query->where('partner_id', $partner_id);
        }
        $partnerList = (object) [];
        if($bank_info_id){
            $query->where('bank_info_id', $bank_info_id);
            $partnerList = Partner::where('cost_type_id', $bank_info_id)->get();
        }        
        
        if($time_type == 1){
            $arrSearch['use_date_from'] = $use_date_from = $date_pay = date('d/m/Y', strtotime($mindate));
            $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));
                      
            $query->where('date_pay','>=', $mindate);                   
            $query->where('date_pay', '<=', $maxdate);
        }elseif($time_type == 2){
            $arrSearch['use_date_from'] = $use_date_from = $date_pay = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

            if($use_date_from){
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('date_pay','>=', $use_date_from_format);
            }
            if($use_date_to){
                $arrSearch['use_date_to'] = $use_date_to;
                $tmpDate = explode('/', $use_date_to);
                $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($use_date_to_format < $use_date_from_format){
                    $arrSearch['use_date_to'] = $use_date_from;
                    $use_date_to_format = $use_date_from_format;   
                }        
                $query->where('date_pay', '<=', $use_date_to_format);
            }
        }else{
            $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_pay = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            
            $arrSearch['use_date_from'] = $use_date_from;
            $tmpDate = explode('/', $use_date_from);
            $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('date_pay','=', $use_date_from_format);
        
        }
        $items = $query->orderBy('date_pay', 'asc')->get();     
        //dd($items);
       
        $cateList = CostType::pluck('name', 'id');
        $partnerList = Partner::pluck('name', 'id'); 

        $i = 0;
        $contents[] = [
                '#' => '#',
                'Ngày' => 'Ngày',
                'Nội dung' => 'Nội dung',
                'Đối tác' => 'Đối tác',
                'Giá' => 'Giá',
                'Số lượng' => 'Số lượng',
                'Thành tiền' => 'Thành tiền',
                'Ghi chú'=> 'Ghi chú'
            ];
            $total  = $totalAmount = 0;
           // dd($items);
        foreach ($items as $item) {
            $total += $item->total_money;
            $totalAmount += $item->amount;
            $i++;            
            $contents[] = [
                '#' => $i,
                'Ngày' => date('d/m', strtotime($item->date_pay)),
                'Nội dung' => $item->bank_info_id > 0 && isset( $cateList[$item->bank_info_id]) ? $cateList[$item->bank_info_id] : "",
                'Đối tác' => $item->partner_id > 0 && isset($partnerList[$item->partner_id]) ? $partnerList[$item->partner_id] : "",
                'Giá' => number_format($item->price),
                'Số lượng' => ($item->amount),
                'Thành tiền' => number_format($item->total_money),
                'Ghi chú'=> $item->notes
            ];   
            
        }    
        $contents[] = [
                '#' => '',
                'Ngày' => '',
                'Nội dung' => '',
                'Đối tác' => '',
                'Giá' => '',
                'Số lượng' => number_format($totalAmount),
                'Thành tiền' => number_format($total),
                'Ghi chú'=> ''
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
                dd($ex);
            }
        }
        

    }
   
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {   
        
        $bank_info_id = $request->bank_info_id ? $request->bank_info_id : null;
        $date_pay = $request->date_pay ? $request->date_pay : null;
        $bankInfoList = BankInfo::all();
        return view('payment-request.create', compact('bank_info_id', 'date_pay', 'bankInfoList'));
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
            'date_pay' => 'required',
            'total_money' => 'required'
            
        ],
        [   
            'date_pay.required' => 'Bạn chưa nhập ngày',
            'total_money.required' => 'Bạn chưa nhập số tiền',         
        ]);       

       
        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);
        $dataArr['urgent'] = isset($dataArr['urgent']) ? 1 : 0;
        $date_pay = $dataArr['date_pay'];
        $tmpDate = explode('/', $dataArr['date_pay']);
        $dataArr['date_pay'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        if($dataArr['image_url'] && $dataArr['image_name']){
            
            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);
            
            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);
            
            $dataArr['image_url'] = $destionation;
        }
        $arr = [
            'date_pay' => $dataArr['date_pay'],
            'notes' => $dataArr['notes'],
            'total_money' => $dataArr['total_money'],
            'content' => $dataArr['content'],                      
            'total_money' => (int) str_replace(',', '', $dataArr['total_money']),
            'image_url' => $dataArr['image_url'],
            'booking_id' => $dataArr['booking_id'],
            'bank_info_id' => $dataArr['bank_info_id'],            
            'city_id' => $dataArr['city_id'],
            'user_id' => Auth::user()->id,
            'status' => 1,
            'urgent' => $dataArr['urgent']    
        ];
        //dd($arr);
        $rs = PaymentRequest::create($arr);
      
        Session::flash('message', 'Tạo mới thành công');

        $month = $tmpDate[1];
        $year = $tmpDate[2];

        return redirect()->route('payment-request.index', ['month' =>$month, 'year' => $year, 'time_type' => 1]);
    }
    public function update(Request $request)
    {
        $dataArr = $request->all();
        $cost_id = $dataArr['id'];
        $model= PaymentRequest::findOrFail($cost_id);
        $oldStatus = $model->status;
        $this->validate($request,[               
            'date_pay' => 'required',
            'total_money' => 'required'
            
        ],
        [   
            'date_pay.required' => 'Bạn chưa nhập ngày',
            'total_money.required' => 'Bạn chưa nhập số tiền',         
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
        if($dataArr['unc_url'] && $dataArr['unc_name']){
            
            $tmp = explode('/', $dataArr['unc_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);
            
            File::move(config('plantotravel.upload_path').$dataArr['unc_url'], config('plantotravel.upload_path').$destionation);
            
            $dataArr['unc_url'] = $destionation;
        }
        //dd($dataArr);
        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);   
        $dataArr['urgent'] = isset($dataArr['urgent']) ? 1 : 0;    
        $date_pay = $dataArr['date_pay'];
        $tmpDate = explode('/', $dataArr['date_pay']);
        $dataArr['date_pay'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0]; 
        $month = $tmpDate[1];
        $year = $tmpDate[2];
              
        $model->update($dataArr);

        if($dataArr['status'] == 2 && $oldStatus == 1){
            //reply zalo
            $this->replyMess($cost_id);
        }
        $booking_id = '';
        if($dataArr['booking_id']){
            $booking_id = trim(strtolower($dataArr['booking_id']));
            $booking_id = str_replace("pth", '', $booking_id);
            $booking_id = str_replace("ptt", '', $booking_id);
            $booking_id = str_replace("ptv", '', $booking_id);
        }
        if($dataArr['unc_url'] && $booking_id != ''){            
            BookingPayment::create([
                'booking_id' => $booking_id,
                'amount' => $dataArr['total_money'],
                'pay_date' => $dataArr['date_pay'],
                'image_url' => $dataArr['unc_url'],
                'notes' => $dataArr['payer'] == 1 ? "Admin chi tiền" : "Kế toán chi tiền"
            ]);
        }

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('payment-request.index', ['month' => $month, 'time_type' => 1, 'year' => $year]);
    }
    public function replyMess($id){

        $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=ZaVgNfRnPLUDG-XRalLgKuT2u5UJwn83YYxgIf302XZv9iX1ljKr5ia6ongBp3bgwYJd19F03q_vDECyjzeoDVGeuJglm6a_yY_hMwpR1IwmRiz6nTv0Bw0igLNz-c1Tv16i0fttL5FYGgC3hAOW3SPB_dA6-0rYw1py1uli77Vn4jCIfifGREGLln2Yfaf3sdEP6OsPSMVDGQCGX_DuRl95kXwEe4b5a6s6J-AhVp2zHwzrwBXP8Prjaotvt4mzkMo1SkE22G2XQze8leeALDj4tX5FQ2s7kZsGxcDL';
        $detail = PaymentRequest::find($id);
        
        $detailUser = User::find($detail->user_id);
        if($detail->payer == 1){
            $payer = 'Nguyễn Hoàng';
        }elseif($detail->payer == 2){
            $payer = 'Thương Trần';
        }elseif($detail->payer == 3){
            $payer = 'Ngọc Nguyễn';
        }elseif($detail->payer == 4){
            $payer = 'Mộng Tuyền';
        }
        $text = '';
        if($detail->booking_id){
            $text = ' của booking '.$detail->booking_id;
        }
       //dd($detailUser->zalo_id);
        $arrData = [
            'recipient' => [
                'user_id' => $detailUser->zalo_id,
            ], 
            'message' => [
                'text' => $payer.' đã thanh toán '.number_format($detail->total_money).$text. ' cho '.$detail->bank->name,
            ]
        ];
        $ch = curl_init( $url );
        # Setup request to send json via POST.
        $payload = json_encode( $arrData );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);


        // $arrData = [
        //     'recipient' => [
        //         'user_id' => '7317386031055599346',
        //     ], 
        //     'message' => [
        //         'text' => 'Đã nhận. Mã booking là '.$booking_code,
        //     ]
        // ];
        // $ch = curl_init( $url );
        // # Setup request to send json via POST.
        // $payload = json_encode( $arrData );
        // curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        // curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        // # Return response instead of printing.
        // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        // # Send request.
        // $result = curl_exec($ch);
        // curl_close($ch);
        # Print response.
        echo "<pre>$result</pre>";

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
        
        $detail = PaymentRequest::find($id);       
        $bankInfoList = BankInfo::all();   
        return view('payment-request.edit', compact( 'detail', 'bankInfoList'));
    }
    public function urgent()
    {
        
        $count = PaymentRequest::where('status', 1)->where('urgent', 1)->count();
        if($count > 0){
            return view('payment-request.urgent', compact( 'count'));    
        }
        

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
        $model = PaymentRequest::find($id);
        $oldStatus = $model->status;
        $month = date('m', strtotime($model->pay_date));
        $year = date('Y', strtotime($model->pay_date));
        $model->update(['status'=>0]);      
        // redirect
        Session::flash('message', 'Xóa thành công'); 
        return redirect()->route('payment-request.index', ['time_type' => 1, 'month' => $month, 'year' => $year]);   
    }
}
