<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Department;
use Helper, File, Session, Auth;

class TaskController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        $status = isset($request->status) ? $request->status : null;  

        $query = Task::where('status','>',0);
        if( $status != null){
            $query->where('status', $status);       
        }
        $items = $query->orderBy('id','desc')->paginate(20);
        return view('task.index', compact( 'items','status'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $departmentList = Department::where('status',1)->orderBy('display_order','ASC')->get();

        return view('task.create', compact('departmentList'));
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
           // 'department_id' => 'required',
            'type' => 'required'       
        ],
        [
            'name.required' => 'Bạn chưa nhập tên công việc',
           // 'department_id.required' => 'Bạn chưa chọn bộ phận',
            'type.required' => 'Bạn chưa chọn loại công việc.',  
        ]);

        if(Auth::user()->role != 1){
            $dataArr['department_id'] = Auth::user()->department_id;    
        }
        $dataArr['status'] = 1;
        $dataArr['created_user'] = Auth::id();
        $dataArr['updated_user'] = Auth::id();
        
        Task::create($dataArr);

        Session::flash('message', 'Tạo mới công việc thành công');

        return redirect()->route('task.index');
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
        $detail = Task::find($id);
        if (Auth::user()->is_staff == 1) {
            if (Auth::id()  != $detail->created_user) {
                Session::flash('message', 'Bạn không thể chỉnh sửa công việc do người khác tạo');        
                return redirect()->route('task.index'); 
            }
        } 
        $departmentList = Department::where('status',1)->orderBy('display_order','ASC')->get();

        return view('task.edit', compact( 'detail','departmentList' ));
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
           // 'department_id' => 'required',
            'type' => 'required'       
        ],
        [
            'name.required' => 'Bạn chưa nhập tên công việc',
         //   'department_id.required' => 'Bạn chưa chọn bộ phận',
            'type.required' => 'Bạn chưa chọn loại công việc.',  
        ]);      
       
        
        $model = Task::find($dataArr['id']);
        $dataArr['updated_user'] = Auth::id();

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật công việc thành công');

        return redirect()->route('task.edit', $dataArr['id']);
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
        $model = Task::find($id);
        $model->delete();

        // redirect
        Session::flash('message', 'Hủy công việc thành công');
        return redirect()->route('task.index');
    }

    public function delete($id)
    {
        // delete
        $model = Task::find($id);
        if (Auth::user()->is_staff == 1) {
            if (Auth::id()  != $model->created_user) {
                Session::flash('message', 'Bạn không thể xóa công việc do người khác tạo');        
                return redirect()->route('task.index'); 
            }
        } 
        $model->update(['status' => 0]);

        // redirect
        Session::flash('message', 'Xóa công việc thành công');
        return redirect()->route('task.index');
    }
    public function ajaxList(Request $request){
        
        $department_id = Auth::user()->department_id;
        $id_selected = $request->id ?? null;
        $taskList = Task::where('department_id', $department_id)->get();
        
        //$tagArr = $query->orderBy('id', 'desc')->get();
        
        return view('task.ajax-list', compact( 'taskList', 'id_selected'));
    }

    public function ajaxSave(Request $request)
    {
        $dataArr = $request->all();
        $this->validate($request,[
            'name' => 'required',
            'type' => 'required',          
            ],
        
        [
            'name.required' => 'Bạn chưa nhập công việc',
            'type.required' => 'Bạn chưa chọn loại công việc',
        ]);
        $user = Auth::user();
        $dataArr['department_id'] = $user->department_id;
        $dataArr['status'] = 1;
        $dataArr['created_user'] = $dataArr['updated_user'] = $user->id;
        $rs = Task::create($dataArr);
        return $rs->id;
    }

}
