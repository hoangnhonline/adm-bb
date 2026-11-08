<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use Helper, File, Session, Auth, Image;

class FaqsController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {           
     
        $query = Faqs::where('status', 1);
        
        $items = $query->orderBy('display_order', 'asc')->paginate(20);        
        
        
        return view('faqs.index', compact( 'items', 'cateArr' , 'title', 'cate_id', 'childList','cateDetail', 'parent_id', 'cateArr'));
    }
    

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {        
        return view('faqs.create');
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
        //  dd($dataArr);
        $this->validate($request,[   
                
            'title' => 'required',
        ],
        [  
            'title.required' => 'Bạn chưa nhập tiêu đề',
        ]);       
      
        $rs = Faqs::create($dataArr);

        $object_id = $rs->id;
    
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('faqs.index');
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

        $detail = Faqs::find($id);
        if( Auth::user()->role > 2 ){
            if($detail->created_user != Auth::user()->id){
                return redirect()->route('home');
            }
        }
        
        return view('faqs.edit', compact( 'detail'));
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
            'title' => 'required',
        ],
        [   
            'title.required' => 'Bạn chưa nhập tiêu đề',                
        ]);

        $model = Faqs::find($dataArr['id']);
       
        $model->update($dataArr);
        
        
        Session::flash('message', 'Cập nhật thành công');        
        
        return redirect()->route('faqs.index');    
        
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
        $model = Faqs::find($id);
        $oldStatus = $model->status;
		$model->update(['status'=>0]);		
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('faqs.index');   
    }
}