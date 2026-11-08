@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Công nợ
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'debt.index' ) }}">Công nợ</a></li>
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
      <a href="{{ route('debt.create') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new</a>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Bộ lọc</h3>
        </div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('debt.index') }}" id="searchForm">
            <div class="form-group">
              <label for="email">Tỉnh/Thành</label>
              <select class="form-control" name="city_id" id="city_id">
                <option value="">--Tất cả--</option>
                <option value="1"  {{ $city_id == 1 ? "selected" : "" }}>Phú Quốc</option>
                <option value="2"  {{ $city_id == 2 ? "selected" : "" }}>Đà Nẵng</option>
              </select>
            </div> 
            <div class="form-group">
              <label for="time_type">&nbsp;&nbsp;&nbsp;Thời gian</label>
              <select class="form-control" name="time_type" id="time_type">                
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>Theo tháng</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Khoảng ngày</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>Ngày cụ thể </option>
              </select>
            </div> 
            @if($time_type == 1)
            <div class="form-group  chon-thang">
                <label>&nbsp;&nbsp;&nbsp;THÁNG</label>
                <select class="form-control" id="month_change" name="month">
                  <option value="">--Chọn--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group  chon-thang">                
                <select class="form-control" id="year_change" name="year">
                  <option value="">--Năm--</option>
                  <option value="2020" {{ $year == 2020 ? "selected" : "" }}>2020</option>
                  <option value="2021" {{ $year == 2021 ? "selected" : "" }}>2021</option>
                  <option value="2022" {{ $year == 2022 ? "selected" : "" }}>2022</option>
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)
            
            <div class="form-group chon-ngay">
              <label for="pay_date_from">&nbsp;&nbsp;&nbsp;@if($time_type == 2) Từ ngày @else Ngày @endif </label>
              <input type="text" class="form-control datepicker" autocomplete="off" name="pay_date_from" placeholder="Từ ngày" value="{{ $arrSearch['pay_date_from'] }}" style="width: 100px">
            </div>
           
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">
              <label for="pay_date_to">&nbsp;&nbsp;&nbsp;Đến ngày</label>
              <input type="text" class="form-control datepicker" autocomplete="off" name="pay_date_to" placeholder="Đến ngày" value="{{ $arrSearch['pay_date_to'] }}" style="width: 100px">
            </div>
             @endif
            @endif
            <div class="form-group">
              <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien">
                <option value="">--Receiver--</option>                
                <option value="1" {{ $nguoi_thu_tien == 1 ? "selected" : "" }}>CTY</option>
                <option value="2" {{ $nguoi_thu_tien == 2 ? "selected" : "" }}>Điều hành</option>
              </select>
            </div>
            <div class="form-group">
              <input type="text" class="form-control" name="content" value="{{ $content }}" placeholder="Nội dung"  style="width: 100px">
            </div>
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button>
          </form>         
        </div>
      </div>
      <div class="box">

        <div class="box-header with-border">
          <h3 class="box-title">List - Tổng công nợ: <span style="color:red">{{ number_format($totalDebt) }}</span></h3>
        </div>
        
        <!-- /.box-header -->
        <div class="box-body">
          <table class="table table-bordered table-hover" id="table-list-data">
            <tr>
              <th style="width: 1%">#</th>                        
              <th>Ngày</th>             
              <th>Nội dung</th>
              <th class="text-right">Số tiền</th>
              <th class="text-center" width="200">Hình ảnh</th>             
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
                  {{ date('d/m/Y', strtotime($item->pay_date)) }}
                </td>        
               <td>{!! $item->content !!}</td>
                <td class="text-right">                  
                 {{ number_format($item->amount) }}
                </td>
                <td class="text-center">
                  @if($item->image_url)
                  <img style="max-width: 200px" src="{{ config('plantotravel.upload_url').$item->image_url }}" height="200" class="img-unc">                  
                  @endif
                </td>
                
                <td style="white-space:nowrap">   
                
                  <a href="{{ route( 'debt.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>                 
                  
                  <a onclick="return callDelete('{{ $item->title }}','{{ route( 'debt.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                
                </td>
              </tr> 
              @endforeach
            @else
            <tr>
              <td colspan="6">Không có dữ liệu.</td>
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
<div class="modal fade" id="uncModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="text-align: center;">
       <div class="modal-header">        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img src="" id="unc_img" style="width: 100%">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>
@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    $('img.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).attr('src'));
      $('#uncModal').modal('show');
    }); 
  });
function callDelete(name, url){  
  swal({
    title: 'Bạn muốn xóa "' + name +'"?',
    text: "Dữ liệu sẽ không thể phục hồi.",
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
$(document).ready(function(){
  $('.change-order').blur(function(){
        var obj = $(this);
        $.ajax({
          url:'{{ route('debt.change-value-by-column')}}',
          type:'GET',
          data: {
            id : obj.data('id'),
            value : obj.val(),
            col : 'Công nợ'
          },
          success : function(doc){
            console.log(data);
          }
        });
        });
  $('#table-list-data tbody').sortable({
        placeholder: 'placeholder',
        handle: ".move",
        start: function (event, ui) {
                ui.item.toggleClass("highlight");
        },
        stop: function (event, ui) {
                ui.item.toggleClass("highlight");
        },          
        axis: "y",
        update: function() {
            var rows = $('#table-list-data tbody tr');
            var strOrder = '';
            var strTemp = '';
            for (var i=0; i<rows.length; i++) {
                strTemp = rows[i].id;
                strOrder += strTemp.replace('row-','') + ";";
            }     
            updateOrder("cate_child", strOrder);
        }
    });
});
function updateOrder(table, strOrder){
  $.ajax({
      url: $('#route_update_order').val(),
      type: "POST",
      async: false,
      data: {          
          str_order : strOrder,
          table : table
      },
      success: function(data){
          var countRow = $('#table-list-data tbody tr span.order').length;
          for(var i = 0 ; i < countRow ; i ++ ){
              $('span.order').eq(i).html(i+1);
          }                        
      }
  });
}
</script>
@stop