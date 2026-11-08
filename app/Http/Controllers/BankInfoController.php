<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\BankInfo;
use Helper, File, Session, Auth;

class BankInfoController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {    

        $bank_no = $request->bank_no ?? null;
        $name = $request->name ?? null;
        $query = BankInfo::where('status', 1);        
        if( $bank_no !='' ){
            $query->where('bank_no', 'LIKE', '%'.$bank_no.'%');
        }

        $items = $query->orderBy('id', 'desc')->paginate(20000);
        
        return view('bank-info.index', compact( 'items', 'bank_no'));
    }
    public function ajaxList(Request $request){

        $id_selected = $request->id ?? null;
        $bankInfoList = BankInfo::all();
        
        //$tagArr = $query->orderBy('id', 'desc')->get();
       
        return view('bank-info.ajax-list', compact( 'bankInfoList', 'id_selected'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $type = $request->type ? $request->type : 1;
        return view('bank-info.create', compact('type'));
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
            'name' => 'required|unique:name'
            ],
        
        [
            'name.required' => 'Bạn chưa nhập tên địa điểm'
        ]);

        $rs = BankInfo::create($dataArr);
        
        $object_id = $rs->id;

        Session::flash('message', 'Tạo mới địa điểm thành công');

        return redirect()->route('bank-info.index');
    }
    public function saveToaDo(Request $request){
        $value = $request->value;
        $id = $request->id;
       
        $rs = BankInfo::find($id);

        $rs->update(['address' => $value]);
    }
    public function ajaxDelete(Request $request){        
        $id = $request->id;        
        $rs = Booking::where('location_id', $id)->get();
        foreach($rs as $r){
            $r->update(['location_id' => 466]);
        } 
        $model = BankInfo::find($id);        
        $model->delete();        
    }
    public function saveName(Request $request){
        $value = $request->value;
        $id = $request->id;
        $rs = BankInfo::find($id);

         $value = ucwords($value);
        $rs->update(['name' => $value]);
    }
    public function ajaxSave(Request $request)
    {
        $dataArr = $request->all();
        $this->validate($request,[
            'name' => 'required',
            'bank_name' => 'required',
            'account_name' => 'required',
            'bank_no' => 'required|unique:bank_info',            
            ],
        
        [
            'name.required' => 'Bạn chưa nhập tên tài khoản',
            'bank_name.required' => 'Bạn chưa nhập tên ngân hàng',
            'account_name.required' => 'Bạn chưa nhập tên chủ tài khoản ',
            'bank_no.required' => 'Bạn chưa nhập số tài khoản'
        ]);
        $rs = BankInfo::create($dataArr);
        return $rs->id;
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
        $detail = BankInfo::find($id);
        $meta = (object) [];
        if ( $detail->meta_id > 0){
            $meta = MetaData::find( $detail->meta_id );
        }       
        $vietNameBanks = \App\Helpers\Helper::getVietNamBanks();
        return view('bank-info.edit', compact( 'detail', 'meta', 'vietNameBanks'));
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
            'bank_name' => 'required',
            'account_name' => 'required',
            'bank_no' => 'required|unique:bank_info,bank_no,'.$dataArr['id'].',id',
        ],
        [
            'name.required' => 'Bạn chưa nhập tên đối tác',
            'bank_name.required' => 'Bạn chưa nhập tên ngân hàng',
            'account_name.required' => 'Bạn chưa nhập Chủ tài khoản.',
            'bank_no.unique' => 'Số tài khoản đã tồn tại.',
        ]);
        
        $model = BankInfo::find($dataArr['id']);

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('bank-info.index');
    }
    public function storeMeta( $id, $meta_id, $dataArr ){
       
        $arrData = [ 'title' => $dataArr['meta_title'], 'description' => $dataArr['meta_description'], 'keywords'=> $dataArr['meta_keywords'], 'custom_text' => $dataArr['custom_text'], 'updated_user' => Auth::user()->id ];
        if( $meta_id == 0){
            $arrData['created_user'] = Auth::user()->id;            
            $rs = MetaData::create( $arrData );
            $meta_id = $rs->id;
            
            $modelSp = BankInfo::find( $id );
            $modelSp->meta_id = $meta_id;
            $modelSp->save();
        }else {
            $model = MetaData::find($meta_id);           
            $model->update( $arrData );
        }              
    }
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    public function deleteMulti(Request $request){
        $idArr = $request->replace_id;
        if(!empty($idArr)){
            foreach($idArr as $id){
                $rs = Booking::where('location_id', $id)->get();
                foreach($rs as $r){
                    $r->update(['location_id' => 466]);
                } 
                $model = BankInfo::find($id);
                $model->delete();
            }
        }
        // redirect
        Session::flash('message', 'Xóa địa điểm thành công');
        return redirect()->route('bank-info.index');
    }
    public function destroy($id)
    {
        // delete
        $rs = Booking::where('location_id', $id)->get();
        foreach($rs as $r){
            $r->update(['location_id' => 466]);
        } 
        $model = BankInfo::find($id);
        $model->delete();

        // redirect
        Session::flash('message', 'Xóa địa điểm thành công');
        return redirect()->route('bank-info.index');
    }
}