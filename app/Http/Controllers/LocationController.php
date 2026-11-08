<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Booking;
use App\Models\Area;
use Helper, File, Session, Auth;
use GuzzleHttp\Client;

class LocationController extends Controller
{
    public function setDistance(){
        $APIKEY = 'LXPUv2eiONmWWtfydadXpgrOfQl2Xct2xi9bsYaC';
        $bookings = Booking::where('type', 1)->get();
        foreach ($bookings as $booking){
            if($booking->location){
                //Get distance
                $lat = $booking->location->latitude;
                $lng = $booking->location->longitude;
                if(!empty($lat) && !empty($lng)){
                    //Cang An Thoi: 10.8523068,103.1764641
                    $url = "https://rsapi.goong.io/DistanceMatrix?origins=" .$lat. "," . $lng . "&destinations=10.8523068,103.1764641&vehicle=car&api_key=$APIKEY";
                    
                    $client = new Client();
                    $response = $client->get($url);
                   // dd($response);
                    $response = $response->getBody();
                    $response = json_decode($response, true);
                    if(!empty($response['rows'])){
                        $distance = $response['rows'][0]['elements'][0]['distance']['value'];
                        $booking->location->distance = $distance;
                        $booking->location->save();
                    }
                }
                // $data[] = [
                //     'lat' => $lat,
                //     'lng' => $lng,
                //     'name' => $booking->name,
                //     'class' => $classes[array_rand($classes)]
                // ];
            }
        }
    }
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
     public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = Location::find($id);        

