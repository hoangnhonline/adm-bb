<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Drivers;
use App\Models\CarCate;
use Helper, File, Session, Auth;

class DriversController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = Drivers::find($id);   
        

        $model->update([$column => $value]);
    }
    public function index(Request $request)
    {     
        
        $name = $request->name ?? null;
        $car_cate_id = $request->car_cate_id ?? null;
        $city_id = $request->city_id ?? null;
        $carCateList = CarDish::all();

        $query = Drivers::where('status', 1);
        if($car_cate_id){
            $query->where('car_cate_id', $car_cate_id);
        }
        if($name){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        if ($city_id) {
            $query->where('city_id', $city_id);
        }
        
        $items = $query->orderBy('id', 'desc')->paginate(50);
        
        return view('drivers.index', compact( 'items', 'name', 'car_cate_id', 'carCateList','city_id'));
    }
    public function ajaxList(Request $request){

        $DriversSelected = (array) $request->DriversSelected;
        
        $str_id = $request->str_id;
        $tmpArr = explode(",", $str_id);
        $DriversSelected = array_merge($DriversSelected, $tmpArr);

        $type = isset($request->type) ? $request->type : 1;

        $query = Drivers::where('type', $type);
        
        $DriversArr = $query->orderBy('id', 'desc')->get();
       
        return view('drivers.ajax-list', compact( 'DriversArr', 'type', 'DriversSelected'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $car_cate_id = $request->car_cate_id ?? null;
        $carCateList = CarDish::all();
        $back_url = $request->back_url ?? null;
        return view('drivers.create', compact('carCateList', 'car_cate_id', 'back_url'));
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
            // 'car_cate_id' => 'required',
            'name' => 'required'            
        ],
        [
            // 'car_cate_id' => 'Bạn chưa chọn phân loại',
            'name.required' => 'Bạn chưa nhập tên'
        ]);      

        $rs = Drivers::create($dataArr);

        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('drivers.index', [ 'car_cate_id' => $dataArr['car_cate_id'] ]);
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
        $detail = Drivers::find($id);             
        $carCateList = CarDish::all();
        return view('drivers.edit', compact( 'detail', 'carCateList'));
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
            'car_cate_id' => 'required',
            'name' => 'required'            
        ],
        [
            'car_cate_id' => 'Bạn chưa chọn phân loại',
            'name.required' => 'Bạn chưa nhập tên'
        ]);    
        
        $model = Drivers::find($dataArr['id']);  

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('drivers.index', [ 'car_cate_id' => $dataArr['car_cate_id'] ]);
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
        $model = Drivers::find($id);
        $model->update(['status' => 0]);

        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('drivers.index', ['car_cate_id' => $model->car_cate_id]);
    }
}