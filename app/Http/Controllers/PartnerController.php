<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\CostType;
use App\Models\TourSystem;
use Helper, File, Session, Auth;

class PartnerController extends Controller
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
        $model = Partner::find($id);   
        

        $model->update([$column => $value]);
    }
    public function index(Request $request)
    {     
        
        $name = $request->name ?? null;
        $cost_type_id = $request->cost_type_id ?? 1;
        $costTypeList = CostType::all();

        $query = Partner::where('status', 1)->where('cost_type_id', $cost_type_id);
        if($name){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        
        $items = $query->orderBy('display_order')->paginate(50);
        
        return view('partner.index', compact( 'items', 'name', 'cost_type_id', 'costTypeList'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $cost_type_id = $request->cost_type_id ?? 1;
        $costTypeList = CostType::all();
        $listTour = TourSystem::where('status',1)->where('city_id',2)->get();
        return view('partner.create', compact('costTypeList', 'cost_type_id','listTour'));
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
            'cost_type_id' => 'required',
            'name' => 'required'            
        ],
        [
            'cost_type_id' => 'Bạn chưa chọn phân loại',
            'name.required' => 'Bạn chưa nhập tên'
        ]);      

        $rs = Partner::create($dataArr);

        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('partner.index', [ 'cost_type_id' => $dataArr['cost_type_id'] ]);
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
        $detail = Partner::find($id);             
        $costTypeList = CostType::all();
        $listTour = TourSystem::where('status',1)->where('city_id',2)->get();
        return view('partner.edit', compact( 'detail', 'costTypeList','listTour'));
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
            'cost_type_id' => 'required',
            'name' => 'required'            
        ],
        [
            'cost_type_id' => 'Bạn chưa chọn phân loại',
            'name.required' => 'Bạn chưa nhập tên'
        ]);    
        
        $model = Partner::find($dataArr['id']);  

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('partner.index', [ 'cost_type_id' => $dataArr['cost_type_id'] ]);
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
        $model = Partner::find($id);
        $model->update(['status' => 0]);

        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('partner.index', ['cost_type_id' => $model->cost_type_id]);
    }
}