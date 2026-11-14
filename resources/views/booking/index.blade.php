@extends('layout')
@section('content')
<div class="content-wrapper">


<!-- Content Header (Page header) -->
<section class="content-header">
  <h1 style="text-transform: uppercase;">
    {{ __('Quản lý bill') }}
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> {{ __('Trang chủ') }}</a></li>
    <li><a href="{{ route( 'booking.index') }}">
    {{ __('Danh sách') }}
    </a></li>
    <li class="active">{{ __('Danh sách') }}</li>
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
  
      @if($time_type == 3)
      <a href="{{ route('booking.create') }}?use_date={{ $arrSearch['use_date_from'] }}" class="btn btn-info btn-sm" style="margin-bottom:5px">{{ __('TẠO BILL') }}</a>
      <a href="{{ route('booking.orders') }}" class="btn btn-warning btn-sm" style="margin-bottom:5px">{{ __('BẾP') }}</a>
      <a href="{{ route('booking.curry') }}" class="btn btn-success btn-sm" style="margin-bottom:5px">{{ __('CURRY') }}</a>
      <a href="{{ route('booking.tandoor') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">{{ __('BÁNH') }}</a>
     
      @else
      <a href="{{ route('booking.create') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">{{ __('Thêm mới') }}</a>
      @endif


      <div class="panel panel-default">

        <div class="panel-body" style="padding: 5px !important;">
          <form class="form-inline" role="form" method="GET" action="{{ route('booking.index') }}" id="searchForm" style="margin-bottom: 0px;">
         
            <div class="form-group">
              <input type="text" class="form-control" autocomplete="off" name="table_no" placeholder="{{ __('BÀN') }}" value="{{ $arrSearch['table_no'] }}" style="width: 100px">
            </div>
            
            
            <div class="form-group">
              <select class="form-control select2" name="time_type" id="time_type" style="width: 150px;">
                <option value="">{{ __('--Thời gian--') }}</option>
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>{{ __('Theo tháng') }}</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>{{ __('Khoảng ngày') }}</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>{{ __('Theo ngày') }} </option>
              </select>
            </div>
            @if($time_type == 1)
            <div class="form-group  chon-thang">
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">{{ __('--THÁNG--') }}</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group  chon-thang">
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">{{ __('--NĂM--') }}</option>                 
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                  <option value="2027" {{ $year == 2027 ? "selected" : "" }}>2027</option>
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)
            <div class="form-group chon-ngay">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="{{ $time_type == 2 ? __('Từ ngày') : __('Ngày') }}" value="{{ $arrSearch['use_date_from'] }}" style="width: 110px">
            </div>
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="{{ __('Đến ngày') }}" value="{{ $arrSearch['use_date_to'] }}" style="width: 110px">
            </div>
             @endif
            @endif       
                    
            
            <div class="form-group">
              <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien" style="width: 120px;">
                <option value="">{{ __('--Người thu--') }}</option>
                @foreach($collecterList as $payer)
                <option value="{{ $payer->id }}" {{ $arrSearch['nguoi_thu_tien'] == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                @endforeach
              </select>
            </div> 
            <div class="form-group">
              <select class="form-control select2" name="doi_tac" style="width: 170px;">
                <option value="">{{ __('--Đối tác / Khách lẻ--') }}</option>
                <option value="le" {{ ($arrSearch['doi_tac'] ?? '') === 'le' ? "selected" : "" }}>{{ __('Khách lẻ') }}</option>
                <option value="all" {{ ($arrSearch['doi_tac'] ?? '') === 'all' ? "selected" : "" }}>{{ __('Tất cả đối tác') }}</option>
                @foreach(($doiTacList ?? []) as $dt)
                <option value="{{ $dt->id }}" {{ ($arrSearch['doi_tac'] ?? '') == $dt->id ? "selected" : "" }}>{{ $dt->name }}</option>
                @endforeach
              </select>
            </div>
             
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">{{ __('Tìm') }}</button>
            <div class="form-group">
              <button type="button" id="btnReset" class="btn btn-default btn-sm">{{ __('Đặt lại') }}</button>
            </div>

            <div>
              @if(!$isView)
              @foreach($branchList as $beach)
                <div class="form-group">
                  &nbsp;&nbsp;&nbsp;<input type="checkbox" name="branch_ids[]" id="branch_ids" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }} value="{{$beach->id}}">
                  <label for="branch_ids">{{$beach->name}}</label>
                </div>
              @endforeach
              @endif
            </div>            
          </form>
        </div>
      </div>     
      <div class="box">
        @if($isView == false)
        <div class="form-group">          
          @if(\App\Services\Perm::can('bill.export'))<a href="javascript:;" class="btn btn-primary btn-sm" id="btnExcel">{{ __('Excel') }}</a>@endif
        </div>
        @endif
         <!-- /.box-header -->
          <div class="box-header with-border">
              <h3 class="box-title">{{ __('Danh sách') }} ( <span class="value">{{ $items->total() }} {{ __('bill') }} )</span>
                @if($isView)
                {{ __('Tổng doanh thu') }}: <span style="color: blue">{{ number_format($totalRevenue) }}</span>
                @endif

              
              @if($isView == false)
                @if($totalRupees > 0)
                  <span style="color: blue">({{ number_format($totalRupees) }} rupees)</span>
                @endif


                @if($totalDrinks > 0)
                - {{ __('Tổng tiền đồ uống') }}: <span style="color: red">{{ number_format($totalDrinks) }}</span>
                @endif
              @endif
              
              </h3>
          </div>
        <!-- /.box-header -->
        <div class="clearfix"></div>
        <div class="box-body">

          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>
          
          <div class="table-responsive">
          <table class="table table-bordered table-hover" id="table-list-data">
            <tr style="background-color: #f4f4f4">
              @if($notNH)
              <th style="width: 1%" class="text-center" ><input type="checkbox" id="check_all" value="1"></th>
              @endif
              <th width="1%">{{ __('STT') }}</th>

              <th style="width: 200px">{{ __('Khách') }}</th>
              <th width="30%">{{ __('Món') }}</th>
              <th class="text-center" width="120">{{ __('Ngày ăn') }}</th>
              <th class="text-right" width="100">{{ __('Tổng tiền') }}</th>
              <th class="text-center"  width="100">{{ __('Người thu') }}</th>
              <th width="1%" style="white-space:nowrap; text-align: right">{{ __('Thao tác') }}</th>
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $l = 0; 
              $total_revenue = 0;
              ?>
              @foreach( $items as $item )
                <?php $l ++; 
                $total_revenue+= $item->con_lai;
                ?>
              <tr class="booking" id="row-{{ $item->booking_id }}" data-id="{{ $item->booking_id }}" data-date="{{ $item->use_date }}" style="border-bottom: 1px solid #000 !important; @if($l%2 == 0)  background-color:#c9ced6 @endif">
                @if($notNH)
                <td>
                  <input type="checkbox" id="checked{{ $item->id }}" class="check_one" value="{{ $item->id }}">
                </td>
                @endif
                <td class="text-center"> {{ $l }}</td>
                <td>
                    @if($item->table_no)
                    {{ __('BÀN') }} <span style="color: blue; font-weight: bold">{{ $item->table_no }} - <strong>{{ date('H:i', strtotime($item->created_at)) }}</strong></span>
                    @endif           
                    @if($item->take_away == 1)
                    <br><span style="color: blue">{{ __('MANG ĐI') }}</span>
                    @endif
                    @if($item->indian == 2)
                    <br><span style="color: red">{{ __('KHÔNG PHẢI KHÁCH ẤN') }}</span>
                    @endif
                    @if($item->branch_id)
                    <br><p style="color: blue">{{ $item->branch->name }}</p>
                    @endif
                    @if($item->doi_tac_id && $item->doiTac)
                    <span class="label label-warning">{{ $item->doiTac->name }}</span>
                    @if($item->ghi_no)<span class="label label-danger">{{ __('Ghi nợ') }}</span>@endif
                    @if($item->com_amount > 0)<br><small>{{ __('HH') }}: {{ number_format($item->com_amount) }}</small>@endif
                    @endif
                </td>
                <td>
                  <div class="row">
                    <select class="form-control select2 change-column-value-booking" data-table="booking"  data-column="nguoi_thu_tien" style="width: 150px !important" data-id="{{ $item->id }}" >
                      @foreach($collecterList as $payer)
                      <option value="{{ $payer->id }}" {{ $item->nguoi_thu_tien == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <table class="table" style="margin-top:5px;margin-bottom: 10px;">

                   @foreach($item->details as $dishs)
                   <tr>
                     <td width="50%">
                       @if($dishs->done_qty == $dishs->amount)
                      <label class="label label-sm label-info">{{ __('Xong') }}</label>
                      @endif
                      @if($dishs->is_drink == 1)
                      <i class="fa fa-coffee" style="color: blue" aria-hidden="true"></i>
                      @endif
                      {{ $dishs->dish ? $dishs->dish->name  : "" }}
                      <br>
                      <span style="color: red; font-weight: bold; text-transform: uppercase;">{{ $dishs->notes }}</span>
                    </td>
                     <td width="20%">{{ $dishs->done_qty }}/{{ $dishs->amount }}</td>
                     <td width="30%" class="text-right">{{ number_format($dishs->total_price) }}</td>
                   </tr>
                   @endforeach
                   @if($item->sms_thu)
                      <p class="alert-success sms">
                          SMS : {{ $item->sms_thu }}
                      </p>
                    @endif
                    @if($item->notes)
                    <div class="clearfix"><span style="color:red; font-style: italic;">{!! nl2br($item->notes) !!}</span></div>
                    @endif
                 </table>
                </td>
                <td class="text-center">
                  {{ date('d/m/Y', strtotime($item->use_date)) }}                 
                </td>
                <td class="text-right">
                  {{ number_format($item->total_price) }}
                  @if($item->discount)
                  <br>
                  Discount: {{ $item->discount ?number_format($item->discount) : '-' }}
                  @endif
                  @if($item->con_lai)
                  <br> Revenue: {{ number_format($item->con_lai) }}<br>

                  @php
            $fx = app(\App\Services\CurrencyService::class);
            $inr = $fx->vndToInr($item->con_lai, 0);
            @endphp
            RUPEES: <strong style="color:red">{{ $inr }}</strong>

                    @if($item->rupees)
                    <br><span style="color: red;">{{ number_format($item->rupees) }} rupees</span>
                    @endif
                  @endif

                  <br>
                  {{ __('Order:') }} <span style="color:blue">{{ $item->id }}</span>
                  @if($item->branch_id == 12)
                  <br> + VAT: {{ number_format($item->con_lai*1.08) }}</td>
                  @endif
                </td>               
          
                <td class="text-center">                   
                 @if($item->nguoi_thu_tien)
                  {{ $collecterNameArr[$item->nguoi_thu_tien] }}
                  @endif
                     
                </td>

                <td class="text-right" style="white-space:nowrap">

                  @if($notNH)                
                  @php $arrEdit = array_merge(['id' => $item->booking_id], $arrSearch) @endphp
                  <a style="float:right; margin-left: 2px" href="{{ route( 'booking.edit', $arrEdit ) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>
                  <br>
                  @endif           
                  <p style="clear:both; margin-top: 5px;">      
                  <a class="btn btn-success btn-sm" href="{{ route( 'booking.detail', $item->id ) }}" >{{ __('HÓA ĐƠN') }}</a>

                  <br><input id="hoan_tat_{{ $item->id }}"  data-table="booking" type="checkbox" data-table="booking" data-column="checked" class="change-column-value-booking" {{ $item->checked == 1 ? "checked" : "" }} value="1" data-id="{{ $item->id }}">
                    <label for="hoan_tat_{{ $item->id }}">{{ __('ĐÃ KIỂM') }}</label>   
                    
                    <br><input id="no_vat_{{ $item->id }}"  data-table="booking" type="checkbox" data-table="booking" data-column="no_vat" class="change-column-value-booking" {{ $item->no_vat == 1  ? "checked" : "" }} value="1" data-id="{{ $item->id }}">
                    <label for="no_vat_{{ $item->id }}">{{ __('KHÔNG VAT') }}</label>                   

                </p>
                </td>
              </tr>
              @endforeach
            @else
            <tr>
              <td colspan="9">{{ __('Không có dữ liệu!') }}</td>
            </tr>
            @endif

          </tbody>
          </table>
          <strong style="color: blue; font-weight: bold;">{{ number_format($totalRevenue) }}</strong> 
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
<div id="uncModal" class="modal fade" role="dialog">
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
                    <textarea class="form-control" rows="6" name="notes" id="notes_payment">{{ old('notes') }}</textarea>
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
<input type="hidden" id="route_upload_tmp_image" value="{{ route('image.tmp-upload') }}">
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    $('.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).data('src'));
      $('#uncModalImg').modal('show');
    });
    $('.btn-qrcode').click(function (e) {
                e.preventDefault();
                $('#qrCodeModal').find('img').attr('src', $(this).attr('href'));
                $('#qrCodeModal').modal('show');
            })
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
      $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
      $('tr.booking').click(function(){
        $(this).find('.check_one').attr('checked', 'checked');
      });
      $("#check_all").click(function(){
          $('input.check_one').not(this).prop('checked', this.checked);
      });
      $('#btnExcel').click(function(){
        var oldAction = $('#searchForm').attr('action');
        $('#searchForm').attr('action', "{{ route('export.cong-no-tour') }}").submit().attr('action', oldAction);
      });      

       $('.change-column-value').change(function(){
          var obj = $(this);
          if(obj.data('column') == 'cano_id'){
           // alert('Tất cả các booking cùng HDV sẽ được gán chung vào cano này');
          }
          $.ajax({
            url : "{{ route('booking.change-value-by-column') }}",
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
                url : "{{ route('booking.change-value-by-column') }}",
                type : 'GET',
                data : {
                  id : $(this).val(),
                  col : obj.data('column'),
                  value: obj.val()
                },
                success: function(data){
                  window.location.reload();
                }
              });
          });

       });

      $('#btnReset').click(function(){
        $('#searchForm select').val('');
        $('#searchForm').submit();
      });
      $('.change-column-value-booking').change(function(){
          var obj = $(this);
          ajaxChange(obj.data('id'), obj);
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
