@extends('layout')
@section('content')
<div class="content-wrapper">
<section class="content-header">
  <h1 style="text-transform: uppercase;">{{ __('Quản lý bill') }}</h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div id="content_alert"></div>
      @if(Session::has('message'))
      <p class="alert alert-info">{{ Session::get('message') }}</p>
      @endif

      @include('booking.partials.list-toolbar', ['showExcel' => true])

      <div class="panel panel-default" style="margin-bottom:10px">
        <div class="panel-body" style="padding:8px !important;">
          <form class="form-inline" role="form" method="GET" action="{{ route('booking.index') }}" id="searchForm" style="margin-bottom:0">
            <div class="form-group">
              <select class="form-control select2" name="time_type" id="time_type" style="width:130px;">
                <option value="">{{ __('--Thời gian--') }}</option>
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>{{ __('Theo tháng') }}</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>{{ __('Khoảng ngày') }}</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>{{ __('Theo ngày') }}</option>
              </select>
            </div>
            @if($time_type == 1)
            <div class="form-group chon-thang">
              <select class="form-control select2" id="month_change" name="month" style="width:90px">
                <option value="">{{ __('--THÁNG--') }}</option>
                @for($i = 1; $i <= 12; $i++)
                <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                @endfor
              </select>
            </div>
            <div class="form-group chon-thang">
              <select class="form-control select2" id="year_change" name="year" style="width:90px">
                <option value="">{{ __('--NĂM--') }}</option>
                @for($y = (int) date('Y') + 1; $y >= 2024; $y--)
                <option value="{{ $y }}" {{ $year == $y ? "selected" : "" }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
            @endif
            @if($time_type == 2 || $time_type == 3)
            <div class="form-group chon-ngay">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="{{ $time_type == 2 ? __('Từ ngày') : __('Ngày') }}" value="{{ $arrSearch['use_date_from'] }}" style="width:105px">
            </div>
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="{{ __('Đến ngày') }}" value="{{ $arrSearch['use_date_to'] }}" style="width:105px">
            </div>
            @endif
            @endif
            <div class="form-group">
              <input type="text" class="form-control" autocomplete="off" name="table_no" placeholder="{{ __('BÀN') }}" value="{{ $arrSearch['table_no'] }}" style="width:80px">
            </div>
            <div class="form-group">
              <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien" style="width:130px;">
                <option value="">{{ __('--Người thu--') }}</option>
                @foreach($collecterList as $payer)
                <option value="{{ $payer->id }}" {{ $arrSearch['nguoi_thu_tien'] == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <select class="form-control select2" name="doi_tac" style="width:170px;">
                <option value="">{{ __('--Đối tác / Khách lẻ--') }}</option>
                <option value="le" {{ ($arrSearch['doi_tac'] ?? '') === 'le' ? "selected" : "" }}>{{ __('Khách lẻ') }}</option>
                <option value="all" {{ ($arrSearch['doi_tac'] ?? '') === 'all' ? "selected" : "" }}>{{ __('Tất cả đối tác') }}</option>
                @foreach(($doiTacList ?? []) as $dt)
                <option value="{{ $dt->id }}" {{ ($arrSearch['doi_tac'] ?? '') == $dt->id ? "selected" : "" }}>{{ $dt->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-search"></i> {{ __('Tìm') }}</button>
              <button type="button" id="btnReset" class="btn btn-default btn-sm">{{ __('Đặt lại') }}</button>
            </div>
            @if(!$isView && count($branchList) > 1)
            <div style="margin-top:6px">
              @foreach($branchList as $beach)
              <label class="bk-branch"><input type="checkbox" name="branch_ids[]" value="{{ $beach->id }}" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }}><span>{{ $beach->name }}</span></label>
              @endforeach
            </div>
            @endif
          </form>
        </div>
      </div>

      @include('booking.partials.list-summary')

      <div class="box">
        <div class="box-body" style="padding:0">
          <div style="text-align:center">{{ $items->appends($arrSearch)->links() }}</div>
          <div class="table-responsive">
          <table class="table table-hover bk-table" id="table-list-data">
            <thead><tr>
              @if($notNH)<th style="width:1%" class="text-center"><input type="checkbox" id="check_all" value="1"></th>@endif
              <th style="width:1%">#</th>
              <th style="width:170px">{{ __('Bàn') }}</th>
              <th>{{ __('Món') }}</th>
              <th class="text-right" style="width:190px">{{ __('Tiền') }}</th>
              <th style="width:150px">{{ __('Người thu') }}</th>
              <th class="text-right" style="width:1%;white-space:nowrap">{{ __('Thao tác') }}</th>
            </tr></thead>
            <tbody>
            @php $fx = app(\App\Services\CurrencyService::class); $l = ($items->currentPage() - 1) * $items->perPage(); @endphp
            @forelse($items as $item)
              @php
                $l++;
                $noVat = \App\Services\AppSetting::isNoVat($item->no_vat, $item->branch_id);
                $vatItem = $noVat ? 0 : \App\Services\AppSetting::vatOf($item->total_price, $item->discount, $item->no_vat, $item->branch_id);
              @endphp
              <tr class="booking" id="row-{{ $item->booking_id }}" data-id="{{ $item->booking_id }}" data-date="{{ $item->use_date }}">
                @if($notNH)<td><input type="checkbox" id="checked{{ $item->id }}" class="check_one" value="{{ $item->id }}"></td>@endif
                <td class="text-muted">{{ $l }}</td>
                <td>
                  <div class="bk-table-no">
                    @if($item->table_no){{ __('BÀN') }} <b>{{ $item->table_no }}</b>@else<span class="text-muted">—</span>@endif
                    <small>{{ date('H:i', strtotime($item->created_at)) }}</small>
                  </div>
                  <div class="text-muted" style="font-size:12px">#{{ $item->id }} · {{ date('d/m/Y', strtotime($item->use_date)) }}</div>
                  @if($item->branch_id)<span class="bk-badge blue">{{ optional($item->branch)->name ?? ('#' . $item->branch_id) }}</span>@endif
                  @if($item->take_away == 1)<span class="bk-badge blue">{{ __('MANG ĐI') }}</span>@endif
                  @if($item->indian == 2)<span class="bk-badge red">{{ __('KHÔNG PHẢI KHÁCH ẤN') }}</span>@endif
                  @if($item->doi_tac_id && $item->doiTac)
                    <span class="bk-badge orange">{{ $item->doiTac->name }}</span>
                    @if($item->ghi_no)<span class="bk-badge red">{{ __('Ghi nợ') }}</span>@endif
                  @endif
                </td>
                <td>
                  <ul class="bk-dishes">
                    @foreach($item->details as $dishs)
                    <li class="{{ $dishs->done_qty >= $dishs->amount ? 'done' : '' }}">
                      <span class="q">{{ $dishs->done_qty }}/{{ $dishs->amount }}</span>
                      <span class="n">@if($dishs->is_drink == 1)<i class="fa fa-coffee text-muted"></i> @endif{{ $dishs->dish ? $dishs->dish->name : "" }}
                        @if($dishs->notes)<span class="bk-note">{{ $dishs->notes }}</span>@endif</span>
                      <span class="p">{{ number_format($dishs->total_price) }}</span>
                    </li>
                    @endforeach
                  </ul>
                  @if($item->notes)<div class="bk-note" style="font-style:italic">{!! nl2br(e($item->notes)) !!}</div>@endif
                  @if($item->sms_thu)<div class="text-success" style="font-size:12px">SMS: {{ $item->sms_thu }}</div>@endif
                </td>
                <td class="text-right bk-money">
                  <div><span class="k">{{ __('Tổng') }}</span> {{ number_format($item->total_price) }}</div>
                  @if($item->discount)<div><span class="k">{{ __('Giảm giá') }}</span> −{{ number_format($item->discount) }}</div>@endif
                  <div class="rev"><span class="k">{{ __('Doanh thu') }}</span> {{ number_format($item->con_lai) }}</div>
                  @if(!$noVat)
                  <div><span class="k">VAT</span> {{ number_format($vatItem) }}</div>
                  <div><span class="k">{{ __('Thanh toán') }}</span> <b>{{ number_format($item->total_price - $item->discount + $vatItem) }}</b></div>
                  @else
                  <div><span class="bk-badge">{{ __('KHÔNG VAT') }}</span></div>
                  @endif
                  @if($item->com_amount > 0)<div><span class="k">{{ __('HH') }}</span> {{ number_format($item->com_amount) }}</div>@endif
                  @if($item->con_lai)<div class="text-muted" style="font-size:11px">≈ {{ $fx->vndToInr($item->con_lai, 0) }} INR @if($item->rupees)· {{ number_format($item->rupees) }} rupees @endif</div>@endif
                </td>
                <td>
                  <select class="form-control input-sm change-column-value-booking" data-table="booking" data-column="nguoi_thu_tien" data-id="{{ $item->id }}" style="width:100%">
                    <option value="" disabled {{ $item->nguoi_thu_tien ? "" : "selected" }}>--</option>
                    @foreach($collecterList as $payer)
                    <option value="{{ $payer->id }}" {{ $item->nguoi_thu_tien == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                    @endforeach
                  </select>
                </td>
                <td class="text-right" style="white-space:nowrap">
                  @if($notNH)
                  @php $arrEdit = array_merge(['id' => $item->booking_id], $arrSearch) @endphp
                  <a href="{{ route('booking.edit', $arrEdit) }}" class="btn btn-warning btn-sm" title="{{ __('Sửa') }}"><span class="glyphicon glyphicon-pencil"></span></a>
                  @endif
                  <a class="btn btn-success btn-sm" href="{{ route('booking.detail', $item->id) }}">{{ __('HÓA ĐƠN') }}</a>
                  <div class="bk-flags">
                    <label><input type="checkbox" data-table="booking" data-column="checked" class="change-column-value-booking" {{ $item->checked == 1 ? "checked" : "" }} value="1" data-id="{{ $item->id }}"> {{ __('ĐÃ KIỂM') }}</label>
                    @if(in_array((int) $item->branch_id, (array) config('bombay.kiot_branches', []), true))
                    <label><input type="checkbox" data-table="booking" data-column="is_kiot" data-revenue="{{ (int) $item->con_lai }}" class="change-column-value-booking" {{ $item->is_kiot == 1 ? "checked" : "" }} value="1" data-id="{{ $item->id }}"> {{ __('KIOT') }}</label>
                    @endif
                    @if(!in_array((int) $item->branch_id, \App\Services\AppSetting::noVatBranches(), true))
                    <label><input type="checkbox" data-table="booking" data-column="no_vat" class="change-column-value-booking" {{ $item->no_vat == 1 ? "checked" : "" }} value="1" data-id="{{ $item->id }}"> {{ __('KHÔNG VAT') }}</label>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted" style="padding:20px">{{ __('Không có dữ liệu!') }}</td></tr>
            @endforelse
            </tbody>
          </table>
          </div>
          <div style="text-align:center">{{ $items->appends($arrSearch)->links() }}</div>
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
  .bk-table > thead > tr > th { background:#f7f8fa; border-bottom:2px solid #e3e6ea; font-size:13px; }
  .bk-table > tbody > tr > td { border-top:1px solid #eceef1; vertical-align:top; }
  .bk-table > tbody > tr:nth-child(even) { background:#fafbfc; }
  .bk-table-no { font-size:16px; } .bk-table-no b { color:#1d6fa5; } .bk-table-no small { color:#888; margin-left:4px; }
  .bk-dishes { list-style:none; padding:0; margin:0; }
  .bk-dishes li { display:flex; gap:8px; padding:2px 0; border-bottom:1px dashed #eee; font-size:13px; }
  .bk-dishes li:last-child { border-bottom:0; }
  .bk-dishes .q { width:36px; color:#888; flex:none; } .bk-dishes .n { flex:1; } .bk-dishes .p { color:#555; white-space:nowrap; }
  .bk-dishes li.done .q { color:#00a65a; font-weight:bold; }
  .bk-note { color:#c0392b; font-weight:bold; text-transform:uppercase; font-size:12px; margin-left:4px; }
  .bk-money { font-size:13px; line-height:1.55; } .bk-money .k { color:#999; font-size:12px; margin-right:4px; }
  .bk-money .rev { font-size:15px; font-weight:bold; color:#1d6fa5; }
  .bk-flags { margin-top:6px; text-align:left; font-size:12px; } .bk-flags label { display:block; font-weight:normal; margin:2px 0; }
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
              value: obj.is(':checkbox') ? (obj.is(':checked') ? 1 : 0) : obj.val()   // ô tick: bỏ tick thì gửi 0
            },
            success: function(data){
                console.log(data);
            }
          });
      }
  </script>
@stop
