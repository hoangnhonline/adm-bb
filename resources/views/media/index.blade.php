@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Link
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'media.index' ) }}">Link</a></li>
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
      <a href="{{ route('media.create') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new</a>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Bộ lọc</h3>
        </div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('media.index') }}" id="searchForm">                              
            <div class="form-group">
              <label for="email">Ngày</label>
              <input type="text" class="form-control datepicker" name="date_photo" value="{{ $date_photo }}">
            </div>
             <div class="form-group">
              <label for="area_id">Địa điểm</label>
              <select class="form-control" name="area_id" id="area_id">
                <option value="">Tất cả</option>
                <option value="1" {{ $area_id == 1 ? "selected" : "" }}>Tour đảo</option>
                <option value="2" {{ $area_id == 2 ? "selected" : "" }}>Grand World</option>
                <option value="3" {{ $area_id == 3 ? "selected" : "" }}>Rạch Vẹm</option>
                <option value="4" {{ $area_id == 4 ? "selected" : "" }}>Hòn Thơm</option>
                                <option value="5" {{ $area_id == 5 ? "selected" : "" }}>Bãi Sao-2 đảo</option>
              </select>
            </div>
            <button type="submit" class="btn btn-default btn-sm">Search</button>
          </form>         
        </div>
      </div>
      <div class="box">

        <div class="box-header with-border">
          <h3 class="box-title">List ( <span class="value">{{ $items->total() }} links )</span></h3>
        </div>
        
        <!-- /.box-header -->
        <div class="box-body">
          <div style="text-align:center">
            {{ $items->appends( ['date_photo' => $date_photo] )->links() }}
          </div>  
          <div class="table-responsive">
          <table class="table table-bordered" id="table-list-data">
            <tr>
              <th style="width: 1%">#</th>                        
              <th>Địa điểm</th>
              <th>Người chụp</th>
              <th>Loại</th>
              <th>Ngày</th>          
              <th width="60%">Link</th>
              <th width="1%;white-space:nowrap">Thao tác</th>
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
              <tr id="row-{{ $item->id }}">
                <td><span class="order">{{ $i }}</span></td>   
                <td>
                  @if($item->area_id == 1)
                  Tour đảo
                  @elseif($item->area_id == 2)
                  Grand World
                  @elseif($item->area_id == 5)
                  Bãi Sao-2 đảo
                  @elseif($item->area_id == 3)
                  Rạch Vẹm
                  @elseif($item->area_id == 4)
                  Hòn Thơm
                  @endif
                </td>
                <td>{{ $item->user->name }}</td>
                <td>{{ $item->type == 1 ? "Ảnh" : "Flycam" }}</td>
                <td>{{ date('d/m/Y', strtotime($item->date_photo)) }}</td>
                <td>                  
                  <a style="font-size:17px" href="{{ route( 'media.edit', [ 'id' => $item->id ]) }}">{{ $item->link }}</a>
                </td>
                
                <td style="white-space:nowrap">                                   
                  <a href="{{ route( 'media.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>                 
                  
                  <a onclick="return callDelete('{{ $item->link }}','{{ route( 'media.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                  
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
          </div>
          <div style="text-align:center">
            {{ $items->appends( ['date_photo' => $date_photo] )->links() }}
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