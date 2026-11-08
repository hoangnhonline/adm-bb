@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Ứng lương
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('ung-luong.index') }}">Ứng lương</a></li>
      <li class="active">Add new</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('ung-luong.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('ung-luong.store') }}" id="dataForm">
    <div class="row">
      <!-- left column -->

      <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Add new</h3>
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
              
              <input type="hidden" name="month" value="{{ $month }}">
            
                         
              <div class="row">
                 
                  <div class="form-group col-xs-6">
                      <label>Nhân viên<span class="red-star">*</span></label>
                      <select class="form-control select2" id="partner_id" name="partner_id">     
                        <option value="">--Chọn--</option>      
                        @foreach($partnerList as $cate)
                        <option value="{{ $cate->id }}" {{ old('partner_id') == $cate->id ? "selected" : "" }}>
                          {{ $cate->name }}
                        </option>
                        @endforeach
                      </select>
                  </div>
                  
                 <div class="form-group col-md-6 col-xs-6">
                    <label for="date_use">Ngày</label>
                    <input type="text" name="date_use" class="form-control datepicker" value="{{ old('date_use' ) }}" autocomplete="off">
                  </div>
              </div>
              <div class="row">                          
                              
                                    
                  <div class="form-group col-xs-6">
                    <label for="total_money">Số tiền</label>
                    <input type="text" name="total_money" class="form-control number total" autocomplete="off" placeholder="Số tiền" value="{{ old('total_money') }}">
                  </div>
                   <div class="form-group col-xs-6">
                    <label for="notes">Notes</label>
                    <input type="text" name="notes" class="form-control" autocomplete="off" placeholder="Notes" value="{{ old('notes') }}">
                  </div>
              </div>
              <div class="row">
               
               
                <div class="form-group col-md-12" >
                      <label>Payer <span class="red-star">*</span></label>
                      <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                        @foreach($collecterList as $payer)
                        <option value="{{ $payer->id }}" {{ old('nguoi_chi') == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                        @endforeach                       
                      </select>
                  </div>            
                             
                 <div class="form-group col-xs-12">
                    <label for="status">Status</label>
                    <select class="form-control select2" name="status" id="status">                    
                      <option value="1"  {{ old('status') == 1 ? "selected" : "" }}>Chưa cấn trừ</option>
                      <option value="2"  {{  old('status') == 2 ? "selected" : "" }}>Đã cấn trừ</option>                    
                    </select>
                  </div>
                </div>
                           
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('ung-luong.index')}}">Cancel</a>
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
   
  });
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
      var total = gia*amount;
      parent.find('.total').val(total);
      tinhtong();
  }
</script>
<script type="text/javascript">
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
  });
</script>
@stop