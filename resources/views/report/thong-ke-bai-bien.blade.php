@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    THỐNG KÊ CHIẾT KHẤU {{ $month }}/{{ $year }}
  </h1>
 <!--  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'food.index' ) }}">Món ăn</a></li>
    <li class="active">List</li>
  </ol> -->
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
      <div class="panel panel-default">        
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('report.thong-ke') }}" id="searchForm">
             <div class="form-group  chon-thang"> 
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">--THÁNG--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>    
              <div class="form-group  chon-thang">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--NĂM--</option>
                  <option value="2023" {{ $year == 2023 ? "selected" : "" }}>2023</option>
                  <option value="2024" {{ $year == 2024 ? "selected" : "" }}>2024</option>
                </select>
              </div>       
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button>
          </form>         
        </div>
      </div>  
      <div class="box">       
        <!-- /.box-header -->
        <div class="box-body">
          <div class="table-responsive">            
            <table class="table table-bordered table-hover table-list-data">
              <tr style="background-color: #06b7a4;color:#FFF">
                <th class="text-center">Ngày</th>
                <th class="text-right">Tổng tiền</th>
                <th class="text-right">Tổng chiết khấu</th>
             
            
              </tr>              
              <?php 
              $total_tong_tien = $total_tong_giam = $total_tong_ck = 0;
              ?>
              @foreach($arrDay as $day => $arr)      
              @php
                $total_tong_tien += $tong_tien = isset($arrDay[$day]['tong_tien']) ? $arrDay[$day]['tong_tien'] : 0;
               
                $total_tong_ck += $tong_ck = ($tong_tien)*0.2;

              @endphp       
              <tr>
                <th class="text-center">{{ $day }}</th>
                <th class="text-right"><a target="_blank" href="{{ route('booking.index', ['time_type' => 3, 'use_date_from' => $day.'/'.$month.'/'.$year]) }}">{{ $tong_tien > 0 ? number_format($tong_tien) : '-' }}</a></th>                
                
                
                
                <th class="text-right" style="color: blue">
                 {{ $tong_ck > 0 ? number_format($tong_ck) : '-' }}

                </th>        
                                 
              </tr>
             
              @endforeach            
              <tr style="background-color: #ccc">
                <th class="text-center">Tổng</th>
                <th class="text-right">{{ $total_tong_tien > 0 ? number_format($total_tong_tien) : '-' }}</th>               
                <th class="text-right" style="color: blue; font-size: 18px">{{ $total_tong_ck > 0 ? number_format($total_tong_ck) : '-' }}</th>
              </tr>
            </table>
          </div>
        </div>
      </div>        
         
    </div>
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>
<style type="text/css">
	table a{
		color: #000;
    font-weight: bold;
	}
  .table-list-data td,.table-list-data th{
    border: 1px solid #000 !important;
    font-weight: bold;
    color: #000
  }
</style>
<input type="hidden" id="table_name" value="articles">
@stop