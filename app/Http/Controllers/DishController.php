<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Branch;
use Helper, File, Session, Auth;

class DishController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index(Request $request)
    {
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $userLogin = Auth::user();        
        $arrSearch['name'] = $name = $request->name ?? null;
        $arrSearch['branch_id'] = $branch_id = $request->branch_id ?? null;
        $arrSearch['category_id'] = $category_id = $request->category_id ?? null;
        $arrSearch['price'] = $price  =  $request->price ? str_replace(",", "", $request->price) : null;
      

        $query = Dish::where('status', 1);
        if($price){
            $query->where('price', $price);
        }    
        if($branch_id){
            $query->where('branch_id', $branch_id);
        }  
        if($category_id){
            $query->where('category_id', $category_id);
        }    
        if($name){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
        $items = $query->orderBy('display_order')->get();
        $branch_id = $request->branch_id ?? null;
        $cateList = Category::all();
        if($branch_id){
            $cateList = Category::where('branch_id', $branch_id)->get();    
        }
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        return view('dish.index', compact( 'items', 'arrSearch', 'cateList', 'branchList'));
    }
    public function changeValueByColumn(Request $request){
        $id = $request->id;
        $column = $request->col;
        $value = $request->value;
        $model = Dish::find($id);
        $model->update([$column => $value]);
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return Response
    */
    public function create(Request $request)
    {
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $branch_id = $request->branch_id ?? null;
        $cateList = Category::all();
        if($branch_id){
            $cateList = Category::where('branch_id', $branch_id)->get();    
        }
        return view('dish.create', compact('cateList', 'branchList'));
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
            'price' => 'required'       
        ],
        [
            'name.required' => 'Please fill name',            
            'price.required' => 'Please fill price',            
        ]);
        
        $dataArr['price'] = str_replace(",", "", $dataArr['price']);
        if($dataArr['image_url'] && $dataArr['image_name']){

            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);

            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);

            $dataArr['image_url'] = $destionation;
        }

        Dish::create($dataArr);

        Session::flash('message', 'Dish added successfully');

        return redirect()->route('dish.index', ['branch_id' => $dataArr['branch_id'], 'category_id' => $dataArr['category_id']]);
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
        $branchList = Branch::where('status', 1)->orderBy('display_order')->get();
        $detail = Dish::find($id);
        $branch_id = $detail->branch_id;
        $cateList = Category::all();
        if($branch_id){
            $cateList = Category::where('branch_id', $branch_id)->get();    
        }
        return view('dish.edit', compact( 'detail', 'branchList', 'cateList'));
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
            'price' => 'required'       
        ],
        [
            'name.required' => 'Please fill name',            
            'price.required' => 'Please fill price',            
        ]);
        if($dataArr['image_url'] && $dataArr['image_name']){

            $tmp = explode('/', $dataArr['image_url']);

            if(!is_dir('uploads/'.date('Y/m/d'))){
                mkdir('uploads/'.date('Y/m/d'), 0777, true);
            }

            $destionation = date('Y/m/d'). '/'. end($tmp);

            File::move(config('plantotravel.upload_path').$dataArr['image_url'], config('plantotravel.upload_path').$destionation);

            $dataArr['image_url'] = $destionation;
        }
        $dataArr['price'] = str_replace(",", "", $dataArr['price']);     

        $model = Dish::find($dataArr['id']);
        $model->update($dataArr);
        Session::flash('message', 'Update successful');

        return redirect()->route('dish.index', ['branch_id' => $dataArr['branch_id'], 'category_id' => $dataArr['category_id']]);
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
        $model = Dish::find($id);        
        $model->update(['status' => 0]);
        
        Session::flash('message', 'Dish deleted successfully');
        return redirect()->route('dish.index');
    }
}
