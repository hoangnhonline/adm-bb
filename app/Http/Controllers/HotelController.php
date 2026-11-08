<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Hotels;
use App\Models\HotelImg;
use App\Models\MetaData;
use App\Models\HotelFeatured;
use App\Models\Featured;
use App\Models\HotelsTypesSettings;
use App\Models\RoomsPrice;

use Helper, File, Session, Auth, Image;

class HotelController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function price(Request $request){
        $all = Hotels::all();
        foreach($all as $h){
            $price_lowest = $h->getHotelMinPrice($h->id);
            $h->update(['lowest_price' => $price_lowest]);
        }
    }
    public function index(Request $request)
    {   
        // if( Auth::user()->role > 2 ){
        //     return redirect()->route('home');
        // }        
 
        $name = isset($request->name) && $request->name != '' ? $request->name : '';
        $stars = $request->stars ? $request->stars : null;
        $partner = $request->partner ? $request->partner : 0;
        $city_id = $request->city_id ? $request->city_id : 1;
        
        $query = Hotels::where('status', 1)->where('city_id', $city_id);      
      
        if($stars > 0){
            $query->where('stars', $stars);
        }
        if($partner > -1){
            $query->where('partner', $partner);
        }
        // check editor
        if( Auth::user()->role > 2 ){
            $query->where('created_user', Auth::user()->id);
        }
        if( $name != ''){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        $items = $query->orderBy('is_hot', 'desc')->orderBy('id', 'desc')->paginate(20);   
        if($partner == 1){
            $view = 'hotel.index-partner';
        }else{
            $view = 'hotel.index';
        }
        return view($view, compact( 'items', 'name', 'stars', 'city_id', 'partner'));
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
        $objectsList = Featured::whereRaw(1)->get();
        $allTypeList = HotelsTypesSettings::where('type', '<>', 3)->get();
        $hotelAmen = $hotelPay = $hotelType = [];
        foreach($allTypeList as $types){
            if($types->type == 1){
                $hotelType[] = $types;
            }elseif($types->type == 2){
                $hotelAmen[] = $types;
            }else{
                $hotelPay[] = $types;
            }
        }        
        $featuredList = Featured::all();
        $partnerList = Hotels::where('partner', 1)->get();  
        $partner = $request->partner ?? null;
        if($partner == 1){
            $view = 'hotel.create-partner';
        }else{
            $view = 'hotel.create';
        }
        return view($view, compact('objectsList', 'hotelType', 'hotelAmen', 'hotelPay', 'featuredList', 'partnerList'));
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
          
        ],
        [
            'name.required' => 'Bạn chưa nhập tên thương hiệu',
          
        ]);
       
        $dataArr['is_hot'] = isset($dataArr['is_hot']) ? 1 : 0;
        $dataArr['partner'] = isset($dataArr['partner']) ? 1 : 0;
        
        $dataArr['alias'] = str_slug($dataArr['name'], " ");
        $dataArr['slug'] = str_slug($dataArr['name'], "-");

        $dataArr['price_lowest'] =  (int) str_replace(",", "", $dataArr['price_lowest']);
        if($dataArr['com_value']){
            $dataArr['com_value'] = (int) str_replace(",", "", $dataArr['com_value']);    
        }

        $dataArr['status'] = 1;
        if(isset($dataArr['amenities'])){
            $dataArr['amenities'] = implode(',', $dataArr['amenities']);
        }
        if(isset($dataArr['partner_id'])){
            $dataArr['related_id'] = implode(',', $dataArr['partner_id']);    
        }
        $dataArr['created_user'] =  $dataArr['updated_user'] = Auth::user()->id;
        
        $rs = Hotels::create($dataArr);

        $hotel_id = $rs->id;       
        if(!empty($dataArr['featured_id'])){
            foreach($dataArr['featured_id'] as $featured_id){
                HotelFeatured::create(['hotel_id' => $hotel_id, 'featured_id' => $featured_id]);
            }
        }
        $this->storeImage($hotel_id, $dataArr);       
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('hotel.index', ['partner' => $dataArr['partner']]);
    }

    public function storeMeta( $id, $meta_id, $dataArr ){
       
        $arrData = [ 'title' => $dataArr['meta_title'], 'description' => $dataArr['meta_description'], 'keywords'=> $dataArr['meta_keywords'], 'custom_text' => $dataArr['custom_text'], 'updated_user' => Auth::user()->id ];
        if( $meta_id == 0){
            $arrData['created_user'] = Auth::user()->id;            
            $rs = MetaData::create( $arrData );
            $meta_id = $rs->id;
            
            $modelSp = Hotels::find( $id );
            $modelSp->meta_id = $meta_id;
            $modelSp->save();
        }else {
            $model = MetaData::find($meta_id);           
            $model->update( $arrData );
        }              
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
        $objectSelected = [];
        $detail = Hotels::find($id);
        // if( Auth::user()->role > 2 ){            
        //     return redirect()->route('home');            
        // }        
        $tmpArr = HotelFeatured::where(['hotel_id' => $id])->get();        
        if( $tmpArr->count() > 0 ){
            foreach ($tmpArr as $value) {
                $objectSelected[] = $value->featured_id;
            }
        }
        $objectsList = Featured::whereRaw(1)->get();
        $meta = (object) [];
        if ( $detail->meta_id > 0){
            $meta = MetaData::find( $detail->meta_id );
        }
        $allTypeList = HotelsTypesSettings::where('type', '<>', 3)->get();
        $hotelAmen = $hotelPay = $hotelType = [];
        foreach($allTypeList as $types){
            if($types->type == 1){
                $hotelType[] = $types;
            }elseif($types->type == 2){
                $hotelAmen[] = $types;
            }else{
                $hotelPay[] = $types;
            }
        }      
        $featuredList = Featured::all();
        $partnerList = Hotels::where('partner', 1)->get();    
        if($detail->partner == 1){
            $view = 'hotel.edit-partner';
        }else{
            $view = 'hotel.edit';
        }
        return view($view, compact('objectsList', 'objectSelected', 'detail', 'meta', 'hotelType', 'hotelAmen', 'hotelPay', 'featuredList', 'partnerList'));
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
        $dataArr['partner'] = isset($dataArr['partner']) ? 1 : 0;
        $dataArr['price_lowest'] = (int) str_replace(",", "", $dataArr['price_lowest']);  
        if($dataArr['com_value']){
            $dataArr['com_value'] = (int) str_replace(",", "", $dataArr['com_value']);    
        }          
        $dataArr['alias'] = str_slug($dataArr['name'], " ");
        $dataArr['slug'] = str_slug($dataArr['name'], "-");

        $dataArr['updated_user'] = Auth::user()->id;
            
        $model = Hotels::find($dataArr['id']);
        HotelFeatured::where('hotel_id', $dataArr['id'])->delete();
        if(!empty($dataArr['featured_id'])){
            foreach($dataArr['featured_id'] as $featured_id){
                HotelFeatured::create(['hotel_id' => $dataArr['id'], 'featured_id' => $featured_id]);
            }
        }
        if(isset($dataArr['amenities'])){
            $dataArr['amenities'] = implode(',', $dataArr['amenities']);    
        }
        if(isset($dataArr['partner_id'])){
            $dataArr['related_id'] = implode(',', $dataArr['partner_id']);    
        }
        $model->update($dataArr);

        if($dataArr['com_type'] == 2){
            $priceList = RoomsPrice::where('hotel_id', $dataArr['id'])->get();
            foreach($priceList as $pr){
                $pr->update(['price' => $pr->price_goc + $dataArr['com_value']]);
            }
        }
        
        $hotel_id = $dataArr['id'];
        
        $this->storeImage($hotel_id, $dataArr);
        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('hotel.index', ['partner' => $dataArr['partner']]);
    }

    public function storeImage($id, $dataArr){     
        //process old image
        $imageIdArr = isset($dataArr['image_id']) ? $dataArr['image_id'] : [];
        $hinhXoaArr = HotelImg::where('hotel_id', $id)->whereNotIn('id', $imageIdArr)->pluck('id');
        if( $hinhXoaArr )
        {
            foreach ($hinhXoaArr as $image_id_xoa) {
                $model = HotelImg::find($image_id_xoa);
                $urlXoa = config('plan.upload_path')."/".$model->image_url;
                if(is_file($urlXoa)){
                    unlink($urlXoa);
                }
                $model->delete();
            }
        }       

        //process new image
        if( isset( $dataArr['thumbnail_img'])){
            $thumbnail_img = $dataArr['thumbnail_img'];

            $imageArr = []; 

            if( !empty( $dataArr['image_tmp_url'] )){

                foreach ($dataArr['image_tmp_url'] as $k => $image_url) {
                    
                    $origin_img = public_path().$image_url;                  
                    
                    if( $image_url ){

                        $imageArr['is_thumbnail'][] = $is_thumbnail = $dataArr['thumbnail_img'] == $image_url  ? 1 : 0;

                        $img = Image::make($origin_img);
                        $w_img = $img->width();
                        $h_img = $img->height();

                        $tmpArrImg = explode('/', $origin_img);
                        
                        $new_img = config('plan.upload_thumbs_path').end($tmpArrImg);

                        if($w_img/$h_img > 550/350){

                            Image::make($origin_img)->resize(null, 350, function ($constraint) {
                                    $constraint->aspectRatio();
                            })->crop(550, 350)->save($new_img);
                        }else{
                            Image::make($origin_img)->resize(550, null, function ($constraint) {
                                    $constraint->aspectRatio();
                            })->crop(550, 350)->save($new_img);
                        }
                        $new_img = config('plan.upload_thumbs_path_2').end($tmpArrImg);
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
                    $rs = HotelImg::create(['hotel_id' => $id, 'image_url' => $name, 'display_order' => 1]);                
                    $image_id = $rs->id;
                    if( $imageArr['is_thumbnail'][$key] == 1){
                        $thumbnail_id = $image_id;
                    }
                }
            }
            $model = Hotels::find( $id );
            $model->thumbnail_id = $thumbnail_id;
            $model->save();
        }
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
        $model = Hotels::find($id);
        $model->delete();
        HotelImg::where('hotel_id', $id)->delete();
        HotelFeatured::where('hotel_id', $id)->delete();
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('hotel.index');
    }
}
