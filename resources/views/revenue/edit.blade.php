@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Doanh thu khác
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('revenue.index') }}">Doanh thu khác</a></li>
      <li class="active">Update</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ $back_url ?? route('revenue.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('revenue.update') }}" id="dataForm">
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
                <div class="form-group">
                    <label for="email">Tỉnh/Thành</label>
                    <select class="form-control" name="city_id" id="city_id">
                      <option value="">--Chọn--</option>
                      <option value="1"  {{ old('city_id', $detail->city_id) == 1 ? "selected" : "" }}>Phú Quốc</option>
                      <option value="2"  {{  old('city_id', $detail->city_id) == 2 ? "selected" : "" }}>Đà Nẵng</option>
                    </select>
                  </div>              
                 <!-- text input -->
                <div class="form-group">
                  <label>Nội dung thu <span class="red-star">*</span></label>
                  <input type="text" class="form-control" name="content" id="contents" value="{{ old('content', $detail->content) }}">
                </div>
                <div class="row">
                  <div class="form-group col-xs-4">
                    <label>Số tiền<span class="red-star">*</span></label>
                    <input type="text" autocomplete="off" class="form-control number" name="amount" id="amount" value="{{ old('amount', $detail->amount) }}">
                  </div>  
                  <div class="form-group col-xs-4">
                        <label>Receiver <span class="red-star">*</span></label>
                        <select class="form-control" name="nguoi_thu_tien" id="nguoi_thu_tien">
                          <option value="">--Chọn--</option>
                          <option value="1" {{ old('nguoi_thu_tien', $detail->nguoi_thu_tien) == 1 ? "selected" : "" }}>CTY</option>
                          <option value="2" {{ old('nguoi_thu_tien', $detail->nguoi_thu_tien) == 2 ? "selected" : "" }}>Điều hành</option>
                        </select>
                    </div>
                    @php
                    if($detail->pay_date){
                        $pay_date = old('pay_date', date('d/m/Y', strtotime($detail->pay_date)));
                    }else{
                        $pay_date = old('pay_date');
                    }
                  @endphp
                  <div class="form-group col-xs-4">
                    <label>Ngày thu<span class="red-star">*</span></label>
                    <input type="text" autocomplete="off"  class="form-control datepicker" name="pay_date" id="pay_date" value="{{ $pay_date }}">
                  </div>
                </div>
                <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
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
            </div>                        
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('revenue.index')}}">Cancel</a>
            </div>
            
        </div>
        <!-- /.box -->     

      </div>
      <div class="col-md-5">
              
        </div>
        <!-- /.box -->     

      </div>
      <!--/.col (left) -->      
    </div>
    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<input type="hidden" id="route_upload_tmp_image" value="{{ route('image.tmp-upload') }}">
@stop
@section('js')
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