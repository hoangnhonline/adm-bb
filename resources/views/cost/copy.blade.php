@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Cost
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('cost.index') }}">Cost</a></li>
      <li class="active">COPY</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('cost.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('cost.store') }}" id="dataForm">      
    <div class="row">
      <!-- left column -->

      <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">COPY</h3>
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
              
              <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="is_fixed" value="1" {{ old('is_fixed', $detail->is_fixed) == 1 ? "checked" : "" }}>
                      <span style="color: red">CHI PHÍ CỐ ĐỊNH</span>
                    </label>
                  </div>               
                </div>
              <div class="row">
                <div class="form-group col-md-4">
                  <label for="status">Status</label>
                  <select class="form-control select2" name="status" id="status">                    
                    <option value="1"  {{ old('status', $detail->status) == 1 ? "selected" : "" }}>Chưa thanh toán</option>
                    <option value="2"  {{  old('status', $detail->status) == 2 ? "selected" : "" }}>Đã thanh toán</option>
                    <option value="3"  {{  old('status', $detail->status) == 3 ? "selected" : "" }}>Thanh toán sau</option>
                  </select>
                </div>
                <div class="form-group col-md-4">
                   <label>Branch</label>
                      <select name="branch_id" id="branch_id" class="form-control select2">
                        @foreach($branchList as $beach)
                        <option value="{{ $beach->id }}" {{ old('branch_id', $detail->branch_id) == $beach->id ? "selected" : "" }}>{{ $beach->name }}</option>
                        @endforeach                          
                      </select>
                 </div>
                 <div class="form-group col-md-4 col-xs-4">
                  <label for="email">BOOKING ID <span style="color: red">(code1, code2, code3, code4 ....)</span></label>
                  <input type="text" name="booking_id" class="form-control" value="{{ old('booking_id', $detail->booking_id) }}" autocomplete="off">
                </div>
                <div class="form-group col-xs-6">
                @php
                    if($detail->date_use){
                        $date_use = old('date_use', date('d/m/Y', strtotime($detail->date_use)));
                    }else{
                        $date_use = old('date_use');
                    }
                  @endphp
                <label for="email">Ngày</label>
                 <input type="text" name="date_use" class="form-control datepicker" value="{{ old('date_use', $date_use) }}" autocomplete="off">
                </div>   
                 <div class="form-group col-md-6">                    
                  <label>Cost category<span class="red-star">*</span></label>
                  <select class="form-control select2" id="cate_id" name="cate_id">     
                      <option value="">--Chọn--</option>      
                      @foreach($cateList as $cate)
                      <option value="{{ $cate->id }}" {{ old('cate_id', $detail->cate_id) == $cate->id ? "selected" : "" }}>{{ $cate->name }}</option>
                      @endforeach
                  </select>
                </div>             
              </div>
               
              
              <div class="row" id="load_doi_tac">
                  @if($partnerList->count() > 0)
                  <div class="form-group col-md-12">
                      <label>Chi tiết<span class="red-star">*</span></label>
                      <select class="form-control select2" id="partner_id" name="partner_id">     
                        <option value="">--Chọn--</option>      
                        @foreach($partnerList as $cate)
                        <option value="{{ $cate->id }}" {{ old('partner_id', $detail->partner_id) == $cate->id ? "selected" : "" }}>
                          {{ $cate->name }}
                        </option>
                        @endforeach
                      </select>
                  </div>
                  @endif
              </div>      
              <div class="form-group input-group" >
                  <label for="type">Tài khoản nhận tiền (<span style="color:red; font-weight: bold">Chỉ "Thêm mới" tài khoản của ĐỐI TÁC</span>)</label>
                  <select class="form-control select2" name="bank_info_id" id="bank_info_id">
                    <option value="">--Tất cả--</option>
                    @foreach($bankInfoList as $cate)
                    <option value="{{ $cate->id }}" {{ old('bank_info_id', $detail->bank_info_id) == $cate->id ? "selected" : "" }}>{{ $cate->name }} - {{ $cate->bank_name }} - {{ $cate->bank_no }}</option>
                    @endforeach
                  </select>
                  <span class="input-group-btn">
                      <button style="margin-top:24px" class="btn btn-primary btn-sm" id="btnAddBankInfo" type="button" data-value="3">
                        Thêm
                      </button>
                    </span>
                </div>         
                <div class="row">
                  <div class="form-group col-xs-12">
                    <label for="notes">Nội dung</label>
                    <textarea class="form-control" name="notes" id="notes">{!! old('notes', $detail->notes) !!}</textarea>
                  </div>
                  <div class="tinh-toan" >
                    <div class="form-group col-xs-4">
                      <label for="price">Giá</label>
                      <input type="text" name="price" class="form-control number gia" placeholder="Giá" value="{{ old('price', $detail->price) }}">
                    </div> 
                    <div class="form-group col-xs-4">
                      <label for="amount">Số lượng</label>
                      <input type="text" name="amount" class="form-control amount" placeholder="Số lượng" value="{{ old('amount', $detail->amount) }}">
                    </div>
                                     
                    <div class="form-group col-xs-4">
                      <label for="total_money">Tổng tiền</label>
                      <input type="text" name="total_money" class="form-control number total" placeholder="Tổng tiền" value="{{ old('total_money', $detail->total_money) }}">
                    </div>
                  </div>
                </div>
                 <div class="form-group" >
                      <label>Payer <span class="red-star">*</span></label>
                      <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                        @foreach($collecterList as $payer)
                        <option value="{{ $payer->id }}" {{ old('nguoi_chi', $detail->nguoi_chi) == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                        @endforeach                       
                      </select>
                  </div>            
                <div class="form-group" style="margin-top: 15px;">  
                  <label class="col-md-3 row">Hình ảnh </label>
                  <div class="col-md-9">
                    <img id="thumbnail_image" src="{{ old('image_url', $detail->image_url) ? Helper::showImageNew(old('image_url', $detail->image_url)) : URL::asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="145" height="85">
                    
                    <input type="file" id="file-image" style="display:none" />
                 
                    <button class="btn btn-default" id="btnUploadImage" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</button>
                  </div>
                  <div style="clear:both"></div>
                  <input type="hidden" name="image_url" id="image_url" value="{{ old('image_url', $detail->image_url) }}"/>          
                  <input type="hidden" name="image_name" id="image_name" value="{{ old('image_name') }}"/>
                </div>     
                
            
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('cost.index')}}">Cancel</a>
            </div>            
        </div>
        <!-- /.box -->     

      </div>
      <div class="col-md-7">
             
    </div>
    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<input type="hidden" id="route_upload_tmp_image" value="{{ route('image.tmp-upload') }}">
<div id="modalNewBankInfo" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
    <form method="POST" action="{{ route('bank-info.ajax-save')}}" id="formAjaxBankInfo">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add new tài khoản ngân hàng đối tác</h4>
      </div>
      <div class="modal-body" id="contentTag">
          <input type="hidden" name="type" value="1">
           <!-- text input -->
          <div class="col-md-12">
            <div class="form-group">
              <label>Tên đối tác<span class="red-star">*</span></label>
              <input type="text" autocomplete="off" class="form-control" id="add_name" value="{{ old('name') }}" name="name"></textarea>
            </div>
            <div class="form-group">
              <label>Tên ngân hàng<span class="red-star">*</span></label>
                <select class="form-control select2" id="add_bank_name" name="bank_name" style="width: 100%">
                  <option value="">--Chọn--</option>
                    @foreach($vietNameBanks as $bank)
                        <option value="{{$bank['shortName']}}">{{$bank['shortName']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
              <label>Chủ tài khoản<span class="red-star">*</span></label>
              <input type="text" autocomplete="off" class="form-control" id="add_account_name" value="{{ old('account_name') }}" name="account_name"></textarea>
            </div>
            <div class="form-group">
              <label>Số tài khoản<span class="red-star">*</span></label>
              <input type="text" autocomplete="off" class="form-control" id="add_bank_no" value="{{ old('bank_no') }}" name="bank_no"></textarea>
            </div>
            <div class="form-group">
              <label>Chi nhánh</label>
              <input type="text" autocomplete="off" class="form-control" id="bank_branch" value="{{ old('bank_branch') }}" name="bank_branch"></textarea>
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
              data: {
                id : id
              },
              type : "get",
              success : function(data){
                  $('#bank_info_id').html(data);
                  $('#bank_info_id').select2('refresh');
              }
            });
          },error: function (error) {
            var errrorMess = jQuery.parseJSON(error.responseText);
            if(errrorMess.message == 'The given data was invalid.'){
              alert('Nhập đầy đủ thông tin có dấu *');
              $('#btnSaveBankAjax').removeAttr('disabled');
            }
            //console.log(error);
        }
        });
     });
  $(document).ready(function(){
    $('#btnAddBankInfo').click(function(){
          $('#modalNewBankInfo').modal('show');
      });  
    $('#cate_id').change(function(){
        $.ajax({
          url : "{{ route('cost.ajax-doi-tac') }}",
          data: {
            cate_id : $(this).val()
          },
          type : "GET", 
          success : function(data){  
            if(data != 'null'){
              $('#load_doi_tac').html(data);
              if($('#partner_id').length==1){
                $('#partner_id').select2();  
              }              
            }
          }
        });
    });
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
      var total = gia*amount;
      parent.find('.total').val(total);
      tinhtong();
  }
  $(document).ready(function(){
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
      $('#type').change(function(){      
        $.ajax({
          url : "{{ route('cost.ajax-cost-type') }}",
          type : 'GET',
          data : {
            type : $(this).val()
          },
          success: function(data){
            $('#cate_id').html(data);
          }
        });
      });
  });
</script>
@stop