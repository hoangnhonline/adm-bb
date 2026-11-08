@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Combo 
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('combo.index') }}">Combo</a></li>
      <li class="active">Update</li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('combo.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('combo.update') }}" id="dataForm" class="productForm">
    <input type="hidden" name="id" value="{{ $detail->id }}">    
    <div class="row">
      <!-- left column -->

      <div class="col-md-12">
        @if(Session::has('message'))
        <p class="alert alert-info" >{{ Session::get('message') }}</p>
        @endif
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
                <div>

                  <!-- Nav tabs -->
                  <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Thông tin chi tiết</a></li>                                          
                    <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Hình ảnh</a></li>
                  
                    <li role="presentation"><a href="#meta" aria-controls="settings" role="tab" data-toggle="tab">Thông tin meta</a></li>                     
                  </ul>

                  <!-- Tab panes -->
                  <div class="tab-content">
                   
                    <div role="tabpanel" class="tab-pane active" id="home">                         
                      <input type="hidden" name="type" value="1">     
                        <div class="form-group">
                        <label for="email">Tỉnh/Thành</label>
                        <select class="form-control" name="city_id" id="city_id">
                          <option value="">--Chọn--</option>
                          <option value="1"  {{ old('city_id', $detail->city_id) == 1 ? "selected" : "" }}>Phú Quốc</option>
                          <option value="2"  {{  old('city_id', $detail->city_id) == 2 ? "selected" : "" }}>Đà Nẵng</option>
                        </select>
                      </div>                                                                                    
                        <div class="row">                                                                             
                        <div class="form-group col-md-6" >                  
                          <label>Tên combo <span class="red-star">*</span></label>
                          <input type="text" class="form-control req" name="name" id="name" value="{{ old('name', $detail->name) }}">
                        </div>
                        <div class="form-group col-md-6">                  
                          <label>Slug <span class="red-star">*</span></label>                  
                          <input type="text" class="form-control req" readonly="readonly" name="slug" id="slug" value="{{ old('slug', $detail->slug) }}">
                        </div>
                      </div>
                        <div class="row">
                          <div class="col-md-12 form-group" >                  
                            <label>Giá<span class="red-star">*</span></label>
                            <input type="text" class="form-control number" name="price" id="price" value="{{ old('price', $detail->price) }}">
                          </div>                                            
                        </div> 
                        <div class="row">
                          <div class="form-group col-md-6">
                            <label>Khách sạn</label>
                            <select class="form-control select2" name="hotel_id" id="hotel_id">  
                              <option value="">--Chọn--</option>  
                              @foreach($hotelList as $hotel)
                              <option value="{{ $hotel->id }}" {{ old('hotel_id', $detail->hotel_id) == $hotel->id  ? "selected" : "" }}>{{ $hotel->name }}</option>
                              @endforeach
                            </select>
                          </div> 
                        
                        <div class="form-group col-md-6">                    
                          <label>Tour</label>
                          <select class="form-control select2" id="tour_id" name="tour_id"> 
                          <option value="">--Chọn--</option>                    
                              <option value="1" {{ old('tour_id', $detail->tour_id) == 1 ? "selected" : "" }}>Tour đảo</option>
                              <option value="3" {{ old('tour_id', $detail->tour_id) == 3 ? "selected" : "" }}>Tour Rạch Vẹm</option>
                              <option value="4" {{ old('tour_id', $detail->tour_id) == 4 ? "selected" : "" }}>Tour Câu Mực</option>
                          </select>
                        </div> 
                        </div>   
                        <div class="row">
                          <div class="col-md-6 form-group">
                            <label>Số đêm</label>
                            <select class="form-control" name="nights" id="nights">
                              @for($i = 2; $i<=5; $i++)
                              <option value="{{ $i }}" {{ old('nights', $detail->nights) == $i  ? "selected" : "" }}>{{ $i+1 }}N{{$i}}D</option>
                              @endfor
                            </select>
                          </div>   
                          
                           <div class="col-md-6 form-group" >                  
                            <label>Video Youtube ID<span class="red-star">*</span></label>
                            <input type="text" class="form-control req" name="video_id" id="video_id" value="{{ old('video_id', $detail->video_id) }}">
                          </div>
                          
                        </div>
                        
                        <div class="form-group col-md-6">
                          <div class="checkbox">
                            <label>
                              <input type="checkbox" name="is_hot" value="1" {{ old('is_hot', $detail->is_hot) == 1 ? "checked" : "" }}>
                              <span style="color:red">COMBO NỔI BẬT</span>
                            </label>
                          </div>               
                        </div>   
                       
                        <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                          <label class="col-md-3 row">Banner ( 1350x500 px)</label>    
                          <div class="col-md-9">                           
                            <img id="thumbnail_banner" src="{{ $detail->banner_url ? Helper::showImage($detail->banner_url ) : asset('admin/dist/img/img.png') }}" class="img-thumbnail" width="300">                    
                            <button class="btn btn-default btn-sm btnSingleUpload" data-set="banner_url" data-image="thumbnail_banner" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</button>
                            <input type="hidden" name="banner_url" id="banner_url" value="{{ old('banner_url', $detail->banner_url) }}"/>
                          </div>
                          <div style="clear:both"></div>
                        </div>                   
                        <div class="form-group" style="margin-top: 15px !important;">
                          <label>Giới thiệu</label>
                          <button class="btnUploadEditor btn btn-info" type="button" style="float:right;margin-bottom: 3px !important;" data-editor="description">Chèn ảnh</button>
                          <div class="clearfix"></div>
                          <textarea class="form-control" rows="4" name="description" id="description">{{ old('description', $detail->description) }}</textarea>
                        </div>   
                        <div class="form-group" style="margin-top: 15px !important;">
                          <label>Chi tiết</label>
                          <button class="btnUploadEditor btn btn-info" type="button" style="float:right;margin-bottom: 3px !important;" data-editor="content">Chèn ảnh</button>
                          <div class="clearfix"></div>
                          <textarea class="form-control" rows="4" name="content" id="content">{{ old('content', $detail->content) }}</textarea>
                        </div>                                                              
                        
                        <div style="margin-bottom:10px;clear:both"></div>
                        <div class="clearfix"></div>
                    </div><!--end thong tin co ban-->                    
                    <input type="hidden" id="editor" value="">                     
                     <div role="tabpanel" class="tab-pane" id="settings">
                        <div class="form-group" style="margin-top:10px;margin-bottom:10px">  
                         
                          <div class="col-md-12" style="text-align:center">                            
                            
                            <input type="file" id="file-image"  style="display:none" multiple/>
                         
                            <button class="btn btn-primary btnMultiUpload" type="button"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</button>
                            <div class="clearfix"></div>
                            <div id="div-image" style="margin-top:10px">
                              @if( $detail->images->count() > 0 )
                                @foreach( $detail->images as $k => $hinh)
                                  <div class="col-md-3">
                                    <img class="img-thumbnail" src="{{ Helper::showImage($hinh->image_url) }}" style="width:100%">
                                    <div class="checkbox">                                   
                                      <label><input type="radio" name="thumbnail_id" class="thumb" value="{{ $hinh->id }}" {{ $detail->thumbnail_id == $hinh->id ? "checked" : "" }}> Ảnh đại diện </label>
                                      <button class="btn btn-danger btn-sm remove-image" type="button" data-value="{{  $hinh->image_url }}" data-id="{{ $hinh->id }}" >Xóa</button>
                                    </div>
                                    <input type="hidden" name="image_id[]" value="{{ $hinh->id }}">
                                  </div>
                                @endforeach
                              @endif

                            </div>
                          </div>
                          <div style="clear:both"></div>
                        </div>

                     </div><!--end hinh anh-->        
                      
                    
                     
                      <div role="tabpanel" class="tab-pane" id="meta">
                      <div class="form-group">
                          <label>Meta title </label>
                          <input type="text" class="form-control" name="meta_title" id="meta_title" value="{{ old('meta_title', $detail->meta_title) }}">
                        </div>
                        <div class="form-group">
                          <label>Meta desciption</label>
                          <textarea class="form-control" rows="6" name="meta_desc" id="meta_desc">{{ old('meta_desc', $detail->meta_desc) }}</textarea>
                        </div>   
                        <div class="form-group">
                          <label>Meta keywords</label>
                          <textarea class="form-control" rows="6" name="meta_keywords" id="meta_keywords">{{ old('meta_keywords', $detail->meta_keywords) }}</textarea>
                        </div>                                                  
                      </div>        
                  </div>

                </div>
                  
            </div>
            <div class="box-footer">              
              <button type="button" class="btn btn-default" id="btnLoading" style="display:none"><i class="fa fa-spin fa-spinner"></i></button>
              <button type="submit" class="btn btn-primary" id="btnSave">Save</button>
              <a class="btn btn-default" class="btn btn-primary" href="{{ route('combo.index')}}">Cancel</a>
            </div>
            
        </div>
        <!-- /.box -->     

      </div>      
    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<input type="hidden" id="route_upload_tmp_image_multiple" value="{{ route('image.tmp-upload-multiple') }}">
<input type="hidden" id="route_upload_tmp_image" value="{{ route('image.tmp-upload') }}">
<style type="text/css">
  .nav-tabs>li.active>a{
    color:#FFF !important;
    background-color: #444345 !important;
  }
  .error{
    border : 1px solid red;
  }
  .select2-container--default .select2-selection--single{
    height: 35px !important;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice{
    color: red !important;    
    font-size: 20px !important; 
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover{
    color: red !important;
    
    font-size:20px !important;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__rendered{
    font-size:20px !important;
  }
</style>
@stop
@section('js')
<script type="text/javascript">

    $(document).ready(function(){
       $(".select2").select2();
      $('#parent_id').change(function(){
        location.href="{{ route('combo.create') }}?parent_id=" + $(this).val();
      })
      
      $('#dataForm').submit(function(){        
        $('#btnSave').hide();
        $('#btnLoading').show();
      });  
      $(document).on('click', '.remove-image', function(){
      if( confirm ("Bạn có chắc chắn không ?")){
        $(this).parents('.col-md-3').remove();
      }
    });
    });
    
</script>
@stop
