@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    KHÁCH GROUP TOUR PHÚ QUỐC
  </h1>
 <!--  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'food.index' ) }}">Món ăn</a></li>
    <li class="active">List</li>
  </ol> -->
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
          <form class="form-inline" role="form" method="GET" action="{{ route('report.ben') }}" id="searchForm">
            <div class="form-group">
                <input type="text" class="form-control" autocomplete="off" id="id_search" name="id_search" value="{{ $arrSearch['id_search'] }}" placeholder="CODE">
              </div>
              <div class="form-group">
                <select class="form-control select2" name="tour_id" id="tour_id">
                <option value="">--Tour--</option>
                <option value="1" {{ $arrSearch['tour_id'] == 1 ? "selected" : "" }}>Tour đảo</option>                
                <option value="3" {{ $arrSearch['tour_id'] == 3 ? "selected" : "" }}>Rạch Vẹm</option>
                <option value="5" {{ $arrSearch['tour_id'] == 5 ? "selected" : "" }}>Grand World</option>
                <option value="4" {{ $arrSearch['tour_id'] == 4 ? "selected" : "" }}>Câu Mực</option>
                <option value="6" {{ $arrSearch['tour_id'] == 6 ? "selected" : "" }}>Bãi Sao-2 Đảo</option>
                <option value="7" {{ $arrSearch['tour_id'] == 7 ? "selected" : "" }}>Bãi Sao-Địa Trung Hải</option>
                <option value="8" {{ $arrSearch['tour_id'] == 8 ? "selected" : "" }}>Bãi Sao-Hòn Thơm</option>
              </select>
              </div>

              <div class="form-group">
              <select class="form-control select2" name="user_id" id="user_id">
                <option value="">--Sales--</option>
                @foreach($listUser as $user)
                <option value="{{ $user->id }}" {{ $arrSearch['user_id'] == $user->id ? "selected" : "" }}>{{ $user->name }}</option>
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
              <div class="form-group  chon-thang">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--Năm--</option>
                  <option value="2020" {{ $year == 2020 ? "selected" : "" }}>2020</option>
                  <option value="2021" {{ $year == 2021 ? "selected" : "" }}>2021</option>
                  <option value="2022" {{ $year == 2022 ? "selected" : "" }}>2022</option>
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)            
            <div class="form-group chon-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) Từ ngày @else Ngày @endif " value="{{ $arrSearch['use_date_from'] }}" style="width: 120px">
            </div>
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" style="width: 120px">
            </div>
             @endif
            @endif   
            <div class="form-group">
              <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien">
                <option value="">--Receiver--</option>
                <option value="1" {{ $arrSearch['nguoi_thu_tien'] == 1 ? "selected" : "" }}>Sales</option>
                <option value="2" {{ $arrSearch['nguoi_thu_tien'] == 2 ? "selected" : "" }}>CTY</option>
                <option value="3" {{ $arrSearch['nguoi_thu_tien'] == 3 ? "selected" : "" }}>HDV</option>
                <option value="4" {{ $arrSearch['nguoi_thu_tien'] == 4 ? "selected" : "" }}>Công nợ</option>
                <option value="5" {{ $arrSearch['nguoi_thu_tien'] == 5 ? "selected" : "" }}>Thao thu</option>
              </select>
            </div>                           
            <button type="submit" class="btn btn-info btn-sm">Search</button>

          </form>         
        </div>
      </div>
      <div class="box" id="load_data">
        <!-- /.box-header -->
         <div class="box-body">
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  <!--phan trang-->
          <div class="table-responsive">
            <div style="font-size: 18px;padding: 10px; border-bottom: 1px solid #ddd">
              Tổng <span style="color: red">{{ $items->total() }}</span> booking -
            Số NL: <span style="color: red">{{ number_format($tong_so_nguoi )}} </span>- Phần ăn: <span style="color: red">{{ $tong_phan_an }}</span></span> 
            </div>
            <ul style="padding: 10px">
             @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
                <li style="border-bottom: 1px solid #ddd; padding-bottom: 10px; padding-top: 10px; clear: both;font-size: 15px">                  
                    @php $arrEdit = array_merge(['id' => $item->id], $arrSearch) @endphp
                    @if($item->status == 1)
                    <span class="label label-info">MỚI</span>
                    @elseif($item->status == 2)
                    <span class="label label-default">HOÀN TẤT</span>
                    @elseif($item->status == 3)
                    <span class="label label-danger">HỦY</span>
                    @endif 
                    @if($item->nguoi_thu_tien == 4)
                    <label class="label label-danger label-sm">CÔNG NỢ</label>
                    @endif
                    <br>
                    <span style="color:#06b7a4; text-transform: uppercase;"><span style="color: #ff9d00;font-weight: bold">PTT{{ $item->id }}</span> - {{ $item->name }} </span> 
                     @if($item->tour_id == 3)
                  <br><label class="label label-warning">Rạch Vẹm</label>
                  @elseif($item->tour_id == 4)
                  <br><label class="label label-warning">Câu Mực</label>
                  @elseif($item->tour_id == 5)
                  <br><label class="label label-warning">Grand World</label>
                  @elseif($item->tour_id == 6)
                  <br><label class="label label-warning">Bãi Sao-2 Đảo</label>
                  @elseif($item->tour_id == 7)
                  <br><label class="label label-warning">Bãi Sao-Địa Trung Hải</label>                        
                  @elseif($item->tour_id == 8)
                  <br><label class="label label-warning">Bãi Sao-Hòn Thơm</label> 
                  @endif
                    @if($item->tour_type == 3)
                  <label class="label label-warning">Thuê cano</label>
                  @elseif($item->tour_type == 2)
                  <label class="label label-danger">Tour riêng</label>                  
                  @endif
                           
                  @php $arrEdit = array_merge(['id' => $item->id], $arrSearch) @endphp
                
                   
                    <i class="fa fa-phone"></i> <a href="tel:{{ $item->phone }}" target="_blank">{{ $item->phone }}</a>

                    
                    <br><i class="glyphicon glyphicon-user"></i> 
                    
               
                  @if($item->user)
                    {{ $item->user->name }}
                  @else
                    {{ $item->user_id }}
                  @endif
                  @if($item->ctv)
                    - {{ $item->ctv->name }}                  
                  @endif
                    <br>
                    <i class="fa fa-calendar"></i> {{ date('d/m/Y', strtotime($item->use_date)) }}
                    <br>
                    <i class="fa fa-map-marker"></i>
                    @if($item->location)
                    {{ $item->location->name }}
                    @else
                    {{ $item->address }}
                    @endif
                    
                    <br>
                    <i class="fa fa-user-circle"></i> NL: <b>{{ $item->adults }}</b> / TE: {{ $item->childs }} / EB: {{ $item->infants }} - 
                    
                    <i class="fa fa-briefcase"></i> {{ $item->meals }}
                    
                    <br>
                    <i class="fa fa-usd"></i> Tiền thu khách: <span style="color:red">{{ number_format($item->con_lai) }}</span>
                    @if($item->ko_cap_treo)
                    <br>
                    <i style="color:red">KHÔNG CÁP</i>
                    @endif
                    @if($item->notes)
                    <br>                    
                    <span style="color:#ff9d00">{!! nl2br($item->notes) !!}</span>
                    @endif   
                    
                      @php
                      $countUNC = $item->payment->count();
                     // dd($countUNC);
                      $strpayment = "";
                      $tong_payment = 0;
                      foreach($item->payment as $p){                        
                        $strpayment .= "+". number_format($p->amount)." - ".date('d/m', strtotime($p->pay_date));                    
                        if($p->type == 1){
                          $strpayment .= " - UNC"."<br>";
                        }else{
                          $strpayment .= " - auto"."<br>";
                        }
                        $tong_payment += $p->amount;                    
                      }
                      if($countUNC > 0)
                      $strpayment .= "Tổng: ".number_format($tong_payment);
                    @endphp 
                    
                    <p style="clear: both; text-align: right;margin-bottom: 0px; padding-top:0px">                      
                        @if($item->nguoi_thu_tien == 4)
                        <button class="btn btn-sm btn-success btnThuTien" data-id="{{ $item->id }}" data-name="{{ $item->name }}" href="{{ route('report.thu-tien') }}?id={{ $item->id }}">Đã thu tiền</button>
                        @endif
                        <button class="btn btn-sm btn-warning btnUnc" title="{!! $strpayment !!}" data-toggle="tooltip" data-html="true" data-id="{{ $item->id }}">{{ $countUNC }} UNC</button>                       
                        
                        <a class="btn btn-sm btn-info" target="_blank" href="{{ route('history.booking', ['id' => $item->id]) }}">Lịch sử</a>    
                    </p>
                    
                   </li>              
              @endforeach
            @else
            <li>
              <p>Không có dữ liệu.</p>
            </li>
            @endif
            </ul>
          
          </div>
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div><!--phan trang-->
        </div><!--table-responsive--> 
      </div><!--body-->
    </div>
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>
<div id="uncModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <form role="form" method="POST" action="{{ route('booking-payment.store') }}" id="dataFormPayment">
    <div class="row">
       <div class="col-md-12">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">UPLOAD UNC PTT<span id="id_load_unc"></span></h3>
            </div>
            <!-- /.box-header -->               
              {!! csrf_field() !!}

              <div class="box-body">
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif                
                <input type="hidden" name="booking_id" value="" id="booking_id_unc">
                  
                  <div class="form-group" >                  
                    <label>Ngày chuyển <span class="red-star">*</span></label>
                    <input type="text" class="form-control datepicker" name="pay_date" id="pay_date" value="{{ old('pay_date') }}"  autocomplete="off">
                  </div>                
                  <div class="form-group" >                  
                    <label>Số tiền <span class="red-star">*</span></label>
                    <input type="text" class="form-control number" name="amount" id="amount" value="{{ old('amount') }}" autocomplete="off">
                  </div> 
                  <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                  <label class="col-md-3 row">Hình ảnh </label>    
                  <div class="col-md-9">
                    <img id="thumbnail_image" src="{{ old('image_url') ? Helper::showImage(old('image_url')) : URL::asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="145" height="85">
                    
                    <input type="file" id="file-image" style="display:none" />
                 
                    <button class="btn btn-default" id="btnUploadImage" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</button>
                  </div>
                  <div style="clear:both"></div>
                  <input type="hidden" name="image_url" id="image_url" value="{{ old('image_url') }}"/>          
                  <input type="hidden" name="image_name" id="image_name" value="{{ old('image_name') }}"/>
                </div>                               
                  
                  <div style="clear:both"></div>              
            
                  <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" rows="6" name="notes" id="notes">{{ old('notes') }}</textarea>
                  </div>            
                  
                 
              </div>          
                                
              <div class="box-footer">
                <button type="button" id="btnSavePayment" class="btn btn-primary btn-sm">Save</button>   
                <button type="button" class="btn btn-default btn-sm" id="btnLoading" style="display:none"><i class="fa fa-spin fa-spinner"></i> Đang xử lý...</button>                
              </div>
              
          </div>
          <!-- /.box -->     

        </div>
    </div>
  </form>
    </div>

  </div>
