<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Helper, File, Session, Auth;
// use Maatwebsite\Excel\Facades\Excel;

// use Excel;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\Exportable;
// use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerController extends Controller 
// implements FromCollection, WithHeadings
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        if(Auth::user()->role == 3 ){
            return redirect()->route(route('dashboard'));
        }
        $code = isset($request->code) && $request->code != '' ? $request->code : '';
        $phone = isset($request->phone) && $request->phone != '' ? $request->phone : '';
        $status = isset($request->status) && $request->status != '' ? $request->status : null;
        $query = Customer::whereRaw('1')->orderBy('id', 'DESC');

        if( $code != ''){
            $query->where('code', 'LIKE', '%'.$code.'%');
        }
        if( $phone != ''){
            $query->where('phone', 'LIKE', '%'.$phone.'%');
        }
        if( $status != ''){
            $query->where('status', $status);
        }

        $items = $query->orderBy('id', 'desc')->paginate(20);
        return view('customer.index', compact( 'items', 'code',  'phone','status'));
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

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return Response
    */
    public function edit($id)
    {
        if(Auth::user()->role == 3 ){
            return redirect()->route('dashboard');
        }

        $detail = Customer::find($id);
        return view('customer.edit', compact('detail'));
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
        if(Auth::user()->role == 3 ){
            return redirect()->route('dashboard');
        }
        $dataArr = $request->all();
        if ($dataArr['birthday']) {
            $tmpDate = explode('/', $dataArr['birthday']);
            $dataArr['birthday'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        }
        $model = Customer::find($dataArr['id']);

        $model->update($dataArr);

        Session::flash('message', 'Cập nhật thành công');        

        return redirect()->route('customer.edit', $dataArr['id']);
    }
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    public function destroy($id)
    {
        if(Auth::user()->role == 3 ){
            return redirect()->route('dashboard');
        }
        // delete
        $model = Customer::find($id);
        $model->delete();

        // redirect
        Session::flash('message', 'Xóa khách hàng thành công');
        return redirect()->route('customer.index');
    }
    public function updateStatus(Request $request)
    {       
        if(Auth::user()->role == 3){
            return redirect()->route('dashboard');
        }
        $model = Customer::find( $request->id );
        
        $model->updated_user = Auth::user()->id;
        $model->status = $request->status;

        $model->save();
        $mess = $request->status == 1 ? "Mở khóa thành công" : "Khóa thành công";
        Session::flash('message', $mess);

        return redirect()->route('customer.index');
    }

//     use Exportable;

//     public function collection()
//     {
//         $customers = Customer::where('phone', '<>', '')->orderBy('id', 'DESC')->get();
//         $i=0;
//         foreach ($customers as $row) {
//             $i++;
//             $customer[] = array(
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

//         return (collect($customer));
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
//         return Excel::download(new CustomerController(), 'KhachHangBookTour_'.date('d-m-Y'). '.xlsx');
//    }
}
