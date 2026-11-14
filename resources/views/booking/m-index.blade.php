@extends('layout')
@section('content')
<div class="content-wrapper">
  
<!-- Content Header (Page header) -->
<section class="content-header" style="padding-top: 10px;">
  <h1 style="text-transform: uppercase;">    
    {{ __('QUẢN LÝ BILL') }} {{$arrSearch['use_date_from']}}
  </h1>
  
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      <div id="content_alert"></div>
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
      @if($notNH)
      @if($time_type == 3)
      <a href="{{ route('booking.create') }}?use_date={{ $arrSearch['use_date_from'] }}" class="btn btn-info btn-sm" style="margin-bottom:5px">{{ __('TẠO BILL') }}</a>
      @if(\App\Http\Controllers\BookingController::canUseKitchen())
       <a href="{{ route('booking.orders') }}" class="btn btn-warning btn-sm" style="margin-bottom:5px">{{ __('BẾP') }}</a>
       <a href="{{ route('booking.curry') }}" class="btn btn-success btn-sm" style="margin-bottom:5px">{{ __('CURRY') }}</a>
      <a href="{{ route('booking.tandoor') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">{{ __('BÁNH') }}</a>
      @endif
      @else
      <a href="{{ route('booking.create') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">{{ __('TẠO BILL') }}</a>
      @endif
      @endif
      
      <div class="panel panel-default">        
        <div class="panel-body">
          
          <form class="form-inline" role="form" method="GET" action="{{ route('booking.index') }}" id="searchForm">
                        <div class="row">
              
              
              <div class="row">
                @foreach($branchList as $beach)
                  <div class="form-group col-xs-4" style="padding:0px; padding-left: 10px;">
                    &nbsp;&nbsp;&nbsp;<input type="checkbox" name="branch_ids[]" id="branch_ids" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }} value="{{$beach->id}}">
                    <label for="branch_ids">{{$beach->name}}</label>
                  </div>
                @endforeach
              </div>
              <div class="form-group @if($time_type == 3) col-xs-6 @else col-xs-4 @endif">              
                <select class="form-control select2" name="time_type" id="time_type">                  
                  <option value="">{{ __('--THỜI GIAN--') }}</option>
                  <option value="1" {{ $time_type == 1 ? "selected" : "" }}>{{ __('Theo tháng') }}</option>
                  <option value="2" {{ $time_type == 2 ? "selected" : "" }}>{{ __('Khoảng ngày') }}</option>
                  <option value="3" {{ $time_type == 3 ? "selected" : "" }}>{{ __('Theo ngày') }} </option>
                </select>
              </div>
              @if($time_type == 1)
            <div class="form-group col-xs-4 chon-thang">                
                <select class="form-control select2 " id="month_change" name="month">
                  <option value="">{{ __('--THÁNG--') }}</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group col-xs-4 chon-thang">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">{{ __('--NĂM--') }}</option>                  
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                </select>
              </div>
            @endif

            @if($time_type == 2 || $time_type == 3)            
              <div class="form-group @if($time_type == 3) col-xs-6 @else col-xs-4 @endif">
                <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="{{ $time_type == 2 ? __('Từ ngày') : __('Ngày') }}" value="{{ $arrSearch['use_date_from'] }}">
              </div>
              
              @if($time_type == 2)
              <div class="form-group col-xs-4">
                <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="{{ __('Đến ngày') }}" value="{{ $arrSearch['use_date_to'] }}" >
              </div>
              @endif
            @endif
            </div>  
           
         
            <div class="row">  
              <div class="form-group col-xs-6">
                <input type="text" class="form-control" autocomplete="off" name="table_no" placeholder="{{ __('BÀN') }}" value="{{ $arrSearch['table_no'] }}">
              </div>       
              <div class="form-group col-xs-6">
                <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien">
                  <option value="">{{ __('-Người thu-') }}</option>
                  @foreach($collecterList as $payer)
                  <option value="{{ $payer->id }}" {{ $arrSearch['nguoi_thu_tien'] == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                  @endforeach
                </select>
              </div>
              
            </div>            
            <button type="submit" class="btn btn-info btn-sm">{{ __('Tìm') }}</button>
            <button type="button" id="btnReset" class="btn btn-danger btn-sm">{{ __('Đặt lại') }}</button>
          </form>         
        </div>
      </div>
 
        <div class="box-header with-border">
              <h3 class="box-title">{{ __('Danh sách') }} ( <span class="value">{{ $items->total() }} {{ __('bill') }} )
                @if($totalRupees > 0)
                <span style="color: blue">({{ number_format($totalRupees) }} rupees)</span>
                @endif
               
                @if($totalDrinks > 0)
              - {{ __('Tổng tiền đồ uống') }}: <span style="color: red">{{ number_format($totalDrinks) }}</span>
              @endif
              
              </h3>
          </div>
      
        <div style="text-align:center; margin-top: 10px;">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  
          <div class="table-responsive" style="font-size: 12px;border: none;">            
            <ul style="padding: 0px; ">
             @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
                <li id="row-{{ $item->booking_id }}" class="booking" style="padding: 10px;background-color: #fff; font-size:15px;margin-bottom: 10px; border-radius: 5px; color: #2c323f;  @if($i%2 == 0)  background-color:#77777721 @endif"  data-id="{{ $item->booking_id }}" data-date="{{ $item->use_date }}"> 
                <span class="label label-sm label-danger" id="error_unc_{{ $item->booking_id }}"></span>       
                 
                    @php $arrEdit = array_merge(['id' => $item->booking_id], $arrSearch) @endphp
                    
                   
                    @if($item->table_no)
                    <p style="font-size:20px !important; font-weight: bold">{{ __('BÀN :') }} <span style="color: blue; font-weight: bold">{{ $item->table_no }}</span> - <strong>{{ date('H:i', strtotime($item->created_at)) }}</strong></p>
                    @endif
                   @if($item->take_away == 1)
                    <span style="color: blue">{{ __('MANG ĐI') }}</span>
                    @endif
                    @if($item->indian == 2)
                    <span style="color: red">{{ __('KHÔNG PHẢI KHÁCH ẤN') }}</span>
                    @endif                    
                    @if($item->branch_id)
                    <br><p style="color: blue">{{ optional($item->branch)->name ?? ('#' . $item->branch_id) }}</p>
                    @endif
                 <table class="table" style="margin-top:5px;margin-bottom: 10px;">
                   @foreach($item->details as $service)
                   <tr>
                     <td width="50%">
                      @if($service->dish)
                      @if($service->is_drink == 1)
                      <i class="fa fa-coffee" style="color: blue" aria-hidden="true"></i>
                      @endif
                       @if($service->done_qty == $service->amount)
                      <label class="label label-sm label-info">{{ __('Xong') }}</label>
                      @endif
                      {{ $service->dish->name }}
                
                      <span style="color: red; font-weight: bold; padding-left: 10px; text-transform: uppercase;">{{ $service->notes }}</span>
                      @else
                      -error-
                      @endif
                    </td>
                     <td width="20%">{{ $service->done_qty }}/{{ $service->amount }} 

                     </td>
                     <td width="30%" class="text-right">{{ number_format($service->total_price) }}</td>
                   </tr>
                   @endforeach
                 </table>

                    - Total: {{ number_format($item->total_price) }} 
                    @if($item->tien_coc > 0)
                    <br>- Cọc: {{ number_format($item->tien_coc) }} 
                    @endif 
                    @if($item->discount > 0) 
                    <br>{{ __('- Giảm giá:') }} <span style="color: red;font-weight: bold;">{{ number_format($item->discount) }}</span>                    
                    @endif
                    @if($item->commision > 0) 
                    <br>{{ __('- Tỷ lệ giảm giá (%):') }} <span style="color: red;font-weight: bold;">{{ number_format($item->commision) }} 
                    @if($item->per_com <= 100 && $item->branch_id != 4 && $item->commision < 100)
                    ({{ $item->per_com }}%)
                    @endif
                    </span>
                    @endif
                    <br>- Revenue: {{ number_format($item->con_lai) }}
                    @if($item->rupees)
                  <span style="color: red;">({{ number_format($item->rupees) }} rupees) </span
                  @endif
                    @if($item->nguoi_thu_tien)
                  <br>- Receiver: {{ $collecterNameArr[$item->nguoi_thu_tien] }}
                  @endif
                    @if($item->notes)                    
                    <br><span style="color:red; font-style: italic;">{!! nl2br($item->notes) !!}</span>
                    @endif    
                    
                    <div class="clearfix" style="margin-bottom: 10px"></div>                   
                    
                   <a style="float:right; margin-left: 2px" href="{{ route( 'booking.edit', $arrEdit ) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>     <a class="btn btn-success btn-sm" href="{{ route( 'booking.detail', $item->id ) }}" target="_blank" >{{ __('Hóa đơn') }}</a>           
                  
                      <div style="clear: both;"></div>
                      <br><input id="hoan_tat_{{ $item->id }}"  data-table="booking" type="checkbox" data-table="booking" data-column="checked" class="change-column-value-booking" {{ $item->checked == 1 ? "checked" : "" }} value="1" data-id="{{ $item->id }}">
                    <label for="hoan_tat_{{ $item->id }}">{{ __('ĐÃ KIỂM') }}</label>
                </li>              
              @endforeach
            @else
            <li>
              <p>{{ __('Không có dữ liệu.') }}</p>
            </li>
            @endif
            </ul>
            <strong style="color: blue; font-weight: bold;">{{ number_format($totalRevenue) }} </strong>
          </div>
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>       
      </div>
     
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>
<div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="text-align: center;">
                <div class="modal-header bg-green">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4>{{ __('MÃ QR') }}</h4>
                </div>
                <div class="modal-body">
                    <img src="" style="width: 100% !important;" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('ĐÓNG') }}</button>
                </div>
            </div>
        </div>
    </div>
