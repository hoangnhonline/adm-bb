@extends('backend.layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Cài đặt     
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="">Cài đặt</a></li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
  
    <form role="form" method="POST" action="{{ route('setting.store') }}">
    <div class="row">
      <!-- left column -->

      <div class="col-md-7">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Cài đặt</h3>
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
                  <label>Link đối tác <span class="red-star">*</span></label>
                  <select name="block_partner_link" class="form-control">
                    <option value="0">Mở</option>
                    <option value="1">Khóa</option>
                  </select>
                </div>
                                        
            </div>                        
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
             
            </div>
            
        </div>
        <!-- /.box -->     

      </div>
      <div class="col-md-5">      
          
        </div>
        <!-- /.box -->     

      </div>
      <!--/.col (left) -->      
    </div><!--col-md-4-->
    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
@stop
