<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Settings;
use App\Models\Text;

use File, Session, DB, Auth;

class SettingsController  extends Controller
{
    public function index(Request $request)
    {   
                  
        if(Auth::user()->role > 2){
            return redirect()->route('w-articles.index');
        }
        $settingArr = Settings::whereRaw('1')->pluck('value', 'name');

        return view('settings.index', compact( 'settingArr'));
    }
    public function text(Request $request)
    {             
        if(Auth::user()->role > 2){
            return redirect()->route('w-articles.index');
        }
        $textList = WText::all();
        
        return view('settings.text', compact( 'textList'));
    }
    public function saveText(Request $request)
    {           
        $dataArr = $request->all();  
        foreach($dataArr['id'] as $key => $id){
            if($id){
                WText::find($id)->update([                    
                    'value' => $dataArr['value'][$key]
                ]);    
            }            
        }

        Session::flash('message', 'Cập nhật thành công.');
        return redirect()->route('text.index');
    }
    public function saveContent(Request $request){
        $id = $request->id;
        $content = $request->content;
        $md = WText::find($id);
        $md->content = $content;
        $md->save();
    }
     public function noti(Request $request)
    {           
        if(Auth::user()->role > 2){
            return redirect()->route('w-articles.index');
        }   
        $settingArr = WSettings::whereRaw('1')->pluck('value', 'name');

        return view('settings.noti', compact( 'settingArr'));
    }
    public function dashboard(Request $request)
    {              
        if(Auth::user()->role > 2){
            return redirect()->route('w-articles.index');
        }
        $settingArr = WSettings::whereRaw('1')->pluck('value', 'name');
        $query = Product::where('product.status', 2);
        
        //$query->join('w_users', 'w_users.id', '=', 'product.created_user');
        $query->join('city', 'city.id', '=', 'product.city_id');        
        $query->leftJoin('product_img', 'product_img.id', '=','product.thumbnail_id'); 
        $query->join('estate_type', 'product.estate_type_id', '=','estate_type.id'); 
        $query->orderBy('product.id', 'desc');   
        $kyguiList = $query->select(['product_img.image_url as image_urls','product.*', 'estate_type.slug as slug_loai'])->get();


        return view('tour.index', compact( 'settingArr', 'kyguiList'));
    }
    public function storeNoti(Request $request){

        $dataArr = $request->all();

        $dataArr['updated_user'] = Auth::user()->id;

        unset($dataArr['_token']);       

        foreach( $dataArr as $key => $value ){
            $data['value'] = $value;
            WSettings::where( 'name' , $key)->update($data);
        }

        Session::flash('message', 'Cập nhật thành công.');

        return redirect()->route('settings.noti');
    }
    public function update(Request $request){

        if(Auth::user()->role > 2){
            return redirect()->route('w-articles.index');
        }
    	$dataArr = $request->all();

    	$this->validate($request,[            
            'site_name' => 'required',            
            'site_title' => 'required',            
            'site_description' => 'required',            
            'site_keywords' => 'required',                                    
        ],
        [            
            'site_name.required' => 'Bạn chưa nhập tên site',            
            'site_title.required' => 'Bạn chưa nhập meta title',
            'site_description.required' => 'Bạn chưa nhập meta desciption',
            'site_keywords.unique' => 'Bạn chưa nhập meta keywords.'
        ]);  
        $dataArr['updated_user'] = Auth::user()->id;

        unset($dataArr['_token']);
        unset($dataArr['logo_name']);
        unset($dataArr['favicon_name']);
        unset($dataArr['banner_name']);

    	foreach( $dataArr as $key => $value ){
    		$data['value'] = $value;
    		WSettings::where( 'name' , $key)->update($data);
    	}

    	Session::flash('message', 'Cập nhật thành công.');

    	return redirect()->route('settings.index');
    }
}
