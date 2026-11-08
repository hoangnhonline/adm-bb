<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Rooms;
use App\Models\HotelImg;
use App\Models\MetaData;
use App\Models\Hotels;
use App\Models\HotelFeatured;
use App\Models\Featured;
use App\Models\HotelsTypesSettings;
use App\Models\RoomsPrice;

use Helper, File, Session, Auth, Image;

class RoomController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {   
        // if( Auth::user()->role > 3 ){
        //     return redirect()->route('home');
        // }        
        $hotel_id = $request->hotel_id  ? $request->hotel_id : null;
        $name = isset($request->name) && $request->name != '' ? $request->name : '';
        
        $query = Rooms::where('status', 1);
        if($hotel_id > 0){
            $query->where('hotel_id', $hotel_id);
        }
      
        // check editor
        if( Auth::user()->role > 2 ){
            $query->where('created_user', Auth::user()->id);
        }
        if( $name != ''){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        $hotelList = Hotels::where('status', 1)->orderBy('id', 'asc')->get();
        $items = $query->orderBy('id', 'asc')->paginate(20);   
        
        return view('room.index', compact( 'items', 'name', 'hotelList', 'hotel_id'));
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
        $allTypeList = HotelsTypesSettings::where('type', 3)->get();
        $roomAmen = [];
        foreach($allTypeList as $types){            
            $roomAmen[] = $types;            
        }        
        $hotelList = Hotels::where('status', 1)->orderBy('id', 'desc')->get();
        $hotel_id = $request->hotel_id ?? null;
        return view('room.create', compact('roomAmen', 'hotelList', 'hotel_id'));
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
            'hotel_id' => 'required',
            'name' => 'required',                                               
        ],
        [
            'hotel_id.required' => 'Bạn chưa chọn khách sạn',
            'name.required' => 'Bạn chưa nhập tên loại phòng',
        ]);
       
        $dataArr['is_hot'] = isset($dataArr['is_hot']) ? 1 : 0;
        if(isset($dataArr['amenities'])){
             $dataArr['amenities'] = implode(',', $dataArr['amenities']);
         }       

        $dataArr['status'] = 1;

        $dataArr['created_user'] = Auth::user()->id;

        $dataArr['updated_user'] = Auth::user()->id;        

        $rs = Rooms::create($dataArr);

        $hotel_id = $rs->id;
       
        $this->storeImage($hotel_id, $dataArr);       
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('room.index', ['hotel_id' => $dataArr['hotel_id']]);
    }

    public function storeMeta( $id, $meta_id, $dataArr ){
       
        $arrData = [ 'title' => $dataArr['meta_title'], 'description' => $dataArr['meta_description'], 'keywords'=> $dataArr['meta_keywords'], 'custom_text' => $dataArr['custom_text'], 'updated_user' => Auth::user()->id ];
        if( $meta_id == 0){
            $arrData['created_user'] = Auth::user()->id;            
            $rs = MetaData::create( $arrData );
            $meta_id = $rs->id;
            
            $modelSp = Rooms::find( $id );
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
       
        $detail = Rooms::find($id);   
              
        $allTypeList = HotelsTypesSettings::where('type', '=', 3)->get();
        $roomAmen = $hotelPay = $hotelType = [];
        
        foreach($allTypeList as $types){            
            $roomAmen[] = $types;            
        }      
        $hotelList = Hotels::where('status', 1)->orderBy('id', 'desc')->get();
        return view('room.edit', compact('detail', 'roomAmen', 'hotelList'));
    }
    public function price($id)
    {       
        // if( Auth::user()->role > 2 ){
        //     return redirect()->route('home');
        // }
       
        $detail = Rooms::find($id);
        

        //get price
        $priceList = RoomsPrice::where('room_id', $id)->orderBy('price', 'asc')->get();       
        //$priceArr = RoomsPrice::getPriceFromTo($id, '2019-08-29', '2019-09-03');
        //$minPrice = RoomsPrice::getRoomMinPrice($id);
        //$minPrice = RoomsPrice::getHotelMinPrice($detail->hotel_id);       
        $dateDefault = RoomsPrice::where('hotel_id', $detail->hotel_id)->groupBy('from_date')->orderBy('price')->get();        
        return view('room.price', compact('detail', 'roomAmen', 'hotelList', 'priceList', 'dateDefault'));
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
            'hotel_id' => 'required',
            'name' => 'required',                                                   
        ],
        [
            'hotel_id.required' => 'Bạn chưa chọn khách sạn',
            'name.required' => 'Bạn chưa nhập tên loại phòng',                  
        ]);
       
        $dataArr['is_hot'] = isset($dataArr['is_hot']) ? 1 : 0; 

        $dataArr['updated_user'] = Auth::user()->id;
            
        $model = Rooms::find($dataArr['id']);

        $model->update($dataArr);
        
        $hotel_id = $dataArr['id'];
        
        $this->storeImage($hotel_id, $dataArr);

        Session::flash('message', 'Cập nhật thành công');

        return redirect()->route('room.index');
    }
    public function storePrice(Request $request){

        $dataArr = $request->all();
        RoomsPrice::where('room_id', $dataArr['room_id'])->delete();      
        foreach($dataArr['from_date'] as $key => $from_date){
            $to_date = $dataArr['to_date'][$key];
            $price = str_replace(",", '', $dataArr['price'][$key]);
            $price_goc = str_replace(",", '', $dataArr['price_goc'][$key]);
            if($from_date != '' && $price_goc > 0){
                $to_date = $to_date != '' ? $to_date : $from_date;                
                $tmpFrom = explode("/", $from_date);
                $tmpTo = explode("/", $to_date);                
                RoomsPrice::create([
                    'from_date' => $tmpFrom[2].'-'.$tmpFrom[1].'-'.$tmpFrom[0],
                    'to_date' => $tmpTo[2].'-'.$tmpTo[1].'-'.$tmpTo[0],
                    'price' => $price,
                    'price_goc' => $price_goc,
                    'hotel_id' => $dataArr['hotel_id'],
                    'room_id' => $dataArr['room_id'] ,
                    'created_user' => Auth::user()->id,
                    'updated_user' => Auth::user()->id,
                ]);
            }
        }
        return redirect()->route('room.index', ['hotel_id' => $dataArr['hotel_id']]);
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
        if( isset( $dataArr[' '])){
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
            $model = Rooms::find( $id );
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
        $model = Rooms::find($id);
        $model->delete();        
        // redirect
        Session::flash('message', 'Xóa thành công');
        return redirect()->route('room.index');
    }
}
