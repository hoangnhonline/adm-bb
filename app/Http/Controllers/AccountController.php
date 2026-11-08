<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Hash;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UserMod;
use App\Models\Account;
use Helper, File, Session, Auth;

class AccountController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function resetPass(Request $request){
        $id = $request->id;
        $detail = Account::findOrFail($id);
        $pass = $request->pass;
        
            $detail->update(['password' => Hash::make($pass)]); 
            // echo "Đã đổi mật khẩu của tài khoản <b>".$detail->name."</b> thành: <b>". $pass."</b>";       
            $mess = "Đã đổi mật khẩu của tài khoản ".$detail->name." thành: ". $pass."";
            Session::flash('message', $mess);     
        
        return redirect()->route('staff.editPass', $id); 
    }
    public function index(Request $request)
    {   
        if( Auth::user()->role > 2 ){
            return redirect()->route('home');
        }      
        $query = Account::where('status', '>', 0)->where('role', '>', 3);
        $user_type = $request->user_type ?? null;
        $role = $request->role ?? null;
        $level = $request->level ?? null;
        $email = $request->email ?? null;
        $user_id_manage = $request->user_id_manage ?? null;
        $debt_type = $request->debt_type ?? null;
        if(Auth::user()->id == 333){
            $user_id_manage = 333;
        }
        $hdv = $request->hdv ?? null;
        $phone = $request->phone ?? null;
        if($user_type){
            $query->where('user_type', $user_type);
        }
        if($role){
            $query->where('role', $role);
        }
        if($user_id_manage){
            $query->where('user_id_manage', $user_id_manage);
        }
        if($debt_type){
            $query->where('debt_type', $debt_type);
        }
        
        if($level){
            $query->where('level', $level);
        }
        if($phone){
            $query->where('phone', $phone);
        }
        if($hdv){
            $query->where('hdv', 1);
        }
        if($email){
            $query->where('email', $email);
        }        
        //dd($role);
        $items = $query->orderBy('id', 'desc')->paginate(1000);        
        return view('account.index', compact('items', 'email', 'phone', 'user_type', 'role', 'level', 'hdv', 'user_id_manage', 'debt_type'));
    }
    public function create()
    {        
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        $modList = Account::where(['role' => 2, 'status' => 1])->get();
        
        return view('account.create', compact('modList'));
    }
    public function createTx()
    {        
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        $modList = Account::where(['role' => 2, 'status' => 1])->get();
        
        return view('account.create-tx', compact('modList'));
    }
    public function changePass(){
        return view('account.change-pass');   
    }

    public function storeNewPass(Request $request){
        $user_id = Auth::user()->id;
        $detail = Account::find($user_id);
        $old_pass = $request->old_pass;
        $new_pass = $request->new_pass;
        $new_pass_re = $request->new_pass_re;
        
         $this->validate($request,[ 
            'old_pass' => 'required',
            'new_pass' => 'required|between:6,30',
            'new_pass_re' => 'required|same:new_pass|between:6,30'
        ],
        [   
            'old_pass.required' => 'Bạn chưa nhập mật khẩu hiện tại',     
            'new_pass.required' => 'Bạn chưa nhập mật khẩu',
            'new_pass.between' => 'Nhập lại mật khẩu phải từ 6 đến 30 ký tự',
            'new_pass_re.required' => 'Bạn chưa nhập lại mật khẩu',
            'new_pass_re.between' => 'Mật khẩu phải từ 6 đến 30 ký tự',
            'new_pass_re.same' => 'Mật khẩu nhập lại không giống'                     
        ]);      
        if( $old_pass == '' || $new_pass == "" || $new_pass_re == ""){
            return redirect()->back()->withErrors(["Chưa nhập đủ thông tin bắt buộc!"])->withInput();
        }
       
        if(!password_verify($old_pass, $detail->password)){
            return redirect()->back()->withErrors(["Nhập mật khẩu hiện tại không đúng!"])->withInput();
        }
        
        if($new_pass != $new_pass_re ){
            return redirect()->back()->withErrors("Xác nhận mật khẩu mới không đúng!")->withInput();   
        }


        $detail->password = Hash::make($new_pass);
        $detail->save();
        Session::flash('message', 'Đổi mật khẩu thành công');

        return redirect()->route('account.change-pass');

    }
    public function storeTx(Request $request)
    {
       
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        $dataArr = $request->all();
        $dataArr['email'] = $dataArr['phone'].'@gmail.com';
        $this->validate($request,[   
            'phone' => 'required|unique:users,phone',   
            'name' => 'required',        
            'email' => 'unique:users,email',                               
        ],
        [            
            'phone.required' => 'Bạn chưa nhập số điện thoại',
            'name.required' => 'Bạn chưa nhập tên',
            'email.required' => 'Bạn chưa nhập email',
            'email.unique' => 'Email đã được sử dụng.',
            'email.email' => 'Bạn nhập email không hợp lệ',
        ]);       
                
        $dataArr['password'] = Hash::make($dataArr['password']);
        
      
        $code = substr(str_shuffle(str_repeat("QERTYUIOPASDFGHJKLZXCVBNM", 5)), 0, 5);
        if(Auth::user()->id ==333){
            $dataArr['user_id_manage'] = 333;
            $dataArr['level'] = 7;
        }
        $dataArr['code'] = $code;        
        $dataArr['partner_type'] = 1;
        $dataArr['is_partner'] = 1;
        $dataArr['role'] = 3;
        $dataArr['city_id'] = 1;
        $dataArr['level'] = null;

        $rs = Account::create($dataArr);       
        /*
        if ( $rs->id > 0 ){
            Mail::send('account.mail', ['fullname' => $request->fullname, 'password' => $tmpPassword, 'email' => $request->email], function ($message) use ($request) {
                $message->from( config('mail.username'), config('mail.name'));

                $message->to( $request->email, $request->fullname )->subject('Mật khẩu đăng nhập hệ thống');
            });   
        }*/

        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('account.index');
    }
    public function store(Request $request)
    {
       
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        $dataArr = $request->all();
        
        $this->validate($request,[   
            'phone' => 'required|unique:users,phone',   
            'name' => 'required',        
            'email' => 'email|required|unique:users,email',
            'password' => 'required|between:6,30',
            're_password' => 'required|same:password|between:6,30',            
            'role' => 'required'
        ],
        [            
            'phone.required' => 'Bạn chưa nhập số điện thoại',
            'name.required' => 'Bạn chưa nhập tên',
            'email.required' => 'Bạn chưa nhập email',
            'email.unique' => 'Email đã được sử dụng.',
            'email.email' => 'Bạn nhập email không hợp lệ',
            'password.required' => 'Bạn chưa nhập mật khẩu',
            'password.between' => 'Nhập lại mật khẩu phải từ 6 đến 30 ký tự',
            're_password.required' => 'Bạn chưa nhập lại mật khẩu',
            're_password.between' => 'Mật khẩu phải từ 6 đến 30 ký tự',
            're_password.same' => 'Mật khẩu nhập lại không giống', 
            'role.required' => 'Bạn chưa chọn phân loại'
        ]);       
                
        $dataArr['password'] = Hash::make($dataArr['password']);
        
      
        $code = substr(str_shuffle(str_repeat("QWERTYUIOPASDFGHJKLZXCVBNM", 5)), 0, 5);
        $dataArr['code'] = $code;
        $rs = Account::create($dataArr);       
        /*
        if ( $rs->id > 0 ){
            Mail::send('account.mail', ['fullname' => $request->fullname, 'password' => $tmpPassword, 'email' => $request->email], function ($message) use ($request) {
                $message->from( config('mail.username'), config('mail.name'));

                $message->to( $request->email, $request->fullname )->subject('Mật khẩu đăng nhập hệ thống');
            });   
        }*/

        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('account.index');
    }
    public function destroy($id)
    {
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        // delete
        $model = Account::find($id);
        $model->update(['status' => 1]);

        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('account.index');
    }
    public function edit($id)
    {
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        $detail = Account::find($id);
        
        return view('account.edit', compact( 'detail'));
    }
    public function update(Request $request)
    {
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        $dataArr = $request->all();
        $model = Account::find($dataArr['id']);
       $this->validate($request,[   
            'code' => 'required|unique:users,code,'.$model->id.',id',  
            'phone' => 'required|unique:users,phone,'.$model->id.',id',  
            'name' => 'required',        
            'email' => 'email|required|unique:users,email,'.$model->id.',id',
            'role' => 'required'
        ],
        [       
            'code.required' => 'Bạn chưa nhập CODE',  
            'code.unique' => 'CODE đã tồn taị',   
            'phone.required' => 'Bạn chưa nhập số điện thoại',
            'phone.unique' => 'Số điện thoại đã tồn tại',
            'name.required' => 'Bạn chưa nhập tên',
            'email.required' => 'Bạn chưa nhập email',
            'email.unique' => 'Email đã được sử dụng.',
            'email.email' => 'Bạn nhập email không hợp lệ',
            'role.required' => 'Bạn chưa chọn phân loại'
        ]); 
        $model = Account::find($dataArr['id']);

      //  $dataArr['updated_user'] = Auth::user()->id;        
        $model->update($dataArr);       

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('account.index');
    }
    public function updateStatus(Request $request)
    {       
        if(Auth::user()->role > 2){
            return redirect()->route('home');
        }
        $model = Account::find( $request->id );

        
        $model->updated_user = Auth::user()->id;
        $model->status = $request->status;

        $model->save();
        $mess = $request->status == 1 ? "Mở khóa thành công" : "Khóa thành công";
        Session::flash('message', $mess);

        return redirect()->route('account.index');
    }
}
