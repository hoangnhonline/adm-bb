@extends('layout')
@section('content')
<div class="content-wrapper">
<section class="content-header" style="padding-top:10px;">
  <h1 style="text-transform:uppercase;font-size:20px">{{ __('QUẢN LÝ BILL') }} <small>{{ $arrSearch['use_date_from'] }}</small></h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div id="content_alert"></div>
      @if(Session::has('message'))
      <p class="alert alert-info">{{ Session::get('message') }}</p>
      @endif

      @include('booking.partials.list-toolbar', ['showExcel' => false])

      <div class="panel panel-default" style="margin-bottom:10px">
        <div class="panel-body" style="padding:8px">
          <form role="form" method="GET" action="{{ route('booking.index') }}" id="searchForm">
            @if(count($branchList) > 1)
            <div style="margin-bottom:8px">
              @foreach($branchList as $beach)
              <label class="bk-branch"><input type="checkbox" name="branch_ids[]" value="{{ $beach->id }}" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }}><span>{{ $beach->name }}</span></label>
              @endforeach
            </div>
            @endif
            <div class="row" style="margin:0 -4px">
              <div class="form-group col-xs-6" style="padding:0 4px">
                <select class="form-control select2" name="time_type" id="time_type" style="width:100%">
                  <option value="">{{ __('--THỜI GIAN--') }}</option>
                  <option value="1" {{ $time_type == 1 ? "selected" : "" }}>{{ __('Theo tháng') }}</option>
                  <option value="2" {{ $time_type == 2 ? "selected" : "" }}>{{ __('Khoảng ngày') }}</option>
                  <option value="3" {{ $time_type == 3 ? "selected" : "" }}>{{ __('Theo ngày') }}</option>
                </select>
              </div>
              @if($time_type == 1)
              <div class="form-group col-xs-3 chon-thang" style="padding:0 4px">
                <select class="form-control" id="month_change" name="month">
                  @for($i = 1; $i <= 12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group col-xs-3 chon-thang" style="padding:0 4px">
                <select class="form-control" id="year_change" name="year">
                  @for($y = (int) date('Y') + 1; $y >= 2024; $y--)
                  <option value="{{ $y }}" {{ $year == $y ? "selected" : "" }}>{{ $y }}</option>
                  @endfor
                </select>
              </div>
              @endif
              @if($time_type == 2 || $time_type == 3)
              <div class="form-group {{ $time_type == 3 ? 'col-xs-6' : 'col-xs-3' }}" style="padding:0 4px">
                <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="{{ $time_type == 2 ? __('Từ ngày') : __('Ngày') }}" value="{{ $arrSearch['use_date_from'] }}">
              </div>
              @if($time_type == 2)
              <div class="form-group col-xs-3" style="padding:0 4px">
                <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="{{ __('Đến ngày') }}" value="{{ $arrSearch['use_date_to'] }}">
              </div>
              @endif
              @endif
              <div class="form-group col-xs-4" style="padding:0 4px">
                <input type="text" class="form-control" autocomplete="off" name="table_no" placeholder="{{ __('BÀN') }}" value="{{ $arrSearch['table_no'] }}">
              </div>
              <div class="form-group col-xs-8" style="padding:0 4px">
                <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien" style="width:100%">
                  <option value="">{{ __('-Người thu-') }}</option>
                  @foreach($collecterList as $payer)
                  <option value="{{ $payer->id }}" {{ $arrSearch['nguoi_thu_tien'] == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-search"></i> {{ __('Tìm') }}</button>
            <button type="button" id="btnReset" class="btn btn-default btn-sm">{{ __('Đặt lại') }}</button>
          </form>
        </div>
      </div>

      @include('booking.partials.list-summary')

      <div style="text-align:center">{{ $items->appends($arrSearch)->links() }}</div>
      <ul class="bk-cards">
      @forelse($items as $item)
        @php
          $arrEdit = array_merge(['id' => $item->booking_id], $arrSearch);
          $noVat = \App\Services\AppSetting::isNoVat($item->no_vat, $item->branch_id);
          $vatItem = $noVat ? 0 : \App\Services\AppSetting::vatOf($item->total_price, $item->discount, $item->no_vat, $item->branch_id);
        @endphp
        <li id="row-{{ $item->booking_id }}" class="booking bk-card" data-id="{{ $item->booking_id }}" data-date="{{ $item->use_date }}">
          <span class="label label-sm label-danger" id="error_unc_{{ $item->booking_id }}"></span>
          <div class="bk-card-head">
            <div class="t">@if($item->table_no){{ __('BÀN') }} <b>{{ $item->table_no }}</b>@else — @endif <small>{{ date('H:i', strtotime($item->created_at)) }}</small></div>
            <div class="r">#{{ $item->id }}</div>
          </div>
          <div>
            @if($item->branch_id)<span class="bk-badge blue">{{ optional($item->branch)->name ?? ('#' . $item->branch_id) }}</span>@endif
            @if($item->take_away == 1)<span class="bk-badge blue">{{ __('MANG ĐI') }}</span>@endif
            @if($item->indian == 2)<span class="bk-badge red">{{ __('KHÔNG PHẢI KHÁCH ẤN') }}</span>@endif
            @if($item->doi_tac_id && $item->doiTac)<span class="bk-badge orange">{{ $item->doiTac->name }}</span>@if($item->ghi_no)<span class="bk-badge red">{{ __('Ghi nợ') }}</span>@endif @endif
          </div>
          <ul class="bk-dishes">
            @foreach($item->details as $service)
            <li class="{{ $service->done_qty >= $service->amount ? 'done' : '' }}">
              <span class="q">{{ $service->done_qty }}/{{ $service->amount }}</span>
              <span class="n">@if($service->is_drink == 1)<i class="fa fa-coffee text-muted"></i> @endif{{ $service->dish ? $service->dish->name : '-error-' }}
                @if($service->notes)<span class="bk-note">{{ $service->notes }}</span>@endif</span>
              <span class="p">{{ number_format($service->total_price) }}</span>
            </li>
            @endforeach
          </ul>
          @if($item->notes)<div class="bk-note" style="font-style:italic;margin:4px 0">{!! nl2br(e($item->notes)) !!}</div>@endif
          <table class="bk-m-money">
            <tr><td>{{ __('Tổng') }}</td><td>{{ number_format($item->total_price) }}</td></tr>
            @if($item->tien_coc > 0)<tr><td>{{ __('Cọc') }}</td><td>{{ number_format($item->tien_coc) }}</td></tr>@endif
            @if($item->discount > 0)<tr><td>{{ __('Giảm giá') }}</td><td style="color:#c0392b">−{{ number_format($item->discount) }}</td></tr>@endif
            <tr class="rev"><td>{{ __('Doanh thu') }}</td><td>{{ number_format($item->con_lai) }} @if($item->rupees)<small>({{ number_format($item->rupees) }} rupees)</small>@endif</td></tr>
            @if(!$noVat)<tr><td>VAT · {{ __('Thanh toán') }}</td><td>{{ number_format($vatItem) }} · <b>{{ number_format($item->total_price - $item->discount + $vatItem) }}</b></td></tr>@endif
            @if($item->com_amount > 0)<tr><td>{{ __('HH') }}</td><td>{{ number_format($item->com_amount) }}</td></tr>@endif
            @if($item->nguoi_thu_tien)<tr><td>{{ __('Người thu') }}</td><td>{{ $collecterNameArr[$item->nguoi_thu_tien] ?? '' }}</td></tr>@endif
          </table>
          <div class="bk-card-foot">
            <label><input type="checkbox" data-table="booking" data-column="checked" class="change-column-value-booking" {{ $item->checked == 1 ? "checked" : "" }} value="1" data-id="{{ $item->id }}"> {{ __('ĐÃ KIỂM') }}</label>
            @if(in_array((int) $item->branch_id, (array) config('bombay.kiot_branches', []), true))
            <label><input type="checkbox" data-table="booking" data-column="is_kiot" class="change-column-value-booking" {{ $item->is_kiot == 1 ? "checked" : "" }} value="1" data-id="{{ $item->id }}"> {{ __('KIOT') }}</label>
            @endif
            <span style="margin-left:auto">
              <a class="btn btn-success btn-sm" href="{{ route('booking.detail', $item->id) }}" target="_blank">{{ __('Hóa đơn') }}</a>
              <a href="{{ route('booking.edit', $arrEdit) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>
            </span>
          </div>
        </li>
      @empty
        <li class="bk-card text-muted">{{ __('Không có dữ liệu.') }}</li>
      @endforelse
      </ul>
      <div style="text-align:center">{{ $items->appends($arrSearch)->links() }}</div>
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
  .form-group{ margin-bottom: 8px !important; }
  .bk-cards { list-style:none; padding:0; margin:0; }
  .bk-card { background:#fff; border:1px solid #e3e6ea; border-radius:8px; padding:10px 12px; margin-bottom:10px; font-size:14px; color:#2c323f; }
  .bk-card-head { display:flex; align-items:baseline; } .bk-card-head .t { font-size:18px; } .bk-card-head .t b { color:#1d6fa5; } .bk-card-head small { color:#888; margin-left:4px; }
  .bk-card-head .r { margin-left:auto; color:#999; font-size:12px; }
  .bk-dishes { list-style:none; padding:0; margin:6px 0; }
  .bk-dishes li { display:flex; gap:8px; padding:3px 0; border-bottom:1px dashed #eee; font-size:13px; }
  .bk-dishes .q { width:34px; color:#888; flex:none; } .bk-dishes .n { flex:1; } .bk-dishes .p { white-space:nowrap; color:#555; }
  .bk-dishes li.done .q { color:#00a65a; font-weight:bold; }
  .bk-note { color:#c0392b; font-weight:bold; text-transform:uppercase; font-size:12px; margin-left:4px; }
  .bk-m-money { width:100%; font-size:13px; margin-top:4px; } .bk-m-money td { padding:1px 0; } .bk-m-money td:first-child { color:#888; } .bk-m-money td:last-child { text-align:right; }
  .bk-m-money tr.rev td { font-weight:bold; color:#1d6fa5; font-size:15px; }
  .bk-card-foot { display:flex; align-items:center; margin-top:8px; padding-top:8px; border-top:1px solid #f0f0f0; } .bk-card-foot label { font-weight:normal; margin:0; }
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
              value: obj.is(':checkbox') ? (obj.is(':checked') ? 1 : 0) : obj.val()   // ô tick: bỏ tick thì gửi 0
            },
            success: function(data){
                console.log(data);
            }
          });
      }
</script>
@stop