        $model->update([$column => $value]);
    }
    public function index(Request $request)
    {     
        // $rs = Location::all();
        // foreach($rs as $r){
        //     $slug= str_slug($r->name, '-');
        //     var_dump($slug);
        //     echo "<hr>";
        //     $r->update(['slug' => $slug]);
        // }
        // dd('1222');
        $use_df_default = Auth::user()->id == 151 ? date('d/m/Y', strtotime('yesterday')) : date('d/m/Y', time());
        $arrSearch['use_date_from'] = $use_date_from = $request->use_date_from ? $request->use_date_from : $use_df_default;
        $tmpDate = explode('/', $use_date_from);
        $use_date_from_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];            
                            
        $arrReplace = $request->replace_id ?? [];
        $area_id = $request->area_id ?? null;
        $name = $request->name ?? null;
        $no_area = $request->no_area ?? null;
        $id_search = $request->id_search ?? null;
        $all = Location::all();
        
        $booking = Booking::where(['type' => 1, 'use_date' => $use_date_from_format])
                    ->whereIn('status', [1, 2])
                    ->whereNotNull('location_id')
                    ->select('location_id')->groupBy('location_id')->pluck('location_id')->toArray();
        
        $query = Location::whereIn('id', $booking);
        if( $id_search ){
            $query->where('id', $id_search);
        }else{
            if( $name !='' ){
                $query->where('name', 'LIKE', '%'.$name.'%');
            }
            if($no_area){
                $query->whereNull('area_id');   
            }
            if($area_id){
                $query->where('area_id', $area_id);   
            }
        }
        
        $id = $request->location_id ?? null;
        //dd($arrReplace, $id);
        // if($id > 0 && !empty($arrReplace)){
        //     foreach($arrReplace as $location_id){

        //         $rsBk = Booking::where('location_id', $location_id)->get();
               
        //         foreach($rsBk as $bk){
        //             //var_dump($bk->id, $id);
        //             $bk->update(['location_id' => $id]);
        //         }
        //         $model = Location::find($location_id);
        //         if($model){
        //            $model->delete();
        //         }
                
        //     }
        // }
        $items = $query->orderBy('id', 'desc')->paginate(1000);
        // foreach($items as $item){
            
        //     $rs = Booking::where('address', $item->name)->get();
        //     foreach($rs as $r){
        //         // echo $item->name;
        //         // echo "<hr>";
        //         $r->update(['location_id' => $item->id]);
        //     }
        // }
        $areaList = Area::orderBy('display_order')->get();
        return view('location.index', compact( 'items', 'name', 'id', 'all', 'areaList', 'no_area', 'area_id', 'id_search', 'arrSearch'));
    }
    public function ajaxList(Request $request){

        $id_selected = $request->str_id ?? null;
        $tagArr = Location::all();
        
        //$tagArr = $query->orderBy('id', 'desc')->get();
       
        return view('location.ajax-list', compact( 'tagArr', 'id_selected'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $type = $request->type ? $request->type : 1;
        return view('location.create', compact('type'));
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
            'name' => 'required|unique:name'
            ],
        
        [
            'name.required' => 'Bạn chưa nhập tên địa điểm'
        ]);

        $rs = Location::create($dataArr);
        
        $object_id = $rs->id;

        Session::flash('message', 'Tạo mới địa điểm thành công');

        return redirect()->route('location.index');
    }
    public function saveToaDo(Request $request){
        $value = $request->value;
        $id = $request->id;
       
        $rs = Location::find($id);

        $rs->update(['address' => $value]);
    }
    public function saveValueColumn(Request $request){
        $value = $request->value;
        $id = $request->id;
        $column = $request->column; 
        $rs = Location::find($id);

        $rs->update([$column => $value]);
    }
    public function ajaxDelete(Request $request){        
        $id = $request->id;        
        $rs = Booking::where('location_id', $id)->get();
        foreach($rs as $r){
            $r->update(['location_id' => 466]);
        } 
        $model = Location::find($id);        
        $model->delete();        
    }
    public function saveName(Request $request){
        $value = $request->value;
        $id = $request->id;
        $rs = Location::find($id);

         $value = ucwords($value);
        $rs->update(['name' => $value]);
    }
    public function ajaxSave(Request $request)
    {
        $dataArr = $request->all();
        
        $str_tag = trim(ucwords($request->str_tag));    
       
        if( $str_tag != ""){
            $slug = str_slug($str_tag,"-");
            
            // check xem co chua
            $arr = Location::where('slug', '=', $slug)->first();
            if( !empty( (array) $arr)) {
                $return_id = $arr->id;
            }else{
                $rs = Location::create(['name'=> $str_tag, 'slug' => $slug, 'created_user' => Auth::user()->id]);
                $return_id= $rs->id;
            }

        }
        return $return_id;

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
        $detail = Location::find($id);
        $meta = (object) [];
        if ( $detail->meta_id > 0){
            $meta = MetaData::find( $detail->meta_id );
        }       

        return view('location.edit', compact( 'detail', 'meta'));
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
            'slug' => 'required|unique:tag,slug,'.$dataArr['id'].',id,type,'.$dataArr['type'],
        ],
        [
            'name.required' => 'Bạn chưa nhập tag',
            'slug.required' => 'Bạn chưa nhập slug',
            'slug.unique' => 'Slug đã được sử dụng.',
        ]);
        $dataArr['alias'] = Helper::stripUnicode($dataArr['name']);
        
        $model = Location::find($dataArr['id']);        

        $dataArr['updated_user'] = Auth::user()->id;

        $model->update($dataArr);

        if( $dataArr['meta_id'] != '' ){

            $this->storeMeta( $dataArr['id'], $dataArr['meta_id'], $dataArr);
        }

        Session::flash('message', 'Cập nhật tag thành công');

        return redirect()->route('location.index', [ 'type' => $dataArr['type'] ]);
    }
    public function storeMeta( $id, $meta_id, $dataArr ){
       
        $arrData = [ 'title' => $dataArr['meta_title'], 'description' => $dataArr['meta_description'], 'keywords'=> $dataArr['meta_keywords'], 'custom_text' => $dataArr['custom_text'], 'updated_user' => Auth::user()->id ];
        if( $meta_id == 0){
            $arrData['created_user'] = Auth::user()->id;            
            $rs = MetaData::create( $arrData );
            $meta_id = $rs->id;
            
            $modelSp = Location::find( $id );
            $modelSp->meta_id = $meta_id;
            $modelSp->save();
        }else {
            $model = MetaData::find($meta_id);           
            $model->update( $arrData );
        }              
    }
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    public function deleteMulti(Request $request){
        $idArr = $request->replace_id;
        if(!empty($idArr)){
            foreach($idArr as $id){
                $rs = Booking::where('location_id', $id)->get();
                foreach($rs as $r){
                    $r->update(['location_id' => 466]);
                } 
                $model = Location::find($id);
                $model->delete();
            }
        }
        // redirect
        Session::flash('message', 'Xóa địa điểm thành công');
        return redirect()->route('location.index');
    }
    public function destroy($id)
    {
        // delete
        $rs = Booking::where('location_id', $id)->get();
        foreach($rs as $r){
            $r->update(['location_id' => 466]);
        } 
        $model = Location::find($id);
        $model->delete();

        // redirect
        Session::flash('message', 'Xóa địa điểm thành công');
        return redirect()->route('location.index');
    }
}