</div>
<input type="hidden" id="route_upload_tmp_image" value="{{ route('image.tmp-upload') }}">
<style type="text/css">
	.table-list-data td,.table-list-data th{
    border: 1px solid #000 !important;
    font-weight: bold;
    color: #000
  }
  tr.vip{
    background-color: #02fa7a
  }
  tr.thue-cano{
    background-color: #ebd405
  }
</style>
<input type="hidden" id="table_name" value="articles">
@stop
@php
$totalLevel = count($arrLevel);
@endphp
@section('js')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $(document).on('click', '.btnThuTien', function(){      
      var id = $(this).data('id');
      var name = $(this).data('name');
      if(confirm('Chắc chắn đã thu tiền tour PTT'+ id + '-' + name)){
        $.ajax({
            url: "{{ route('report.thu-tien') }}",
            type: "GET",            
            data: {
              id : id
            },
            beforeSend : function(){
              
            },
            success: function (response) {
              
              window.location.reload();
            },
            error: function(response){                             
                alert('Có lỗi xảy ra');
            }
          });
      }
    });
    $('#btnSavePayment').click(function(){
          $.ajax({
            url: "{{ route('booking-payment.store') }}",
            type: "POST",
            async: false,      
            data: $('#dataFormPayment').serialize(),
            processData: false,
            contentType: false,
            beforeSend : function(){
              $('#btnSavePayment').hide();
            $('#btnLoading').show();
            },
            success: function (response) {
              
              window.location.reload();
            },
            error: function(response){                             
                alert('Có lỗi xảy ra');
            }
          });
    });
    $('#btnUploadImage').click(function(){        
        $('#file-image').click();
      });      
      var files = "";
      $('#file-image').change(function(e){
        $('#thumbnail_image').attr('src', "{{ URL::asset('admin/dist/img/loading.gif') }}");
         files = e.target.files;
         
         if(files != ''){
           var dataForm = new FormData();        
          $.each(files, function(key, value) {
             dataForm.append('file', value);
          });   
          
          dataForm.append('date_dir', 1);
          dataForm.append('folder', 'tmp');

          $.ajax({
            url: $('#route_upload_tmp_image').val(),
            type: "POST",
            async: false,      
            data: dataForm,
            processData: false,
            contentType: false,
            beforeSend : function(){
              $('#thumbnail_image').attr('src', "{{ URL::asset('admin/dist/img/loading.gif') }}");
            },
            success: function (response) {
              if(response.image_path){
                $('#thumbnail_image').attr('src',$('#upload_url').val() + response.image_path);
                $( '#image_url' ).val( response.image_path );
                $( '#image_name' ).val( response.image_name );
              }
              console.log(response.image_path);
                //window.location.reload();
            },
            error: function(response){                             
                var errors = response.responseJSON;
                for (var key in errors) {
                  
                }
                //$('#btnLoading').hide();
                //$('#btnSave').show();
            }
          });
        }
      });
  });