<input type="hidden" id="table_name" value="articles">
<!-- Modal -->
<div id="capnhatModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content" id="modal_content">
      
    </div>

  </div>
</div>
<style type="text/css">
  .form-group{
    margin-bottom: 10px !important;
  }
</style>

<input type="hidden" id="route_upload_tmp_image" value="{{ route('image.tmp-upload') }}"><div id="uncModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <form role="form" method="GET" action="{{ route('booking-payment.store') }}" id="dataFormPayment">
    <div class="row">
       <div class="col-md-12">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">{{ __('UPLOAD UNC PTT') }}<span id="id_load_unc"></span></h3>
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
                 
                  <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                  <label class="col-md-3 row">{{ __('Hình ảnh') }} </label>    
                  <div class="col-md-9">
                    <img id="thumbnail_image" src="{{ old('image_url') ? Helper::showImage(old('image_url')) : URL::asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="145" height="85">
                    
                    <input type="file" id="file-image" style="display:none" />
                 
                    <button class="btn btn-default" id="btnUploadImage" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> {{ __('Tải lên') }}</button>
                  </div>
                  <div style="clear:both"></div>
                  <input type="hidden" name="image_url" id="image_url" value="{{ old('image_url') }}"/>          
                  <input type="hidden" name="image_name" id="image_name" value="{{ old('image_name') }}"/>
                </div>                               
                  
                  <div style="clear:both"></div>              
            
                  <div class="form-group">
                    <label>{{ __('Ghi chú') }}</label>
                    <textarea class="form-control" rows="6" name="notes" id="notes">{{ old('notes') }}</textarea>
                  </div>            
                  
                 
              </div>          
                                
              <div class="box-footer">
                <button type="button" id="btnSavePayment" class="btn btn-primary btn-sm">{{ __('Lưu') }}</button>   
                <button type="button" class="btn btn-default btn-sm" id="btnLoading" style="display:none"><i class="fa fa-spin fa-spinner"></i> {{ __('Đang xử lý...') }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Đóng') }}</button>                
              </div>
              
          </div>
          <!-- /.box -->     

        </div>
    </div>
  </form>
    </div>

  </div>
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
              <h3 class="box-title">{{ __('UPLOAD UNC PTT') }}<span id="id_load_unc"></span></h3>
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
                    <label>{{ __('Ngày chuyển') }} <span class="red-star">*</span></label>
                    <input type="text" class="form-control datepicker" name="pay_date" id="pay_date" value="{{ old('pay_date') }}"  autocomplete="off">
                  </div>                
                  <div class="form-group" >                  
                    <label>{{ __('Số tiền') }} <span class="red-star">*</span></label>
                    <input type="text" class="form-control number" name="amount" id="amount" value="{{ old('amount') }}" autocomplete="off">
                  </div> 
                  <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                  <label class="col-md-3 row">{{ __('Hình ảnh') }} </label>    
                  <div class="col-md-9">
                    <img id="thumbnail_image" src="{{ old('image_url') ? Helper::showImage(old('image_url')) : URL::asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="145" height="85">
                    
                    <input type="file" id="file-image" style="display:none" />
                 
                    <button class="btn btn-default" id="btnUploadImage" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> {{ __('Tải lên') }}</button>
                  </div>
                  <div style="clear:both"></div>
                  <input type="hidden" name="image_url" id="image_url" value="{{ old('image_url') }}"/>          
                  <input type="hidden" name="image_name" id="image_name" value="{{ old('image_name') }}"/>
                </div>                               
                  
                  <div style="clear:both"></div>              
            
                  <div class="form-group">
                    <label>{{ __('Ghi chú') }}</label>
                    <textarea class="form-control" rows="6" name="notes" id="notes">{{ old('notes') }}</textarea>
                  </div>            
                  
                 
              </div>          
                                
              <div class="box-footer">
                <button type="button" id="btnSavePayment" class="btn btn-primary btn-sm">{{ __('Lưu') }}</button>   
                <button type="button" class="btn btn-default btn-sm" id="btnLoading" style="display:none"><i class="fa fa-spin fa-spinner"></i> {{ __('Đang xử lý...') }}</button>                
              </div>
              
          </div>
          <!-- /.box -->     

        </div>
    </div>
  </form>
    </div>

  </div>
</div>
<div class="modal fade" id="uncModalImg" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Đóng') }}</button>
      </div>
    </div>
  </div>
</div>

@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    
    $('.btn-qrcode').click(function (e) {
                e.preventDefault();
                $('#qrCodeModal').find('img').attr('src', $(this).attr('href'));
                $('#qrCodeModal').modal('show');
            })
    $('.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).data('src'));
      $('#uncModalImg').modal('show');
    }); 
    $('#btnSavePayment').click(function(){
          $.ajax({
            url: "{{ route('booking-payment.store') }}",
            type: "GET",          
            data: {
              image_url : $('#image_url').val(),
              image_name : $('#image_name').val(),
              notes : $('#notes_payment').val(),
              booking_id : $('#booking_id_unc').val()
            },            
            beforeSend : function(){
              //$('#btnSavePayment').hide();
             // $('#btnLoading').show();
            },
            success: function (response) {
              
              window.location.reload();
            },
            error: function(response){                             
                alert({!! json_encode(__('Có lỗi xảy ra')) !!});
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
<script type="text/javascript">
  $(document).ready(function(){
    $(document).on('click', '.btnUnc',function(){     
         $('#uncModal').modal('show');
        $('#booking_id_unc').val($(this).data('id'));
        $('#id_load_unc').html($(this).data('id'));
       
      });
      $('#btnReset').click(function(){
        $('#searchForm select').val('');
        $('#searchForm').submit();
      });
    $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
    $('.change-column-value-booking').change(function(){
        var obj = $(this);
        if(confirm({!! json_encode(__('Chắc chắn đã thu tiền?')) !!})){
            
            ajaxChange(obj.data('id'), obj);
        }else {
          obj.removeAttr('checked');
        }  
       });
        
    
  });
  function ajaxChange(id, obj){
        $.ajax({
            url : "{{ route('booking.change-value-by-column') }}",
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