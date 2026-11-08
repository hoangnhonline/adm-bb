@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Thông báo
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'noti.index' ) }}">Thông báo</a></li>
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
     
      <div class="box">

        <div class="box-header with-border">
          <h3 class="box-title">List ( <span class="value">{{ $items->count() }} thông báo )</span></h3>
        </div>
        
        <!-- /.box-header -->
        <div class="box-body">
           <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Bộ lọc</h3>
            </div>
            <div class="panel-body">
              <form class="form-inline" role="form" method="GET" action="{{ route('noti.index') }}" id="searchForm"> 
              <div class="form-group">
                <label for="email">Status </label>
                  <select class="form-control" name="is_read" id="is_read">
                    <option value="-1">--Tất cả--</option>
                    <option value="0" {{ $is_read == 0 ? "selected" : "" }}>Chưa xem</option>
                    <option value="1" {{ $is_read == 1 ? "selected" : "" }}>Đã xem</option>
                  </select>
              </div>                       
               
                <button type="submit" class="btn btn-default btn-sm">Search</button>
              </form>         
            </div>
          </div>
          <form method="GET" action="{{ route('noti.update-multi') }}">
          <button type="submit" onclick="return confirm('Bạn đã xem các thông báo đã chọn?')" class="btn btn-sm btn-warning" style="margin-bottom: 5px;">Đã xem các mục đã chọn</button>
          <table class="table table-bordered" id="table-list-data">
            <tr>
              <th style="width: 1%">
                <input type="checkbox" id="check_all">
              </th>
              <th style="width: 1%">#</th>     

              <th class="text-left" width="100px">Thời gian</th>
              <th class="text-left">Nội dung</th>
              <th class="text-center" style="white-space: nowrap;" width="1%">Thao tác</th>
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
              <tr id="row-{{ $item->id }}">
                <td>
                  @if($item->is_read == 0)
                  <input type="checkbox" name="id[]" value="{{ $item->id }}" class="check_one">
                  @endif
                </td>
                <td><span class="order">{{ $i }}</span></td>   
                
                <td class="text-left">  
                    {{ date('d/m H:i', strtotime($item->created_at)) }}                  
                </td>
                <td>
                  <p style="color: #3c8dbc; font-weight: bold">{{ $item->title }} @if($item->is_read == 1)
                  <label class="label label-sm label-default">Đã xem</label>
                  @else
                  <label class="label label-sm label-danger">Chưa xem</label>
                  @endif</p>
                  {!! $item->content !!}                  
                </td>
                <td class="text-center">
                  @if($item->is_read == 0)
                  <a href="{{ route('noti.read', ['id' => $item->id]) }}" class="btn btn-sm btn-warning" onclick="return confirm('Bạn đã xem thông báo này?')">Đã xem</a>
                  @endif
                </td>

              </tr> 
              @endforeach
            @else
            <tr>
              <td colspan="4">Không có dữ liệu.</td>
            </tr>
            @endif

          </tbody>
          </table>
         
        </div>   
        </form>     
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
  $(document).ready(function(){
    $("#check_all").click(function(){
      $('.check_one').not(this).prop('checked', this.checked);
    });
    $('img.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).attr('src'));
      $('#uncModal').modal('show');
    }); 
  });
</script>
@stop