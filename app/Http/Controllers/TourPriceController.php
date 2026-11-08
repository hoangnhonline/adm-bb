<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\TourPrice;
use App\Models\Booking;
use App\User;
use App\Models\Settings;
use Helper, File, Session, Auth;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

class TourPriceController extends Controller
{
    
    public function edit($id, Request $request)
    {
        
        $tagSelected = [];

        $detail = Booking::find($id);
        $listUser = User::where('role', 4)->where('status', 1)->get();

         $listTag = Location::all();
        if($detail->user_id != Auth::user()->id && Auth::user()->role == 2){
            dd('Bạn không có quyền truy cập.');
        }
        $arrSearch = $request->all();
        
        
        $ticketType = TicketType::all();
        $ticketList = $detail->tickets;
        $ticketArr = [];
        foreach($ticketList as $t){
            $ticketArr[] = $t;
        }
        return view('ticket.edit-ticket', compact( 'detail', 'listUser', 'arrSearch', 'ticketType', 'ticketArr', 'listTag'));
    

    }
     public function viewPdf($id, Request $request)
    {
        
        $tagSelected = [];

        $detail = Booking::find($id);
        $listUser = User::where('role', 4)->where('status', 1)->get();
        
         $listTag = Location::all();
        if($detail->user_id != Auth::user()->id && Auth::user()->role == 2){
            dd('Bạn không có quyền truy cập.');
        }
        $arrSearch = $request->all();
        
        
        $ticketType = TicketType::all();
        $ticketList = $detail->tickets;
        $ticketArr = [];
        foreach($ticketList as $t){
            $ticketArr[] = $t;
        }
        $detailUser = User::find($detail->user_id);
        $sales = "";        
        $sales = $detailUser->name;
        $sales_phone = $detailUser->phone;
        $ticketTypeArr = TicketType::pluck('name', 'id')->toArray();
        return view('ticket.view-pdf', compact( 'detail', 'listUser', 'arrSearch', 'ticketType', 'ticketArr', 'listTag', 'sales', 'sales_phone', 'ticketTypeArr'));
    

    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    
  
    public function related(Request $request){
        $hotel_id = $request->hotel_id;
        $detail = Hotels::find($hotel_id);
        $related = $detail->related_id;
        $tmp = explode(',', $related);
        $relatedArr = [];
        
        foreach($tmp as $id){
            if($id > 0){
                $relatedArr[] = Hotels::find($id);
            }          
            
        }
        return view('booking.related', compact('relatedArr'));

    }
    public function export(Request $request)
    {        
        
        
         $arrSearch['book_date_from'] = $book_date_from =  $request->date;
         $tmparr = explode('/', $book_date_from);
        
        
        $query = Booking::where('type', 1)->where('status', 1)->where('export', 2);
        
        
       
        $arrSearch['book_date_from'] = $book_date_from;
        $tmpDate = explode('/', $book_date_from);
        $book_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
        $query->where('use_date', $book_date_from_format);
           
        $query->orderBy('tour_id', 'asc');
        $query->orderBy('tour_type', 'asc');
        $query->orderBy('address', 'asc');
        $query->orderBy('id', 'asc');
            
        
        
        $allList = $query->get();
        $items  = $query->paginate(1000); 

        $i = 0;
        foreach ($items as $item) {
            $i++;
            //dd($data->item);  
            if($item->tour_id == 1){
            	$loai_tour = "";
            }elseif($item->tour_id == 2){
            	$loai_tour = "\n"."2 ĐẢO";
              //  dd($loai_tour);
            }else{
            	$loai_tour = "\n"."RẠCH VẸM";
            } 
            $address = $item->location_id > 0 ? $item->location->name : $item->address;
            //dd($loai_tour);
            $notes = "";
            if($item->ko_cap_treo == 1){
                $notes = "KHÔNG ĐI CÁP TREO\n\r";
            }
            $notes .= $item->notes;
            $contents[] = [
                'CODE' => 'PTT'.$item->id,
                'Tên KH' => $item->name,
                'Điện thoại' => $item->phone."-".$item->phone_1,
                'Xác nhận' => '',
                'Nơi đón' => $address.$loai_tour."\n".$notes,
                'NL' => $item->adults,
                'TE' => $item->childs,
                'EB' => $item->infants,
                'AN' => $item->meals,
                'Tổng tiền' => $item->total_price,
                'Phụ thu' => $item->extra_fee + $item->total_price_child,
                'Cọc' => $item->tien_coc,
                'CK cho ai' => '',
                'Giảm' => $item->discount,
                'Còn lại' => $item->con_lai,
                'Sales' => $item->user ? $item->user->name: "",
            ];    
            $item->update(['export' => 1]);
                       
        }        
       //dd($contents);
        $rsExportTime = Settings::find(205);
        $rsExportTime->update(['value' => date('Y-m-d H:i:s')]);
        
        if(!empty($contents)){
                Excel::create('Tour4Dao_'.$tmparr[0].$tmparr[1], function ($excel) use ($contents) {
                // Set sheets
                $excel->sheet('KH', function ($sheet) use ($contents) {
                    $sheet->fromArray($contents, null, 'A1', false, false);
                });
            })->download('xls');
        }
        

    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {   
        $type = $request->type ? $request->type : 1;    
        $view = $type == 1 ? "booking.add-tour" : 'booking.add-hotel';
        if($type == 3){
            $view = 'booking.add-ticket';
        }
        $listUser = User::where('role', 4)->where('status', 1)->get();
        $listTag = Location::all();
        if($type == 1){
            return view($view, compact('type', 'listUser', 'listTag'));
        }else if($type == 2){
            $hotelList = Hotels::where('partner', 0)->get();
            return view($view, compact('type', 'listUser', 'hotelList'));
        }else if($type == 3){
            $ticketType = TicketType::all();
            return view($view, compact('type', 'listUser', 'ticketType'));
        }
        
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
            'name' => 'required',
            'phone' => 'required',
            'use_date' => 'required',
            'location_id' => 'required'
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'phone.required' => 'Bạn chưa nhập điện thoại',
            'use_date.required' => 'Bạn chưa nhập ngày đi',
            'location_id.required' => 'Bạn chưa chọn nơi đón',
        ]);       
        $dataArr['total_price_adult'] = (int) str_replace(',', '', $dataArr['total_price_adult']);
        $dataArr['total_price_child'] = (int) str_replace(',', '', $dataArr['total_price_child']);
        $dataArr['total_price'] =(int) str_replace(',', '', $dataArr['total_price']);
        $dataArr['tien_coc'] = (int) str_replace(',', '', $dataArr['tien_coc']);
        $dataArr['extra_fee'] = (int) str_replace(',', '', $dataArr['extra_fee']);
        $dataArr['discount'] = (int) str_replace(',', '', $dataArr['discount']);
        $dataArr['con_lai'] = (int) str_replace(',', '', $dataArr['con_lai']);
        $dataArr['phone'] = str_replace('.', '', $dataArr['phone']);
        $dataArr['phone'] = str_replace(' ', '', $dataArr['phone']);
        $tmpDate = explode('/', $dataArr['use_date']);
        $dataArr['use_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        if($dataArr['ngay_coc']){
            $tmpDate = explode('/', $dataArr['ngay_coc']);
            $dataArr['ngay_coc'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }
        if($dataArr['book_date']){
            $tmpDate = explode('/', $dataArr['book_date']);
            $dataArr['book_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
        }else{
            $dataArr['book_date'] = date('Y-m-d');    
        }

        if(Auth::user()->role < 3){
            $dataArr['user_id'] = $dataArr['user_id'];
        }else{
            $dataArr['user_id'] = Auth::user()->id;
        }
        $dataArr['ko_cap_treo'] = isset($dataArr['ko_cap_treo']) ? 1 : 0;
        $dataArr['price_adult'] = $dataArr['total_price_adult']/$dataArr['adults'];
        $dataArr['name'] = ucwords($dataArr['name']);
        $rs = Booking::create($dataArr);
        $id = $rs->id;
        $this->replyMess($dataArr, $rs);
        unset($dataArr['_token']);
        BookingLogs::create([
            'booking_id' => $id,
            'content' => json_encode($dataArr),
            'user_id' => Auth::user()->id,
            'action' => 1
        ]);
        Session::flash('message', 'Tạo mới thành công');
        $use_date = date('d/m/Y', strtotime($dataArr['use_date']));
        return redirect()->route('booking.index', ['type' => $dataArr['type'], 'book_date_from' => $use_date]);  
    }
    public function storeHotel(Request $request)
    {
        $dataArr = $request->all();
        
        $this->validate($request,[
            'name' => 'required',
            'phone' => 'required',
            //'email' => 'email',
            'checkin' => 'required',
            'checkout' => 'required',
            'hotel_id' => 'required',
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'phone.required' => 'Bạn chưa nhập điện thoại',
           // 'email.required' => 'Bạn chưa nhập email',
           // 'email.email' => 'Email không hợp lệ',
            'checkin.required' => 'Bạn chưa nhập ngày đến',
            'checkout.required' => 'Bạn chưa nhập ngày đi',
            'hotel_id.required' => 'Bạn chưa chọn khách sạn',
        ]);       
       
        $dataArr['total_price'] =(int) str_replace(',', '', $dataArr['total_price']);
        $dataArr['tien_coc'] = (int) str_replace(',', '', $dataArr['tien_coc']);
        $dataArr['extra_fee'] = (int) str_replace(',', '', $dataArr['extra_fee']);
        $dataArr['con_lai'] = (int) str_replace(',', '', $dataArr['con_lai']);
        $dataArr['phone'] = str_replace('.', '', $dataArr['phone']);
        $dataArr['phone'] = str_replace(' ', '', $dataArr['phone']);
        
        if($dataArr['checkin']){
            $tmpDate = explode('/', $dataArr['checkin']);
            $dataArr['checkin'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }
        if($dataArr['checkout']){
            $tmpDate = explode('/', $dataArr['checkout']);
            $dataArr['checkout'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }

        if($dataArr['book_date']){
            $tmpDate = explode('/', $dataArr['book_date']);
            $dataArr['book_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        }else{
            $dataArr['book_date'] = date('Y-m-d');    
        }
        if($dataArr['ngay_coc']){
            $tmpDate = explode('/', $dataArr['ngay_coc']);
            $dataArr['ngay_coc'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }
        if(Auth::user()->role < 3){
            $dataArr['user_id'] = $dataArr['user_id'];
        }else{
            $dataArr['user_id'] = Auth::user()->id;
        }
        $dataArr['name'] = ucwords($dataArr['name']);
        $dataArr['danh_sach'] = ucwords($dataArr['danh_sach']);
        $rs = Booking::create($dataArr);
        $booking_id = $rs->id;
        foreach($dataArr['room_name'] as $key => $room_name){
            $dataArr['price_sell'][$key] = (int) str_replace(',', '', $dataArr['price_sell'][$key]);

            if($dataArr['price_sell'][$key] > 0){
                $dataArr['original_price'][$key] = (int) str_replace(',', '', $dataArr['original_price'][$key]);
                $dataArr['room_total_price'][$key] = (int) str_replace(',', '', $dataArr['room_total_price'][$key]);
                $dataRoom = [
                    'booking_id' => $booking_id,
                    'room_name' => $room_name,
                    'price_sell' => $dataArr['price_sell'][$key],
                    'nights' => $dataArr['room_nights'][$key],
                    'original_price' => $dataArr['original_price'][$key],
                    'room_amount' => $dataArr['room_amount'][$key],
                    'room_notes' => $dataArr['room_notes'][$key],
                    'total_price' => $dataArr['room_total_price'][$key]
                ];
                BookingRooms::create($dataRoom);
            }
        }
        $detailUser = User::find($dataArr['user_id']);
        $this->replyMessHotel($dataArr, $booking_id, $detailUser);
        Session::flash('message', 'Tạo mới thành công');
        $book_date = date('d/m/Y', strtotime($dataArr['book_date']));
        return redirect()->route('booking.index', ['type' => 2, 'book_date' => $book_date, 'hotel_id' => $dataArr['hotel_id']]); 
    }
     public function storeTicket(Request $request)
    {
        $dataArr = $request->all();
        
        $this->validate($request,[
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required', 
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'phone.required' => 'Bạn chưa nhập điện thoại',
            'address.required' => 'Bạn chưa chọn nơi giao.', 
           
        ]);       
       
        $dataArr['total_price'] =(int) str_replace(',', '', $dataArr['total_price']);
        $dataArr['tien_coc'] = (int) str_replace(',', '', $dataArr['tien_coc']);
        $dataArr['extra_fee'] = 0;
        $dataArr['con_lai'] = (int) str_replace(',', '', $dataArr['con_lai']);
        $dataArr['phone'] = str_replace('.', '', $dataArr['phone']);
        $dataArr['phone'] = str_replace(' ', '', $dataArr['phone']);
        $tmpDate = explode('/', $dataArr['use_date']);
        $dataArr['use_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        if($dataArr['book_date']){
            $tmpDate = explode('/', $dataArr['book_date']);
            $dataArr['book_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
        }else{
            $dataArr['book_date'] = date('Y-m-d');    
        }

      
        if($dataArr['ngay_coc']){
            $tmpDate = explode('/', $dataArr['ngay_coc']);
            $dataArr['ngay_coc'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }
        if(Auth::user()->role < 3){
            $dataArr['user_id'] = $dataArr['user_id'];
        }else{
            $dataArr['user_id'] = Auth::user()->id;
        }
        $dataArr['name'] = ucwords($dataArr['name']);
        $rs = Booking::create($dataArr);
        $booking_id = $rs->id;
        foreach($dataArr['ticket_type_id'] as $key => $ticket_type_id){
            $dataArr['price_sell'][$key] = (int) str_replace(',', '', $dataArr['price_sell'][$key]);

            if($dataArr['price_sell'][$key] > 0){
                $dataArr['price'][$key] = (int) str_replace(',', '', $dataArr['price'][$key]);
                $dataArr['total'][$key] = (int) str_replace(',', '', $dataArr['total'][$key]);
                $dataArr['commission'][$key] = (int) str_replace(',', '', $dataArr['commission'][$key]);
                $dataRoom = [
                    'booking_id' => $booking_id,
                    'ticket_type_id' => $ticket_type_id,
                    'price_sell' => $dataArr['price_sell'][$key],
                    'price' => $dataArr['price'][$key],
                    'amount' => $dataArr['amount'][$key],
                    'total' => $dataArr['total'][$key],
                    'commission' => $dataArr['commission'][$key],
                ];
                Tickets::create($dataRoom);
            }
        }
        $detailUser = User::find($dataArr['user_id']);
        $this->replyMessTicket($dataArr, $rs);
        Session::flash('message', 'Tạo mới thành công');
        $book_date = date('d/m/Y', strtotime($dataArr['book_date']));
        return redirect()->route('booking.index', ['type' => 3, 'book_date' => $book_date]); 
    }

     public function updateTicket(Request $request)
    {
        $dataArr = $request->all();
        
        $this->validate($request,[
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required', 
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'phone.required' => 'Bạn chưa nhập điện thoại',
            'address.required' => 'Bạn chưa chọn nơi giao.', 
           
        ]);       
       
      
        $dataArr['phone'] = str_replace('.', '', $dataArr['phone']);
        $dataArr['phone'] = str_replace(' ', '', $dataArr['phone']);        

        $dataArr['name'] = ucwords($dataArr['name']);
        $detail = Booking::find($dataArr['id']);
        
        $detail->update($dataArr);
        $booking_id = $detail->id;
       
        Session::flash('message', 'Cập nhật thành công');
        $book_date = date('d/m/Y', strtotime($detail->book_date));
        return redirect()->route('ticket.manage', ['book_date' => $book_date]); 
    }
   
  
    public function trangthaigoi($call_status){
        switch ($call_status) {
            case '1':
                $text = "Chưa gọi";
                break;
            case '2':
                $text = "Gọi OK";
                break;
            case '3':
                $text = "Chưa nghe máy";
                break;
            case '4':
                $text = "Thuê bao";
                break;
            case '5':
                $text = "Sai số";
                break;
            case '6':
                $text = "Dời ngày";
                break;
            case '7':
                $text = "Khách hủy";
                break;
            default:
                # code...
                break;
        }
        return $text;
    }
    public function replyMessCapNhat($rs){
        $id = $rs->id;
        $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe';
        //$strpad = str_pad($booking_id, 5, '0', STR_PAD_LEFT);
       // $use_date = date('d/m', strtotime($dataArr['use_date']));
        //$booking_code = 'T'.$ctv_id.$strpad;
        
        // reply cho sales
        $textStr = '*** CẬP NHẬT ***'."\n\r";
        $textStr .= 'Mã booking: PTT'.$id."\n\r";       
        $textStr .= 'Tên KH: '.$rs->name."\n\r";
        $textStr .= 'Nơi đón: '.$rs->address."\n\r";
        $textStr .= 'Trạng thái: '.$this->trangthaigoi($rs->call_status)."\n\r";
        if($rs->hdv){
             $textStr .= 'HDV: '.$rs->hdv->name."\n\r";
        }else{
            $textStr .= 'HDV: chưa chọn'."\n\r";
        }
        if($rs->hdv_notes){
             $textStr .= 'Ghi chú: '.$rs->hdv_notes."\n\r";
        }
        
        $arrData = [
            'recipient' => [
                'user_id' => '7317386031055599346',
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";

        $arrData = [
            'recipient' => [
                'user_id' => $rs->user->zalo_id,
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";

    }
    public function replyMess($dataArr, $rs){
        $id = $rs->id;
        $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe';
        //$strpad = str_pad($booking_id, 5, '0', STR_PAD_LEFT);
        $use_date = date('d/m', strtotime($dataArr['use_date']));
        //$booking_code = 'T'.$ctv_id.$strpad;
        $detailUser = User::find($dataArr['user_id']);
        $sales = "";
        $zalo_sales_id = null;
        $zalo_sales_id = $detailUser->zalo_id;
        $sales = $detailUser->name;
        $sales_phone = $detailUser->phone;

        // reply cho sales
        if($rs->tour_id == 1){
            $loai_tour = '4 ĐẢO';
        }elseif($rs->tour_id == 2){
            $loai_tour = '2 ĐẢO';
        }else{
            $loai_tour = 'RẠCH VẸM';
        }
        if($rs->tour_type == 1){
            $tour_type = 'Tour ghép';
        }elseif($rs->tour_type == 2){
            $tour_type = 'Tour VIP';
        }else{
            $tour_type = 'Thuê cano';
        }
        $address = $rs->location_id > 0 ? $rs->location->name : $rs->address;
        $textStr = 'Mã booking: PTT'.$id."\n\r";
        $textStr .= 'Loại tour: '.$loai_tour."\n\r";
        $textStr .= 'Hình thức: '.$tour_type."\n\r";
        $textStr .= 'Ngày đi: '.date('d/m/Y', strtotime($rs->use_date))."\n\r";
        $textStr .= 'Tên KH: '.$rs->name."\n\r";
        $textStr .= 'Số điện thoại: '.$rs->phone."\n\r";
        $textStr .= 'Nơi đón: '.$address."\n\r";
        $textStr .= 'Người lớn: '.$rs->adults."\n\r";
        if($rs->childs){
            $textStr .= 'Trẻ em: '.$rs->childs."\n\r";    
        }
        if($rs->infants){
            $textStr .= 'Em bé: '.$rs->infants."\n\r";    
        }
        
        $textStr .= 'Phần ăn: '.$rs->meals."\n\r";  
        if($rs->extra_fee){
            $textStr .= 'Phụ thu: '.number_format($rs->extra_fee)."\n\r";    
        }  
        if($rs->discount){
            $textStr .= 'Giảm giá: '.number_format($rs->discount)."\n\r";    
        }
        $textStr .= 'Tổng tiền: '.number_format($rs->total_price)."\n\r";    

        if($rs->tien_coc){
            if($rs->tien_coc == $rs->total_price){
                $textStr .= 'ĐÃ THANH TOÁN'."\n\r";        
            }else{
                $textStr .= 'Đã cọc: '.number_format($rs->tien_coc)."\n\r";        
                $textStr .= 'Còn lại: '.number_format($rs->con_lai)."\n\r";        
            }
            
        }
        if($rs->notes){
            if($rs->ko_cap_treo == 1){
                $textStr .= "KHÔNG ĐI CÁP TREO\n\r";
            }
            $textStr .= $rs->notes."\n\r";
        }
        
        if(isset($sales)){
            $textStr .= 'Sales: '.$sales.' - '.$sales_phone."\n\r";;    
        }
        $textStr .= 'Hotline: 0911 380 111';
        $arrData = [
            'recipient' => [
                'user_id' => '7317386031055599346',
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";

        if($zalo_sales_id){
            $arrData = [
                'recipient' => [
                    'user_id' => $zalo_sales_id,
                ], 
                'message' => [
                    'text' => $textStr,
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
            # Print response.
            echo "<pre>$result</pre>";
        }
        
        $arrData = [
            'recipient' => [
                'user_id' => '991620930417152188',
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";
    
      
    }

    public function replyMessTicket($dataArr, $rs){
        $id = $rs->id;
        $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe';
        //$strpad = str_pad($booking_id, 5, '0', STR_PAD_LEFT);
        $use_date = date('d/m', strtotime($dataArr['use_date']));
        //$booking_code = 'T'.$ctv_id.$strpad;
        $detailUser = User::find($dataArr['user_id']);
        $sales = "";
        $zalo_sales_id = null;
        $zalo_sales_id = $detailUser->zalo_id;
        $sales = $detailUser->name;
        $sales_phone = $detailUser->phone;

        // reply cho sales        
        $textStr = 'Mã booking: PTV'.$id."\n\r";
        $textStr .= 'Ngày giao: '.date('d/m/Y', strtotime($rs->use_date))."\n\r";
        $textStr .= 'Tên KH: '.$rs->name."\n\r";
        $textStr .= 'Số ĐT: '.$rs->phone."\n\r";
        $textStr .= 'Nơi giao: '.$rs->address."\n\r";

        $ticketTypeArr = TicketType::pluck('name', 'id')->toArray();
        $textStr .= '************************'."\n\r";
        foreach($rs->tickets as $r){
            $textStr .= $ticketTypeArr[$r->ticket_type_id]." - ".number_format($r->price_sell)." * ".$r->amount." vé".' = '.number_format($r->total)."\n\r";
        }
        $textStr .= '************************'."\n\r";       
        $textStr .= 'Tổng tiền: '.number_format($rs->total_price)."\n\r";    

        if($rs->tien_coc){
            if($rs->tien_coc == $rs->total_price){
                $textStr .= 'ĐÃ THANH TOÁN'."\n\r";        
            }else{
                $textStr .= 'Đã cọc: '.number_format($rs->tien_coc)."\n\r";        
                $textStr .= 'Còn lại: '.number_format($rs->con_lai)."\n\r";        
            }
            
        }
        if($rs->notes){
            $textStr .= $rs->notes."\n\r";
        }
        
        if(isset($sales)){
            $textStr .= 'Sales: '.$sales.' - '.$sales_phone."\n\r";;    
        }
        $textStr.='*********'."\n\r";
        $arrData = [
            'recipient' => [
                'user_id' => '7317386031055599346',
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";
        //
       // Gửi thủy
        $arrData = [
            'recipient' => [
                'user_id' => '7377773620524751389',
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";

        if($zalo_sales_id){
            $arrData = [
                'recipient' => [
                    'user_id' => $zalo_sales_id,
                ], 
                'message' => [
                    'text' => $textStr,
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
            # Print response.
            echo "<pre>$result</pre>";
        }
        

    }
    public function replyMessTicketUpdate($dataArr, $rs){
        $id = $rs->id;
        $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=D_H0KyiFGoWqy49MpmjCB3xnIbhE5IvOL-yfGFfpJJzL_p8whM117Ws7G1kN8qKrCwWR8x8aPLy_b1yWsmXaMIU8PblN46XyPxbdVlauP01WeWq6mrbq14tN5Y_iA6DLSjC4KlzoUabnx0SrmXy7CcMXHq_93ZP7OPPLFyCBMZnpyLyvr7ak06IAKdtdSG5bIyv_URDk70O9wLbudsGEPIVZTcwM7WLB6er9Dhak2Wu5gKOfgo57KGMZ7rcZ1rjmNgeiQTe3AqmXktDAfq83JHYyT6wPEd9soSesJyqGHIe';
        //$strpad = str_pad($booking_id, 5, '0', STR_PAD_LEFT);
        $use_date = date('d/m', strtotime($dataArr['use_date']));
        //$booking_code = 'T'.$ctv_id.$strpad;
        $detailUser = User::find($dataArr['user_id']);
        $sales = "";
        $zalo_sales_id = null;
        $zalo_sales_id = $detailUser->zalo_id;
        $sales = $detailUser->name;
        $sales_phone = $detailUser->phone;

        // reply cho sales   
        $textStr = '**** CẬP NHẬT ****'."\n\r";     
        $textStr .= 'Mã booking: PTV'.$id."\n\r";
        $textStr .= 'Ngày giao: '.date('d/m/Y', strtotime($rs->use_date))."\n\r";
        $textStr .= 'Tên KH: '.$rs->name."\n\r";
        $textStr .= 'Số ĐT: '.$rs->phone."\n\r";
        $textStr .= 'Nơi giao: '.$rs->address."\n\r";

        $ticketTypeArr = TicketType::pluck('name', 'id')->toArray();
        $textStr .= '************************'."\n\r";
        foreach($rs->tickets as $r){
            $textStr .= $ticketTypeArr[$r->ticket_type_id]." - ".number_format($r->price_sell)." * ".$r->amount." vé".' = '.number_format($r->total)."\n\r";
        }
        $textStr .= '************************'."\n\r";       
        $textStr .= 'Tổng tiền: '.number_format($rs->total_price)."\n\r";    

        if($rs->tien_coc){
            if($rs->tien_coc == $rs->total_price){
                $textStr .= 'ĐÃ THANH TOÁN'."\n\r";        
            }else{
                $textStr .= 'Đã cọc: '.number_format($rs->tien_coc)."\n\r";        
                $textStr .= 'Còn lại: '.number_format($rs->con_lai)."\n\r";        
            }
            
        }
        if($rs->notes){
            $textStr .= $rs->notes."\n\r";
        }
        
        if(isset($sales)){
            $textStr .= 'Sales: '.$sales.' - '.$sales_phone."\n\r";;    
        }
        $textStr.='*********'."\n\r";
        $arrData = [
            'recipient' => [
                'user_id' => '7317386031055599346',
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";
        //
       // Gửi thủy
        $arrData = [
            'recipient' => [
                'user_id' => '7377773620524751389',
            ], 
            'message' => [
                'text' => $textStr,
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
        # Print response.
        echo "<pre>$result</pre>";

        if($zalo_sales_id){
            $arrData = [
                'recipient' => [
                    'user_id' => $zalo_sales_id,
                ], 
                'message' => [
                    'text' => $textStr,
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
            # Print response.
            echo "<pre>$result</pre>";
        }
        

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
            'phone' => 'required',
            'use_date' => 'required',
            'location_id' => 'required'
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'phone.required' => 'Bạn chưa nhập điện thoại',
            'use_date.required' => 'Bạn chưa nhập ngày đi',
            'location_id.required' => 'Bạn chưa chọn nơi đón',
        ]);       
        $dataArr['total_price_adult'] = (int) str_replace(',', '', $dataArr['total_price_adult']);
        $dataArr['total_price_child'] = (int) str_replace(',', '', $dataArr['total_price_child']);
        $dataArr['total_price'] =(int) str_replace(',', '', $dataArr['total_price']);
        $dataArr['tien_coc'] = (int) str_replace(',', '', $dataArr['tien_coc']);
        $dataArr['extra_fee'] = (int) str_replace(',', '', $dataArr['extra_fee']);
        $dataArr['discount'] = (int) str_replace(',', '', $dataArr['discount']);
        $dataArr['con_lai'] = (int) str_replace(',', '', $dataArr['con_lai']);
        $dataArr['phone'] = str_replace('.', '', $dataArr['phone']);
        $dataArr['phone'] = str_replace(' ', '', $dataArr['phone']);
        $tmpDate = explode('/', $dataArr['use_date']);
        $dataArr['use_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        if($dataArr['ngay_coc']){
            $tmpDate = explode('/', $dataArr['ngay_coc']);
            $dataArr['ngay_coc'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }

        if($dataArr['book_date']){
            $tmpDate = explode('/', $dataArr['book_date']);
            $dataArr['book_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
        }else{
            $dataArr['book_date'] = date('Y-m-d');    
        }

        if(Auth::user()->role < 3){
            $dataArr['user_id'] = $dataArr['user_id'];
        }else{
            $dataArr['user_id'] = Auth::user()->id;
        }
        
        $dataArr['price_adult'] = $dataArr['total_price_adult']/$dataArr['adults'];

        if($dataArr['status'] == 3){
            $dataArr['hoa_hong_cty'] = $dataArr['hoa_hong_sales'] = 0;
        }
        $dataArr['hoa_hong_cty'] = (int) str_replace(',', '', $dataArr['hoa_hong_cty']);
        $dataArr['hoa_hong_sales'] = (int) str_replace(',', '', $dataArr['hoa_hong_sales']);
        $dataArr['ko_cap_treo'] = isset($dataArr['ko_cap_treo']) ? 1 : 0;

        $use_date = date('d/m/Y', strtotime($dataArr['use_date']));
        $model = Booking::find($dataArr['id']);
        $oldData = $model->toArray();
        
        unset($dataArr['_token']);
        //
        //unset($oldData['updated_at']);
        
        $book_date_old = $model->use_date;
        if($book_date_old != $use_date){
           // $dataArr['status'] = 1;
           // $dataArr['export'] = 2;
        }
        if($dataArr['hoa_hong_sales'] > 0){
           // $dataArr['status']= 2;
        }
        $dataArr['export'] = 2;
        //$dataArr['notes'] = 'Updated.'.$dataArr['notes'];
        $model->update($dataArr); 
        if(isset($dataArr['ma']) && $dataArr['ma'] == 1){           
            $this->replyMess($dataArr, $model);
        }
        unset($dataArr['ma']);
        $contentDiff = array_diff_assoc($dataArr, $oldData);
        
        if(!empty($contentDiff)){
            $oldContent = [];

            foreach($contentDiff as $k => $v){
                $oldContent[$k] = $oldData[$k];
            }
            BookingLogs::create([
                'booking_id' => $model->id,
                'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
                'action' => 2,
                'user_id' => Auth::user()->id
            ]);
        }
        
        Session::flash('message', 'Cập nhật thành công');        
        
        return redirect()->route('booking.index', ['type' => $dataArr['type'], 'book_date_from' => $use_date, 'user_id' => $dataArr['user_id'], 'tour_type' => $dataArr['tour_type']]);    
        
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
        $model = Booking::find($id);        
        $use_date = date('d/m/Y', strtotime($model->use_date));
        $type = $model->type;
		$model->delete();		
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('booking.index', ['type' => $type, 'book_date_from' => $use_date]);   
    }

    function updateHoaHong($bk){

        $arr30 = [2,3];
        $user_id = $bk->user_id;
        $percentCty = in_array($user_id, $arr30 ) ? 30:20;
        $i = 0;
       
            $i++;
            $rooms = $bk->rooms;
            $tong_hoa_hong = 0;
            foreach($rooms as $r){
                $price_sell = $r->price_sell;
                $nights = $r->nights;
                $total_price = $r->total_price;
                $original_price = $r->original_price;
                if(strlen($original_price) < 5 && $original_price > 0){
                    $original_price = $original_price*1000;
                }
                if($original_price == 0){
                    $original_price = $price_sell-50000;
                }elseif($original_price > $price_sell){
                    $original_price = $original_price/$nights/$r->room_amount;
                }
                $tong_gia_goc = $original_price*$r->room_amount*$r->nights;
                $tong_hoa_hong+= $total_price - $tong_gia_goc;

            }
            var_dump($original_price, $price_sell, '------');
            $hoa_hong_cty = $percentCty*$tong_hoa_hong/100;
            $hoa_hong_sales = $tong_hoa_hong-$hoa_hong_cty;
            var_dump($i, $bk->id, $tong_hoa_hong, $hoa_hong_cty, $hoa_hong_sales);
            echo "<hr>";
            $bk->update(['hoa_hong_sales' => $hoa_hong_sales, 'hoa_hong_cty' => $hoa_hong_cty]);
    }
}
