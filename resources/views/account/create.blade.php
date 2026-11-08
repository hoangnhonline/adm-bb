@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Tạo tài khoản
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('account.index') }}">Tài khoản</a></li>
      <li class="active">Add new</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('account.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('account.store') }}" id="formData">
    <div class="row">
      <!-- left column -->

      <div class="col-md-7">
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
                <!-- text input -->
                <div class="form-group">
                  <label>Số điện thoại <span class="red-star">*</span></label>
                  <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                  <label>Tên hiển thị<span class="red-star">*</span></label>
                  <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
                </div>           
                 <div class="form-group">
                  <label>Email<span class="red-star">*</span></label>
                  <input type="text" class="form-control" name="email" id="email" value="{{ old('email') }}">
                </div>   
                <div class="form-group">
                  <label>Mật khẩu <span class="red-star">*</span></label>
                  <input type="password" class="form-control" name="password" id="password" value="{{ old('password') }}">
                </div>   
                <div class="form-group">
                  <label>Nhập lại mật khẩu <span class="red-star">*</span></label>
                  <input type="password" class="form-control" name="re_password" id="re_password" value="{{ old('re_password') }}">
                </div>      
                <div class="form-group">
                  <label>Phân loại</label>
                  <select class="form-control" name="role" id="role">      
                    <option value="" >--Chọn role--</option>                       
                    <option value="1" {{ old('role') == 1 ? "selected" : "" }}>Super Admin</option>  
                    <option value="2" {{ old('role') == 2 ? "selected" : "" }}>Kế toán</option>  
                    <option value="3" {{ old('role') == 3 ? "selected" : "" }}>Điều hành</option>  
                    <option value="4" {{ old('role') == 4 ? "selected" : "" }}>Sales</option>  
                    <option value="5" {{ old('role') == 5 ? "selected" : "" }}>HDV</option>  
                    <option value="6" {{ old('role') == 6 ? "selected" : "" }}>Đối tác</option>  
                  </select>
                </div>    
                @if(Auth::user()->id !=333)
                <div class="form-group">
                  <label>Người phụ trách</label>
                  <select class="form-control" name="user_id_manage" id="user_id_manage">      
                    <option value="" >--Chọn--</option>                       
                    <option value="84" {{ old('user_id_manage') == 84 ? "selected" : "" }}>Lâm Như</option>  
                    <option value="219" {{ old('user_id_manage') == 219 ? "selected" : "" }}>Trang Tạ</option>  
                    <option value="333" {{ old('user_id_manage') == 333 ? "selected" : "" }}>Group Tour</option>
                    <option value="451" {{ old('user_id_manage') == 451 ? "selected" : "" }}>Thảo Lê</option>
                  </select>
                </div>    
                @endif
                <div class="form-group">
                  <label>Phân loại công nợ</label>
                  <select class="form-control" name="debt_type" id="debt_type">      
                    <option value="" >--Chọn--</option>                       
                    <option value="1" {{ old('debt_type') == 1 ? "selected" : "" }}>Ngày</option>  
                    <option value="2" {{ old('debt_type') == 2 ? "selected" : "" }}>Tuần</option>  
                    <option value="3" {{ old('debt_type') == 3 ? "selected" : "" }}>Tháng</option>
                  </select>
                </div>              
                <div class="clearfix"></div>                     
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status">
                    <option value="1" {{ old('status') == 1 || old('status') == NULL ? "selected" : "" }}>Mở</option>
                    <option value="2" {{ old('status') == 2 ? "selected" : "" }}>Khóa</option>
                  </select>
                </div>
                <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                  <label class="col-md-3 row">Ảnh Avatar </label>    
                  <div class="col-md-9">
                    <img id="thumbnail_image" src="{{ old('image_url') ? Helper::showImage(old('image_url')) : asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="145" height="85">                 
                    <button class="btn btn-default btn-sm btnSingleUpload" data-set="image_url" data-image="thumbnail_image" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</button>
                  </div>
                  <div style="clear:both"></div>
              </div>  <!--image-->
              <input type="hidden" name="image_url" id="image_url" value="{{ old('image_url') }}"/> 
            </div>
            <div class="box-footer">             
              <button type="submit" class="btn btn-primary btn-sm" id="btnSave">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('account.index')}}">Cancel</a>
            </div>
            
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
@stop
@section('js')
<script type="text/javascript">
    $(document).ready(function(){
      $('#formData').submit(function(){
        $('#btnSave').html('<i class="fa fa-spinner fa-spin">').attr('disabled', 'disabled');
      });      
    });
    
</script>
@stop
