@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Dish
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'dish.index' ) }}">Dish</a></li>
    <li class="active">List</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
      <a href="{{ route('dish.create') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new</a>
      <div class="box">
        <div class="panel-body">
        @php
       // $role = 4;
        @endphp
        <form class="form-inline" role="form" method="GET" action="{{ route('dish.index') }}">
           <div class="form-group">           
              <select name="branch_id" id="branch_id" class="form-control select2">
                <option value="">--Branch--</option>
                @foreach($branchList as $beach)
                <option value="{{ $beach->id }}" {{ $arrSearch['branch_id'] == $beach->id ? "selected" : "" }}>{{ $beach->name }}</option>
                @endforeach                      
              </select>
         </div>         
         <div class="form-group">                  
                  <select name="category_id" id="category_id" class="form-control select2">
                    <option value="">--Category--</option>
                    @foreach($cateList as $cate)
                    <option value="{{ $cate->id }}" {{ $arrSearch['category_id'] == $cate->id ? "selected" : "" }}>{{ $cate->name }} - 
                      @php
                      if($cate->branch_id == 1){
                        echo "Boroda";
                      }elseif($cate->branch_id == 2){
                        echo "Night Market";
                      }else{
                        echo "River view";
                      }                      
                      @endphp
                    </option>
                    @endforeach
                  </select>
                </div> 
          <div class="form-group">          
            <input type="text" name="name" value="{{ $arrSearch['name'] }}" class="form-control">
          </div>                   
          <button class="btn btn-info" type="submit">Search</button>     
          </form>
      </div>
      </div>
        <div class="box-header with-border">
          <h3 class="box-title">List</h3>
        </div>
        
        <!-- /.box-header -->
        <div class="box-body">
          <table class="table table-bordered" id="table-list-data">
            <tr>
              <th style="width: 1%">#</th>
              
              <th>Dish name</th> 
              <th>Image</th>
              <th class="text-right">Price</th>
              <th class="text-right">Display order</th>                                          
              <th width="1%" style="white-space: nowrap;">Action</th>
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
              <tr id="row-{{ $item->id }}">
                <td><span class="order">{{ $i }}</span></td>
                
                <td>                  
                  <a href="{{ route( 'dish.edit', [ 'id' => $item->id ]) }}">{{ $item->name }}</a>               

                </td> 
                <td>
                  <img src="{{ config('plantotravel.upload_url').$item->image_url }}" class="lazy" width="150" height="150" />

                </td>
                <td class="text-right">
                  {{ number_format($item->price) }}
                </td> 
                <td class="text-right">
                  {{ number_format($item->display_order) }}
                </td>                                          
                <td style="white-space:nowrap">                 
                  <a href="{{ route( 'dish.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>
                  <a onclick="return callDelete('{{ $item->name }}','{{ route( 'dish.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
             
                </td>
              </tr> 
              @endforeach
            @else
            <tr>
              <td colspan="9">No data!</td>
            </tr>
            @endif

          </tbody>
          </table>
        </div>        
      </div>
      <!-- /.box -->     
    </div>
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>
@stop
@section('js')
<script type="text/javascript">
function callDelete(name, url){  
  swal({
    title: 'Do you want to delete "' + name +'"?',
    text: "Once deleted, the data cannot be recovered..",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes'
  }).then(function() {
    location.href= url;
  })
  return flag;
}

function ajaxChange(id, obj){
        $.ajax({
            url : "{{ route('dish.change-value-by-column') }}",
            type : 'GET',
            data : {
              id : id,
              col : obj.data('column'),
              value: obj.val()
            },
            success: function(data){
                console.log(data);
            }
          });
      }
</script>
@stop