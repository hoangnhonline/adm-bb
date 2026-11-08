<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\BookingBbc;
use App\Models\Cate;
use App\Models\CateBbc;
use App\Models\BookingBbcDetail;
use App\Models\ChietKhau;
use App\Models\BookingBbcLogs;
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
class BookingBbcController extends Controller
{
       
    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = BookingBbc::find($id);
        $model->update([$column => $value]);
    }

    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        $month_do = date('m');
        $userLogin = Auth::user();       
        $arrSearch['status'] = $status = $request->status ? $request->status : [1,2,4];
        $arrSearch['da_thu'] = $da_thu = $request->da_thu > -1 ? $request->da_thu : -1;       
        $arrSearch['user_id'] = $user_id = $request->user_id ? $request->user_id : null;        
        $arrSearch['cate_id'] = $cate_id = $request->cate_id ?? null;
        $arrSearch['nguoi_tu_van'] = $nguoi_tu_van = $request->nguoi_tu_van ?? null;       
        $arrSearch['phone'] = $phone = $request->phone ? $request->phone : null;
        $arrSearch['sort_by'] = $sort_by = $request->sort_by ? $request->sort_by : 'created_at';
        $arrSearch['nguoi_thu_tien'] = $nguoi_thu_tien = $request->nguoi_thu_tien ? $request->nguoi_thu_tien : null;
        $arrSearch['nguoi_thu_coc'] = $nguoi_thu_coc = $request->nguoi_thu_coc ? $request->nguoi_thu_coc : null;
        if($userLogin->role == 3){
            $defaultTimeType = 1;
        }else{
            $defaultTimeType = 3;
        }
        $arrSearch['time_type'] = $time_type = $request->time_type ? $request->time_type : $defaultTimeType;
        $arrSearch['search_by'] = $search_by = $request->search_by ? $request->search_by : 2;

            $use_df_default = $userLogin->id == 151 ? date('d/m/Y', strtotime('yesterday')) : date('d/m/Y', time());
            $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
        $arrSearch['keyword'] = $keyword = $request->keyword ? $request->keyword : null;

        $arrSearch['created_at'] = $created_at = $request->created_at ? $request->created_at :  null;
        $chi_tien_mat = $chi_khac = 0;
        
        $query = BookingBbc::where('city_id', 1);            

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
    
        if($da_thu > -1){
            $query->where('da_thu', $da_thu);
        }
        if($status){
            $query->whereIn('status', $status);
        }
        if($cate_id){
            $query->join('booking_bbc_detail', 'booking_bbc_detail.booking_id', '=', 'booking_bbc.id')
            ->where('booking_bbc_detail.cate_id', $cate_id);
        }
        if($nguoi_tu_van){

            $arrSearch['nguoi_tu_van'] = $nguoi_tu_van;
            $query->where('nguoi_tu_van', $nguoi_tu_van);
        }
               

        $branch_id = 7;
        $branch_ids = [7];

        if($nguoi_thu_tien){
            $query->where('nguoi_thu_tien', $nguoi_thu_tien);
        }

        if($userLogin->role == 1 || $userLogin->role == 2){
            if($user_id && $user_id > 0){
                $arrSearch['user_id'] = $user_id;
                $query->where('user_id', $user_id);
            }
        }elseif($userLogin->role == 4){
            $arrSearch['branch_id'] = $branch_id = Auth::user()->branch_id;
            $query->where('branch_id', $branch_id);
        }else{
            $arrSearch['user_id'] = Auth::user()->id;
            $query->where(function ($query) {
                $query->where('user_id', '=', Auth::user()->id)
                      ->orWhere('partner_id', '=', Auth::user()->id);
            });
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
                
                if($branch_ids){
                  $queryTienmat->whereIn('branch_id', $branch_ids);
                }
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
        
        $chietkhauList = ChietKhau::orderBy('sort_order')->get();
        
     
        $query->select('booking_bbc.*', 'booking_bbc.id as booking_id')->orderBy($sort_by, 'desc');
        
        

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
        $arrCateChupAnh = Cate::where('chup_anh', 1)->pluck('id')->toArray();
        $arrData['chup_anh'] = [];
        foreach($allList as $item){
            $arrData['tong_bk']++;
            $arrData['tong_giam'] += $item->discount;
            $arrData['tong_chietkhau'] += $item->commision;
            

            foreach($item->details as $detail){
                //chup anh
                if(in_array($detail->cate_id, $arrCateChupAnh)){
                    if(!isset($arrData['chup_anh'][$detail->cate_id])){
                        $arrData['chup_anh'][$detail->cate_id]['amount'] = 0;
                        $arrData['chup_anh'][$detail->cate_id]['total_price'] = 0;
                    }
                    $arrData['chup_anh'][$detail->cate_id]['amount'] += $detail->amount;
                    $arrData['chup_anh'][$detail->cate_id]['total_price'] += $detail->total_price;
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

        }
      //  dd($arrData['chup_anh']);
        $agent = new Agent();
      
       
        if($agent->isMobile()){
            $view = 'booking-bbc.m-index';
        }else{
            $view = 'booking-bbc.index';
        }
        
        if($userLogin->branch_id == 7){
       
                $cateList = CateBbc::where('status', 1)->orderBy('display_order')->get();    
                $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
            
            $partners = Account::where('is_partner', 1)->where('branch_id', 7)->get();
        }else{
            $cateList = Cate::orderBy('display_order')->get();                
            $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
            $partners = Account::where('is_partner', 1)->get();
        }
        
        $beachArr = [];
        foreach($branchList as $beach){
            $beachArr[$beach->id] = $beach->name;
        }
        
        
        $tuvanList = NguoiTuVan::where('status', 1 )->orderBy('display_order')->get();
        return view($view, compact( 'items', 'arrSearch', 'keyword', 'time_type', 'month', 'year', 'month_do', 'arrData', 'chi_tien_mat', 'chietkhauList', 'chi_khac', 'branch_id', 'cateList', 'cate_id', 'use_date_from_format', 'use_date_to_format', 'branchList', 'beachArr', 'partners', 'tuvanList'));

    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */

    public function create(Request $request)
    {
        $user = Auth::user();

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $dateDefault = date('d/m/Y');

        $use_date = $request->use_date ?? $dateDefault;
        $tmp = explode('/', $use_date);
        $bill = 1;
        if($use_date){
            $use_date_format = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];

            $max = BookingBbc::where('use_date', $use_date_format)->max('bill_no');
            $bill = $max + 1;
        }
        $chietkhauList = ChietKhau::orderBy('sort_order')->get();
        $partners = Account::where('is_partner', 1)->get();
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
       
        
        $cateList = CateBbc::where('chup_anh', 1)->orderBy('display_order')->get();    
        $branchList = Branch::where('id', 7)->get();
        
        $partners = Account::where('is_partner', 1)->where('branch_id', 7)->get();
        
        $tuvanList = NguoiTuVan::where('status', 1 )->orderBy('display_order')->get();
        return view('booking-bbc.add', compact('cateList', 'use_date', 'bill', 'chietkhauList', 'partners', 'branchList', 'tuvanList'));
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
            $dataArr['phone'] = '0943757943';
        }
        // ------------- add customer
       
        $dataArr['chup_anh'] = 1;
        $dataArr['da_thu'] = 1;
       
        $dataArr['created_user'] = $dataArr['updated_user'] = Auth::user()->id;
        $rs = BookingBbc::create($dataArr);
        $booking_id = $rs->id;

        //
        foreach($dataArr['cate_id'] as $k => $cate_id){
            if($dataArr['amount'][$k] > 0 && $dataArr['total'][$k] > 0){
               // dd($dataArr['total'][$k]);
                $total = str_replace(',', '', $dataArr['total'][$k]);

                BookingBbcDetail::create([
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
        $rsLog = BookingBbcLogs::create([
            'booking_id' => $booking_id,
            'content' => json_encode($dataArr),
            'user_id' => $user->id,
            'action' => 1
        ]);


        Session::flash('message', 'Tạo mới thành công');
        $use_date = date('d/m/Y', strtotime($dataArr['use_date']));

        return redirect()->route('booking-bbc.index', ['use_date_from' => $use_date]);
    }



    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return Response
    */
    public function edit($id, Request $request)
    {

        $detail = BookingBbc::find($id);
        $chietkhauList = ChietKhau::orderBy('sort_order')->get();        
       
        $cateList = CateBbc::where('status', 1)->orderBy('display_order')->get();    
        $branchList = Branch::where('id', 7)->get();            
        $partners = Account::where('is_partner', 1)->where('branch_id', 7)->get();        
        $tuvanList = NguoiTuVan::where('status', 1 )->orderBy('display_order')->get();
        return view('booking-bbc.edit' , compact( 'detail', 'cateList','chietkhauList', 'partners', 'branchList', 'tuvanList'));

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
        $dataArr['xe_4t'] = isset($dataArr['xe_4t']) ? 1 : 0;
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
        $model = BookingBbc::find($dataArr['id']);
        $booking_id = $model->id;
        $oldData = $model->toArray();
        BookingBbcDetail::where('booking_id', $booking_id)->delete();
        foreach($dataArr['cate_id'] as $k => $cate_id){
            if($dataArr['amount'][$k] > 0 && $dataArr['total'][$k] > 0){
               // dd($dataArr['total'][$k]);
                $total = str_replace(',', '', $dataArr['total'][$k]);

                BookingBbcDetail::create([
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
            $rsLog = BookingBbcLogs::create([
                'booking_id' => $booking_id,
                'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
                'action' => 2,
                'user_id' => $user->id
            ]);
        }

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('booking-bbc.index', ['use_date_from' => $use_date]);

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
        $model = BookingBbc::find($id);
        $use_date = date('d/m/Y', strtotime($model->use_date));
        $type = $model->type;
        $model->update(['status' => 0]);
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('booking-bbc.index', ['type' => $type, 'use_date_from' => $use_date, 'tour_id' => $model->tour_id]);
    }
}
