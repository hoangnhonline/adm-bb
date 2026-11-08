<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\NguoiTuVan;
use Helper, File, Session, Auth;

class NguoiTuVanController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        $userLogin = Auth::user();       
        

        $query = NguoiTuVan::where('status', 1);
       
        $items = $query->orderBy('display_order')->get();
        return view('ntv.index', compact( 'items'));
    }
    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = NguoiTuVan::find($id);
        $model->update([$column => $value]);
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        
        return view('ntv.create');
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
        ],
        [
            'name.required' => 'Bạn chưa nhập tên',         
        ]);
        
       
        NguoiTuVan::create($dataArr);

        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('ntv.index');
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
        $detail = NguoiTuVan::find($id);
        return view('ntv.edit', compact( 'detail'));
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
            'name.required' => 'Bạn chưa nhập tên',        
        ]);

        $model = NguoiTuVan::find($dataArr['id']);
        $model->update($dataArr);
        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('ntv.index');
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
        $model = NguoiTuVan::find($id);        
        $model->update(['status' => 0]);
        
        Session::flash('message', 'Xóa dịch vụ thành công');
        return redirect()->route('ntv.index');
    }
}
