<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\BookingBill;
use App\Models\Booking;

use Helper, File, Session, Auth, Image;

class BookingBillController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {           
        $booking_id = $request->id ?? null;        
        $detailBooking = Booking::findOrFail($booking_id);        
        
        $query = BookingBill::where('status', 1)->where('booking_id', $booking_id)->orderBy('id', 'desc');
        $items = $query->get();
        $back_url = $request->back_url ?? null;        
        return view('booking-bill.index', compact( 'items', 'booking_id', 'detailBooking', 'back_url'));
    }
    

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {   
        $booking_id = $request->booking_id ? $request->booking_id : null;
             
        return view('booking-bill.create', compact('booking_id'));
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
                
            'pay_date' => 'required',
        ],
        [  
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
        


        $rs = BookingBill::create($dataArr);
    
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('booking-bill.index', ['booking_id' => $dataArr['booking_id']]);
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
        
        $tagSelected = [];

        $detail = BookingBill::find($id);
        if( Auth::user()->role > 2 ){
            if($detail->created_user != Auth::user()->id){
                return redirect()->route('home');
            }
        }
        $booking_id = $detail->booking_id;
        return view('booking-bill.edit', compact( 'detail', 'booking_id'));
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
            'name' => 'required',
        ],
        [   
            'title.required' => 'Bạn chưa nhập tên',                
        ]);
        $dataArr['display_order'] = (int) $dataArr['display_order'];
        $dataArr['thumbnail_url'] = strpos($dataArr['thumbnail_url'], 'ttps:') ? $dataArr['thumbnail_url'] :  "https://enb.vn".$dataArr['thumbnail_url'];
        $model = BookingBill::find($dataArr['id']);
       
        $model->update($dataArr);        
        
        Session::flash('message', 'Cập nhật thành công');        
        
        return redirect()->route('booking-bill.index');    
        
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
        $model = BookingBill::find($id);
        $oldStatus = $model->delete();
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('booking-bill.index', ['booking_id' => $model->booking_id]);   
    }
}
