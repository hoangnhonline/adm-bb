<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Helper, File, Session, Auth;

class BannerController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {      
          
        $arrSearch['status'] = $status = isset($request->status) ? $request->status : null;
        $arrSearch['object_id'] = $object_id = $request->object_id;
        $arrSearch['object_type'] = $object_type = $request->object_type;
        $detail = (object) [];
        if( $object_type == 1){
            $detail->name = "Slide home";
        }
        
        $query = Banner::where(['object_id'=>$object_id, 'object_type' => $object_type]);
        if( $status ){
            $query->where('status', $status);
        }
       
        $items = $query->orderBy('display_order')->get();
       // dd($items->count());die;
        return view('app-banner.index', compact( 'items', 'detail', 'arrSearch'));
    }
    public function lists(Request $request){
          
        return view('app-banner.list');   
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
          
        $detail = (object) [];
        $object_id = $request->object_id;
        $object_type = $request->object_type;
        $detail = (object) [];
        if( $object_type == 1){
            $detail->name = "Slide home";
        }
        
        return view('app-banner.create', compact('object_id', 'object_type', 'detail'));
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
        
        /*$this->validate($request,[
            'name' => 'required',
            'slug' => 'required',
        ],
        [
            'name.required' => 'Bạn chưa nhập tên danh mục',
            'slug.required' => 'Bạn chưa nhập slug',
        ]);
        */
        $dataArr['display_order'] = (int) $dataArr['display_order'];
        $dataArr['status'] = isset($dataArr['status'])  ? 1 : 0;       

        $dataArr['image_url'] = strpos($dataArr['image_url'], 'ttps:') ? $dataArr['image_url'] :  "https://enb.vn".$dataArr['image_url'];
        Banner::create($dataArr);

        Session::flash('message', 'Tạo mới banner thành công');

        return redirect()->route('banner.index', ['object_id' => $dataArr['object_id'], 'object_type' => $dataArr['object_type']]);
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
    public function edit(Request $request)
    {
          
        $id = $request->id;
        $detailBanner = Banner::find($id);
        $detail = Banner::find($id);
        $object_id = $request->object_id;
        $object_type = $request->object_type;
        $detail = (object) [];
        if( $object_type == 1){
            $detail->name = "Slide home";
        }
        
        return view('app-banner.edit', compact( 'detail', 'detailBanner', 'object_id', 'object_type'));
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
        $dataArr['status'] = isset($dataArr['status'])  ? 1 : 0;
       
        $dataArr['image_url'] = strpos($dataArr['image_url'], 'ttps:') ? $dataArr['image_url'] :  "https://enb.vn".$dataArr['image_url'];
        $dataArr['display_order'] = (int) $dataArr['display_order'];
        $model = Banner::find($dataArr['id']);

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật banner thành công');

        return redirect()->route('banner.index', ['object_id' => $dataArr['object_id'], 'object_type' => $dataArr['object_type']]);
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
        $model = Banner::find($id);
        $model->delete();

        // redirect
        Session::flash('message', 'Xóa banner thành công');
        return redirect()->route('banner.index', ['object_type' => $model->object_type, 'object_id' => $model->object_id]);
    }
}
