@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Tạo tài khoản đối tác
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
    <form role="form" method="POST" action="{{ route('account.store-tx') }}" id="formData">
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
                 <div class="form-group">
                  <label>Email</label>
                  <input type="text" class="form-control" name="email" id="email" value="{{ old('email') }}">
                </div>               
                <input type="hidden" name="role" value="6">
                <input type="hidden" name="password" value="123465">
                <input type="hidden" name="re_password" value="123465">
                <input type="hidden" name="level" value="7">
                <input type="hidden" name="status" value="1">
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
