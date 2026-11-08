@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Món ăn
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('food.index') }}">Món ăn</a></li>
      <li class="active">Update</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('food.index') }}" style="margin-bottom:5px">Back</a>    
    <form role="form" method="POST" action="{{ route('food.update') }}" id="dataForm">
    <div class="row">
      <!-- left column -->
      <input name="id" value="{{ $detail->id }}" type="hidden">
      <div class="col-md-5">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            Chỉnh sửa
          </div>
          <!-- /.box-header -->               
            {!! csrf_field() !!}

            <div class="box-body">
              @if(Session::has('message'))
              <p class="alert alert-info" >{{ Session::get('message') }}</p>
              @endif
              @if (count($errors) > 0)
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif                
                
                <div class="form-group" >                  
                  <label>Tên món<span class="red-star">*</span></label>
                  <input type="text" class="form-control" name="name" id="name" value="{{ $detail->name }}">
                </div> 
                <div class="form-group" >                  
                  <label>Giới hạn trong tuần<span class="red-star">*</span></label>
                  <input type="text" class="form-control" name="limit_in_week" id="limit_in_week" value="{{ $detail->limit_in_week }}">
                </div> 
                <div class="form-group" >                  
                  <label>Thứ tự hiển thị<span class="red-star">*</span></label>
                  <input type="text" class="form-control" name="display_order" id="display_order" value="{{ $detail->display_order }}">
                </div>                
                <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                  <label class="col-md-3 row">Ảnh đại diện ( 360 x 240 px)</label>    
                  <div class="col-md-9">
                    <img id="thumbnail_image" src="{{ $detail->thumbnail_url ? Helper::showImage($detail->thumbnail_url ) : asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="145" height="85">
                 
                    <button class="btn btn-default btn-sm btnSingleUpload" data-set="thumbnail_url" data-image="thumbnail_image" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</button>
                  </div>
                  <div style="clear:both"></div>
                  <input type="hidden" name="thumbnail_url" id="thumbnail_url" value="{{ $detail->thumbnail_url }}"/>
                </div>
                                        
                <div class="form-group">
                  <label>Mô tả</label>
                  <textarea class="form-control" rows="6" name="description" id="description">{{ $detail->description }}</textarea>
                </div> 
                <div class="form-group">
                  <label>Thành phần dinh dưỡng</label>
                  <textarea class="form-control" rows="6" name="components" id="components">{{ $detail->components }}</textarea>
                </div>
                <div class="form-group">
                  <label>Cách thực hiện</label>
                  <textarea class="form-control" rows="6" name="how_to_cook" id="how_to_cook">{{ $detail->how_to_cook }}</textarea>
                </div> 
                <input type="hidden" id="editor" value="content">
                  
            </div>          
            
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('food.index')}}">Cancel</a>
            </div>
            
        </div>
        <!-- /.box -->     

      </div>
      <div class="col-md-7">
        <!-- general form elements -->
        
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
     if($('#how_to_cook').length == 1){
      CKEDITOR.replace( 'how_to_cook', {
        height : 300
      });
    }
    if($('#components').length == 1){
      CKEDITOR.replace( 'components', {
        height : 300
      });
    }
  });
</script>
@stop