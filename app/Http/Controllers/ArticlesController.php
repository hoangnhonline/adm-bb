<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Articles;
use App\Models\Rating;

use Helper, File, Session, Auth, Image;

class ArticlesController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {           
        $cate_id = $request->cate_id ? $request->cate_id : 1;
        $title = $request->title ? $request->title : null;
        
        $query = Articles::where('status', 1)->where('cate_id', $cate_id)->orderBy('id', 'desc');
        if($title){
            $query->where('title', 'LIKE', '%'.$title.'%');
        }
        $items = $query->orderBy('id', 'desc')->paginate(20);        
        
        
        return view('articles.index', compact( 'items', 'title', 'cate_id'));
    }
    

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {   
        $cate_id = $request->cate_id ? $request->cate_id : 1;     
        return view('articles.create', compact('cate_id'));
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
            'title.required' => 'Bạn chưa nhập tên',
        ]);       
        $dataArr['display_order'] = (int) $dataArr['display_order'];
        $dataArr['thumbnail_url'] = strpos($dataArr['thumbnail_url'], 'ttps:') ? $dataArr['thumbnail_url'] :  "https://enb.vn".$dataArr['thumbnail_url'];
        $rs = Articles::create($dataArr);
    
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('articles.index');
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

        $detail = Articles::find($id);
        if( Auth::user()->role > 2 ){
            if($detail->created_user != Auth::user()->id){
                return redirect()->route('home');
            }
        }
        $cate_id = $detail->cate_id;
        return view('articles.edit', compact( 'detail', 'cate_id'));
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
        $model = Articles::find($dataArr['id']);
       
        $model->update($dataArr);        
        
        Session::flash('message', 'Cập nhật thành công');        
        
        return redirect()->route('articles.index');    
        
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
        $model = Articles::find($id);
        $oldStatus = $model->status;
		$model->update(['status'=>0]);		
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('articles.index');   
    }
}
