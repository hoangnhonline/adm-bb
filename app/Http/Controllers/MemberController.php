<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\WMetaData;

use Helper, File, Session, Auth, Image;

class MemberController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    { 
        $name = isset($request->name) && $request->name != '' ? $request->name : '';
        
        $query = Member::where('status', 1);
        // check editor
        if( Auth::user()->role > 2 ){
            $query->where('created_user', Auth::user()->id);
        }
        if( $name != ''){
            $query->where('alias', 'LIKE', '%'.$name.'%');
        }

        $items = $query->orderBy('is_hot', 'desc')->orderBy('id', 'desc')->paginate(20);
        
       
        
        return view('member.index', compact( 'items', 'name'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        return view('member.create');
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
            'name' => 'required'           
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',           
        ]);       
      
        $dataArr['alias'] = str_slug($dataArr['name'], " ");      
        $dataArr['slug'] = str_slug($dataArr['name'], "-");   
        $dataArr['created_user'] = Auth::user()->id;

        $dataArr['updated_user'] = Auth::user()->id;
        $dataArr['type'] = 1;
        $dataArr['is_hot'] = isset($dataArr['is_hot']) ? 1 : 0;  

        $rs = Member::create($dataArr);

        $object_id = $rs->id;

        $this->storeMeta( $object_id, 0, $dataArr);

        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('member.index');
    }
    public function storeMeta( $id, $meta_id, $dataArr ){
       
        $arrData = [ 'title' => $dataArr['meta_title'], 'description' => $dataArr['meta_description'], 'keywords'=> $dataArr['meta_keywords'], 'custom_text' => $dataArr['custom_text'], 'updated_user' => Auth::user()->id ];
        if( $meta_id == 0){
            $arrData['created_user'] = Auth::user()->id;            
            $rs = WMetaData::create( $arrData );
            $meta_id = $rs->id;
            
            $modelSp = Member::find( $id );
            $modelSp->meta_id = $meta_id;
            $modelSp->save();
        }else {
            $model = WMetaData::find($meta_id);           
            $model->update( $arrData );
        }              
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
        $detail = Member::find($id);
        if( Auth::user()->role > 2 ){
            if($detail->created_user != Auth::user()->id){
                return redirect()->route('w-articles.index');
            }
        }
        $meta = (object) [];
        if ( $detail->meta_id > 0){
            $meta = WMetaData::find( $detail->meta_id );
        }
        return view('member.edit', compact('detail', 'meta'));
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
            'name' => 'required'           
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',           
        ]);
        
        $dataArr['alias'] = str_slug($dataArr['name'], " ");      
        $dataArr['slug'] = str_slug($dataArr['name'], "-"); 
        
        $dataArr['type'] = 1;
        $dataArr['updated_user'] = Auth::user()->id;
        $dataArr['is_hot'] = isset($dataArr['is_hot']) ? 1 : 0;  
         
        $model = Member::find($dataArr['id']);

        $model->update($dataArr);
        
        $this->storeMeta( $dataArr['id'], $dataArr['meta_id'], $dataArr);
     
        Session::flash('message', 'Cập nhật thành công');        

        return redirect()->route('member.index');
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
        $model = Member::find($id);
        $model->update(['status'=>0]);
        
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('member.index', ['cate_id' => $cate_id]);
    }
}
