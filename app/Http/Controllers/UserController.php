<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Users;
use Helper, File, Session, Hash, Auth;

class UserController extends Controller
{    

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function loginForm()
    {        
        if(Auth::check()){

            return redirect()->route('w-articles.index');
            
        } 
        return view('login');
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  Request  $request
    * @return Response
    */
    public function checkLogin(Request $request)
    {
        $dataArr = $request->all();
        
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'
        ],
        [
            'email.required' => 'Bạn chưa nhập email',
            'password.required' => 'Bạn chưa nhập mật khẩu'            
        ]);
        $dataArr = [
            'email' => $request->email,
            'password' => $request->password            
        ];
        if (Auth::validate($dataArr)) {
            $dataArr['status'] = 1;
            if (Auth::attempt($dataArr)) {                    
                return redirect()->route('w-articles.index'); 
            }else{
                Session::flash('error', 'Tài khoản đã bị khóa.'); 
                return redirect()->route('login-form'); 
            }
        }else {
            // if any error send back with message.
            Session::flash('error', 'Email hoặc mật khẩu không đúng.'); 
            return redirect()->route('login-form');
        }

        return redirect()->route('parent-cate.index');
    }
  
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login-form');
    }   
}
