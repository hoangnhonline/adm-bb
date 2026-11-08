<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Department;
use Helper, File, Session, Auth;

// use Excel;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\Exportable;
// use Maatwebsite\Excel\Concerns\WithHeadings;

class StaffController extends Controller 
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
        $name = isset($request->name) && $request->name != '' ? $request->name : '';
        $phone = isset($request->phone) && $request->phone != '' ? $request->phone : '';
        $status = isset($request->status) && $request->status != '' ? $request->status : null;
        $department_id = isset($request->department_id) && $request->department_id != '' ? $request->department_id : '';
        $city_id = isset($request->city_id) && $request->city_id != '' ? $request->city_id : null;
        $query = Account::where('is_staff', 1)->where('status', 1);

        if( $name != ''){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        if( $phone != ''){
            $query->where('phone', 'LIKE', '%'.$phone.'%');
        }        

        if( $department_id != ''){
            $query->where('department_id', $department_id);
        }

        if( $city_id != ''){
            $query->where('city_id', $city_id);
        }

        $items = $query->orderBy('role', 'asc')->orderBy('department_id', 'asc')->paginate(100);

        $departmentList = Department::where('status',1)->orderBy('display_order','ASC')->get();

        return view('staff.index', compact( 'items', 'name',  'phone','status','department_id','departmentList','city_id'));
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
        $departmentList = Department::where('status',1)->orderBy('display_order','ASC')->get();
        
        return view('staff.create', compact('departmentList'));
    }

    public function store(Request $request)
    {
        $dataArr = $request->all();
        
        $this->validate($request,[
            'department_id' => 'required',
            'name' => 'required',
            'date_join' => 'required',
            
        ],
        [
            'name.required' => 'Bạn chưa nhập họ tên',
            'department_id.required' => 'Bạn chưa nhập bộ phận',
            'date_join.required' => 'Bạn chưa nhập ngày gia nhập',

        ]);
        
        $dataArr['status'] = isset($dataArr['status'])  ? 1 : 0; 
        $dataArr['role'] = 3;
        $dataArr['is_staff'] = 1;
        $dataArr['is_leader'] = isset($dataArr['is_leader'])  ? 1 : 0;
        $dataArr['salary'] =(int) str_replace(',', '', $dataArr['salary']);
        if ($dataArr['birthday']) {
            $tmpDate = explode('/', $dataArr['birthday']);
            $dataArr['birthday'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        }
        if ($dataArr['date_join']) {
            $tmpDate = explode('/', $dataArr['date_join']);
            $dataArr['date_join'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        }


        if($dataArr['image_url'] && $dataArr['image_name']){
            
            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);
            
            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);
            
            $dataArr['image_url'] = $destionation;
        }
        
        Account::create($dataArr);

        Session::flash('message', 'Tạo mới nhân viên thành công');

        return redirect()->route('staff.index');
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

        $detail = Account::find($id);
        $departmentList = Department::where('status',1)->orderBy('display_order','ASC')->get();
        return view('staff.edit', compact('detail','departmentList'));
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
        $dataArr['is_leader'] = $request->is_leader == 1 ? 1 :0;
        
        if ($dataArr['birthday']) {
            $tmpDate = explode('/', $dataArr['birthday']);
            $dataArr['birthday'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        }
        if ($dataArr['date_join']) {
            $tmpDate = explode('/', $dataArr['date_join']);
            $dataArr['date_join'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        }
        $dataArr['salary'] =(int) str_replace(',', '', $dataArr['salary']);

        if($dataArr['image_url'] && $dataArr['image_name']){
            
            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);
            
            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);
            
            $dataArr['image_url'] = $destionation;
        }
        $model = Account::find($dataArr['id']);

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');        

        return redirect()->route('staff.edit', $dataArr['id']);
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
        $model = Account::find($id);
        $model->update(['status' => 0]);

        // redirect
        Session::flash('message', 'Xóa nhân viên thành công');
        return redirect()->route('staff.index');
    }
    public function updateStatus(Request $request)
    {       
        if(Auth::user()->role != 1){
            return redirect()->route('dashboard');
        }
        $model = Account::find( $request->id );
        
        $model->updated_user = Auth::user()->id;
        $model->status = $request->status;

        $model->save();
        $mess = $request->status == 1 ? "Mở khóa thành công" : "Khóa thành công";
        Session::flash('message', $mess);

        return redirect()->route('staff.index');
    }

    public function editPass($id)
    {
        if(Auth::user()->role == 3 ){
            return redirect()->route('dashboard');
        }

        $detail = Account::find($id);
        $departmentList = Department::where('status',1)->orderBy('display_order','ASC')->get();
        return view('staff.editPass', compact('detail','departmentList'));
    }

    public function getModalStaff(Request $request){
    	if( $request->ajax() ){
    		$id = $request->id;
    		if( $id ){
                $detail = Account::find($id);
    		}
    	}
    	return view('staff.modal',compact('detail'));    
    }

//     use Exportable;

//     public function collection()
//     {
//         $staffs = Account::where('phone', '<>', '')->orderBy('id', 'DESC')->get();
//         $i=0;
//         foreach ($staffs as $row) {
//             $i++;
//             $staff[] = array(
//                 '0' => $i,
//                 '1' => $row->name,
//                 '2' => $row->phone,
//                 '3' => $row->email,
//                 '4' => $row->address,
//                 '5' => $row->birthday,
//                 '6' => $row->code ? $row->code : "Chờ cấp",
//                 '7' =>  date('d/m/Y', strtotime($row->use_date)),
//                 '8' => ($row->status == 1 ? "Mở":"Khóa"),
//                 '9' => date('d/m/Y', strtotime($row->created_at)),
                
//             );
//         }

//         return (collect($staff));
//     }

//     public function headings(): array
//     {
//         return [
//             'STT',
//             'Họ Tên',
//             'Số điện thoại',
//             'Email',
//             'Địa chỉ',
//             'Ngày sinh',
//             'Mã Code',
//             'Ngày đi',
//             'Trạng thái',
//             'Ngày book tour',

//         ];
//     }

//     public function export(){
//         return Excel::download(new StaffController(), 'KhachHangBookTour_'.date('d-m-Y'). '.xlsx');
//    }
}
