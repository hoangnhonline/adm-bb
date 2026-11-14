@extends('layout')
@section('content')
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
  <h1 style="text-transform: uppercase;">
      {{ __('Sửa bill') }}
    </span></h1>
  </section>
@php
  if($detail->use_date){
      $use_date = old('use_date', date('d/m/Y', strtotime($detail->use_date)));
  }else{
      $use_date = old('use_date');
  }
@endphp
  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('booking.index') }}" style="margin-bottom:5px">{{ __('Quay lại') }}</a>
    <a class="btn btn-success btn-sm" href="{{ route('booking.index') }}" style="margin-bottom:5px">{{ __('QUẢN LÝ BILL') }}</a>
    <form role="form" method="POST" action="{{ route('booking.update') }}" id="dataForm">
      <input type="hidden" name="id" value="{{ $detail->id }}">
      <input type="hidden" name="count_services" id="count_services" value="{{ $detail->details->count() }}">
    <div class="row">
      <!-- left column -->

      <div class="col-md-12">
        <div id="content_alert"></div>
        <!-- general form elements -->
        <div class="box box-primary">

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
               <div class="row">
                @if(Auth::user()->id < 3)
               <!--  <div class="form-group col-xs-12">
                      <label style="font-weight: bold; color: blue">
                        <input type="checkbox" id="xe_4t" name="xe_4t" value="1" {{ old('xe_4t', $detail->xe_4t) == 1 ? "checked" : "" }}>
                        4T
                      </label>
                  </div> -->
                  @endif
                
                </div>
              <div class="row">
                
                <div class="form-group col-xs-6">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="take_away" value="1" {{ old('take_away', $detail->take_away) == 1 ? "checked" : "" }}>
                      <span style="color:blue">{{ __('MANG ĐI') }}</span>
                    </label>
                  </div>               
                </div> 
                <div class="form-group col-xs-6">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="indian" value="2" {{ old('indian', $detail->indian) == 2 ? "checked" : "" }}>
                      <span style="color:red">{{ __('KHÔNG PHẢI KHÁCH ẤN') }}</span>
                    </label>
                  </div>               
                </div> 
                <div class="form-group col-md-4 col-xs-4">
                  <label>{{ __('SỐ BÀN') }}</label>
                  <input type="text" class="form-control" name="table_no" id="table_no" value="{{ old('table_no', $detail->table_no) }}" autocomplete="off">
                </div>
                 <div class="form-group col-md-4 col-xs-4">
                  @php
                  $branch_id = old('branch_id', $detail->branch_id);
                  @endphp
                   <label>{{ __('Chi nhánh') }}</label>
                      <select name="branch_id" id="branch_id" class="form-control select2">
                        @foreach($branchList as $beach)
                        <option value="{{ $beach->id }}" {{ $branch_id == $beach->id ? "selected" : "" }}>{{ $beach->name }}</option>
                        @endforeach                        
                      </select>
                 </div>
                 <div class="form-group col-md-4 col-xs-4">
                    <label>{{ __('Ngày ăn') }} <span class="red-star">*</span></label>
                    <input type="text" class="form-control datepicker" name="use_date" id="use_date" value="{{ $use_date }}" autocomplete="off">
                  </div>
                 
                  <div class="form-group col-md-4 col-xs-4" style="display: none;">
                    <label>{{ __('Số bill') }}</label>
                    <input type="text" class="form-control" name="bill_no" id="bill_no" value="{{ old('bill_no', $detail->bill_no) }}" autocomplete="off">
                  </div>
                  <div class="form-group col-md-6 col-xs-6" style="display: none;">
                    <label>{{ __('Số điện thoại') }} <span class="red-star">*</span></label>
                    <input type="text" maxlength="20" class="form-control" name="phone" id="phone" value="{{ old('phone', $detail->phone) }}" autocomplete="off">
                  </div>
                  <div class="form-group col-md-6 col-xs-6" style="display: none;">
                    <label>{{ __('Tên khách') }}</label>
                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $detail->name) }}" autocomplete="off">
                  </div>
                </div>
                @foreach($detail->details as $item)
                <div class="row services" style="margin-top: 5px;margin-bottom: 10px; background-color: #ddd;padding-top:10px;padding-bottom: 5px;" >
                  <div class="form-group col-xs-6 col-md-3" style="padding-right: 0px">
                    <!-- <label>{{ __('Món') }}</label> -->
                    <select name="dish_id[]" class="form-control select2 cate">
                      <option value="">{{ __('-Món-') }}</option>
                      @foreach($cateList as $cate)
                        <option value="{{ $cate->id }}" {{ $item->dish_id == $cate->id ? "selected" : "" }} data-price="{{ $cate->price }}">{{ $cate->id }} - {{ $cate->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-xs-3 col-md-3">
                   <!--  <label>{{ __('Số lượng') }}</label> -->
                    <select name="amount[]" class="form-control select2 amount">
                      <option value="">{{ __('-SL-') }}</option>
                     @for($i = 1; $i <= 100; $i++)
                        <option value="{{ $i }}"
                      {{ $item->amount == $i ? "selected" : "" }}   >{{ $i }}</option>
                      @endfor
                      </select>
                  </div>
                  <div class="form-group col-xs-3 col-md-3" style="padding-left: 0px;">
                    <input type="text" name="total[]" class="form-control number total" placeholder="{{ __('Thành tiền') }}" value="{{ $item->total_price }}">
                  </div>
                  <div class="form-group col-xs-12 col-md-3">
                    <input type="text" name="notes_dish[]" class="form-control notes" placeholder="{{ __('Ghi chú') }}" value="{{ $item->notes }}">
                  </div>
                </div>
                @endforeach
                @php
                $con_lai = 25 - $detail->details->count();
                @endphp
                @for($k = 0; $k < $con_lai; $k++)
                <div class="row services" style="margin-top: 4px;margin-bottom: 4px; background-color: #ddd;padding-top:10px;padding-bottom: 5px;" >
                  <div class="form-group col-xs-6 col-md-3" style="padding-right: 0px">
                    <!-- <label>{{ __('Món') }}</label> -->
                    <select name="dish_id[]" class="form-control select2 cate">
                      <option value="">{{ __('-Món-') }}</option>
                      @foreach($cateList as $cate)
                        <option value="{{ $cate->id }}" data-price="{{ $cate->price }}">{{ $cate->id }} - {{ $cate->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-xs-3 col-md-3">
                   <!--  <label>{{ __('Số lượng') }}</label> -->
                    <select name="amount[]" class="form-control select2 amount">
                     @for($i = 1; $i <= 100; $i++)
                        <option value="{{ $i }}"
                        >{{ $i }}</option>
                      @endfor
                      </select>
                  </div>
                  <div class="form-group col-xs-3 col-md-3" style="padding-left: 0px;">
                    <input type="text" name="total[]" class="form-control number total" placeholder="{{ __('Thành tiền') }}">
                  </div>
                  <div class="form-group col-md-3 col-xs-12">
                    <input type="text" name="notes_dish[]" class="form-control notes" placeholder="{{ __('Ghi chú') }}">
                  </div>
                </div>
                @endfor
                <!-- <div class="col-md-12" style="padding-right: 0px; text-align: right;">
                  <button type="button" id="btnAdd" class="btn btn-info btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> {{ __('Thêm') }}</button>
                </div> -->

                <div class="row">
                  <div class="form-group col-xs-6" >
                      <label>{{ __('Tổng tiền') }}</label>
                    <input type="text" class="form-control number" autocomplete="off" name="total_price" id="total_price" value="{{ old('total_price', $detail->total_price) }}">
                  </div>
                  <div class="form-group col-xs-6" >
                      <label>{{ __('Giảm giá') }}</label>
                    <input type="text" class="form-control number" autocomplete="off" name="discount" id="discount" value="{{ old('discount', $detail->discount) }}">
                  </div>
                  <div class="form-group col-xs-6" style="display: none;">
                      <label>{{ __('Giảm giá (%)') }}</label>
                      <select name="per_com" id="per_com" class="form-control">
                        <option value="">--%--</option>
                         @foreach($chietkhauList as $ck)
                        <option value="{{ $ck->value }}" {{ old('per_com', $detail->per_com) == $ck->value ? "selected" : "" }}>{{ $ck->name }}</option>
                        @endforeach
                      </select>
                  </div>
                  <div class="form-group col-md-4 col-xs-4"  style="display: none;">
                      <label>{{ __('Tiền giảm giá') }}</label>
                    <input type="text" class="form-control number" name="commision" id="commision" value="{{ old('commision', $detail->commision) }}">
                  </div>
                 
                   <div class="form-group col-md-4 col-xs-4" >
                      <label>{{ __('Doanh thu') }}</label>
                      <input type="text" class="form-control number" name="con_lai" id="con_lai" value="{{ old('con_lai', $detail->con_lai) }}">
                  </div>
                  <div class="form-group col-xs-4" >
                      <label>{{ __('Người thu') }} <span class="red-star">*</span></label>
                      <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien">
                        @foreach($collecterList as $payer)
                        <option value="{{ $payer->id }}" {{ old('nguoi_thu_tien', $detail->nguoi_thu_tien) == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                        @endforeach
                      </select>
                  </div>
                  <div class="form-group col-md-4 col-xs-4" >
                      <label>{{ __('Rupees') }}</label>
                      <input type="text" class="form-control number" name="rupees" id="rupees" value="{{ old('rupees', $detail->rupees) }}">
                  </div>
                </div>


                @include('booking.partials.doi-tac')
                <div class="form-group">
                  <label>{{ __('Ghi chú') }}</label>
                  <textarea class="form-control" rows="6" name="notes" id="notes">{{ old('notes', $detail->notes) }}</textarea>
                </div>
                 <div class="form-group col-xs-12">
                     <label>{{ __('Trạng thái') }} <span class="red-star">*</span></label>
                      <select class="form-control select2" name="status" id="status">
                        <option value="1" {{ old('status', $detail->status) == 1 ? "selected" : "" }}>{{ __('Mới') }}</option>
                        <option value="4" {{ old('status', $detail->status) == 4 ? "selected" : "" }}>{{ __('Đã tiếp nhận') }}</option>
                        <option value="5" {{ old('status', $detail->status) == 5 ? "selected" : "" }}>{{ __('Đang triển khai') }}</option>
                        <option value="2" {{ old('status', $detail->status) == 2 ? "selected" : "" }}>{{ __('Hoàn tất') }}</option>
                        <option value="3" {{ old('status', $detail->status) == 3 ? "selected" : "" }}>{{ __('Hủy') }}</option>
                      </select>
                  </div>
            </div>

            <div class="box-footer">
              <button type="button" class="btn btn-default btn-sm" id="btnLoading" style="display:none"><i class="fa fa-spin fa-spinner"></i> {{ __('Đang xử lý...') }}</button>
              <button type="submit" id="btnSave" class="btn btn-primary btn-sm">{{ __('Lưu') }}</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('booking.index') }}">{{ __('Hủy') }}</a>
              @if(Auth::user()->id == 1)
              <a class="btn btn-default btn-sm pull-right" target="_blank" href="{{ route('activity-log.index', ['tab' => 'data', 'model' => 'booking', 'model_id' => $detail->id]) }}"><i class="fa fa-history"></i> {{ __('Lịch sử') }}</a>
              @endif
            </div>

        </div>
        <!-- /.box -->

      </div>

    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<style type="text/css">
  .select2-container .select2-selection--single .select2-selection__rendered{
    padding-left: 0px !important;
    padding-right: 0px !important;
  }
</style>
@stop
@section('js')
<script type="text/javascript">
  function setPrice(){
    var total_price = 0;
    var total_price_ck = 0;
    var count_services = 0;
    $('.services').each(function(){
      var row = $(this);
      var dish_id = row.find('.cate').val();
      var amount = row.find('.amount').val();
      //console.log(dish_id, amount);
      if(dish_id > 0 && amount > 0){
        count_services++;
        var price = row.find('.cate option:selected').data('price');
        var total = price*amount;
        row.find('.total').val(total);
        total_price = total_price + total;
        if(dish_id != 15 && dish_id != 16 && dish_id != 27){
          total_price_ck = total_price_ck + total;
        }
      }
    });
    var per_com = $('#per_com').val();
    var discount = 0;
    if($('#discount').val()){
      discount = parseInt($('#discount').val());
      total_price_ck = total_price_ck - discount;
    }
    var commision = $('#commision').val();
    if($('#branch_id').val() != 4){
      if(per_com > 0){
        if(per_com <= 100){
          commision = per_com*total_price_ck/100;
        }else{
          commision = per_com;
        }
      }
      $('#commision').val(commision);
    }
    
    var tien_coc = 0;
    if($('#tien_coc').val()){
      tien_coc = parseInt($('#tien_coc').val());
    }
    var con_lai = parseInt(total_price) - discount - commision - tien_coc;
    
    $('#total_price').val(total_price);
    $('#con_lai').val(con_lai);
    if(count_services > 0){
      $('#count_services').val(count_services);
    }
  }
  var levelLogin = {{ Auth::user()->level }};

  $(document).on('click','#btnSave', function(){

    if(parseInt($('#tien_coc').val()) > 0 && $('#nguoi_thu_coc').val() == ''){
      alert({!! json_encode(__('Bạn chưa chọn người thu cọc')) !!});
      return false;
    }
  });
  $('#btnAdd').click(function(){
    $('.hidden:first').removeClass('hidden');
  });

  $(document).ready(function(){
    $('#branch_id').change(function(){
   
      $.ajax({
          url : "{{ route('booking.get-dish') }}",
          data: {
            branch_id : $(this).val()
          },
          type : "get", 
          success : function(data){
              $('select.cate').html(data);
              $('select.cate').select2('refresh');
              
          }
        });
    });
    $('.cate, .amount, #per_com').change(function(){
      setPrice();
    });
    $('#discount, #tien_coc, #commision').keyup(function(){
      setPrice();
    });


    $('#dataForm').submit(function(){
      $('#btnSave').hide();
      $('#btnLoading').show();
    });

    $('#meals, #tien_coc, #discount, #extra_fee, #user_id').change(function(){
      var level = $("#user_id option:selected" ).data('level');

      //  setPrice();

    });

    $('#tien_coc').blur(function(){
      var level = $("#user_id option:selected" ).data('level');

       // setPrice();


    });

  });

</script>
@stop
