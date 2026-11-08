@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    SMS
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'ung-luong.index' ) }}">Ứng lương</a></li>
    <li class="active">List</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div id="content_alert"></div>
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
     
      
      <div class="box">
        <form action="{{ route('ung-luong.parse-sms') }}" method="post">
          {{ csrf_field() }}
          <div class="input-group" style="padding: 15px;">
            <input type="text" name="sms" placeholder="Nhập SMS ..." class="form-control" autocomplete="off">
            <span class="input-group-btn">
            <button type="submit" class="btn btn-warning btn-flat">Parse SMS</button>
             </span>
          </div>
        </form>
         
      </div>
      <!-- /.box -->     
    </div>
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>

@stop