<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\MediaCate;
use App\Models\Media;
use App\Models\Account;
use App\User;
use App\Models\SmsPayment;
use Helper, File, Session, Auth, Image, Hash;

class MediaController extends Controller
{
    public function diemDanhPublic(Request $request){
        //dd(Hash::make('123654@'));
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));

        $codeUser = $request->code ?? null;
        $detailUser = Account::where('code', $codeUser)->first();
        $user_id = $detailUser->id;
        $mediaList = Media::where('user_id', $user_id)
                    ->where('date_photo', '>=', $mindate)->where('date_photo', '<=', $maxdate)->orderBy('link', 'asc')->get();

        $mediaDay = $detailArr = $cateArr = $areaArr = [];
       
        $huylam = 0;
        foreach($mediaList as $media){
            $day = date('d', strtotime($media->date_photo));
            $mediaDay[$day] = $media->link;
            $detailArr[$day][$media->type] = $media;
            $cateArr[$day] = $media->tour_id;
            $areaArr[$day] = $media->area_id;
            if($media->huy_lam == 1){
                $huylam++;
            }
        }
        $luong = $user_id == 32 ? 4500000 : 3000000;
        $congtacphi = count($mediaDay)*300000 - $huylam*100000;
        $totalLuong = $luong + $congtacphi;
        //dd($totalLuong);
       //dd($huylam, $mediaDay);      
        //dd($cateArr);
        //dd($detailArr);
        $userList = User::whereIn('id', [32, 33, 41, 58, 65, 76, 258, 406, 447])->get();             
        return view('layout-diem-danh', compact('userList', 'user_id', 'mediaDay', 'month', 'year', 'detailArr', 'cateArr', 'totalLuong', 'codeUser','detailUser', 'areaArr'));
    }

    public function diemDanh(Request $request){
        //dd(Hash::make('123654@'));
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $mindate = "$year-$month-01";
        $maxdate = date("Y-m-t", strtotime($mindate));

        if(Auth::user()->role == 1){
            $user_id = $request->user_id;    
        }else{
            $user_id = Auth::user()->id;            
        }
        $mediaList = Media::where('user_id', $user_id)
                    ->where('date_photo', '>=', $mindate)->where('date_photo', '<=', $maxdate)->orderBy('link', 'asc')->get();

        $mediaDay = $detailArr = $cateArr = $areaArr = [];
     //   dd($mediaList);
        $huylam = 0;
        foreach($mediaList as $media){
            $day = date('d', strtotime($media->date_photo));
            $mediaDay[$day] = $media->link;
            $detailArr[$day][$media->type] = $media;
            $cateArr[$day] = $media->tour_id;
            $areaArr[$day] = $media->area_id;
            if($media->huy_lam == 1){
                $huylam++;
            }
        }
        $luong = $user_id == 32 ? 4500000 : 3000000;
        $congtacphi = count($mediaDay)*300000 - $huylam*100000;
        $totalLuong = $luong + $congtacphi;
        //dd($totalLuong);
       //dd($huylam, $mediaDay);      
        //dd($cateArr);
        //dd($detailArr);
        $userList = User::whereIn('id', [32, 33, 41, 58, 65, 76, 258, 406, 447])->get();
        if(Auth::user()->role == 1) $view = 'media.admin-diem-danh';
        else $view = 'media.diem-danh';        
        return view($view, compact('userList', 'user_id', 'mediaDay', 'month', 'year', 'detailArr', 'cateArr', 'totalLuong', 'areaArr'));
    }
    public function index(Request $request)
    {         
        $arrSearch['date_photo'] = $date_photo = $request->date_photo ? $request->date_photo : date('d/m/Y', strtotime('-1 day'));  
        $tmpDate = explode('/', $date_photo);
        $date_photo_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        $area_id = $request->area_id ?? null;
        $rs = Media::where('date_photo', $date_photo_format);
        if($area_id){
            $rs->where('area_id', $area_id);
        }
        $items = $rs->orderBy('area_id')->orderBy('user_id')->orderBy('type', 'desc')->paginate(20);  

        return view('media.index', compact( 'items', 'date_photo', 'area_id'));
    }
    public function ajaxStore(Request $request){

        $dataArr = $request->all();       
        $dataArr['date_photo'] = $dataArr['year'].'-'.$dataArr['month'].'-'.$dataArr['day'];
        $dataArr['user_id'] = Auth::user()->role == 1 ? $dataArr['user_id'] : Auth::user()->id;
        
        $dataArr['type'] = 1;
        $dataArr['link'] = $dataArr['link_anh'];
        $rsCheck = Media::where([
            'date_photo' => $dataArr['date_photo'],
            'user_id' => $dataArr['user_id'],
            'type' => $dataArr['type'],            
        ])->first();

        if($rsCheck){   
            $rsCheck->update($dataArr);
        }else{
            if($dataArr['link_anh']){
                Media::create($dataArr);
            }            
        }
    
    
        $dataArr2 = $dataArr;
        $dataArr2['type'] = 2;
        $dataArr2['link'] = $dataArr['link_flycam'];      
        
        $rsCheck2 = Media::where([
            'date_photo' => $dataArr2['date_photo'],
            'user_id' => $dataArr2['user_id'],
            'type' => $dataArr2['type'],                
        ])->first();
        if($rsCheck2){            
            $rsCheck2->update($dataArr2);
        }else{
            if($dataArr['link_flycam']){
                Media::create($dataArr2);   
            } 
        }
        
        
        return json_encode(['success' => 1]);        
    }
    public function ajaxStorePublic(Request $request){
        $dataArr = $request->all();       
        $dataArr['date_photo'] = $dataArr['year'].'-'.$dataArr['month'].'-'.$dataArr['day'];
         $codeUser = $request->codeUser ?? null;
        $detailUser = Account::where('code', $codeUser)->first();
        $dataArr['user_id'] = $user_id = $detailUser->id;        
        
        $dataArr['type'] = 1;            
       // Media::create($dataArr);
        $dataArr2 = [];
        
        
            $dataArr['type'] = 1;
            $dataArr['link'] = $dataArr['link_anh'];
            $rsCheck = Media::where([
                'date_photo' => $dataArr['date_photo'],
                'user_id' => $dataArr['user_id'],
                'type' => $dataArr['type'],            
            ])->first();

            if($rsCheck){   
                $rsCheck->update($dataArr);
            }else{
                if($dataArr['link_anh']){
                    Media::create($dataArr); 
                }   
            }
        
        
            $dataArr2 = $dataArr;
            $dataArr2['type'] = 2;
            $dataArr2['link'] = $dataArr['link_flycam'];          
            $rsCheck2 = Media::where([
                'date_photo' => $dataArr2['date_photo'],
                'user_id' => $dataArr2['user_id'],
                'type' => $dataArr2['type'],                
            ])->first();
            if($rsCheck2){   
                $rsCheck2->update($dataArr2);
            }else{
                if($dataArr['link_flycam']){
                    Media::create($dataArr2);   
                } 
            }
        
        
        return json_encode(['success' => 1]);        
    }
    public function smsList(Request $request)
    {         
        $arrSearch['send_date'] = $send_date = $request->send_date ? $request->send_date : date('d/m/Y');  
        $tmpDate = explode('/', $send_date);
        $send_date_format = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        $items = SmsPayment::where('send_date', $send_date_format)->where('type', 2)->orderBy('id', 'desc')->paginate(1000);  
        return view('media.sms', compact( 'items', 'send_date'));
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {  
        $userList = User::whereIn('id', [32, 33, 41, 58, 65, 76, 258, 406, 447])->get();
        return view('media.create', compact('userList'));
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
            'user_id' => 'required',
            'type' => 'required',
            'date_photo' => 'required',
            'link' => 'required',
        ],
        [  
           
        ]);       
        $old_date = $dataArr['date_photo'];
        $tmpDate = explode('/', $dataArr['date_photo']);
        $dataArr['date_photo'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        
        $rs = Media::create($dataArr);
    
        Session::flash('message', 'Tạo mới thành công');

        return redirect()->route('media.index', ['date_photo' => $old_date]);
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
        
        $detail = Media::find($id);
        $userList = User::where('camera', 1)->get();
        return view('media.edit', compact( 'detail', 'userList'));
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
            'user_id' => 'required',
            'type' => 'required',
            'date_photo' => 'required',
            'link' => 'required',
        ],
        [  
           
        ]);       
        $old_date = $dataArr['date_photo'];
        $tmpDate = explode('/', $dataArr['date_photo']);
        $dataArr['date_photo'] = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
        $model = Media::find($dataArr['id']);
       
        $model->update($dataArr);        
        
        Session::flash('message', 'Cập nhật thành công');        
        
        return redirect()->route('media.index', ['date_photo' => $old_date]);    
        
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
        $model = Media::find($id);      
        $model->delete();      
        // redirect
        Session::flash('message', 'Xóa thành công');        
        return redirect()->route('media.index');   
    }
}
