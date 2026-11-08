<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\TicketType;

use App\User;
use App\Models\Settings;
use Helper, File, Session, Auth, Image, Hash;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

class TicketTypeController extends Controller
{
    
    public function edit($id, Request $request)
    {

        $detail = TicketType::find($id);

        return view('ticket-type.edit', compact( 'detail'));
    

    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
  
    public function index(Request $request)
    {
        $status = $request->status ?? null;

        $query = TicketType::whereRaw('1');
        if($status){
            $query->where('status', $status);
        }
        $items  = $query->orderBy('display_order')->get();
        $view = 'ticket-type.index';
           
        return view($view, compact( 'items', 'status'));
       
    }
    

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request) {      
        $view = 'ticket-type.create';
        return view($view);
        
        
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
            'price' => 'required', 
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'price.required' => 'Bạn chưa nhập giá',
           
        ]);       
       
        $dataArr['price'] = str_replace(',', '', $dataArr['price']);        


        $rs = TicketType::create($dataArr);
        
        Session::flash('message', 'Tạo mới thành công');
        return redirect()->route('ticket-type.index');  
    }
  
    
     public function update(Request $request)
    {
        $dataArr = $request->all();
        
        $this->validate($request,[
            'name' => 'required',
            'price' => 'required', 
        ],
        [  
            'name.required' => 'Bạn chưa nhập tên',
            'price.required' => 'Bạn chưa nhập giá',
           
        ]);       
       
        $dataArr['price'] = str_replace(',', '', $dataArr['price']);        

        $detail = TicketType::find($dataArr['id']);
      
        $detail->update($dataArr);       
        Session::flash('message', 'Cập nhật thành công');
        return redirect()->route('ticket-type.index'); 
    }
    /**
    * Update the specified resource in storage.
    *
    * @param  Request  $request
    * @param  int  $id
    * @return Response
    */
    

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    public function destroy($id)
    {
        // delete
        $model = TicketType::find($id);        
        $use_date = date('d/m/Y', strtotime($model->use_date));
        $type = $model->type;
		$model->delete();		
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('ticket-type.index');   
    }

  
}
