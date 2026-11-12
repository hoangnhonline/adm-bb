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
      <li class="active">Update</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('cost.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('cost.update') }}" id="dataForm">
      <input type="hidden" name="id" value="{{ $detail->id }}">
    <div class="row">
      <!-- left column -->

      <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Update</h3>
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
             
              <div class="row">
                <div class="form-group col-md-4 col-md-12">
                  <label for="status">Status</label>                 
                  <select class="form-control select2" name="status" id="status">
                    <option value="2"  {{  old('status', $detail->status) == 2 ? "selected" : "" }}>Paid</option>
                    <option value="1"  {{ old('status', $detail->status) == 1 ? "selected" : "" }}>Not yet paid</option>
                    <option value="3"  {{  old('status', $detail->status) == 3 ? "selected" : "" }}>Pay later</option>
                  </select>
                </div>
                <div class="form-group col-md-4 col-xs-6">
                   <label>Branch</label>
                      <select name="branch_id" id="branch_id" class="form-control select2">
                        @foreach($branchList as $beach)
                        <option value="{{ $beach->id }}" {{ old('branch_id', $detail->branch_id) == $beach->id ? "selected" : "" }}>{{ $beach->name }}</option>
                        @endforeach      
                      </select>
                 </div>
                 <div class="form-group col-xs-6 col-md-4">
                @php
                    if($detail->date_use){
                        $date_use = old('date_use', date('d/m/Y', strtotime($detail->date_use)));
                    }else{
                        $date_use = old('date_use');
                    }
                  @endphp
                <label for="date_use">Date</label>
                 <input type="text" name="date_use" class="form-control datepicker" value="{{ old('date_use', $date_use) }}" autocomplete="off">
                </div>   
                
              </div>
              <div class="row"> 
                 <div class="form-group col-md-12 col-xs-12">                    
                  <label>Cost category<span class="red-star">*</span></label>
                  <select class="form-control select2" id="cate_id" name="cate_id">     
                      <option value="">--Chọn--</option>      
                      @include('cost._cate-options', ['selectedCate' => old('cate_id', $detail->cate_id)])
                  </select>
                </div>             
              <input type="hidden" name="type" value="1">               
              </div>               
                     
                <div class="row">
                  <div class="form-group col-xs-12">
                    <label for="notes">Content</label>
                    <textarea class="form-control" name="notes" id="notes">{!! old('notes', $detail->notes) !!}</textarea>
                  </div>
                  @include('cost._details')
                  <div class="tinh-toan" >
                    <div class="form-group col-md-4 col-xs-6">
                      <label for="price">Price</label>
                      <input type="text" name="price" class="form-control number gia" autocomplete="off" placeholder="Giá" value="{{ old('price', $detail->price) }}">
                    </div> 
                    <div class="form-group col-md-4 col-xs-6">
                      <label for="amount">Amount</label>
                      <input type="text" name="amount" class="form-control amount" autocomplete="off" placeholder="Số lượng" value="{{ old('amount', $detail->amount) }}">
                    </div>
                                     
                    <div class="form-group col-md-4 col-xs-12">
                      <label for="total_money">Total</label>
                      <input type="text" name="total_money" class="form-control number total" autocomplete="off" placeholder="Tổng tiền" value="{{ old('total_money', $detail->total_money) }}">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-md-6">
                    <label>Nhà cung cấp</label>
                    <select class="form-control select2" name="ncc_id" id="ncc_id">
                      <option value="">--Chọn--</option>
                      @foreach($nccList as $ncc)
                        <option value="{{ $ncc->id }}" {{ old('ncc_id', $detail->ncc_id) == $ncc->id ? "selected" : "" }}>
                          {{ $ncc->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                   <div class="form-group  col-md-6" >
                        <label>Payer <span class="red-star">*</span></label>
                        <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                          @foreach($collecterList as $payer)
                          <option value="{{ $payer->id }}" {{ old('nguoi_chi', $detail->nguoi_chi) == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                          @endforeach                       
                        </select>
                    </div>       
                </div>     
                <div class="form-group" style="margin-top: 15px;">  
                  <label class="col-md-3 row">Images </label>
                  <div class="col-md-9">
                    <img id="thumbnail_image" src="{{ old('image_url', $detail->image_url) ? Helper::showImageNew(old('image_url', $detail->image_url)) : URL::asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="145" height="85">
                    
                    <input type="file" id="file-image" style="display:none" />
                 
                    <button class="btn btn-default" id="btnUploadImage" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</button>
                  </div>
                  <div style="clear:both"></div>
                  <input type="hidden" name="image_url" id="image_url" value="{{ old('image_url', $detail->image_url) }}"/>          
                  <input type="hidden" name="image_name" id="image_name" value="{{ old('image_name') }}"/>
                </div>  
                <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                         
                          <div class="col-md-12" style="text-align:left">                            
                            
                            <input type="file" id="file-image"  style="display:none" multiple/>
                         
                            <button class="btn btn-primary btnMultiUpload" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload multi images</button>
                            <div class="clearfix"></div>
                            <div id="div-image" style="margin-top:10px">
                              @php
                              $imageList = [];
                              if($detail->image_list){
                              $imageList = json_decode($detail->image_list);
                              //dd($imageList);
                            }
                              @endphp
                              @if( count($imageList) > 0 )
                                @foreach( $imageList as $k => $hinh)
                                  <div class="col-md-2">
                                    <img class="img-thumbnail" src="{{ Helper::showImage($hinh) }}" style="width:auto !important; height: 200px !important;">
                                    <div class="checkbox">
                                      <button class="btn btn-danger btn-sm remove-image" type="button" data-value="{{  $hinh }}"  >Delete</button>
                                    </div>
                                    <input type="hidden" name="old_image[]" value="{{ $hinh }}">
                                  </div>
                                @endforeach
                              @endif

                            </div>
                          </div>
                          <div style="clear:both"></div>
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
@include('cost._details-js')
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
      $(document).on('click', '.remove-image', function(){
        if( confirm ("Bạn có chắc chắn không ?")){
          $(this).parents('.col-md-2').remove();
        }
      });
  });
</script>
@stop