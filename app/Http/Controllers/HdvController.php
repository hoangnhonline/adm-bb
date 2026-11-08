<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Hdv;
use App\Models\Department;
use App\Models\Account;
use Helper, File, Session, Auth;

// use Excel;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\Exportable;
// use Maatwebsite\Excel\Concerns\WithHeadings;

class HdvController extends Controller 
// implements FromCollection, WithHeadings
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        if(Auth::user()->role != 1 ){
            return redirect()->route(route('dashboard'));
        }
        $partners = Account::where('is_partner', 1)->get();        
        $user_id = $request->user_id ?? null;
        $query = Hdv::where('status', 1);

        if($user_id){
            $query->where('user_id', $user_id);
        }

        $items = $query->get();      
        return view('hdv.index', compact( 'items', 'user_id', 'partners'));
    }    
    /**
    * Store a newly created resource in storage.
    *
    * @param  Request  $request
    * @return Response
    */    

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

    public function create()
    {        
        if(Auth::user()->role != 1 ){
            return redirect()->route('dashboard');
        } 

        $partners = Account::where('is_partner', 1)->get();   

        return view('hdv.create', compact('partners'));
    }

    public function store(Request $request)
    {
        $dataArr = $request->all();
        
        $this->validate($request,[
            'user_id' => 'required',
            'name' => 'required',                       
        ],
        [
            'name.required' => 'Bạn chưa nhập tên',
            'user_id.required' => 'Bạn chưa nhập đối tác',
        ]);
        
        
        Hdv::create($dataArr);

        Session::flash('message', 'Tạo mới hdv thành công');

        return redirect()->route('hdv.index');
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return Response
    */
    public function edit($id)
    {
        if(Auth::user()->role != 1 ){
            return redirect()->route('dashboard');
        }

        $detail = Hdv::find($id);        
        $partners = Account::where('is_partner', 1)->get();    
        return view('hdv.edit', compact('detail', 'partners'));
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
        if(Auth::user()->role != 1 ){
            return redirect()->route('dashboard');
        }
        $dataArr = $request->all();
         $this->validate($request,[
            'user_id' => 'required',
            'name' => 'required',                       
        ],
        [
            'name.required' => 'Bạn chưa nhập tên',
            'user_id.required' => 'Bạn chưa nhập đối tác',
        ]);
        

        $model = Hdv::find($dataArr['id']);

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật hdv thành công');        

        return redirect()->route('hdv.edit', $dataArr['id']);
    }
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    public function destroy($id)
    {
        if(Auth::user()->role != 1 ){
            return redirect()->route('dashboard');
        }
        // delete
        $model = Hdv::find($id);
        $model->update(['status' => 0]);

        // redirect
        Session::flash('message', 'Xóa nhân viên thành công');
        return redirect()->route('hdv.index');
    }
    

}