</script>
<script>
  $(document).ready(function(){
   $(document).on('click', '.btnUnc',function(){     
       $('#uncModal').modal('show');
      $('#booking_id_unc').val($(this).data('id'));
      $('#id_load_unc').html($(this).data('id'));
     
    });
    $('#id_search').keyup(function(){
      var obj = $(this);
      if(obj.val().length == 5){
        $.ajax({
            url : "{{ route('report.ajax-search-ben') }}",
            type : 'GET',
            data : {
              id_search : $('#id_search').val()
            },
            success: function(data){
              $('#load_data').html(data);
            }
          });
      }
      
    });
  });
const ctx = document.getElementById('myChart');
const myChart = new Chart(ctx, {
    type: 'bar',
    data:  {
      labels: [
     
      @foreach($arrLevel as $level => $count)
      
      '{{ Helper::getLevel($level) }}',     
      @endforeach
      ],      
      datasets: [
        {
          label: 'Thống kê khách theo level',
          data: [          
          @foreach($arrLevel as $level => $count)      
            {{ $count }},     
          @endforeach
          ] ,
         backgroundColor: ['Red', 'Orange', 'Yellow', 'blue', 'green'],       
        }
      ],
      options:{
          tooltips: {
              enabled: false
          },
          plugins: {
              datalabels: {
                  formatter: (value, ctx) => {
                      let sum = 0;
                      let dataArr = ctx.chart.data.datasets[0].data;
                      dataArr.map(data => {
                          sum += data;
                      });
                      let percentage = (value*100 / sum).toFixed(2)+"%";
                      return percentage;
                  },
                  color: '#fff',
              }
          }
      }
    },
      
});
</script>
@stop