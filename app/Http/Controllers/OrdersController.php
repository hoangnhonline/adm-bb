<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\FoodCate;
use App\Models\Food;
use App\Models\Orders;
use App\Models\OrderDetail;
use App\User;
use Helper, File, Session, Auth, Image, Hash;

class OrdersController extends Controller
{

    public function image(){
        // $all = Orders::all();
        // foreach($all as $a){
        //     $date = date('d.m', strtotime($a->date_use));
        //     $img_name = date('d-m-'.$a->table_no, strtotime($a->date_use));
            
        //     $str_img = "/uploads/images/food/".$date."/".$img_name.".jpg";
        //     $a->update(['image_url' => $str_img]);
        // }
    }
    public function index(Request $request)
    {           
        $cate_id = $request->cate_id ? $request->cate_id : null;
        $name = $request->name ? $request->name : null;
        
        $query = Orders::where('status', 1);
        if($cate_id){
            $query->where('cate_id', $cate_id)->orderBy('id', 'desc');
        }        
        if($name){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : '05/02/2020';
        $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : '17/03/2020';

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
        $items = $query->orderBy('id', 'desc')->paginate(1000);     
        $total_actual_amount = 0;   
        foreach($items as $o){
            $total_actual_amount+= $o->actual_amount;
        }   

        $foodCate = FoodDish::all();
        $listUser = User::all();
        return view('orders.index', compact( 'items', 'name', 'cate_id', 'foodCate', 'listUser', 'arrSearch', 'date_use', 'total_actual_amount'));
    }
    

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {   
        $foodCate = FoodDish::all();
        $cate_id = $request->cate_id ? $request->cate_id : null;  
        $listUser = User::all();  
        $foodList = Food::all(); 
        $date_use = $request->date_use ? $request->date_use : null;
        return view('orders.create', compact('cate_id', 'foodCate', 'listUser', 'foodList', 'date_use'));
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
            'date_use' => 'required'
            
        ],
        [  
            
            'date_use.required' => 'Bạn chưa nhập ngày',
        ]);       

        $dataArr['actual_amount'] = (int) str_replace(',', '', $dataArr['actual_amount']);
        $dataArr['discount'] = (int) str_replace(',', '', $dataArr['discount']);
        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);
        if($dataArr['actual_amount'] ==0){
            $dataArr['actual_amount'] = $dataArr['total_money'];
        }
        if($dataArr['discount'] == 0){
            $dataArr['discount'] = $dataArr['total_money'] - $dataArr['actual_amount'];
        }
        $dataArr['percent_discount'] = (int) str_replace(',', '', $dataArr['percent_discount']);
        $date_use = $dataArr['date_use'];
        $tmpDate = explode('/', $dataArr['date_use']);
        $dataArr['date_use'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        $dataArr['total_food'] = 0;
        $rs = Orders::create($dataArr);
        $order_id = $rs->id;
        $countTotalFood = 0;
        foreach($dataArr['food_id'] as $k => $food_id){
            if($food_id > 0 ){
                $price = (int) str_replace(',', '', $dataArr['price'][$k]);
                $amount = str_replace(',', '', $dataArr['amount'][$k]);
                $total = (int) str_replace(',', '', $dataArr['total'][$k]);
                if($total > 0){
                    $countTotalFood++;
                    OrderDetail::create([
                        'food_id' => $food_id,
                        'price' => $price,
                        'amount' => $amount,
                        'total' => $total,
                        'order_id' => $order_id
                    ]);
                }
                
            }
            
        }
        $rs->update(['total_food' => $countTotalFood]);
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('orders.index', ['use_date_from' => $date_use]);
    }
    public function update(Request $request)
    {
        $dataArr = $request->all();
        $order_id = $dataArr['id'];
        $model= Orders::findOrFail($order_id);
        $this->validate($request,[   
            'date_use' => 'required'            
        ],
        [              
            'date_use.required' => 'Bạn chưa nhập ngày',
        ]);       

        $dataArr['actual_amount'] = (int) str_replace(',', '', $dataArr['actual_amount']);
        $dataArr['discount'] = (int) str_replace(',', '', $dataArr['discount']);
        $dataArr['total_money'] = (int) str_replace(',', '', $dataArr['total_money']);
        if($dataArr['actual_amount'] ==0){
            $dataArr['actual_amount'] = $dataArr['total_money'];
        }
        if($dataArr['discount'] == 0){
            $dataArr['discount'] = $dataArr['total_money'] - $dataArr['actual_amount'];
        }
        $dataArr['percent_discount'] = (int) str_replace(',', '', $dataArr['percent_discount']);
        $date_use = $dataArr['date_use'];
        $tmpDate = explode('/', $dataArr['date_use']);
        $dataArr['date_use'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        $dataArr['total_food'] = 0;
        $model->update($dataArr);
        
        $countTotalFood = 0;
        OrderDetail::where('order_id', $order_id)->delete();
        foreach($dataArr['food_id'] as $k => $food_id){
            if($food_id > 0 ){
                $price = (int) str_replace(',', '', $dataArr['price'][$k]);
                $amount = str_replace(',', '', $dataArr['amount'][$k]);
                $total = (int) str_replace(',', '', $dataArr['total'][$k]);
                if($total > 0){
                    $countTotalFood++;
                    OrderDetail::create([
                        'food_id' => $food_id,
                        'price' => $price,
                        'amount' => $amount,
                        'total' => $total,
                        'order_id' => $order_id
                    ]);
                }
                
            }
            
        }
        $model->update(['total_food' => $countTotalFood]);
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('orders.index', ['use_date_from' => $date_use]);
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
        
        $detail = Orders::find($id);
        $listUser = User::all();  
        $foodList = Food::all(); 
        return view('orders.edit', compact( 'detail', 'listUser', 'foodList'));
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
        $model = Orders::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=>0]);      
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('orders.index');   
    }
}
