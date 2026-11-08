@extends('layout')
@section('content')
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
  <h1 style="text-transform: uppercase;">
      MAKE A BILL
    </span></h1>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('booking.index') }}" style="margin-bottom:5px">Back</a>
    <a class="btn btn-success btn-sm" href="{{ route('booking.index') }}" style="margin-bottom:5px">Bill List</a>
    <form role="form" method="POST" action="{{ route('booking.store') }}" id="dataForm">
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
                <div class="form-group col-md-4 col-xs-6">
                  <label>Bill No</label>
                  <input type="text" class="form-control" name="bill_no" id="bill_no" value="{{ old('bill_no', $bill) }}" autocomplete="off">
                </div>
                 <div class="form-group col-md-4 col-xs-6">
                   <label>Branch</label>
                      <select name="branch_id" id="branch_id" class="form-control select2">
                        @foreach($branchList as $beach)
                        <option value="{{ $beach->id }}" {{ old('branch_id', 7) == $beach->id ? "selected" : "" }}>{{ $beach->name }}</option>
                        @endforeach
                        
                      </select>
                 </div>
                <div class="form-group col-md-4 col-xs-12">
                  <label>Dining date <span class="red-star">*</span></label>
                  <input type="text" class="form-control datepicker" name="use_date" id="use_date" value="{{ old('use_date', $use_date) }}" autocomplete="off">
                </div>
                
                 <div class="form-group col-md-6 col-xs-6" >
                    <label>Customer name</label>
                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" autocomplete="off">
                  </div>
                  <div class="form-group col-md-6 col-xs-6" >
                    <label>Phone number <span class="red-star">*</span></label>
                    <input type="text" maxlength="20" class="form-control" name="phone" id="phone" value="{{ old('phone') }}" autocomplete="off">
                  </div>
                  
                </div>
                @for($k = 0; $k < 15; $k++)
                <div class="row services" style="margin-top: 4px;margin-bottom: 4px;" >
                  <div class="form-group col-xs-5" style="padding-right: 0px">
                    <!-- <label>Dish</label> -->
                    <select name="dish_id[]" class="form-control select2 cate">
                      <option value="">-Dish name-</option>
                      @foreach($cateList as $cate)
                        <option value="{{ $cate->id }}" data-price="{{ $cate->price }}">{{ $cate->name }}- {{ number_format($cate->price) }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-xs-3">
                   <!--  <label>Số lượng</label> -->
                    <select name="amount[]" class="form-control select2 amount">

                     @for($i = 1; $i <= 30; $i++)
                        <option value="{{ $i }}"
                        >{{ $i }}</option>
                      @endfor
                      </select>
                  </div>
                  <div class="form-group col-xs-4" style="padding-left: 0px;">
                    <input type="text" name="total[]" class="form-control number total" placeholder="Amount">
                  </div>
                </div>
                @endfor
                <!-- <div class="col-md-12" style="padding-right: 0px; text-align: right;">
                  <button type="button" id="btnAdd" class="btn btn-info btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Thêm</button>
                </div> -->

                <div class="row">
                  <div class="form-group col-xs-6" >
                      <label>Total</label>
                    <input type="text" class="form-control number" autocomplete="off" name="total_price" id="total_price" value="{{ old('total_price') }}">
                  </div>
                  <div class="form-group col-xs-6" >
                      <label>Discount</label>
                    <input type="text" class="form-control number" autocomplete="off" name="discount" id="discount" value="{{ old('discount') }}">
                  </div>
                </div>
                <div class="row" style="display: none;">
                  <div class="form-group col-xs-6">
                      <label>Discount rate / Discount (%)</label>
                      <select name="per_com" id="per_com" class="form-control select2">
                        <option value="">--%--</option>
                        @foreach($chietkhauList as $ck)
                        <option value="{{ $ck->value }}" {{ old('per_com') == $ck->value ? "selected" : "" }}>{{ $ck->name }}</option>
                        @endforeach
                      </select>
                  </div>
                  <div class="form-group col-xs-6">
                      <label>Discount amount</label>
                      <input type="text" class="form-control number" name="commision" id="commision" value="{{ old('commision') }}">
                  </div>
                </div>
                <div class="row">  
                <div class="form-group col-xs-4">
                      <label>Revenue</label>
                      <input type="text" class="form-control number" name="con_lai" id="con_lai" value="{{ old('con_lai') }}">
                  </div>               
                  <div class="form-group col-xs-4" >
                      <label>Receiver <span class="red-star">*</span></label>
                      <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien">
                        @foreach($collecterList as $payer)
                        <option value="{{ $payer->id }}" {{ old('nguoi_thu_tien') == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                        @endforeach
                      </select>
                  </div>
                  <div class="form-group col-xs-4" >
                      <label>Rupees</label>
                      <input type="text" class="form-control number" name="rupees" id="rupees" value="{{ old('rupees') }}">
                  </div>
                </div>


                <div class="form-group" style="display: none;">
                     <label>Status <span class="red-star">*</span></label>
                      <select class="form-control" name="status" id="status">
                        <option value="1" {{ old('status') == 1 ? "selected" : "" }}>Mới</option>
                        <option value="2" {{ old('status') == 2 ? "selected" : "" }}>Hoàn tất</option>
                        <option value="3" {{ old('status') == 3 ? "selected" : "" }}>Cancel</option>
                      </select>
                </div>

                <div class="form-group">
                  <label>Notes</label>
                  <textarea class="form-control" rows="6" name="notes" id="notes">{{ old('notes') }}</textarea>
                </div>
               
            </div>

            <div class="box-footer">
              <button type="button" class="btn btn-default btn-sm" id="btnLoading" style="display:none"><i class="fa fa-spin fa-spinner"></i> Đang xử lý...</button>
              <button type="submit" id="btnSave" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('booking.index') }}">Cancel</a>
              <input type="hidden" name="count_services" id="count_services" value="">
            </div>

        </div>
        <!-- /.box -->

      </div>

    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
{{ old('hdv_id') }}
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
    var discount = 0;
    var tien_coc = 0;
    if($('#discount').val()){
      discount = parseInt($('#discount').val());
      total_price_ck = total_price_ck - discount;
    }
    var per_com = $('#per_com').val();
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
      alert('Bạn chưa chọn người thu cọc');
      return false;
    }
  });
  @if(old('partner_id') > 0)
    $.ajax({
          url : "{{ route('booking.hdv-list') }}",
          data: {
            partner_id : {{ old('partner_id') }}
          },
          type : "get", 
          success : function(data){
              $('#hdv_id').html(data);
              $('#hdv_id').select2('refresh');
              @if(old('hdv_id') > 0)
              $('#hdv_id').val({{ old('hdv_id') }}).select2('refresh');
              @endif
          }
        });
  @endif
  $('#btnAdd').click(function(){
    $('.hidden:first').removeClass('hidden');
  });

  $(document).ready(function(){
    $('#partner_id').change(function(){
   
      $.ajax({
          url : "{{ route('booking.hdv-list') }}",
          data: {
            partner_id : $(this).val()
          },
          type : "get", 
          success : function(data){
              $('#hdv_id').html(data);
              $('#hdv_id').select2('refresh');
              
          }
        });
    });
    $('.cate, .amount, #per_com').change(function(){
      setPrice();
    });
    $('#discount, #tien_coc').keyup(function(){
      setPrice();
    });
    $('#commision').blur(function(){
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
