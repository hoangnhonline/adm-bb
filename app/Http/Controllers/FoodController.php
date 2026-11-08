<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\FoodCate;
use App\Models\Food;
use App\User;
use Helper, File, Session, Auth, Image, Hash;

class FoodController extends Controller
{

    public function index(Request $request)
    {           
        $cate_id = $request->cate_id ? $request->cate_id : null;
        $name = $request->name ? $request->name : null;
        
        $query = Food::where('status', 1);
        if($cate_id){
            $query->where('cate_id', $cate_id)->orderBy('id', 'desc');
        }        
        if($name){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        $items = $query->orderBy('id', 'desc')->paginate(20);        
        
        $foodCate = FoodDish::all();
        return view('food.index', compact( 'items', 'name', 'cate_id', 'foodCate'));
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
        return view('food.create', compact('cate_id', 'foodCate'));
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
            'cate_id' => 'required',
            'name' => 'required',
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'cate_id.required' => 'Bạn chưa chọn danh mục',
        ]);       
        $dataArr['display_order'] = (int) $dataArr['display_order'];
        $dataArr['price'] = (int) str_replace(",", "", $dataArr['price']);
        
        $rs = Food::create($dataArr);
    
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('food.index');
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

        $detail = Food::find($id);
        if( Auth::user()->role > 2 ){
            if($detail->created_user != Auth::user()->id){
                return redirect()->route('home');
            }
        }
        $cate_id = $detail->cate_id;
        return view('food.edit', compact( 'detail', 'cate_id'));
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
        $model = Food::find($dataArr['id']);
       
        $model->update($dataArr);        
        
        Session::flash('message', 'Cập nhật thành công');        
        
        return redirect()->route('food.index');    
        
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
        $model = Food::find($id);
        $oldStatus = $model->status;
        $model->update(['status'=>0]);      
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('food.index');   
    }
}
