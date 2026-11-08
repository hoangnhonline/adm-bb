@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Món ăn
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'food.index' ) }}">Món ăn</a></li>
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
      <a href="{{ route('food.create',['cate_id' => $cate_id]) }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new</a>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Bộ lọc</h3>
        </div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('food.index') }}" id="searchForm"> 
          <div class="form-group">
            <label for="email">&nbsp;&nbsp;&nbsp;Danh mục :</label>
              <select class="form-control select2" name="cate_id" id="cate_id">
                <option value="">--Tất cả--</option>
                @foreach($foodCate as $cate)
                <option value="{{ $cate->id }}" {{ $cate_id == $cate->id ? "selected" : "" }}>{{ $cate->name }}</option>
                @endforeach
              </select>
          </div>                       
            <div class="form-group">
              <label for="email">&nbsp;&nbsp;&nbsp;Từ khóa :</label>
              <input type="text" class="form-control" name="title" value="{{ $name }}">
            </div>
            <button type="submit" class="btn btn-default btn-sm">Search</button>
          </form>         
        </div>
      </div>
      <div class="box">

        <div class="box-header with-border">
          <h3 class="box-title">List ( <span class="value">{{ $items->total() }} món )</span></h3>
        </div>
        
        <!-- /.box-header -->
        <div class="box-body">
          <div style="text-align:center">
            {{ $items->appends( ['name' => $name] )->links() }}
          </div>  
          <table class="table table-bordered" id="table-list-data">
            <tr>
              <th style="width: 1%">#</th>                        
              <th>Thumbnail</th>             
              <th>Thông tin</th>
              <th>Giá</th>
              <th width="1%;white-space:nowrap">Thao tác</th>
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
              <tr id="row-{{ $item->id }}">
                <td><span class="order">{{ $i }}</span></td>   
                 
                <td width="150">
                  <img class="img-thumbnail" src="{{ Helper::showImage($item->thumbnail_url)}}" width="145">
                </td>        
               
                <td>                  
                  <a style="font-size:17px" href="{{ route( 'food.edit', [ 'id' => $item->id ]) }}">{{ $item->name }}</a>
                  
                  @if( $item->is_hot == 1 )
                  <label class="label label-danger">HOT</label>
                  @endif
                 
                  <p>{{ $item->description }}</p>
                </td>
                <td>
                  {{ number_format($item->price) }}
                </td>
                <td style="white-space:nowrap">                                   
                  <a href="{{ route( 'food.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>                 
                  
                  <a onclick="return callDelete('{{ $item->title }}','{{ route( 'food.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                  
                </td>
              </tr> 
              @endforeach
            @else
            <tr>
              <td colspan="9">Không có dữ liệu.</td>
            </tr>
            @endif

          </tbody>
          </table>
          <div style="text-align:center">
            {{ $items->appends( ['name' => $name] )->links() }}
          </div>  
        </div>        
      </div>
      <!-- /.box -->     
    </div>
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>
<input type="hidden" id="table_name" value="articles">
@stop