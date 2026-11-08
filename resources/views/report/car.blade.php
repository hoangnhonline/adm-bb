@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1 style="text-transform: uppercase;">    
    Thống kê đặt xe    
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'report.car') }}">
     Thống kê đặt xe</a></li>
    <li class="active">Thống kê</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">

    <div class="col-md-12">      
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif      
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Bộ lọc</h3>
        </div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('report.car') }}" id="searchForm">
            <input type="hidden" name="type" value="4">
           
            <div class="form-group">
              <select class="form-control select2" name="driver_id" id="driver_id">
                <option value="">--Tài xế--</option>
                @foreach($driverList as $driver)
                <option value="{{ $driver->id }}" {{ $arrSearch['driver_id'] == $driver->id  ? "selected" : "" }}>{{ $driver->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <select class="form-control select2" name="tour_id" id="tour_id">
                <option value="">--Loại xe--</option>
                @foreach($carCate as $cate)
                <option value="{{ $cate->id }}" {{ $arrSearch['tour_id'] == $cate->id  ? "selected" : "" }}>{{ $cate->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">              
              <select class="form-control select2" name="time_type" id="time_type">                  
                <option value="">--Thời gian--</option>
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>Theo tháng</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Khoảng ngày</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>Ngày cụ thể </option>
              </select>
            </div> 
            @if($time_type == 1)
            <div class="form-group  chon-thang">                
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">--THÁNG--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)            
            <div class="form-group chon-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) Từ ngày @else Ngày @endif " value="{{ $arrSearch['use_date_from'] }}" style="width: 90px">
            </div>
           
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" style="width: 90px">
            </div>
             @endif
            @endif           
           
            @if(Auth::user()->role == 1)
            <div class="form-group">
              <select class="form-control select2" name="user_id" id="user_id">
                <option value="">--Sales--</option>
                @foreach($listUser as $user)
                <option value="{{ $user->id }}" {{ $arrSearch['user_id'] == $user->id ? "selected" : "" }}>{{ $user->name }}</option>
                @endforeach
              </select>
            </div>                     
            @endif           
           
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button>         
         
          </form>         
        </div>
      </div>      
       
      <div class="box">    
        <div style="background-color: #dbdbd5" class="table-responsive">
          <table class="table table-bordered" id="table_report">
              <tr>
                <th class="text-center">Số chuyến</th>
                <th class="text-right">Tổng tiền</th>
                <th class="text-right">CTY thu</th>
                <th class="text-right">TX thu</th>
                <th class="text-right">Sales thu</th>
                
              </tr>
              <tr>
                <td class="text-center" style="vertical-align: middle;">{{ number_format($t_chuyen) }}</td> 
                <td class="text-right" style="vertical-align: middle;">{{ number_format($t_tong ) }}</td>
                <td class="text-right" style="vertical-align: middle;">{{ number_format($t_cty ) }}</td>
                <td class="text-right" style="vertical-align: middle;">{{ number_format($t_tx ) }}</td>
                <td class="text-right" style="vertical-align: middle;">{{ number_format($t_sales ) }}</td>
              </tr>
          </table>
         
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  
          <div class="table-responsive">
          <table class="table table-bordered table-hover table-striped" id="table-list-data">
            <tr>
              <th style="width: 1%">#</th>
              <th>Tên TX</th>
              <th class="text-center" >Số chuyến</th>
              <th class="text-right">Tổng tiền</th>
              <th class="text-right">Tiền TX Thu</th>
              <th class="text-right" >Tiền CTY Thu</th>  
              <th class="text-right" >Tiền Sales Thu</th>
            </tr>
            <tbody>
            @if( count($driverArrName) > 0 )
              <?php $i = 0; ?>
              @foreach( $driverArrName as $driverId => $driverName )
                <?php $i ++; ?>
               @if(isset($arrDriver[$driverId]))
               <?php 
               $arrDetail = $arrDriver[$driverId];
               ?>
              <tr>
                <td style="text-align: center;"><span class="order">{{ $i }}</span></td>                 
                <td>      
                  
                   <a style="font-size:17px" href="">{{ $driverName }}</a>                  
                  
                  
                </td>
                <td class="text-center">
                  {{ number_format($arrDetail['so_lan_chay']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arrDetail['tong_tien']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arrDetail['so_tien_tx_thu']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arrDetail['so_tien_cty_thu']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arrDetail['so_tien_sales_thu']) }}
                </td>
              </tr> 
              @endif
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
            {{ $items->appends( $arrSearch )->links() }}
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
<input type="hidden" id="table_name" value="articles">
@stop
<style type="text/css">
  label{
    cursor: pointer;
  }
</style>
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
     $('#no_driver').change(function(){
      $('#searchForm').submit();
    });
    $('img.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).attr('src'));
      $('#uncModal').modal('show');
    }); 
  });
</script>
<script type="text/javascript">
    $(document).ready(function(){
      $('#btnExport').click(function(){
        var oldAction = $('#searchForm').attr('action');
        $('#searchForm').attr('action', "{{ route('export.cong-no-tour') }}").submit().attr('action', oldAction);
      });
      $('#temp').click(function(){
        $(this).parents('form').submit();
      });
    	$('.change_status').click(function(){
		      var obj = $(this);      
		      $.ajax({
		        url : "{{ route('change-export-status') }}",
		        type : 'GET',
		        data : {
		          id : obj.data('id')
		        },
		        success: function(){
		          window.location.reload();
		        }
		      });
		    });
       $('.change_status_bk').click(function(){
          var obj = $(this);      
          $.ajax({
            url : "{{ route('change-status') }}",
            type : 'GET',
            data : {
              id : obj.data('id')
            },
            success: function(){
              //window.location.reload();
            }
          });
        });
       $('.change-column-value').change(function(){
          var obj = $(this);      
          $.ajax({
            url : "{{ route('change-value-by-column') }}",
            type : 'GET',
            data : {
              id : obj.data('id'),
              col : obj.data('column'),
              value: obj.val()
            },
            success: function(data){
                console.log(data);
            }
          });
       });
      $('.hoa_hong_sales').blur(function(){
        var obj = $(this);
        $.ajax({
          url:'{{ route('save-hoa-hong')}}',
          type:'GET',
          data: {
            id : obj.data('id'),
            hoa_hong_sales : obj.val()
          },
          success : function(doc){
            
          }
        });
        
      });
      $('.change_tien_thuc_thu').blur(function(){
        var obj = $(this);
        $.ajax({
          url:'{{ route('change-value-by-column')}}',
          type:'GET',
          data: {
            id : obj.data('id'),
            value : obj.val(),
            col : 'tien_thuc_thu'
          },
          success : function(doc){
            console.log(data);
          }
        });
        });
    });
  </script>
@stop