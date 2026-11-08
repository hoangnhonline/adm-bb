@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    DOANH SỐ ĐỐI TÁC {{ $month }}/{{ $year }}
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
        <div class="panel-heading">
          <h3 class="panel-title">Bộ lọc</h3>
        </div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('report.ds-doi-tac') }}" id="searchForm">
             <div class="form-group">              
              <select class="form-control select2" name="time_type" id="time_type">                  
                <option value="">--Thời gian--</option>
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>Theo tháng</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Khoảng ngày</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>Ngày cụ thể </option>
              </select>
            </div> 
            @if($time_type == 1)
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
                  <option value="">--Năm--</option>
                  <option value="2020" {{ $year == 2020 ? "selected" : "" }}>2020</option>
                  <option value="2021" {{ $year == 2021 ? "selected" : "" }}>2021</option>
                  <option value="2022" {{ $year == 2022 ? "selected" : "" }}>2022</option>
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)            
            <div class="form-group chon-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) Từ ngày @else Ngày @endif " value="{{ $arrSearch['use_date_from'] }}" style="width: 120px">
            </div>
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" style="width: 120px">
            </div>
             @endif
            @endif  
            @if(Auth::user()->role == 1)
             <div class="form-group">
            <select class="form-control select2" name="level" id="level">      
              <option value="" >--Phân loại sales--</option>
              <option value="1" {{ $level == 1 ? "selected" : "" }}>Level 1 - 9</option>  
              <option value="2" {{ $level == 2 ? "selected" : "" }}>Level 2 - TX</option>  
              <option value="3" {{ $level == 3 ? "selected" : "" }}>Level 3 - 3949</option>
              <option value="4" {{ $level == 4 ? "selected" : "" }}>Level 4 - 3848</option>
              <option value="5" {{ $level == 5 ? "selected" : "" }}>Level 5 - 10</option>
              <option value="6" {{ $level == 6 ? "selected" : "" }}>Level 6 - 0</option>
            </select>
          </div>
            
            <div class="form-group">
              <select class="form-control select2" name="user_id" id="user_id">
                <option value="">--Sales--</option>
                @foreach($listUser as $user)
                <option value="{{ $user->id }}" {{ $arrSearch['user_id'] == $user->id ? "selected" : "" }}>{{ $user->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <select class="form-control select2" name="ctv_id" id="ctv_id">
                <option value="">--Người book--</option>
                @foreach($ctvList as $ctv)
                <option value="{{ $ctv->id }}" {{ $arrSearch['ctv_id'] == $ctv->id ? "selected" : "" }}>{{ $ctv->name }}</option>
                @endforeach
              </select>
            </div>
            
            @endif        
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button>
          </form>         
        </div>
      </div>
      <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
          <div class="table-responsive">
            <!-- <p style="color: red; font-weight: bold">CHI PHÍ THÁNG {{ $month }}</p> -->
            <table class="table table-bordered table-hover table-list-data">
              <tr>
                <th width="1%" class="text-center">STT</th>
                <th class="text-center">Ngày</th>
                <th class="text-center">Tổng NL</th>
                <th class="text-right">Tổng tiền</th>                
              </tr>
              @php $i = 0; @endphp
              @foreach($arrByDay as $day => $arr)
              @php $i++; @endphp
              <tr>
                <td class="text-center">{{ $i }}</td>
                <td class="text-center">{{ date('d/m', strtotime($day)) }}</td>
                <td class="text-center">{{ $arr['total_adults'] }}</td>
                <td class="text-right">{{ number_format($arr['total_money']) }}</td>
              </tr>
              @endforeach
              <tr style="background-color: #999; font-weight: bold; font-size: 15px;">
                <td></td>
                <td class="text-center">Tổng</td>
                <td class="text-center">{{ $total_adults }}</td>
                <td class="text-right">{{ number_format($total_money) }}</td>
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
  .table-list-data td,.table-list-data th{
    border: 1px solid #000 !important;
    font-weight: bold;
    color: #000
  }
  tr.vip{
    background-color: #02fa7a
  }
  tr.thue-cano{
    background-color: #ebd405
  }
</style>
<input type="hidden" id="table_name" value="articles">
@stop