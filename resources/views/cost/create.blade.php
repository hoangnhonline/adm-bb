@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Cost</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('cost.index') }}">Cost</a></li>
      <li class="active">Add new</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('cost.index') }}" style="margin-bottom:5px">Back</a>

    <form role="form" method="POST" action="{{ route('cost.store') }}" id="dataForm">
      <div class="row">
        <div class="col-md-12">

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Add new</h3>
            </div>

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

              <input type="hidden" name="month" value="{{ $month }}">

              <div class="row">
                <div class="form-group col-md-4 col-xs-12">
                  <label for="status">Status</label>
                  <select class="form-control select2" name="status" id="status">
                    <option value="2" {{ old('status') == 2 ? "selected" : "" }}>Paid</option>
                    <option value="1" {{ old('status') == 1 ? "selected" : "" }}>Not yet paid</option>
                    <option value="3" {{ old('status') == 3 ? "selected" : "" }}>Pay later</option>
                  </select>
                </div>

                <div class="form-group col-xs-6 col-md-4">
                  <label>Branch</label>
                  <select name="branch_id" id="branch_id" class="form-control select2">
                    @foreach($branchList as $beach)
                      <option value="{{ $beach->id }}" {{ old('branch_id') == $beach->id ? "selected" : "" }}>
                        {{ $beach->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-4 col-xs-6">
                  <label for="date_use">Date</label>
                  <input type="text"
                         name="date_use"
                         class="form-control datepicker"
                         value="{{ old('date_use') }}"
                         id="date_use"
                         autocomplete="off">
                </div>
              </div>

              <div class="row">
                <input type="hidden" name="type" value="1">

                <div class="form-group col-md-12 col-xs-12">
                  <label>Cost category<span class="red-star">*</span></label>

                  {{-- QUICK LABELS --}}
                  <div id="cate_quick" style="margin:6px 0 10px;">
                    @foreach($cateListShow as $cate)
                      <button
                        type="button"
                        class="btn btn-info btn-sm btnCateQuick"
                        data-cate-id="{{ $cate->id }}"
                        style="margin:0 6px 6px 0;"
                        title="{{ $cate->name }}"
                      >
                        {{ $cate->name }}
                      </button>
                    @endforeach
                  </div>

                  <select class="form-control select2" id="cate_id" name="cate_id">
                    <option value="">--Chọn--</option>
                    @foreach($cateList as $cate)
                      <option value="{{ $cate->id }}" {{ old('cate_id', $cate_id) == $cate->id ? "selected" : "" }}>
                        {{ $cate->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                {{-- nếu route cost.ajax-doi-tac trả về partner select, nó sẽ render vào đây --}}
                <div class="col-md-12" id="load_doi_tac"></div>
              </div>

              <div class="row">
                <div class="form-group col-md-12">
                  <label for="notes">Content</label>
                  <textarea class="form-control" name="notes" id="notes" rows="3">{!! old('notes') !!}</textarea>
                </div>

                <div class="tinh-toan">
                  <div class="form-group col-md-4 col-xs-6">
                    <label for="price">Price</label>
                    <input type="text" name="price" class="form-control number gia" autocomplete="off"
                           placeholder="Giá" value="{{ old('price') }}">
                  </div>

                  <div class="form-group col-md-4 col-xs-6">
                    <label for="amount">Amount</label>
                    <input type="text" name="amount" class="form-control amount" autocomplete="off"
                           placeholder="Số lượng" value="{{ old('amount', 1) }}">
                  </div>

                  <div class="form-group col-md-4 col-xs-12">
                    <label for="total_money">Total</label>
                    <input type="text" name="total_money" class="form-control number total" autocomplete="off"
                           placeholder="Tổng tiền" value="{{ old('total_money') }}">
                  </div>
                </div>

                <div class="form-group col-md-12 input-group" style="padding-left: 15px;">
                  <label for="bank_info_id">Bank account</label>
                  <select class="form-control select2" name="bank_info_id" id="bank_info_id">
                    <option value="">--All--</option>
                    @foreach($bankInfoList as $cate)
                      <option value="{{ $cate->id }}" {{ old('bank_info_id') == $cate->id ? "selected" : "" }}>
                        {{ $cate->name }} - {{ $cate->bank_name }} - {{ $cate->bank_no }}
                      </option>
                    @endforeach
                  </select>

                  <span class="input-group-btn">
                    <button style="margin-top:24px" class="btn btn-primary btn-sm" id="btnAddBankInfo" type="button" data-value="3">
                      Add new
                    </button>
                  </span>
                </div>
                <div class="form-group col-md-6">
                  <label>Nhà cung cấp</label>
                  <select class="form-control select2" name="ncc_id" id="ncc_id">
                    <option value="">--Chọn--</option>
                    @foreach($nccList as $ncc)
                      <option value="{{ $ncc->id }}" {{ old('ncc_id') == $ncc->id ? "selected" : "" }}>
                        {{ $ncc->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label>Payer <span class="red-star">*</span></label>
                  <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                    @foreach($collecterList as $payer)
                      <option value="{{ $payer->id }}" {{ old('nguoi_chi') == $payer->id ? "selected" : "" }}>
                        {{ $payer->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-12" style="margin-top: 15px;">
                  <label class="col-md-3 row">Images</label>
                  <div class="col-md-9">
                    <img id="thumbnail_image"
                         src="{{ old('image_url') ? Helper::showImage(old('image_url')) : URL::asset('admin/dist/img/img.png') }}"
                         class="img-thumbnail" width="145" height="85">

                    <input type="file" id="file-image" style="display:none" />
                    <button class="btn btn-default" id="btnUploadImage" type="button">
                      <span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload
                    </button>
                  </div>

                  <div style="clear:both"></div>
                  <input type="hidden" name="image_url" id="image_url" value="{{ old('image_url') }}"/>
                  <input type="hidden" name="image_name" id="image_name" value="{{ old('image_name') }}"/>
                </div>

                <div class="form-group" style="margin-top:10px;margin-bottom:10px">
                  <div class="col-md-12" style="text-align:left">
                    <button class="btn btn-primary btnMultiUpload" type="button">
                      <span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload multi images
                    </button>
                    <div class="clearfix"></div>
                    <div id="div-image" style="margin-top:10px"></div>
                  </div>
                  <div style="clear:both"></div>
                </div>

              </div> {{-- /row --}}

            </div> {{-- /box-body --}}

            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" href="{{ route('cost.index')}}">Cancel</a>
            </div>

          </div> {{-- /box --}}

        </div>
      </div>
    </form>
  </section>
</div>

<input type="hidden" id="route_upload_tmp_image" value="{{ route('image.tmp-upload') }}">

{{-- Modal add new bank account --}}
<div id="modalNewBankInfo" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('bank-info.ajax-save')}}" id="formAjaxBankInfo">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add new bank account</h4>
        </div>

        <div class="modal-body" id="contentTag">
          <input type="hidden" name="type" value="1">

          <div class="col-md-12">
            <div class="form-group">
              <label>Name<span class="red-star">*</span></label>
              <input type="text" autocomplete="off" class="form-control" id="add_name" value="{{ old('name') }}" name="name">
            </div>

            <div class="form-group">
              <label>Bank name<span class="red-star">*</span></label>
              <select class="form-control select2" id="add_bank_name" name="bank_name" style="width: 100%">
                <option value="">--Chọn--</option>
                @foreach($vietNameBanks as $bank)
                  <option value="{{$bank['shortName']}}">{{$bank['shortName']}}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label>Account name<span class="red-star">*</span></label>
              <input type="text" autocomplete="off" class="form-control" id="add_account_name" value="{{ old('account_name') }}" name="account_name">
            </div>

            <div class="form-group">
              <label>Account No<span class="red-star">*</span></label>
              <input type="text" autocomplete="off" class="form-control" id="add_bank_no" value="{{ old('bank_no') }}" name="bank_no">
            </div>

            <div class="form-group">
              <label>Branch</label>
              <input type="text" autocomplete="off" class="form-control" id="bank_branch" value="{{ old('bank_branch') }}" name="bank_branch">
            </div>
          </div>

          <div classs="clearfix"></div>
        </div>

        <div style="clear:both"></div>

        <div class="modal-footer" style="text-align:center">
          <button type="button" class="btn btn-primary btn-sm" id="btnSaveBankAjax"> Save</button>
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" id="btnCloseModalTag">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('js')
<script type="text/javascript">
  // Save bank account via ajax
  $(document).on('click', '#btnSaveBankAjax', function(){
    $(this).attr('disabled', 'disabled');
    $.ajax({
      url : $('#formAjaxBankInfo').attr('action'),
      data: $('#formAjaxBankInfo').serialize(),
      type : "post",
      success : function(id){
        $('#btnCloseModalTag').click();
        $.ajax({
          url : "{{ route('bank-info.ajax-list') }}",
          data: { id : id },
          type : "get",
          success : function(data){
            $('#bank_info_id').html(data);
            // select2 refresh (chỗ này bạn đang dùng 'refresh', tùy version có thể dùng trigger change)
            try { $('#bank_info_id').trigger('change.select2'); } catch(e) {}
          }
        });
      },
      error: function (error) {
        var errrorMess = jQuery.parseJSON(error.responseText);
        if(errrorMess.message == 'The given data was invalid.'){
          alert('Nhập đầy đủ thông tin có dấu *');
          $('#btnSaveBankAjax').removeAttr('disabled');
        }
      }
    });
  });

  $(document).ready(function(){
    // open modal
    $('#btnAddBankInfo').click(function(){
      $('#modalNewBankInfo').modal('show');
    });

    // Load partner list when category changes (giữ logic cũ)
    $('#cate_id').change(function(){
      $.ajax({
        url : "{{ route('cost.ajax-doi-tac') }}",
        data: { cate_id : $(this).val() },
        type : "GET",
        success : function(data){
          if(data != 'null'){
            $('#load_doi_tac').html(data);
            if($('#partner_id').length==1){
              $('#partner_id').select2();
            }
          }else{
            $('#load_doi_tac').html('');
          }
        }
      });
    });
  });

  // Calculate total
  $(document).ready(function(){
    $('.tinh-toan .amount, .tinh-toan .gia').blur(function(){
      var parent = $(this).parents('.tinh-toan');
      tinhtoangia(parent);
    });
  });

  function tinhtong(){
    var tong = 0;
    $('.total').each(function(){
      var total = parseInt($(this).val());
      if(total > 0){
        tong += total;
      }
    });
    $('#total_money').val(tong);
  }

  function tinhtoangia(parent){
    var amount = parent.find('.amount').val();
    var gia = parent.find('.gia').val();
    var total = gia * amount;
    parent.find('.total').val(total);
    tinhtong();
  }
</script>

<script type="text/javascript">
  $(document).ready(function(){

    // Change type -> reload categories (giữ logic cũ; nhưng trong form đang hidden type=1)
    $('#type').change(function(){
      $.ajax({
        url : "{{ route('cost.ajax-cost-type') }}",
        type : 'GET',
        data : { type : $(this).val() },
        success: function(data){
          $('#cate_id').html(data);
          $('#cate_id').trigger('change');
        }
      });
    });

    // Upload single image
    $('#btnUploadImage').click(function(){
      $('#file-image').click();
    });

    var files = "";
    $('#file-image').change(function(e){
      $('#thumbnail_image').attr('src', "{{ URL::asset('admin/dist/img/loading.gif') }}");
      files = e.target.files;

      if(files && files.length){
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
              // chú ý: cần có #upload_url ở layout (như code gốc bạn đang dùng)
              $('#thumbnail_image').attr('src', $('#upload_url').val() + response.image_path);
              $('#image_url').val(response.image_path);
              $('#image_name').val(response.image_name);
            }
          },
          error: function(response){
            // handle errors if needed
          }
        });
      }
    });

  });
</script>

{{-- QUICK LABELS + LOCALSTORAGE DATE --}}
<script>
  (function () {
    var LS_DATE_KEY = 'cost_date_use';

    function setCateActive(cateId) {
      $('#cate_quick .btnCateQuick')
        .removeClass('btn-primary')
        .addClass('btn-default');

      if (cateId) {
        $('#cate_quick .btnCateQuick[data-cate-id="' + cateId + '"]')
          .removeClass('btn-default')
          .addClass('btn-primary');
      }
    }

    function saveDateToLocalStorage() {
      var v = ($('#date_use').val() || '').trim();
      if (v) localStorage.setItem(LS_DATE_KEY, v);
      else localStorage.removeItem(LS_DATE_KEY);
    }

    $(document).ready(function () {
      // 1) Restore Date from localStorage (only if empty)
      var $date = $('#date_use');
      var currentDate = ($date.val() || '').trim();
      var savedDate = (localStorage.getItem(LS_DATE_KEY) || '').trim();

      if (!currentDate && savedDate) {
        $date.val(savedDate);
        // nếu dùng bootstrap-datepicker
        try { $date.datepicker('update', savedDate); } catch (e) {}
      }

      // 2) Update localStorage when user changes date
      $date.on('change blur', saveDateToLocalStorage);
      $date.on('changeDate', saveDateToLocalStorage); // bootstrap-datepicker

      // 3) Quick labels click -> set cate select2 and trigger change (to run ajax-doi-tac)
      $(document).on('click', '.btnCateQuick', function () {
        var cateId = $(this).data('cate-id');
        $('#cate_id').val(cateId).trigger('change');
        setCateActive(String(cateId));
      });

      // init highlight
      setCateActive($('#cate_id').val());

      // when change by dropdown
      $('#cate_id').on('change', function () {
        setCateActive($(this).val());
      });
    });
  })();
</script>
@stop