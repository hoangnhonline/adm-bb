<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\ComboImg;
use App\Models\MetaData;
use App\Models\HotelFeatured;
use App\Models\Featured;
use App\Models\HotelsTypesSettings;
use App\Models\RoomsPrice;
use App\Models\Hotels;

use Helper, File, Session, Auth, Image;

class ComboController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function price(Request $request){
        $all = Combo::all();
        foreach($all as $h){
            $price_lowest = $h->getHotelMinPrice($h->id);
            $h->update(['lowest_price' => $price_lowest]);
        }
    }
    public function index(Request $request)
    {  
        
        $name = isset($request->name) && $request->name != '' ? $request->name : '';
        $nights = $request->nights ? $request->nights : null;     
        $city_id = $request->city_id ? $request->city_id : 1;
        
        $query = Combo::where('status', 1)->where('city_id', $city_id); 
     
        if($nights > 0){
            $query->where('nights', $nights);
        }
        // check editor
        if( Auth::user()->role > 2 ){
            $query->where('created_user', Auth::user()->id);
        }
        if( $name != ''){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        $items = $query->orderBy('is_hot', 'desc')->orderBy('id', 'desc')->paginate(20);   
       
            $view = 'combo.index';
        
        return view($view, compact( 'items', 'name', 'city_id', 'nights'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        // if( Auth::user()->role > 2 ){
        //     return redirect()->route('home');
        // }        
        $hotelList = Hotels::where('partner', 0)->get();
        $view = 'combo.create';
       
        return view($view, compact('hotelList'));
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
            'price' => 'required',
          
        ],
        [
            'name.required' => 'Bạn chưa nhập tên combo',
            'price.required' => 'Bạn chưa nhập giá',
          
        ]);
        $dataArr['price'] = isset($dataArr['price']) ? (int) str_replace(',', '', $dataArr['price']) : 0;
        $dataArr['is_hot'] = isset($dataArr['is_hot']) ? 1 : 0;
        $dataArr['type'] = isset($dataArr['type']) ?? 1;
        $dataArr['slug'] = str_slug($dataArr['name'], "-");      

        $dataArr['status'] = 1;
        $dataArr['created_user'] =  $dataArr['updated_user'] = Auth::user()->id;
        
        $rs = Combo::create($dataArr);

        $combo_id = $rs->id;
       
        $this->storeImage($combo_id, $dataArr);       
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('combo.index');
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
        // if( Auth::user()->role > 2 ){
        //     return redirect()->route('home');
        // }
    
        $detail = Combo::find($id);
    
        $view = 'combo.edit';
        $hotelList = Hotels::where('partner', 0)->get();
        return view($view, compact('detail', 'hotelList'));
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
                                                
        ],
        [
            'name.required' => 'Bạn chưa nhập tên thương hiệu',
            
        ]);
       
        $dataArr['is_hot'] = isset($dataArr['is_hot']) ? 1 : 0;   
        $dataArr['price'] = isset($dataArr['price']) ? (int) str_replace(',', '', $dataArr['price']) : 0;
        $dataArr['slug'] = str_slug($dataArr['name'], "-");

        $dataArr['updated_user'] = Auth::user()->id;
            
        $model = Combo::find($dataArr['id']);
       
        $model->update($dataArr);

        $combo_id = $dataArr['id'];
        
        $this->storeImage($combo_id, $dataArr);
        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('combo.index');
    }

    public function storeImage($id, $dataArr){     
        //process old image
        $imageIdArr = isset($dataArr['image_id']) ? $dataArr['image_id'] : [];
        $hinhXoaArr = ComboImg::where('combo_id', $id)->whereNotIn('id', $imageIdArr)->pluck('id');
        if( $hinhXoaArr )
        {
            foreach ($hinhXoaArr as $image_id_xoa) {
                $model = ComboImg::find($image_id_xoa);
                $urlXoa = config('plantotravel.upload_path')."/".$model->image_url;
                if(is_file($urlXoa)){
                    unlink($urlXoa);
                }
                $model->delete();
            }
        }       

        //process new image
            if( isset( $dataArr['thumbnail_img'])){
                $thumbnail_img = $dataArr['thumbnail_img'];
            }
            $imageArr = []; 

            if( !empty( $dataArr['image_tmp_url'] )){

                foreach ($dataArr['image_tmp_url'] as $k => $image_url) {
                    
                    $origin_img = public_path().$image_url;                  
                    
                    if( $image_url ){

                        if( isset( $dataArr['thumbnail_img'])){
                            $imageArr['is_thumbnail'][] = $is_thumbnail = $dataArr['thumbnail_img'] == $image_url  ? 1 : 0;
                        }
                        $img = Image::make($origin_img);
                        $w_img = $img->width();
                        $h_img = $img->height();

                        $tmpArrImg = explode('/', $origin_img);
                        
                        $new_img = config('plantotravel.upload_thumbs_path').end($tmpArrImg);

                        if($w_img/$h_img > 550/350){

                            Image::make($origin_img)->resize(null, 350, function ($constraint) {
                                    $constraint->aspectRatio();
                            })->crop(550, 350)->save($new_img);
                        }else{
                            Image::make($origin_img)->resize(550, null, function ($constraint) {
                                    $constraint->aspectRatio();
                            })->crop(550, 350)->save($new_img);
                        }
                        $new_img = config('plantotravel.upload_thumbs_path_2').end($tmpArrImg);
                        if($w_img/$h_img > 350/300){

                            Image::make($origin_img)->resize(null, 300, function ($constraint) {
                                    $constraint->aspectRatio();
                            })->crop(350, 300)->save($new_img);
                        }else{
                            Image::make($origin_img)->resize(550, null, function ($constraint) {
                                    $constraint->aspectRatio();
                            })->crop(350, 300)->save($new_img);
                        }
                        $imageArr['name'][] = $image_url;
                        
                    }
                }
            }
            if( !empty($imageArr['name']) ){
                foreach ($imageArr['name'] as $key => $name) {
                    $rs = ComboImg::create(['combo_id' => $id, 'image_url' => $name, 'display_order' => 1]);                
                    $image_id = $rs->id;
                    if( isset( $dataArr['thumbnail_img'])){
                        if( $imageArr['is_thumbnail'][$key] == 1){
                            $thumbnail_id = $image_id;
                        }
                    }
                }
            }
            $model = Combo::find( $id );
            if( isset( $dataArr['thumbnail_img'])){
                $model->thumbnail_id = $thumbnail_id;
            }
            $model->save();
        
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
        $model = Combo::find($id);
        $model->update(['status' => 0]);
        ComboImg::where('combo_id', $id)->update(['status' => 0]);        
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('combo.index');
    }
}
