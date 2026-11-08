<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth, Mail;
use App\Models\Booking;
use App\Models\BookingLogs;
use App\Models\Hotels;
use App\Models\UserNotification;
use App\Models\TaskDetail;
use App\Models\Account;
use App\Models\Ctv;
use App\Models\GrandworldSchedule;

use App\User;
use Session, Hash, Helper;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard(Request $request){
       
        $user_id = Auth::user()->id;
       
        $rsTour = Booking::whereRaw('(use_date = "'
            .date('Y-m-d').'" )')
            ->where('status', 1);
        $totalGrand = GrandworldSchedule::where('date_book', date('Y-m-d'))->where('status', 1)->sum('adults');
        
        if(Auth::user()->role != 1){
            $rsTour->where('user_id', $user_id);
        }
        $tours = $rsTour->get();
       //dd($tours);
        $allTour = $allHotel = $allTicket = $allCar = [];
        foreach($tours as $tour){
            if($tour->type == 1){
                $allTour[] = $tour;
            }elseif($tour->type == 2){
                $allHotel[] = $tour;
            }elseif($tour->type == 4){
                $allCar[] = $tour;
            }else{
                $allTicket[] = $tour;
            }
        }
        $taskCount = TaskDetail::where('task_date', date('Y-m-d'))->get()->count();
        $nvCount = Account::where('is_staff', 1)->get()->count();


        //report 
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;
        $type = $request->type ?? 1;
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";        
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 3;
        $query = Booking::where('type', 1)->where('status', '<', 3);
        if(Auth::user()->role != 1){
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
        
        }
        $items = $query->get();
        $arrResult = [];      

        foreach($items as $item){
            $day = date('d/m', strtotime($item->use_date));
            if(!isset($arrResult[$day])){
                $arrResult[$day] = [];
            }
            if(!isset($arrResult[$day][$item->tour_type])){
                $arrResult[$day][$item->tour_type] = 0;
            }
            if($item->tour_type == 1){
                $arrResult[$day][$item->tour_type] += $item->adults;                   
            }else{
                $arrResult[$day][$item->tour_type]++; 
            }
            if(!isset($arrResult[$day]['meals'])){
                $arrResult[$day]['meals'] = 0;
            }
            $arrResult[$day]['meals'] += $item->meals + $item->meals_te;
        }
       
        return view('dashboard', compact('allTour', 'allHotel', 'allTicket', 'allCar', 'taskCount', 'nvCount','month', 'year', 'time_type', 'arrSearch', 'arrResult', 'totalGrand'));   
    }
    public function mailPreview(Request $request){

        $id = $request->id;
        $tour_id = $request->tour_id ?? null;

        $detail = Booking::find($id);
        if($detail->mail_hotel == 1){
            die('Đã gửi mail book phòng');
        }
        $userDetail = User::find($detail->user_id);
        if($tour_id == 4){
            return view('mail-preview.tour', compact('detail', 'userDetail'));
        }else{           
            $detailHotel = Hotels::find($detail->hotel_id);           
            
            if($detail->hotel_id == 31 || $detail->hotel_id == 35){
                return view('mail-preview.hotel-vin', compact('detail', 'detailHotel', 'userDetail'));
            }else{
                return view('mail-preview.hotel', compact('detail', 'detailHotel', 'userDetail'));
            }
        }
        
        
        
    }
    public function mailConfirm(Request $request){

        $id = $request->id;
        $detail = Booking::find($id);
        if($detail->mail_customer == 1){
            die('Đã gửi mail cho khách');
        }       
        $detailHotel = Hotels::find($detail->hotel_id);
        $userDetail = User::find($detail->user_id);
        
        return view('mail-confirm', compact('detail', 'detailHotel', 'userDetail'));
        
    }
    public function saveBookingCode(Request $request){
        $id = $request->id;
        $booking_code = $request->booking_code;
        $detail = Booking::find($id);
        $detail->update(['booking_code' => $booking_code]);
    }
    public function saveHoaHong(Request $request){
        $user = Auth::user();
        $id = $request->id;
        $hoa_hong_sales = $request->hoa_hong_sales;
        $hoa_hong_sales = (int) str_replace(',', '', $hoa_hong_sales);
        // if(strlen($hoa_hong_sales) <= 4){
        //     $hoa_hong_sales = $hoa_hong_sales."000";
        // }
        $detail = Booking::find($id);

        // luu log
        $oldData = ['hoa_hong_sales' => $detail->hoa_hong_sales, 'status' => $detail->status];
        $dataArr = ['hoa_hong_sales' => $hoa_hong_sales, 'status' => 2];
        $contentDiff = array_diff_assoc($dataArr, $oldData);
        
        if(!empty($contentDiff)){
            $oldContent = [];

            foreach($contentDiff as $k => $v){
                $oldContent[$k] = $oldData[$k];
            }
            $rsLog = BookingLogs::create([
                'booking_id' =>  $id,
                'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
                'action' => 3, // ajax hoa hong
                'user_id' => Auth::user()->id
            ]);
            // push notification
           // dd($rs);
           // $userIdPush = Helper::getUserIdPushNoti($id, 1);
          // dd($userIdPush);
            // foreach($userIdPush as $idPush){
            //     if($idPush > 0){
            //         UserNotification::create([
            //             'title' => 'Hoa hồng PTT'.$id.' vừa được '. $user->name." cập nhật",
            //             'content' => Helper::parseLog($rsLog),
            //             'user_id' => $idPush,
            //             'booking_id' => $id,
            //             'date_use' => $detail->use_date,
            //             //'data' => json_encode($dataArr),
            //             'type' => 2, // tinh hoa hong
            //             'is_read' => 0
            //         ]);
            //     }            
            // }
        }
        // cap nhat
        $detail->update($dataArr);
    }
    public function bookPhong(Request $request){
        $id = $request->id;
        $detail = Booking::find($id);
         if($detail->mail_hotel == 1){
            die('Đã gửi mail book phòng');
        }
        //dd($detail->rooms);
       
        $detailHotel = Hotels::find($detail->hotel_id);
        $userDetail = User::find($detail->user_id);
        if($detail->hotel_book > 0){
            $detailBook = Hotels::find($detail->hotel_book);
            $tmpEmail = explode(';', $detailBook->email);
        }else{
            $tmpEmail = explode(';', $detailHotel->email);
        }
        $emailArr = (array) $tmpEmail[0];
        $emailCC = array_slice($tmpEmail, 1);
        $arrCtvPhung = [305, 306, 307, 308, 309, 310, 311, 312, 313];
        if($detail->ctv_id > 0){
            $detailCtv = Ctv::find($detail->ctv_id);
            // cc cho email ctv
            if(in_array($detailCtv->id, $arrCtvPhung)){
                $emailCC[] = 'phungtravel1988@gmail.com';
                $emailCC[] = $detailCtv->email;
            }else{
                $emailCC[] = $detailCtv->email;    
            }           
        }
       // dd($emailCC);
         // cc cho email chinh           
        $emailCC[] = $userDetail->email;
        // neu email booking ko trung với email user thì cc email trong booking
        // if($detail->email != $userDetail->email){
        //     $emailCC[] = $detail->email;
        // }       
        
        if($userDetail->email == 'phungtravel1988@gmail.com'){
            $emailCC[] = "tunganphung88@gmail.com";
        }
     
        $emailCC[] = 'acc@plantotravel.vn';
     
        if ($detail->hotel_id == 31 || $detail->hotel_id == 35) {
            Mail::send('mail.mail-hotel-vin',
            [                   
                'detail'             => $detail,
                'detailHotel' => $detailHotel
            ],
            function($message) use ($detail, $emailArr, $emailCC, $detailHotel) {  
                
                 $title = $detail->name." - ";            
                $title .= date('d/m/Y', strtotime($detail->checkin))." - ".date('d/m/Y', strtotime($detail->checkout))." - VIN 5 SAO PHÚ QUÔC";
             
                

                $message->subject($title);
                $message->to($emailArr);
                $message->cc($emailCC);
                //$message->replyTo('', $dataArr['full_name']);
                $message->from('booking@plantotravel.vn', 'Plan To Travel');
                $message->sender('booking@plantotravel.vn', 'Plan To Travel');
        });
        }else{
            Mail::send('mail.mail-hotel',
            [                   
                'detail'             => $detail,
                'detailHotel' => $detailHotel,
                'userDetail' => $userDetail
            ],
            function($message) use ($detail, $emailArr, $emailCC, $detailHotel, $userDetail) {  
                //if($detailHotel->id == 23 || $detailHotel->id == 39){
                    $title = $detailHotel->name. "/".$detail->name."/".date('d.m', strtotime($detail->checkin))."-".date('d.m.y', strtotime($detail->checkout))."-".$detail->phone;
                // }else{
                //     $title = 'Plan To Travel gửi yêu cầu đặt phòng ';            
                //     $title .= date('d/m/Y', strtotime($detail->checkin))." - ".$detail->name." - ".$detail->phone;
                // }
                
                $message->subject($title);
                $message->to($emailArr);
                $message->cc($emailCC);
                //$message->replyTo('', $dataArr['full_name']);
                $message->from('booking@plantotravel.vn', 'Plan To Travel');
                $message->sender('booking@plantotravel.vn', 'Plan To Travel');
        });
        }
        
        $detail->update(['mail_hotel' => 1]);
        Session::flash('message', 'Gửi mail book phòng thành công');
        return redirect()->route('booking-hotel.index', ['book_date' => date('d/m/Y', strtotime($detail->book_date)), 'hotel_id' => $detail->hotel_id]);
    }
    public function bookTourCauMuc(Request $request){
        $id = $request->id;
        $detail = Booking::find($id);
         if($detail->mail_hotel == 1){
            die('Đã gửi mail book tour');
        }
        //dd($detail->rooms);      
        
        $userDetail = User::find($detail->user_id);
        
        $emailArr = ['salemanager.johnstours@phuquoctrip.com'];      
        
        $emailCC[] = $userDetail->email;
      
        $emailCC[] = 'acc@plantotravel.vn';
      
        $emailCC[] = 'nhungoc@plantotravel.vn';
       
            Mail::send('mail.tour-cau-muc',
            [                   
                'detail'             => $detail,
                'userDetail' => $userDetail
            ],
            function($message) use ($detail, $emailArr, $emailCC, $userDetail) {  
                
                $title = 'Plan To Travel gửi yêu cầu đặt tour câu mực ';            
                $title .= date('d/m/Y', strtotime($detail->use_date))." - ".$detail->name." - ".$detail->phone;
                
                $message->subject($title);
                $message->to($emailArr);
                $message->cc($emailCC);
                //$message->replyTo('', $dataArr['full_name']);
                $message->from('booking@plantotravel.vn', 'Plan To Travel');
                $message->sender('booking@plantotravel.vn', 'Plan To Travel');
        });
    
        
        $detail->update(['mail_hotel' => 1]);
        Session::flash('message', 'Gửi mail book tour câu mực thành công');
        return redirect()->route('booking.index', ['type'=> 1, 'use_date_from' => date('d/m/Y', strtotime($detail->use_date)), 'tour_id' => 4]);
    }
    public function confirmPhong(Request $request){
        $id = $request->id;
        $detail = Booking::find($id);
         if($detail->mail_customer == 1){
            die('Đã gửi mail book phòng');
        }
        //dd($detail->rooms);
       
        $detailHotel = Hotels::find($detail->hotel_id);
        $userDetail = User::find($detail->user_id);
        //$emailArr = [$detail->email, $userDetail->email];
        $emailArr = [$detail->email, $userDetail->email];
        //dd($userDetail);
        //return view('mail', compact('detail'));
        Mail::send('confirm',
            [                   
                'detail'             => $detail,
                'detailHotel' => $detailHotel
            ],
            function($message) use ($detail, $detailHotel, $emailArr) {   
                $title = 'Plan To Travel gửi xác nhận đặt phòng tại '.$detailHotel->name." ngày ";            
                $title .= date('d/m/Y', strtotime($detail->checkin));   

                $message->subject($title);
                $message->to($emailArr);
                //$message->replyTo('', $dataArr['full_name']);
                $message->from('booking@plantotravel.vn', 'Plan To Travel');
                $message->sender('booking@plantotravel.vn', 'Plan To Travel');
        });
        $detail->update(['mail_customer' => 1]);
        Session::flash('message', 'Gửi mail book phòng thành công');
        return redirect()->route('booking-hotel.index', ['book_date' => date('d/m/Y', strtotime($detail->book_date))]);
    }
}
