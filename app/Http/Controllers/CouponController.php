<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Shop;
use Jenssegers\Agent\Agent;
use App\User;

use Helper, File, Session, Auth, Image, Hash;

class CouponController extends Controller
{

    public function cal(){
       $all = Coupon::all();
       foreach($all as $a){
            $date_use = $a->date_use;
            foreach($a->details as $b){               
                $b->update(['date_use' => $date_use]);
            }
       }
    }
    public function index(Request $request)
    {          
        
        $shopList = Shop::where('status', 1)->get();
        $arrSearch['shop_id'] = $shop_id = $request->shop_id ? $request->shop_id : null;
        
        $query = Coupon::where('status', 1);
        
       
        if($shop_id){
            $query->where('shop_id', $shop_id);
        }   
        if(Auth::user()->role > 1){
            $query->where('user_id', Auth::user()->id);
        }     
       
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
        $items = $query->orderBy('id', 'desc')->paginate(2000);         
        return view('coupon.index', compact( 'items','shop_id', 'arrSearch', 'date_use', 'shopList', 'shop_id'));
    }
    

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {   
        $shopList = Shop::where('status', 1)->get();
        $shop_id = $request->shop_id ? $request->shop_id : null;
        $date_use = $request->date_use ? $request->date_use : null;
        $cateList = CostType::orderBy('display_order')->get();
        return view('coupon.create', compact('shop_id', 'date_use', 'cateList', 'shopList'));
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
            'shop_id' => 'required',
        ],
        [   
            'shop_id.required' => 'Bạn chưa chọn nhà hàng/đối tác.',
        ]);       
        $shop_id = $dataArr['shop_id'];
        $user_id = Auth::user()->id;
        $detailShop = Shop::find($shop_id);

        $code = $detailShop->precode.$user_id.'-'.rand(1000,9999);  
        $date_use = date('Y-m-d');
        Coupon::create(['code' => $code, 'zalo_id' => Auth::user()->zalo_id, 'ctv_id' => $user_id, 'user_id' => $user_id,  'shop_id' => $shop_id, 'date_use' => $date_use]);
        
        Session::flash('message', 'Tạo mới thành công');
        $date_use_format = date('d/m/Y');
        return redirect()->route('coupon.index', ['use_date_from' => $date_use_format]);
    }
    public function update(Request $request)
    {
        $dataArr = $request->all();
        $cost_id = $dataArr['id'];
        $model= Coupon::findOrFail($cost_id);
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
        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('coupon.index', ['use_date_from' => $date_use]);
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
        
        $detail = Coupon::find($id);       
        $cateList = CostType::orderBy('display_order')->get(); 
        return view('coupon.edit', compact( 'detail', 'cateList'));
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
        $model = Coupon::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=>0]);      
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('coupon.index');   
    }
}
