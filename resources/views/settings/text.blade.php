@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Text
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('w-text.index') }}">Text</a></li>
      <li class="active">Update</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">   
    <form role="form" method="POST" action="{{ route('w-text.save') }}">
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
              @if(Session::has('message'))
              <p class="alert alert-info" >{{ Session::get('message') }}</p>
              @endif
                 <!-- text input -->
               <table class="table table-bordered">
                  @foreach($textList as $text)
                  <tr>                    
                    <input type="hidden" name="id[]" class="form-control" value="{!! $text['id'] !!}">
                    
                    <td>
                      <input type="text" name="value[]" class="form-control" value="{!! $text['value'] !!}">
                    </td>
                  </tr>
                  @endforeach
               </table>   
               
               
            </div>                        
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>         
            </div>
            
        </div>
        <!-- /.box -->     

      </div>     

    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
@stop