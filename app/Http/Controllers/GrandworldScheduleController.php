<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\GrandworldSchedule;
use Maatwebsite\Excel\Facades\Excel;
use App\User;
use Helper, File, Session, Auth, Image, Hash;

class GrandworldScheduleController extends Controller
{

 
    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = GrandworldSchedule::find($id);
        $model->update([$column => $value]);
    }
    public function index(Request $request)
    {
        $cameraList = User::where('camera', 1)->where('status', 1)->get();
        $monthDefault = date('m');
        $month = $request->month ?? $monthDefault;        
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";        
        $maxdate = date("Y-m-t", strtotime($mindate));
        //dd($maxdate);
        //$maxdate = '2021-03-01';
        $maxDay = date('d', strtotime($maxdate));

        $arrSearch['booking_id'] = $booking_id = $request->booking_id ? $request->booking_id : null;        
        $arrSearch['camera_id'] = $camera_id = $request->camera_id ? $request->camera_id : null;
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : 1;
                
        $query = GrandworldSchedule::where('status', 1);
        if($booking_id){
            $query->where('booking_id', $booking_id);
        }
        if($camera_id){
            $query->where('camera_id', $camera_id);
        }          
        
        if($time_type == 1){
            $arrSearch['date_book_from'] = $date_book_from = $date_book = date('d/m/Y', strtotime($mindate));
            $arrSearch['date_book_to'] = $date_book_to = date('d/m/Y', strtotime($maxdate));
                      
            $query->where('date_book','>=', $mindate);                   
            $query->where('date_book', '<=', $maxdate);
        }elseif($time_type == 2){
            $arrSearch['date_book_from'] = $date_book_from = $date_book = $request->date_book_from ? $request->date_book_from : date('d/m/Y', time());
            $arrSearch['date_book_to'] = $date_book_to = $request->date_book_to ? $request->date_book_to : $date_book_from;

            if($date_book_from){
                $arrSearch['date_book_from'] = $date_book_from;
                $tmpDate = explode('/', $date_book_from);
                $date_book_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('date_book','>=', $date_book_from_format);
            }
            if($date_book_to){
                $arrSearch['date_book_to'] = $date_book_to;
                $tmpDate = explode('/', $date_book_to);
                $date_book_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($date_book_to_format < $date_book_from_format){
                    $arrSearch['date_book_to'] = $date_book_from;
                    $date_book_to_format = $date_book_from_format;   
                }        
                $query->where('date_book', '<=', $date_book_to_format);
            }
        }else{
            $arrSearch['date_book_from'] = $date_book_from = $arrSearch['date_book_to'] = $date_book_to = $date_book = $request->date_book_from ? $request->date_book_from : date('d/m/Y', time());
            
            $arrSearch['date_book_from'] = $date_book_from;
            $tmpDate = explode('/', $date_book_from);
            $date_book_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('date_book','=', $date_book_from_format);
        
        }
        
        $items = $query->orderBy('date_book', 'asc')->paginate(2000);     
        // $total_actual_amount = $total_quantity = 0;   
        // foreach($items as $o){
        //     $total_actual_amount+= $o->total_money;
        //     $total_quantity += $o->amount;
        // }

      
        return view('grandworld-schedule.index', compact( 'items', 'cate_id', 'arrSearch', 'date_book', 'total_actual_amount', 'total_quantity', 'month', 'time_type', 'year', 'cameraList'));
    }
    /**
    * Store a newly created resource in storage.
    *
    * @param  Request  $request
    * @return Response
    */
    
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
        $detail = GrandworldSchedule::find($id);       
        $cateList = CostType::orderBy('display_order')->get(); 
        $partnerList = Partner::where('cost_type_id', $detail->cate_id)->get();        
        return view('grandworld-schedule.edit', compact( 'detail', 'cateList', 'partnerList'));
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
        $model = GrandworldSchedule::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=>0]);      
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('grandworld-schedule.index');   
    }
}
