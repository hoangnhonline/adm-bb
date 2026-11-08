<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\LandingProjects;
use App\Models\WArticlesCate;
use App\Models\WPages;
use App\Models\Menu;
use App\Models\BoatPrices;
use App\Models\BookingLogs;
use App\Models\Account;
use App\Models\Booking;
use DB, Session;
class GeneralController extends Controller
{
    public function debug(Request $request){
        // $all = Booking::where('use_date', '>=', '2021-01-01')->get();
        // foreach ($all as $bk) {
        //     if($bk->user){
                
        //         try{
        //             $level = $bk->user->level;
        //             $bk->update(['level' => $level]);
        //         }catch(Exception $ex){
        //             dd($bk->user_id);
        //         }
                
        //     }
            
        // }
    }
    public function updateOrder(Request $request){
        if ($request->ajax())
        {
        	$dataArr = $request->all();
        	$str_order = $dataArr['str_order'];        	
            $table = $dataArr['table'];        
            if( $str_order ){
            	$tmpArr = explode(";", $str_order);
            	$i = 0;
            	foreach ($tmpArr as $id) {
            		if( $id > 0 ){
            			$i++;
            			DB::table($table)
				        ->where('id', $id)				        
				        ->update(array('display_order' => $i));			
            		}
            	}
            }
        }        
    }
    public function changeValueByColumnChung(Request $request){
        $table = $request->table;
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = Account::find($id);
        //dd($model);
        //dd($model);
        //$model = User::find($id);
         // luu log
        // $oldData = [$column => $model->status];
        // $dataArr = [$column => $value];
        // // $contentDiff = array_diff_assoc($dataArr, $oldData);
        // // if(!empty($contentDiff)){
        // //     $oldContent = [];

        // //     foreach($contentDiff as $k => $v){
        // //         $oldContent[$k] = $oldData[$k];
        // //     }
        // //     BookingLogs::create([
        // //         'booking_id' =>  $id,
        // //         'content' =>json_encode(['old' => $oldContent, 'new' => $contentDiff]),
        // //         'action' => 5, // ajax hoa hong
        // //         'user_id' => Auth::user()->id
        // //     ]);
        // // }


        $model->update([$column => $value]);
    }
    public function getBoatPrices(Request $request){
        $no = $request->no;
        $all = BoatPrices::all();
        $priceReturn = 0;
        $priceMax = 0;
        foreach($all as $row){
            if($row->price > $priceMax){
                $priceMax = $row->price;
            }
            if($no >= $row->pax_from && $no <= $row->pax_to){
                $priceReturn = $row->price;
            }
        }
        if($no > 19){
            $priceReturn = ($no-19)*100000 + $priceMax;
        }
        return $priceReturn;
    }
    public function getSlug(Request $request){
    	$strReturn = '';
    	if( $request->ajax() ){
    		$str = $request->str;
    		if( $str ){
    			$strReturn = str_slug( $str );
    		}
    	}
    	return response()->json( ['str' => $strReturn] );
    }
    public function setupMenu(Request $request){        
        $articlesCateList = WArticlesDish::where('status', 1)->orderBy('display_order', 'asc')->get();
        $pageList = WPages::where('status', 1)->get();
        return view('menu.index', compact( 'landingList', 'articlesCateList', 'pageList'));
    }
    public function renderMenu(Request $request){        
        $dataArr = $request->all();       
        return view('menu.render-menu', compact( 'dataArr' ));   
    }
    public function changeValue(Request $request){
        $value = $request->value;
        $column = $request->column;
        $table = $request->table;     
        $id = $request->id;
        
        DB::table($table)->where('id', $id)->update([$column => $value]);                    
    }
    public function storeMenu(Request $request){
        $data = $request->all();
        Menu::where('menu_id', 1)->delete();
        if(!empty($data)){
            $i = 0;
            foreach($data['title'] as $k => $title){
                $i++;
                Menu::create([
                    'menu_id' => 1,
                    'title' => $title,
                    'url' => $data['url'][$k],
                    'slug' => $data['slug'][$k],
                    'type' => $data['type'][$k],
                    'object_id' => $data['object_id'][$k],
                    'status' => 1,
                    'title_attr' => $title,
                    'display_order' => $i
                ]);
            }
        }
        Session::flash('message', 'Cập nhật menu thành công.');

        return redirect()->route('menu.index');
    }
}
