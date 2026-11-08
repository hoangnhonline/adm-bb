<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cate;
use App\Models\Hotels;
use App\Models\BookingDetail;
use App\Models\ChietKhau;
use App\Models\BookingLogs;
use App\Models\Account;
use App\Models\Cost;
use App\Models\NguoiTuVan;
use App\Models\Branch;
use App\User;
use App\Models\Settings;
use Helper, File, Session, Auth, Image, Hash;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UserNotification;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class BookingNoAuthController extends Controller
{
    public function maps(Request $request){
        return view('booking.maps');
    }
    public function fastSearch(Request $request){
        $arrSearch['keyword'] = $keyword = $request->keyword ? $request->keyword : null;
        if($keyword){
            if(strlen($keyword) <= 9){
                $id_search = $keyword;
                $id_search = strtolower($id_search);
                $id_search = str_replace("ptt", "", $id_search);
                $id_search = str_replace("pth", "", $id_search);
                $id_search = str_replace("ptv", "", $id_search);
                $id_search = str_replace("ptx", "", $id_search);
                $id_search = str_replace("ptc", "", $id_search);
                $arrSearch['id_search'] = $id_search;

                $detail = Booking::findOrFail($id_search);
                if($detail->type == 1){
                    return redirect()->route('booking.edit', ['id' => $id_search, 'keyword' => $keyword]);
                }elseif($detail->type == 2){
                    return redirect()->route('booking-hotel.edit', ['id' => $id_search, 'keyword' => $keyword]);
                }elseif($detail->type == 3){
                    return redirect()->route('booking-ticket.edit', ['id' => $id_search, 'keyword' => $keyword]);
                }elseif($detail->type == 4){
                    return redirect()->route('booking-car.edit', ['id' => $id_search, 'keyword' => $keyword]);
                }elseif($detail->type == 5){
                    return redirect()->route('booking-camera.edit', ['id' => $id_search, 'keyword' => $keyword]);
                }
            }else{
                $phone = $keyword;

                $detail = Booking::where('phone', $phone)->first();
                if($detail->type == 1){
                    return redirect()->route('booking.edit', ['id' => $detail->id, 'keyword' => $keyword]);
                }elseif($detail->type == 2){
                    return redirect()->route('booking-hotel.edit', ['id' => $detail->id, 'keyword' => $keyword]);
                }elseif($detail->type == 3){
                    return redirect()->route('booking-ticket.edit', ['id' =>$detail->id, 'keyword' => $keyword]);
                }elseif($detail->type == 4){
                    return redirect()->route('booking-car.edit', ['id' => $detail->id, 'keyword' => $keyword]);
                }elseif($detail->type == 5){
                    return redirect()->route('booking-camera.edit', ['id' => $detail->id, 'keyword' => $keyword]);
                }
            }
        }


    }
    public function notExport(){

        $query = Booking::where('type', 1)->where('status', 1)->where('export', 2);
        $query->where('use_date', date('Y-m-d'));
        if(Auth::user()->role > 2){
            $query->where('user_id', Auth::user()->id);
        }
        $allList = $query->get();
        $items  = $query->paginate(1000);
        $count = $items->count();
        if($count > 0){
            return view('alert.not_export', compact( 'count'));
        }
    }
    public function checkError(Request $request){
        $setting = Settings::pluck('value', 'name')->toArray();

        $price_cable_adult = $setting['price_cable_adult'];
        $price_cable_child = $setting['price_cable_child'];
        $id = $request->id;
        $rs = Booking::find($id);
        //dd($rs);
        $user = Account::find($rs->user_id);
        $adults = $rs->adults;
        $child = $rs->child;
        $cap_nl = $rs->cap_nl;
        $cap_te = $rs->cap_te;
        $meals = $rs->meals;
        $meals_te = $rs->meals_te;
        $price_adult = $rs->price_adult;
        $price_child = $rs->price_child;
       // dd($price_adult, $price_child, $meals, $meals_te, $cap_te, $cap_te);
      //  dd($rs->discount);
        $errorStr = '';
        $defaultDiscount = 0;
        if($rs->level == 1){ // HH 90k
            if($rs->tour_type == 1){ // tour ghep
                if($adults > 3 && $adults <= 7){
                    $defaultDiscount = 20000;
                    $price_adult = $price_adult - 20000;
                }elseif($adults > 7 && $adults <= 12){
                    $defaultDiscount = 40000;
                    $price_adult = $price_adult - 40000;
                }elseif($adults > 12){
                    $defaultDiscount = 80000;
                    $price_adult = $price_adult - 80000;
                }
                // tổng tiền giảm giá mặc định
                $totalDiscountDefault = $defaultDiscount*$adults;
                $defaultHoaHong = $adults*90000;
                $salesDiscount = $rs->discount - $totalDiscountDefault;
                //dd($salesDiscount);
                if($totalDiscountDefault < $rs->discount){
                    $defaultHoaHong = $defaultHoaHong - $salesDiscount;
                }
                // tính tổng tiền
                $total_price = $adults*$price_adult
                                + $child*$price_child
                                + $meals*200000 + $meals_te*100000
                                + $price_cable_adult*$cap_nl + $price_cable_child*$cap_te
                                + $rs->phu_thu;
                // check tổng tiền đúng hay sai?
                if($total_price != ($rs->total_price + $salesDiscount)){
                    $errorStr .= "Tổng tiền sai";
                    if($rs->total_price > ($total_price - $salesDiscount)){
                        $errorStr .= "(dư ".number_format($rs->total_price-$total_price)."), ";
                    }else{
                        $errorStr .= "(thiếu ".number_format($total_price-$rs->total_price)."), ";
                    }
                }

                //check hoa hồng
                if($defaultHoaHong != $rs->hoa_hong_sales){
                    $errorStr .= "Hoa hồng sai";
                    if($defaultHoaHong > $rs->hoa_hong_sales){
                        $errorStr .= "(thiếu ".number_format($defaultHoaHong-$rs->hoa_hong_sales)."), ";
                    }else{
                        $errorStr .= "(dư ".number_format($rs->hoa_hong_sales-$defaultHoaHong)."), ";
                    }
                }
                //check thực thu
                $tong_thuc_thu = $rs->tien_thuc_thu + $rs->tien_coc + $salesDiscount;
                if($tong_thuc_thu != $total_price){
                    $errorStr .= "Thực thu sai";
                    if($tong_thuc_thu > $total_price){
                        $errorStr .= "(dư ".number_format($tong_thuc_thu-$total_price)."), ";
                    }else{
                        $errorStr .= "(thiếu ".number_format($total_price-$tong_thuc_thu)."), ";
                    }
                }

            }elseif($rs->tour_type == 3){ // thue cano
                if($rs->hoa_hong_sales != 200000){
                    return 1;
                }
            }
        }elseif($rs->level == 2 ){
            // tính tổng tiền
            $total_price = $adults*300000
                            + $child*235000
                            + $meals*200000 + $meals_te*100000
                            + $price_cable_adult*$cap_nl + $price_cable_child*$cap_te
                            + $rs->phu_thu;
            //check thực thu
            $tong_thuc_thu = $rs->tien_thuc_thu + $rs->tien_coc;
            if($tong_thuc_thu != $rs->total_price){
                $errorStr .= "Thực thu sai";
                if($tong_thuc_thu > $rs->total_price){
                    $errorStr .= "(dư ".number_format($tong_thuc_thu-$rs->total_price)."), ";

                }else{
                    $errorStr .= "(thiếu ".number_format($rs->total_price-$tong_thuc_thu)."), ";

                }
                $tien_thua = $tong_thuc_thu - $rs->total_price;
                //dd($tien_thua);
                if($tien_thua < 0){
                    if($rs->hoa_hong_sales > 0){
                        $errorStr .= "đang âm tiền nên KO CÓ HH, ";
                    }
                    $errorStr .= "Hoa hồng sai";

                    $errorStr .= "(dư ".number_format($rs->hoa_hong_sales)."), ";

                }

            }else{
                $tien_thua_net = $tong_thuc_thu - $total_price;
                if($rs->hoa_hong_sales != $tien_thua_net){
                    $errorStr .= "Hoa hồng sai";
                    if($tien_thua_net > $rs->hoa_hong_sales){
                        $errorStr .= "(thiếu ".number_format($tien_thua_net-$rs->hoa_hong_sales)."), ";
                    }else{
                        $errorStr .= "(dư ".number_format($rs->hoa_hong_sales-$tien_thua_net)."), ";
                    }
                }
            }


        }
        return $errorStr;
    }
    public function calCommissionHotel(Request $request){
        $from_date = $request->from_date;
        $to_date = $request->to_date;
       $all = Booking::where('type', 2)->where('checkin', '>=', $from_date)->where('checkin', '<=', $to_date)
       ->whereNotIn('status',[3,4])->get();

        foreach($all as $bk){

            $user_id = $bk->user_id;
            if($user_id == 18){
                $percentCty = 100;
            }else{
                $percentCty = 30;
            }
            $i = 0;
            $i++;
            $rooms = $bk->rooms;
            $tong_hoa_hong = 0;
            foreach($rooms as $r){
                if($r->total_price == 0){
                    $r->update(['total_price' => $r->price_sell*$r->nights]);
                }
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

            $hoa_hong_cty = $percentCty*$tong_hoa_hong/100;
            $hoa_hong_sales = $tong_hoa_hong-$hoa_hong_cty;

            echo "<hr>";
            $bk->update(['hoa_hong_sales' => $hoa_hong_sales, 'hoa_hong_cty' => $hoa_hong_cty]);

        }

    }

    public function calTour(){
        //$arr30 = [2,3];
        $all = Booking::where('type', 1)->where('tour_type', 1)->where('use_date', '>=', '2021-02-01')->where('use_date', '<=', '2021-02-28')
        ->whereIn('user_id', [2,3,5,6,8,11,19,20,31,36,44,50,52,64,48,46])
       // ->where('user_id', 75)
        ->get();

        foreach($all as $bk){
            // $adults = $bk->adults;
            // if($adults*700000 + $bk->childs*300000 > $bk->total_price && $bk->meals > 0){
            //     $bk->update(['ko_cap_treo' => 1]);
            // }
            if($bk->discount > 0){
                $flag = $bk->adults*120000 == $bk->discount ? true : false;
                if($flag){
                    $hoa_hong_sales = $hoa_hong_cty = $bk->adults*80000;
                    $bk->update(['hoa_hong_sales' => $hoa_hong_sales]);
                }
                $flag = $bk->adults*100000 == $bk->discount ? true : false;
                if($flag){
                    $hoa_hong_sales = $hoa_hong_cty = $bk->adults*90000;
                    $bk->update(['hoa_hong_sales' => $hoa_hong_sales]);
                }
                $flag = $bk->discount%70000 == 0 ? true : false;
                if($flag){
                    $hoa_hong_sales = $hoa_hong_cty = $bk->adults*90000;
                    $bk->update(['hoa_hong_sales' => $hoa_hong_sales]);
                }
            }
        }
    }
    public function totalByUser(Request $request){
        $month = $request->month;
        //$user_id = $request->user_id;
        $fromdate = '2020-'.$month.'-01';
        $todate = '2020-'.$month.'-31';

        $listAll = Booking::where('use_date', '>=', $fromdate)
                    ->where('use_date', '<=', $todate)
                    ->where('type', 1)
                    ->get();
        $arrUser = [];
        foreach($listAll as $bk){
            if($bk->status != 3){
                if(!isset($arrUser[$bk->user_id])){
                    $arrUser[$bk->user_id][$bk->use_date] = $bk->use_date;
                }
                if(!isset($arrUser[$bk->user_id][$bk->use_date])){
                    $arrUser[$bk->user_id][$bk->use_date] = $bk->use_date;
                }
            }
        }

        dd($arrUser);
    }
    public function daily(){
        $today = date('Y-m-d');
        $listAll = Booking::where('use_date',$today)
                    ->where('type', 1)
                    ->where('status', 1)
                    ->get();
        $arr = [];
        $total = 0;
        foreach($listAll as $bk){
            $total += $bk->adults;
            if(!isset($arr[$bk->user_id])){
                $arr[$bk->user_id] = $bk->adults;
            }else{
                $arr[$bk->user_id] += $bk->adults;
            }
        }
        foreach($arr as $user_id => $total_by_user){
            $detailUser = User::find($user_id);

            $url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=ZaVgNfRnPLUDG-XRalLgKuT2u5UJwn83YYxgIf302XZv9iX1ljKr5ia6ongBp3bgwYJd19F03q_vDECyjzeoDVGeuJglm6a_yY_hMwpR1IwmRiz6nTv0Bw0igLNz-c1Tv16i0fttL5FYGgC3hAOW3SPB_dA6-0rYw1py1uli77Vn4jCIfifGREGLln2Yfaf3sdEP6OsPSMVDGQCGX_DuRl95kXwEe4b5a6s6J-AhVp2zHwzrwBXP8Prjaotvt4mzkMo1SkE22G2XQze8leeALDj4tX5FQ2s7kZsGxcDL';
            //$strpad = str_pad($booking_id, 5, '0', STR_PAD_LEFT);
            $str = "Chào ".$detailUser->name.", hôm nay bạn có tổng cộng ".$total_by_user." khách đi tour 4 đảo. Vui lòng kiểm tra lại và gọi ngay hotline 0911380111 nếu có thiếu sót. Cảm ơn bạn!";
            //$booking_code = 'T'.$ctv_id.$strpad;


            $zalo_sales_id = $detailUser->zalo_id;

            $arrData = [
                'recipient' => [
                    'user_id' => $zalo_sales_id,
                ],
                'message' => [
                    'text' => $str,
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
    public function revertExport(){
        $today = date('Y-m-d', strtotime('tomorrow'));
        $listAll = Booking::where('use_date',$today)
                    ->where('type', 1)
                    ->where('status','<>', 3)
                    ->get();
        $arr = [];
        $total = 0;
        //dd($listAll);
        foreach($listAll as $bk){
            echo $bk->id."<hr>";
             // luu log
                $oldData = ['status' => $bk->status, 'export' => $bk->export];
                $dataArr = ['status' => 1, 'export' => 2];
                // $contentDiff = array_diff_assoc($dataArr, $oldData);
                // if(!empty($contentDiff)){
                //     $oldContent = [];

                //     foreach($contentDiff as $k => $v){
                //         $oldContent[$k] = $oldData[$k];
                //     }
                //     $rsLog = BookingLogs::create([
                //         'booking_id' =>  $bk->id,
                //         'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
                //         'action' => 3, // ajax hoa hong
                //         'user_id' => Auth::user()->id
                //     ]);
                // }
            $bk->update($dataArr);
        }


    }
    public function changeExport(Request $request){
        $id = $request->id;
        $model = Booking::find($id);

        // luu log
        $oldData = ['export' => $model->export];
        $dataArr = ['export' => 1];
        $contentDiff = array_diff_assoc($dataArr, $oldData);
        if(!empty($contentDiff)){
            $oldContent = [];

            foreach($contentDiff as $k => $v){
                $oldContent[$k] = $oldData[$k];
            }
            BookingLogs::create([
                'booking_id' =>  $id,
                'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
                'action' => 3, // ajax hoa hong
                'user_id' => Auth::user()->id
            ]);
        }
        // update

        $model->update(['export' => 1]);
    }
    public function changeStatus(Request $request){
        $id = $request->id;
        $model = Booking::find($id);

         // luu log
        $oldData = ['status' => $model->status];
        $dataArr = ['status' => 2];
        $contentDiff = array_diff_assoc($dataArr, $oldData);
        if(!empty($contentDiff)){
            $oldContent = [];

            foreach($contentDiff as $k => $v){
                $oldContent[$k] = $oldData[$k];
            }
            BookingLogs::create([
                'booking_id' =>  $id,
                'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
                'action' => 3, // ajax hoa hong
                'user_id' => Auth::user()->id
            ]);
        }
        // update
        $model->update(['status' => 2]);
    }
    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = Booking::find($id);


        $model->update([$column => $value]);
    }

    public function tinhHoaHong(Request $request){
       //  $listAll = Booking::where('use_date', '>=', '2020-11-01')
       //           ->where('use_date', '<=', '2020-11-30')
       //           ->where('type', 1)
       //           ->whereIn('user_id', [18])
       //           ->where('status','<>', 3)
       //              ->where('tour_type', 1)
       //              //->where('discount', 0)
       //           //->join('users', 'users.id', '=', 'booking.user_id')
       //           ->get();
       // // dd($listAll);
       //  foreach($listAll as $data){
       //    $data->update(['status' => 2]);
       //   // $hoa_hong_cty = 0;
       //   //    if($data->discount%70000 == 0){
       //   //        $hoa_hong_sales = $data->adults*90000;
       //   //        $data->update(['status' => 2, 'hoa_hong_sales' => $hoa_hong_sales, 'hoa_hong_cty' => $hoa_hong_cty]);
       //   //    }
       //   //    if($data->discount%100000 == 0){
       //   //        $hoa_hong_sales = $data->adults*80000;
       //   //        $data->update(['status' => 2, 'hoa_hong_sales' => $hoa_hong_sales, 'hoa_hong_cty' => $hoa_hong_cty]);
       //   //    }


       //  //   $hoa_hong_sales = $hoa_hong_sales - $data->discount;


       //  }
    }

    public function info(Request $request){
        $id = $request->id;
        $detail = Booking::find($id);
        $listUser = User::whereIn('level', [1,2,3,4,5,6,7])->where('status', 1)->get();
        return view('booking.modal', compact( 'detail', 'listUser'));
    }

    public function saveInfo(Request $request){
        $detail = Booking::find($request->booking_id);
        $hdv_id = $request->hdv_id;
        $call_status = $request->call_status;
        $hdv_notes = $request->hdv_notes;
        $detail->update(['hdv_id' => $hdv_id, 'hdv_notes' => $hdv_notes, 'call_status' => $call_status]);
        //$this->replyMessCapNhat($detail); //chatbot
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        $month_do = date('m');

        $arrSearch['id_search'] = $id_search = $request->id_search ? $request->id_search : null;
        $arrSearch['status'] = $status = $request->status ? $request->status : [1,2,4];
        $arrSearch['da_thu'] = $da_thu = $request->da_thu > -1 ? $request->da_thu : -1;
        $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;
        $arrSearch['branch_id'] = $branch_id = $request->branch_id ? $request->branch_id : null;
        $arrSearch['branch_ids'] = $branch_ids = $request->branch_ids ? $request->branch_ids : [];
        $arrSearch['cate_id'] = $cate_id = $request->cate_id ?? null;
        $arrSearch['nguoi_tu_van'] = $nguoi_tu_van = $request->nguoi_tu_van ?? null;
        $arrSearch['partner_id'] = $partner_id = $request->partner_id ?? null;
        $arrSearch['phone'] = $phone = $request->phone ? $request->phone : null;

        $arrSearch['per_com'] = $per_com = $request->per_com ? $request->per_com : null;

        $arrSearch['sort_by'] = $sort_by = $request->sort_by ? $request->sort_by : 'created_at';

        $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ? $request->nguoi_thu_tien : null;
        $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc = $request->nguoi_thu_coc ? $request->nguoi_thu_coc : null;
        if(Auth::check() && Auth::user()->role == 3){
            $defaultTimeType = 1;
        }else{
            $defaultTimeType = 3;
        }
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : $defaultTimeType;
        $arrSearch['search_by'] = $search_by = $request->search_by ? $request->search_by : 2;

            $use_df_default = Auth::check() && Auth::user()->id == 151 ? date('d/m/Y', strtotime('yesterday')) : date('d/m/Y', time());
            $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
        $arrSearch['keyword'] = $keyword = $request->keyword ? $request->keyword : null;

        $arrSearch['created_at'] = $created_at = $request->created_at ? $request->created_at :  null;
        $chi_tien_mat = $chi_khac = 0;

        $query = Booking::where('city_id', 1);

        if($keyword){
            if(strlen($keyword) <= 8){
                $id_search = $keyword;
            }else{
                $phone = $keyword;
            }
        }
        $arrSearch['month'] = $month = $request->month ?? date('m');
        $arrSearch['year'] = $year = $request->year ?? date('Y'); ;
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));
        // if($ko_cap_treo > -1){
        //     $query->where('ko_cap_treo', $ko_cap_treo);
        // }
        $use_date_from_format = $use_date_to_format = null;
        if($id_search){
           //  dd($id_search);
            $id_search = strtolower($id_search);
            $id_search = str_replace("ptt", "", $id_search);
            $id_search = str_replace("pth", "", $id_search);
            $id_search = str_replace("ptv", "", $id_search);
            $id_search = str_replace("ptx", "", $id_search);
            $id_search = str_replace("ptc", "", $id_search);
            $arrSearch['id_search'] = $id_search;
            $query->where('id', $id_search);
        }elseif($phone){
            $arrSearch['phone'] = $phone;
            $query->where('phone', $phone);
        }else{
            if($da_thu > -1){
                $query->where('da_thu', $da_thu);
            }

            if($status){
                $query->whereIn('status', $status);
            }
            if($cate_id){
                $query->join('booking_detail', 'booking_detail.booking_id', '=', 'booking.id')
                ->where('booking_detail.cate_id', $cate_id);
            }

            if($per_com){

                $arrSearch['per_com'] = $per_com;
                $query->where('per_com', $per_com);
            }

            if($phone){
                $query->where('phone', $phone);
            }
            // if($branch_id){
            //     $query->where('branch_id', $branch_id);
            // }
            if($partner_id){
                $query->where('partner_id', $partner_id);
            }
            if($branch_ids){
              $query->whereIn('branch_id', $branch_ids);
            }
            if($nguoi_tu_van){

                $arrSearch['nguoi_tu_van'] = $nguoi_tu_van;
                $query->where('nguoi_tu_van', $nguoi_tu_van);
            }
            
            $arrSearch['xe_4t'] = $xe_4t = 0;
            $query->where('xe_4t', 0);

            if($nguoi_thu_tien){
                $query->where('nguoi_thu_tien', $nguoi_thu_tien);
            }
            if($nguoi_thu_coc){
                $query->where('nguoi_thu_coc', $nguoi_thu_coc);
            }
            if(Auth::check()){
                if(Auth::user()->role == 1 || Auth::user()->role == 2){
                    if($user_id && $user_id > 0){
                        $arrSearch['user_id'] = $user_id;
                        $query->where('user_id', $user_id);
                    }
                }elseif(Auth::user()->role == 4){
                    $arrSearch['branch_id'] = $branch_id = Auth::user()->branch_id;
                    $query->where('branch_id', $branch_id);

                }else{
                    $arrSearch['user_id'] = Auth::user()->id;
                    $query->where(function ($query) {
                        $query->where('user_id', '=', Auth::user()->id)
                              ->orWhere('partner_id', '=', Auth::user()->id);
                    });
                }
            }
            

            if($created_at){
                $tmpDate = explode('/', $created_at);
                $created_at_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                $query->where('created_at','>=', $created_at_format." 00:00:00");
                $query->where('created_at','<=', $created_at_format." 23:59:59");
            }else{
                if($time_type == 1){ // theo thangs
                    $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
                    $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));

                    $query->where('use_date','>=', $mindate);
                    $query->where('use_date', '<=', $maxdate);
                    $queryTienmat = Cost::where('status','>', 0)
                                ->where('nguoi_chi', 1);
                    $queryTienmat->where('date_use','>=', $mindate);
                    $queryTienmat->where('date_use',  '<=', $maxdate);
                    
                    // if($branch_ids){
                    //   $queryTienmat->whereIn('branch_id', $branch_ids);
                    // }
                    $chi_tien_mat = $queryTienmat->sum('total_money');

                    $queryCostOther = Cost::where('status', 1)
                                ->where('nguoi_chi','<>', 1);
                    $queryCostOther->where('date_use','>=', $mindate);
                    $queryCostOther->where('date_use',  '<=', $maxdate);
                    $chi_khac = $queryCostOther->sum('total_money');


                }elseif($time_type == 2){ // theo khoang ngay
                    $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
                    $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

                    $queryTienmat = Cost::where('status','>', 0)
                                ->where('nguoi_chi', 1);


                    if($use_date_from){
                        $arrSearch['use_date_from'] = $use_date_from;
                        $tmpDate = explode('/', $use_date_from);
                        $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                        $query->where('use_date','>=', $use_date_from_format);
                        $queryTienmat->where('date_use','>=', $use_date_from_format);
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
                        $queryTienmat->where('date_use', '<=', $use_date_to_format);
                    }
                    $chi_tien_mat = $queryTienmat->sum('total_money');
                }else{
                    $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());

                    $tmpDate = explode('/', $use_date_from);
                    $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                    $query->where('use_date','=', $use_date_from_format);
                    $day = $tmpDate[0];
                    $month_do = $tmpDate[1];
                    $chi_tien_mat = Cost::where('date_use', $use_date_from_format)
                                ->where('status','>', 0)
                                ->where('nguoi_chi', 1)
                                ->sum('total_money');



                }
            }
        }//end else
        $chietkhauList = ChietKhau::orderBy('sort_order')->get();

        $query->select('booking.*', 'booking.id as booking_id')->orderBy($sort_by, 'desc');

        $allList = $query->get();

        $items  = $query->paginate(300);
       // dd($items);


        $arrData = [
            'tong_bk' => 0,
            'tong_tien' => 0,
            'tong_tien_ko_cam_jt' => 0,
            'tong_giam' => 0,
            'tong_chietkhau' => 0,
            'tong_conlai' => 0,
            'tong_tien_mat' => 0,
            'tong_tien_coc' => 0,
            'tong_chuyen_khoan' => 0,
            'ck_john' => 0,
            'ck_20' => 0,
            'tong_cam' => 0,
            'tong_du_doi' => 0,
            'tong_du_don' => 0
        ];
        foreach($allList as $item){
            $arrData['tong_bk']++;
            $arrData['tong_giam'] += $item->discount;
            $arrData['tong_chietkhau'] += $item->commision;

            foreach($item->details as $detail){
                if($detail->cate_id == 68){
                    $arrData['tong_du_doi'] += $detail->amount;
                }
                if($detail->cate_id == 63){
                    $arrData['tong_du_don'] += $detail->amount;       
                }
                if($detail->cate_id == 71) // cam 500 JohnTour
                {
                    $arrData['tong_tien_ko_cam_jt'] += 0;
                    $arrData['tong_cam'] += $detail->total_price;
                }else{
                    $arrData['tong_tien_ko_cam_jt'] += $detail->total_price;
                }
            }
            $arrData['tong_tien'] += $item->total_price;
            if($item->nguoi_thu_tien == 1){
                $arrData['tong_tien_mat'] += $item->total_price;
            }elseif($item->nguoi_thu_tien == 2 || $item->nguoi_thu_tien == 3 || $item->nguoi_thu_tien == 5 || $item->nguoi_thu_tien == 6){

                $arrData['tong_chuyen_khoan'] += $item->total_price - $item->discount - $item->tien_coc;
            }
            if($item->nguoi_thu_coc == 4){ // tien mat - Han thu
                $arrData['tong_tien_mat'] += $item->tien_coc;
            }else{
                $arrData['tong_tien_coc'] += $item->tien_coc;
            }
            $arrData['tong_conlai'] += $item->con_lai;

            if($item->branch_id == 4){
                $arrData['ck_john'] += $item->total_price*0.25;
            }
            if($item->branch_id == 3 || $item->branch_id == 5){
                $arrData['ck_20'] += $item->total_price*0.20;
            }
        }
        //dd($arrData);
        $agent = new Agent();
        if(Auth::check()){
            if(Auth::user()->role == 1){
                if($agent->isMobile()){
                    $view = 'booking.m-index';
                }else{
                    $view = 'booking.index';
                }
            }else{
                if(Auth::user()->branch_id > 0){
                   if($agent->isMobile()){
                        $view = 'booking.beach-m-index';
                    }else{
                        $view = 'booking.beach-index';
                    } 
                }else{
                    if($agent->isMobile()){
                        $view = 'booking.mod-m-index';
                    }else{
                        $view = 'booking.mod-index';
                    }
                }

                    
            }
        }else{
            if($agent->isMobile()){
                $view = 'guest.m-index';
            }else{
                $view = 'guest.index';
            }
        }
        
        $cateList = Dish::orderBy('display_order')->get();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $beachArr = [];
        foreach($branchList as $beach){
            $beachArr[$beach->id] = $beach->name;
        }
        $partners = Account::where('is_partner', 1)->get();
        $tuvanList = NguoiTuVan::where('status', 1 )->orderBy('display_order')->get();
        return view($view, compact( 'items', 'arrSearch', 'keyword', 'time_type', 'month', 'year', 'month_do', 'arrData', 'chi_tien_mat', 'chietkhauList', 'chi_khac', 'branch_id', 'cateList', 'cate_id', 'use_date_from_format', 'use_date_to_format', 'branchList', 'beachArr', 'partners', 'partner_id', 'tuvanList'));

    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */

    public function create(Request $request)
    {
        $user = Auth::user();

        $cateList = Dish::orderBy('display_order')->get();

        $view = "booking.add-tour";

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $dateDefault = date('d/m/Y');

        $use_date = $request->use_date ?? $dateDefault;
        $tmp = explode('/', $use_date);
        $bill = 1;
        if($use_date){
            $use_date_format = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];

            $max = Booking::where('use_date', $use_date_format)->max('bill_no');
            $bill = $max + 1;
        }
        $chietkhauList = ChietKhau::orderBy('sort_order')->get();
        $partners = Account::where('is_partner', 1)->get();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view($view, compact('cateList', 'use_date', 'bill', 'chietkhauList', 'partners', 'branchList'));
    }
    /**
    * Store a newly created resource in storage.
    *
    * @param  Request  $request
    * @return Response
    */
    public function store(Request $request)
    {
        $user = Auth::user();
        $dataArr = $request->all();

        $this->validate($request,[
            'use_date' => 'required',
            'count_services' => 'required',
            'total_price' => 'required',
            'nguoi_thu_tien' => 'required'
        ],
        [
            'use_date.required' => 'Bạn chưa nhập Ngày chơi',
            'count_services.required' => 'Bạn chưa chọn Dịch vụ',
            'nguoi_thu_tien.required' => 'Bạn chưa chọn Người thu tiền',
            'total_price.required' => 'Bạn chưa nhập Tổng tiền',
        ]);

        $dataArr['total_price'] =(int) str_replace(',', '', $dataArr['total_price']);
        $dataArr['commision'] = (int) str_replace(',', '', $dataArr['commision']);
        $dataArr['tien_coc'] = (int) str_replace(',', '', $dataArr['tien_coc']);
        $dataArr['discount'] = (int) str_replace(',', '', $dataArr['discount']);
        $dataArr['con_lai'] = (int) str_replace(',', '', $dataArr['con_lai']);
        $dataArr['phone'] = str_replace('.', '', $dataArr['phone']);
        $dataArr['phone'] = str_replace(' ', '', $dataArr['phone']);
        $dataArr['da_thu'] = isset($dataArr['da_thu']) ? 1 : 0;
        $tmpDate = explode('/', $dataArr['use_date']);

        $dataArr['use_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];

        $dataArr['name'] = ucwords($dataArr['name']);
        if($dataArr['name'] == ''){
            $dataArr['name'] = 'Khach';
        }
        if($dataArr['phone'] == ''){
            $dataArr['phone'] = '0901424868';
        }
        // ------------- add customer
        //dd($dataArr);
        $rs = Booking::create($dataArr);
        $booking_id = $rs->id;

        //
        foreach($dataArr['cate_id'] as $k => $cate_id){
            if($dataArr['amount'][$k] > 0 && $dataArr['total'][$k] > 0){
               // dd($dataArr['total'][$k]);
                $total = str_replace(',', '', $dataArr['total'][$k]);

                BookingDetail::create([
                    'booking_id' => $booking_id,
                    'cate_id' => $cate_id,
                    'price' => $total/$dataArr['amount'][$k],
                    'amount' => $dataArr['amount'][$k],
                    'total_price' => $total
                ]);
            }
        }

        unset($dataArr['_token']);
        //store log
        $rsLog = BookingLogs::create([
            'booking_id' => $booking_id,
            'content' => json_encode($dataArr),
            'user_id' => $user->id,
            'action' => 1
        ]);


        Session::flash('message', 'Tạo mới thành công');
        $use_date = date('d/m/Y', strtotime($dataArr['use_date']));

        return redirect()->route('booking.index', ['use_date_from' => $use_date]);
    }



    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return Response
    */
    public function edit($id, Request $request)
    {

        $detail = Booking::find($id);
        $cateList = Dish::orderBy('display_order')->get();
        $chietkhauList = ChietKhau::orderBy('sort_order')->get();
        $partners = Account::where('is_partner', 1)->get();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view('booking.edit-tour', compact( 'detail', 'cateList','chietkhauList', 'partners', 'branchList'));

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
        $user = Auth::user();

        $dataArr = $request->all();

        $this->validate($request,[
            'use_date' => 'required',
            'count_services' => 'required',
            'total_price' => 'required',
            'nguoi_thu_tien' => 'required'
        ],
        [
            'use_date.required' => 'Bạn chưa nhập Ngày chơi',
            'count_services.required' => 'Bạn chưa chọn Dịch vụ',
            'nguoi_thu_tien.required' => 'Bạn chưa chọn Người thu tiền',
            'total_price.required' => 'Bạn chưa nhập Tổng tiền',
        ]);

        $dataArr['total_price'] =(int) str_replace(',', '', $dataArr['total_price']);
        $dataArr['commision'] = (int) str_replace(',', '', $dataArr['commision']);
        $dataArr['tien_coc'] = (int) str_replace(',', '', $dataArr['tien_coc']);
        $dataArr['discount'] = (int) str_replace(',', '', $dataArr['discount']);
        $dataArr['con_lai'] = (int) str_replace(',', '', $dataArr['con_lai']);
        $dataArr['phone'] = str_replace('.', '', $dataArr['phone']);
        $dataArr['phone'] = str_replace(' ', '', $dataArr['phone']);
        $dataArr['da_thu'] = isset($dataArr['da_thu']) ? 1 : 0;
        $tmpDate = explode('/', $dataArr['use_date']);

        $dataArr['use_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];

        $dataArr['name'] = ucwords($dataArr['name']);
        if($dataArr['name'] == ''){
            $dataArr['name'] = 'Khach';
        }
        if($dataArr['phone'] == ''){
            $dataArr['phone'] = '0901424868';
        }

        $use_date = date('d/m/Y', strtotime($dataArr['use_date']));
        $model = Booking::find($dataArr['id']);
        $booking_id = $model->id;
        $oldData = $model->toArray();
        BookingDetail::where('booking_id', $booking_id)->delete();
        foreach($dataArr['cate_id'] as $k => $cate_id){
            if($dataArr['amount'][$k] > 0 && $dataArr['total'][$k] > 0){
               // dd($dataArr['total'][$k]);
                $total = str_replace(',', '', $dataArr['total'][$k]);

                BookingDetail::create([
                    'booking_id' => $booking_id,
                    'cate_id' => $cate_id,
                    'price' => $total/$dataArr['amount'][$k],
                    'amount' => $dataArr['amount'][$k],
                    'total_price' => $total
                ]);
            }
        }

        unset($dataArr['_token']);
        unset($dataArr['count_services']);
        unset($dataArr['cate_id']);
        unset($dataArr['amount']);
        unset($dataArr['total']);
        $model->update($dataArr);

        $contentDiff = array_diff_assoc($dataArr, $oldData);

        $booking_id = $model->id;
        if(!empty($contentDiff)){
            $oldContent = [];

            foreach($contentDiff as $k => $v){
                $oldContent[$k] = $oldData[$k];
            }
            $rsLog = BookingLogs::create([
                'booking_id' => $booking_id,
                'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
                'action' => 2,
                'user_id' => $user->id
            ]);
        }

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('booking.index', ['use_date_from' => $use_date]);

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
        $model->update(['status' => 0]);
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('booking.index', ['type' => $type, 'use_date_from' => $use_date, 'tour_id' => $model->tour_id]);
    }


    public function checkUnc(Request $request){
        $id = $request->id;
        $rs = Booking::find($id);
        //dd($rs);
        $errorStr = '';
        if($rs->nguoi_thu_tien == 2){
            $paymentList = $rs->payment;
            if($paymentList->count() == 0){
                $errorStr = 'Thiếu UNC';
            }
        }

        return $errorStr;
    }
    public function parseSms(Request $request){
        $dataArr['body'] = $request->sms;
        Helper::smsParser($dataArr);
    }
    public function sms(Request $request)
    {
        return view('booking.sms');
    }
    public function smsPayment(Request $request){
        $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
        fwrite($myfile, "LOG " . date("Y/m/d H:i:s") . ": " . json_encode($request->all()) . " \n");
        fclose($myfile);

        //Try v1
        try{
            $dataArr = $request->all();

            // +++
            if ((array_key_exists($_ = 'ID', $dataArr) && empty($dataArr[$_]))
                && (array_key_exists($_ = 'so_tien', $dataArr) && empty($dataArr[$_]))
                && (array_key_exists($_ = 'thoi_gian', $dataArr) && empty($dataArr[$_]))
                && (array_key_exists($_ = 'noi_dung', $dataArr) && empty($dataArr[$_]))
                && (array_key_exists($_ = 'so_tk', $dataArr) && empty($dataArr[$_]))
                && (array_key_exists($_ = 'body', $dataArr) && !empty($dataArr[$_]))
            ) {
                $isSuccess = Helper::smsParser($dataArr);
                return response()->json([
                    'success' => $isSuccess
                ]);
            }
        }catch(\Exception $ex){
            $myfile = fopen("logs.txt", "a") or die("Unable to open file!");
            fwrite($myfile, 'Errors: ' . date("Y/m/d H:i:s") . " " .$ex->getMessage()."\n");
            fclose($myfile);
        }
    }
}
