<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\Account;
use App\Models\Department;

use Helper, File, Session, Auth;

class TaskDetailController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {   
        
        $arrSearch['task_id'] = $task_id = isset($request->task_id) ? $request->task_id : null;        
        $arrSearch['staff_id'] = $staff_id = isset($request->staff_id) ? $request->staff_id : null;        
        $arrSearch['department_id'] = $department_id = isset($request->department_id) ? $request->department_id : null;  

        $arrSearch['time_type'] = $time_type = isset($request->time_type) && $request->time_type != '' ? $request->time_type : 3;
        $arrSearch['use_df_default'] = $use_df_default =  date('d/m/Y', time());
        $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
        $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
        $arrSearch['month'] = $month = $request->month ?? date('m') - 1;        
        $arrSearch['year'] = $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";        
        $maxdate = date("Y-m-t", strtotime($mindate));
        
        $query = TaskDetail::where('status','>',0);


        if($time_type == 1){ // theo thangs
            $arrSearch['use_date_from'] = $use_date_from = $date_use = date('d/m/Y', strtotime($mindate));
            $arrSearch['use_date_to'] = $use_date_to = date('d/m/Y', strtotime($maxdate));
          
            $query->where('task_date','>=', $mindate);                   
            $query->where('task_date', '<=', $maxdate);
        }elseif($time_type == 2){ // theo khoang ngay
            $arrSearch['use_date_from'] = $use_date_from = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;

            if($use_date_from){
                $arrSearch['use_date_from'] = $use_date_from;
                $tmpDate = explode('/', $use_date_from);
                $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                $query->where('task_date','>=', $use_date_from_format);
            }
            if($use_date_to){
                $arrSearch['use_date_to'] = $use_date_to;
                $tmpDate = explode('/', $use_date_to);
                $use_date_to_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];   
                if($use_date_to_format < $use_date_from_format){
                    $arrSearch['use_date_to'] = $use_date_from;
                    $use_date_to_format = $use_date_from_format;   
                }        
                $query->where('task_date', '<=', $use_date_to_format);
            }
        }else{
            $arrSearch['use_date_from'] = $use_date_from = $arrSearch['use_date_to'] = $use_date_to = $date_use = $request->use_date_from ? $request->use_date_from : date('d/m/Y', time());
            
            $arrSearch['use_date_from'] = $use_date_from;
            $tmpDate = explode('/', $use_date_from);
            $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
            $query->where('task_date','=', $use_date_from_format);
        
        }


        $task = [];
        if( $task_id != null){
            $query->where('task_id', $task_id);
            $task = Task::find($task_id);
        }
        if( $staff_id != null){
            $query->where('staff_id', $staff_id);       
        }
        if( $department_id != null){
            $query->where('department_id', $department_id);
        }

        $items = $query->orderBy('id', 'desc')->paginate(20);

        
        $departmentArr = Department::where('status', 1)->get();
        if($department_id){
            $taskArr = Task::where('status', 1)->where('department_id', $department_id)->orderBy('id', 'DESC')->get();
            $staffArr = Account::where('is_staff',1)->where('status',1)
                ->where('department_id', $department_id)
            ->orderBy('id', 'ASC')->get();    
        }else{
            $taskArr = Task::where('status', 1)->orderBy('id', 'DESC')->get();
            $staffArr = Account::where('is_staff',1)->where('status',1)->orderBy('id', 'ASC')->get();    
        }
        // dd(date("H:i:s", mktime(0, 0, 0)));
        
        return view('task-detail.index', compact( 'items', 'taskArr','departmentArr','staffArr' , 'task_id','staff_id',
         'department_id','task','time_type','arrSearch','month','year'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $task_id = isset($request->task_id) ? $request->task_id : null;       
        $task = [];
        
        if($task_id > 0){
            $task = Task::find($task_id);
        }

        $staffArr = Account::where('is_staff',1)->where('status',1)->orderBy('id', 'ASC')->get();

        $taskArr = Task::where('status', 1)->orderBy('id', 'DESC')->get();

        return view('task-detail.create', compact(  'taskArr', 'task_id', 'task','staffArr'));
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
            'task_id' => 'required',            
            //'staff_id' => 'required',            
            'content' => 'required',
            'task_date' => 'required',
           // 'task_deadline' => 'required',

        ],
        [               
            'task_id.required' => 'Bạn chưa chọn danh mục công việc',            
           // 'staff_id.required' => 'Bạn chưa chọn nhân viên',
            'content.required' => 'Bạn chưa nhập nội dung',
            'task_date.required' => 'Bạn chưa chọn ngày bắt đầu',
          //  'task_deadline.required' => 'Bạn chưa chọn deadline'
        ]); 

        if($dataArr['task_date']){
            $tmpDate = explode('/', $dataArr['task_date']);
            $dataArr['task_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }
        if($dataArr['task_deadline']){
            $tmpDate = explode('/', $dataArr['task_deadline']);
            if($dataArr['hour']) {
                $tmpHour = explode(':', $dataArr['hour']);
                $dataArr['task_deadline'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0]. " " .$tmpHour[0].":".$tmpHour[1].":00";  
            } else {
                $dataArr['task_deadline'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
            }
            
        }
        if(!isset($dataArr['staff_id'])){
            $dataArr['staff_id'] = Auth::user()->id;
        }

        $rs = TaskDetail::create($dataArr);

     
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('task-detail.index', ['task_id' => $dataArr['task_id']]);
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
    public function edit($id, Request $request)
    {
        $arrSearch = $request->all();
        $detail = TaskDetail::find($id);
        if (Auth::user()->is_staff == 1) {
            if (Auth::id()  != $detail->staff_id) {
                Session::flash('message', 'Bạn không thể chỉnh sửa công việc người khác');        
                return redirect()->route('task-detail.index', ['task_id' => $detail->task_id]); 
            }
        }
          

        $staffArr = Account::where('is_staff',1)->where('status',1)->orderBy('id', 'ASC')->get();

        $taskArr = Task::where('status', 1)->get();
    
        $task_id = $detail->task_id;
        $task = Task::find($task_id);

        return view('task-detail.edit', compact( 'detail', 'taskArr', 'staffArr', 'task_id','task','arrSearch'));
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
        $arrSearch['task_id'] = $task_id = isset($request->task_id) ? $request->task_id : null;        
        $arrSearch['staff_id'] = $staff_id = isset($request->staff_id) ? $request->staff_id : null;        
        $arrSearch['department_id'] = $department_id = isset($request->department_id) ? $request->department_id : null;  

        $arrSearch['time_type'] = $time_type = isset($request->time_type) && $request->time_type != '' ? $request->time_type : 3;
        $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
        $arrSearch['use_date_to'] = $use_date_to = $request->use_date_to ? $request->use_date_to : $use_date_from;
        $arrSearch['month'] = $month = $request->month ?? date('m') - 1;        
        $arrSearch['year'] = $year = $request->year ?? date('Y');
        
        unset($dataArr['staff_id']);
        unset($dataArr['department_id']);
        unset($dataArr['time_type']);
        unset($dataArr['use_date_from']);
        unset($dataArr['use_date_to']);
        unset($dataArr['month']);
        unset($dataArr['year']);
        unset($dataArr['use_df_default']);

        $this->validate($request,[               
            'task_id' => 'required',            
            // 'staff_id' => 'required',            
            'content' => 'required',
            'task_date' => 'required',
            // 'task_deadline' => 'required',

        ],
        [               
            'task_id.required' => 'Bạn chưa chọn danh mục công việc',            
            // 'staff_id.required' => 'Bạn chưa chọn nhân viên',
            'content.required' => 'Bạn chưa nhập nội dung',
            'task_date.required' => 'Bạn chưa chọn ngày bắt đầu',
            // 'task_deadline.required' => 'Bạn chưa chọn deadline'
        ]);    
      
        if(!isset($dataArr['staff_id'])){
            $dataArr['staff_id'] = Auth::user()->id;
        }
        // $dataArr['department_id'] = $user->department_id;
        $dataArr['percent'] =(int) str_replace(',', '', $dataArr['percent']);

        if ($dataArr['percent'] == 100) {
            $dataArr['status'] = 2;
        }

        if($dataArr['task_date']){
            $tmpDate = explode('/', $dataArr['task_date']);
            $dataArr['task_date'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
        }
        if($dataArr['task_deadline']){
            $tmpDate = explode('/', $dataArr['task_deadline']);
            if($dataArr['hour']) {
                $tmpHour = explode(':', $dataArr['hour']);
                $dataArr['task_deadline'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0]. " " .$tmpHour[0].":".$tmpHour[1].":00";  
            } else {
                $dataArr['task_deadline'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];  
            }
        }
        
      
        $model = TaskDetail::find($dataArr['id']);

        $model->update($dataArr);
        
        Session::flash('message', 'Cập nhật thành công');        

        return redirect()->route('task-detail.index', $arrSearch);    
        
        
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
        $model = TaskDetail::find($id);

        if (Auth::user()->is_staff == 1) {
            if (Auth::id()  != $model->staff_id) {
                Session::flash('message', 'Bạn không thể xóa công việc người khác');        
                return redirect()->route('task-detail.index', ['task_id' => $model->task_id]); 
            }
        }

		$model->update(['status'=>0]);
		$task_id = $model->task_id;
        // redirect
        Session::flash('message', 'Xóa thành công');

        return redirect()->route('task-detail.index', ['task_id' => $task_id]);    
        
        
    }
}
