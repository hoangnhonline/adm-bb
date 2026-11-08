<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Helper, Auth, Session;
use App\Models\UserNotification;

class NotiController extends Controller
{

    public function index(Request $request){
        
        $is_read = $request->is_read ? $request->is_read : 0;
           
                 
       
        $rs = UserNotification::where('user_id', Auth::user()->id);
        if($is_read > -1){
            $rs->where('is_read', $is_read);
        }
        $items = $rs->orderBy('id', 'desc')->paginate(100);

        return view('notification.index', compact('items', 'is_read'));
    }
    public function read(Request $request){
    	$id = $request->id;
    	$rs = UserNotification::find($id);
    	$rs->update(['is_read' => 1]);
    	return redirect()->route('noti.index');
    }
    public function updateMulti(Request $request){
        $idArr = $request->id;
        if(!empty($idArr)){
            foreach($idArr as $id){
                $rs = UserNotification::find($id);                
                $rs->update(['is_read' => 1]);            
            }
            Session::flash('message', 'Cập nhật thành công');
        }
        
        return redirect()->route('noti.index');
    }
}
