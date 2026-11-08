@extends('layout')
@section('content')
<div class="content-wrapper">


<!-- Content Header (Page header) -->
<section class="content-header">
  <h1 style="text-transform: uppercase;">    
    Quản lý công nợ  
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'debt.report') }}">
      Công nợ</a></li>
    <li class="active">List</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">

    <div class="col-md-12">
      <div id="content_alert"></div>
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
      
      <div class="panel panel-default">
        
        <div class="panel-body" style="padding: 5px !important;">
          <form class="form-inline" role="form" method="GET" action="{{ route('debt.report') }}" id="searchForm" style="margin-bottom: 0px;">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="form-group">
              <input type="text" class="form-control" autocomplete="off" name="id_search" placeholder="PTT ID" value="{{ $arrSearch['id_search'] }}" style="width: 70px">
            </div>
            <div class="form-group">
              <select class="form-control select2" name="tour_id" id="tour_id">
                <option value="">--Tour--</option>
                <option value="1" {{ $arrSearch['tour_id'] == 1 ? "selected" : "" }}>Tour đảo</option>                
                <option value="3" {{ $arrSearch['tour_id'] == 3 ? "selected" : "" }}>Rạch Vẹm</option>
                <option value="4" {{ $arrSearch['tour_id'] == 4 ? "selected" : "" }}>Câu Mực</option>
                <option value="5" {{ $arrSearch['tour_id'] == 5 ? "selected" : "" }}>Grand World</option>
                <option value="6" {{ $arrSearch['tour_id'] == 6 ? "selected" : "" }}>Bãi Sao-2 Đảo</option>
                <option value="7" {{ $arrSearch['tour_id'] == 7 ? "selected" : "" }}>Bãi Sao-ĐTH</option>
                <option value="8" {{ $arrSearch['tour_id'] == 8 ? "selected" : "" }}>Bãi Sao-Hòn Thơm</option>
              </select>
            </div>          
            <div class="form-group">              
              <select class="form-control select2" name="time_type" id="time_type">                  
                <option value="">--Thời gian--</option>
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>Tháng</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Khoảng</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>Ngày</option>
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
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) Từ ngày @else Ngày @endif " value="{{ $arrSearch['use_date_from'] }}" style="width: 110px">
            </div>
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" style="width: 110px">
            </div>
             @endif
            @endif
            @if(Auth::user()->role == 1)
             <div class="form-group">
            <select class="form-control select2" name="level" id="level">      
              <option value="" >--Phân loại sales--</option>
             
              <option value="2" {{ $level == 2 ? "selected" : "" }}>Level 2 - TX</option>  
              
              <option value="7" {{ $level == 7 ? "selected" : "" }}>Level 7 - Bến</option>
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
            <select class="form-control select2" name="debt_type" id="debt_type">      
              <option value="" >--Loại công nợ--</option>
             
              <option value="1" {{ $debt_type == 1 ? "selected" : "" }}>Ngày</option>  
              
              <option value="2" {{ $debt_type == 2 ? "selected" : "" }}>Tuần</option>
              <option value="3" {{ $debt_type == 3 ? "selected" : "" }}>Tháng</option>
            </select>
          </div>
            <div class="form-group">
              <select class="form-control select2" name="user_id_manage" id="user_id_manage">
                <option value="">--Phụ trách--</option>
                <option value="84" {{ $arrSearch['user_id_manage'] == 84 ? "selected" : "" }}>Lâm Như</option>
                <option value="219" {{ $arrSearch['user_id_manage'] == 219 ? "selected" : "" }}>Trang Tạ</option>
                <option value="333" {{ $arrSearch['user_id_manage'] == 333 ? "selected" : "" }}>Group Tour</option>
              </select>
            </div>
            @endif 
           
            <div class="form-group">
              <select class="form-control select2" name="nguoi_thu_coc" id="nguoi_thu_coc">
                <option value="">--Thu cọc--</option>
                <option value="1" {{ $arrSearch['nguoi_thu_coc'] == 1 ? "selected" : "" }}>Sales</option>
                <option value="2" {{ $arrSearch['nguoi_thu_coc'] == 2 ? "selected" : "" }}>CTY</option>
                <option value="3" {{ $arrSearch['nguoi_thu_coc'] == 3 ? "selected" : "" }}>HDV</option>
              </select>
            </div>             
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button>
            <div class="form-group">
              <button type="button" id="btnReset" class="btn btn-default btn-sm">Reset</button>
            </div>
            <div>
              @if($arrSearch['tour_id'] != 4)  
              <div class="form-group">
              <input type="checkbox" name="tour_type[]" id="tour_type_1" {{ in_array(1, $arrSearch['tour_type']) ? "checked" : "" }} value="1">
              <label for="tour_type_1">GHÉP({{ $ghep }})</label>
            </div>   
            <div class="form-group">
              &nbsp;&nbsp;&nbsp;<input type="checkbox" name="tour_type[]" id="tour_type_2" {{ in_array(2, $arrSearch['tour_type']) ? "checked" : "" }} value="2">
              <label for="tour_type_2">VIP({{ $vip }}-{{ $tong_vip }}NL)&nbsp;&nbsp;&nbsp;&nbsp;</label>
            </div>   
            <div class="form-group" style="border-right: 1px solid #9ba39d">
              &nbsp;&nbsp;&nbsp;<input type="checkbox" name="tour_type[]" id="tour_type_3" {{ in_array(3, $arrSearch['tour_type']) ? "checked" : "" }} value="3">
              <label for="tour_type_3">THUÊ CANO({{ $thue }})&nbsp;&nbsp;&nbsp;&nbsp;</label>
            </div>  
            @endif
              <div class="form-group">
              &nbsp;&nbsp;&nbsp;<input type="checkbox" name="status[]" id="status_1" {{ in_array(1, $arrSearch['status']) ? "checked" : "" }} value="1">
              <label for="status_1">Mới</label>
            </div>   
            <div class="form-group">
              &nbsp;&nbsp;&nbsp;<input type="checkbox" name="status[]" id="status_2" {{ in_array(2, $arrSearch['status']) ? "checked" : "" }} value="2">
              <label for="status_2">Hoàn Tất</label>
            </div> 
           
                                
            </div>
          </form>         
        </div>
      </div>
      
      <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="padding: 5px;">
          <div class="table-responsive">
          <table class="table table-bordered" id="table_report" style="margin-bottom:0px;font-size: 14px;">
              <tr style="background-color: #f4f4f4">
                <th class="text-center">Tổng BK</th>
                <th class="text-center">NL/TE</th>
                <th class="text-center">Ăn NL/TE</th>
                <th class="text-center">Cáp NL/TE</th>
                <th class="text-right">Tổng công nợ</th>
                <th class="text-right">Thực thu</th>                                
                <th class="text-right">Tổng cọc</th>                
              </tr>
              <tr>
                <td class="text-center">{{ number_format($items->total()) }}</td> 
                <td class="text-center">{{ number_format($tong_so_nguoi ) }} / {{ number_format($tong_te ) }}</td> 
                <td class="text-center">{{ number_format($tong_phan_an ) }} / {{ number_format($tong_phan_an_te ) }}</td> 
                <td class="text-center">{{ number_format($cap_nl ) }} / {{ number_format($cap_te ) }}</td>
                <td class="text-right"><span id="tong_cong_no" style="color:red; font-weight: bold;"></span></td>
                <td class="text-right">{{ number_format($tong_thuc_thu ) }}</td>                
                <td class="text-right">{{ number_format($tong_coc ) }}</td> 
              </tr>
          </table>
        
        </div>
        </div>
      </div>     
      <p style="text-align: right;"><a href="javascript:;" class="btn btn-primary btn-sm" id="btnExport">Export Excel</a>      
      <div class="box">
        
        
        @if(Auth::user()->role == 1)
        <div class="form-inline" style="padding: 5px">
          <div class="form-group">
            <select class="form-control select2 multi-change-column-value" data-column="status">
                <option value="">--SET TRẠNG THÁI--</option>
                <option value="1">Mới</option>
                <option value="2">Hoàn tất</option>              
              </select>
             <select class="form-control select2 multi-change-column-value" data-column="nguoi_thu_tien">
                <option value="">--SET THU TIỀN--</option>
                <option value="1">Sales</option>
                <option value="2">CTY</option>
                <option value="3">HDV</option>
                <option value="4">Công nợ</option>                
              </select>
          </div>
        </div>
        @endif
        <!-- /.box-header -->
        <div class="box-body">
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  
          <div class="table-responsive">
          <table class="table table-bordered table-hover" id="table-list-data">
            <tr style="background-color: #f4f4f4">              
              <th style="width: 1%" class="text-center" ><input type="checkbox" id="check_all" value="1"></th>
              <th style="width: 1%"></th>
              <th width="200">Customer name</th>
              <th  width="140">Sales</th>                    
              <th class="text-center" width="80">NL/TE</th>
              <th class="text-center" width="80">ĂN NL/TE</th>
              <th class="text-center" width="80">CÁP NL/TE</th>
              <th class="text-right" width="100">Cọc</th>
              <th class="text-right" width="150">Tổng tiền</th>
              <th class="text-right" width="100">Phụ thu<br> Discount</th>
              <th class="text-right" width="100">Công nợ tạm</th>
              <th class="text-right" width="140" >Thực thu</th>
              <th class="text-center" width="60">Ngày đi</th>              
              <th width="1%;white-space:nowrap">Thao tác</th>
            </tr>
            <tbody>
              <?php 
              $tong_cong_no = 0;
              ?>
            @if( $items->count() > 0 )
              <?php $l = 0; ?>
              @foreach( $items as $item )
                <?php $l ++; ?>

                @php 
                  $tong_tien = $cong_no = 0;
                  $rsPrice = App\Helpers\Helper::calTourPrice($item->tour_id, $item->tour_type, $item->level, $item->adults, $item->childs, $item->use_date);
                 
                  if(!empty($rsPrice)){
                    if($item->tour_type == 3){
                        $tong_tien = $rsPrice['price'] + $item->meals*$rsPrice['meals'] + $item->meals_te*$rsPrice['meals_te'] 
                        + $item->cap_nl*$rsPrice['cap_nl'] + $item->cap_te*$rsPrice['cap_te'] + $rsPrice['extra_fee'];
                        $cong_no = $tong_tien + $item->extra_fee - $item->discount;
                    }else{
                        $tong_tien =  $item->adults*$rsPrice['price'] + $item->childs*$rsPrice['price_child'] + $item->meals*$rsPrice['meals'] + $item->meals_te*$rsPrice['meals_te'] 
                        + $item->cap_nl*$rsPrice['cap_nl'] + $item->cap_te*$rsPrice['cap_te'] ;
                        $cong_no = $tong_tien + $item->extra_fee - $item->discount;
                    }
                  }  
                  $tong_cong_no += $cong_no;                  
                  @endphp
              <tr class="booking" id="row-{{ $item->booking_id }}" data-id="{{ $item->id }}" data-date="{{ $item->use_date }}" style="border-bottom: 1px solid #000 !important;@if($item->status == 3) background-color: #f77e7e; @endif">
                <td class="text-center" style="line-height: 30px">
                  <input type="checkbox" id="checked{{ $item->booking_id }}" class="check_one" value="{{ $item->booking_id }}">
                  
                </td>
                <td style="text-align: center;white-space: nowrap; line-height: 30px;">
                  <strong style="color: red;">PTT{{ $item->booking_id }}</strong>
                </td>                 
                <td style="position: relative; line-height: 30px;">       
                  
                  
                  
                  @php $arrEdit = array_merge(['id' => $item->booking_id], $arrSearch) @endphp
                  <a style="font-weight: bold" href="{{ route( 'booking.edit', $arrEdit) }}">{{ $item->name }}</a> 
                  @if($item->notes)
                  <i style="cursor: pointer;" class="fa fa-sticky-note-o" data-toggle="tooltip" data-html="true" title="{!! $item->notes !!}"></i>
                  @endif
                  @if($item->tour_id == 3)
                  <br><label class="label label-warning">Rạch Vẹm</label>
                  @elseif($item->tour_id == 4)
                  <br><label class="label label-warning">Câu Mực</label>
                  @elseif($item->tour_id == 5)
                  <br><label class="label label-warning">Grand World</label> 
                  @elseif($item->tour_id == 6)
                  <br><label class="label label-warning">Bãi Sao-2 Đảo</label>
                  @elseif($item->tour_id == 7)
                  <br><label class="label label-warning">Bãi Sao-ĐTH</label>
                  @elseif($item->tour_id == 8)
                  <br><label class="label label-warning">Bãi Sao-Hòn Thơm</label>                   
                  @endif
                  @if($item->tour_type == 3)
                  <br><label class="label label-warning">Thuê cano</label>
                  @elseif($item->tour_type == 2)
                  <br><label class="label label-danger">VIP</label>                
                  @endif
                  
                </td>
                <td>
                    @if($item->user)
                    {{ $item->user->name }}
                    @endif
                    @if($item->ctv)
                      <br> {{ $item->ctv->name }}                  
                    @endif
                </td>
               
                 <td class="text-center">
                    {{ $item->adults }} / {{ $item->childs > 0 ? $item->childs : '-'}}
                </td>
                <td class="text-center">
                     {{ $item->meals }} / {{ $item->meals_te  > 0 ? $item->meals_te :  '-' }}
                </td>
                <td class="text-center">
                     {{ $item->cap_nl }} / {{ $item->cap_te  > 0 ? $item->cap_te :  '-' }}
                </td>
              
                <td class="text-right">
                  {{ $item->tien_coc ? number_format($item->tien_coc) : "-" }}
                              
                </td>
                <td class="text-right">
                  
                  {{ $tong_tien ? number_format($tong_tien) : "-" }}
                  @if(isset($rsPrice['extra_fee']) && $rsPrice['extra_fee'] > 0)
                    <br> <i class="fa fa-camera"></i> <span style="color: blue">+{{ number_format($rsPrice['extra_fee']) }}</span>
                    @endif
                              
                </td>
                <td class="text-right">
                  @if($item->extra_fee)
                    {{ $item->extra_fee ? number_format($item->extra_fee) : "-" }}
                     <br>
                    @endif
                    
                    @if($item->discount > 0)
                   
                    <span style="color: red">-{{ $item->discount ? number_format($item->discount) : "-" }}</span>
                    @endif
                </td>
                <td class="text-right">
                  
                  {{ $cong_no ? number_format($cong_no) : '-' }}
                </td> 
                               
                <td class="text-right">
                    {{ $item->tien_thuc_thu ? number_format($item->tien_thuc_thu) : "-" }}
                  
                </td>
                <td class="text-center">
                  {{ date('d/m', strtotime($item->use_date)) }}
                </td>
                <td style="white-space:nowrap; text-align: right;position: relative;"> 
                  @if($item->status != 3)
                    @php
                    $countUNC = $item->payment->count();
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
                    
                  <a data-toggle="tooltip" data-html="true" title="{!! $strpayment !!}" href="{{ route( 'booking-payment.index', ['booking_id' => $item->booking_id] ) }}" class="btn btn-info btn-sm">{{ $countUNC > 0 ?? $countUNC }} <span class="glyphicon glyphicon-usd"></span></a>
                    @php $arrEdit = array_merge(['id' => $item->booking_id], $arrSearch) @endphp            
                    <a href="{{ route( 'booking.edit', $arrEdit ) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>                     
                    
                  @endif                
                  
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
            {{ $items->appends( $arrSearch )->links() }}
          </div> 
          @if(Auth::user()->role == 1)
          <div class="form-inline" style="padding: 5px">
            <div class="form-group">
              <select class="form-control select2 multi-change-column-value" name="status" id="status">
                  <option value="">--SET TRẠNG THÁI--</option>
                  <option value="1">Mới</option>
                  <option value="2">Hoàn tất</option>
                  <option value="3">Cancel</option>
                </select>
              <select class="form-control select2 multi-change-column-value" name="nguoi_thu_tien" id="nguoi_thu_tien">
                  <option value="">--SET THU TIỀN--</option>
                  <option value="1">Sales</option>
                  <option value="2">CTY</option>
                  <option value="3">HDV</option>
                  <option value="4">Công nợ</option>                  
              </select>               
            </div> 
          </div>
          @endif
           
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
  .hdv{
    cursor: pointer;
  }
  .hdv:hover, .hdv.selected{
    background-color: #06b7a4;
    color: #FFF
  }  
  label{
    cursor: pointer;
  }
  #table_report th td {padding: 2px !important;}
  #searchForm, #searchForm input{
    font-size: 13px;
  }
  .form-control{
    font-size: 13px !important; 
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered{
    
    font-size: 12px !important;
  } 
  tr.error{
    background-color:#ffe6e6
  }  
</style>
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
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
        $('#searchForm').attr('action', "{{ route('debt.export') }}").submit().attr('action', oldAction);
      });
      $('#tong_cong_no').html('{{ number_format($tong_cong_no) }}');
      @if(Auth::user()->role == 1)      
      $('tr.booking').each(function(){
        var tr = $(this);
        var id = tr.data('id');
        var use_date = tr.data('date');
        var today = new Date();        
        if(use_date < "{{ date('Y-m-d') }}"){
          $.ajax({
            url : '{{ route('booking.checkError') }}?id=' + id,
            type : 'GET',
            success : function(data){
              $('#error_' + id).text(data);
            }
          });
          $.ajax({
            url : '{{ route('booking.check-unc') }}?id=' + id,
            type : 'GET',
            success : function(data){
              $('#error_unc_' + id).text(data);
            }
          });
        }
      });
      @endif
      $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
      $('tr.booking').click(function(){
        $(this).find('.check_one').attr('checked', 'checked');
      });
      $("#check_all").click(function(){
          $('input.check_one').not(this).prop('checked', this.checked);
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
          if(obj.data('column') == 'cano_id'){
           // alert('Tất cả các booking cùng HDV sẽ được gán chung vào cano này');
          }
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
       $('.multi-change-column-value').change(function(){
          var obj = $(this);      
          $('.check_one:checked').each(function(){
              $.ajax({
                url : "{{ route('change-value-by-column') }}",
                type : 'GET',
                data : {
                  id : $(this).val(),
                  col : obj.data('column'),
                  value: obj.val()
                },
                success: function(data){

                }
              });
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
      $('.hdv').click(function(){
        var hdv_id = $(this).data('id');
        if(hdv_id != ""){
          $('#hdv_id').val($(this).data('id'));  
          $('#searchForm').submit();
        }               
       
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
       $('.change_tien_coc').blur(function(){
        var obj = $(this);
        $.ajax({
          url:'{{ route('change-value-by-column')}}',
          type:'GET',
          data: {
            id : obj.data('id'),
            value : obj.val(),
            col : 'tien_coc'
          },
          success : function(doc){
            console.log(data);
          }
        });
        });
      $('.change_total_price').blur(function(){
        var obj = $(this);
        $.ajax({
          url:'{{ route('change-value-by-column') }}',
          type:'GET',
          data: {
            id : obj.data('id'),
            value : obj.val(),
            col : 'total_price'
          },
          success : function(doc){
            console.log(data);
          }
        });
        });
      $('.change_price_net').click(function(){
        var obj = $(this);
        var price_net = 0;
        if(obj.prop('checked') == true){
          price_net = 1
        }
        $.ajax({
          url:'{{ route('change-value-by-column')}}',
          type:'GET',
          data: {
            id : obj.data('id'),
            value : price_net,
            col : 'price_net'
          },
          success : function(doc){
            console.log(data);
          }
        });
        });
      $('#btnReset').click(function(){
        $('#searchForm select').val('');
        $('#searchForm').submit();
      });
    });
  </script>
@stop