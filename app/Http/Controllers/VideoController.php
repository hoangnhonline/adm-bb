<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Video;
use Helper, File, Session, Auth;

class VideoController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        
        $items = Video::all()->sortBy('display_order');
        return view('video.index', compact( 'items' ));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create()
    {
        
        return view('video.create');
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
            'youtube_id' => 'required',
        ],
        [
            'name.required' => 'Bạn chưa nhập tên',
            'youtube_id.required' => 'Bạn chưa nhập Youtube ID'            
        ]);

        Video::create($dataArr);

        Session::flash('message', 'Tạo mới video thành công');

        return redirect()->route('video.index');
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
        
        $detail = Video::find($id);

        return view('video.edit', compact( 'detail' ));
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
            'youtube_id' => 'required',
        ],
        [
            'name.required' => 'Bạn chưa nhập tên',
            'youtube_id.required' => 'Bạn chưa nhập Youtube ID'            
        ]);
        
        $model = Video::find($dataArr['id']);

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật video thành công');

        return redirect()->route('video.index');
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
        $model = Video::find($id);
        $model->delete();

        // redirect
        Session::flash('message', 'Xóa video thành công');
        return redirect()->route('video.index');
    }
}
