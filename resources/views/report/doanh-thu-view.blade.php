@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    THỐNG KÊ THÁNG {{ $month }}/{{ $year }}
  </h1>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
      
      <!-- Form Tìm kiếm -->
      <div class="panel panel-default">       
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('report.doanh-thu') }}" id="searchForm">
             <div class="form-group chon-thang">    
                <label for="month">THÁNG</label>          
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">--CHỌN--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>    
              <div class="form-group chon-thang">            
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--Năm--</option>
                  <option value="2023" {{ $year == 2023 ? "selected" : "" }}>2023</option>
                  <option value="2024" {{ $year == 2024 ? "selected" : "" }}>2024</option>
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                </select>
              </div>       
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button>
          </form>         
        </div>
      </div>      

      <!-- Bảng tổng kết Doanh thu theo Người thu tiền trong tháng -->
      @if(!empty($tong_nguoi_thu))
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">TỔNG DOANH THU THEO NGƯỜI THU TIỀN (THÁNG {{ $month }}/{{ $year }})</h3>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr style="background-color: #3c8dbc; color: #fff;">
                  <th>Thu tiền</th>
                  <th class="text-right">Tổng tiền</th>
                </tr>
              </thead>
              <tbody>
                @php $tong_all_nguoi_thu = 0; @endphp
                @foreach($tong_nguoi_thu as $nguoiThuId => $tongTien)
                <tr>
                  <td>
                    <strong>
                      {{ $nguoiThuId > 0 ? (isset($collecterNameArr[$nguoiThuId]) ? $collecterNameArr[$nguoiThuId] : 'Người thu #'.$nguoiThuId) : 'Chưa xác định / Khác' }}
                    </strong>
                  </td>
                  <td class="text-right" style="color: green; font-weight: bold;">
                    {{ number_format($tongTien) }}
                  </td>
                </tr>
                @php $tong_all_nguoi_thu += $tongTien; @endphp
                @endforeach
                <tr style="background-color: #f4f4f4; font-weight: bold;">
                  <td>TỔNG CỘNG</td>
                  <td class="text-right" style="color: red; font-size: 18px;">
                    {{ number_format($tong_all_nguoi_thu) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      <!-- Bảng chi tiết Doanh thu theo ngày -->
      <div class="box">       
        <div class="box-header with-border">
          <h3 class="box-title">CHI TIẾT THEO NGÀY</h3>
        </div>
        <div class="box-body">
          <div class="table-responsive">            
            <table class="table table-bordered table-hover table-list-data">
              <thead>
                <tr style="background-color: #06b7a4; color:#FFF">
                  <th class="text-center">Ngày</th>
                  <th class="text-right">Tổng tiền</th>                 
                  
                  <!-- Các cột người thu tiền (Nếu muốn hiển thị theo từng người) -->                  
                  @if(!empty($tong_nguoi_thu))
                    @foreach($tong_nguoi_thu as $nguoiThuId => $val)
                      <th class="text-right" style="background-color: #008d4c;">
                        {{ $nguoiThuId > 0 ? (isset($collecterNameArr[$nguoiThuId]) ? $collecterNameArr[$nguoiThuId] : 'Thu #'.$nguoiThuId) : 'Khác' }}
                      </th>
                    @endforeach
                  @endif
                </tr>
              </thead>
              <tbody>
                @php
                $tong_tien = 0;
                ksort($arrDay);
                @endphp
                
                @foreach($arrDay as $day => $arr)
                @php
                  $tong_tien += $arr['tong_tien'];
                @endphp
                <tr>
                  <th class="text-center">{{ $day }}</th>
                  <th class="text-right">
                    <a target="_blank" href="{{ route('booking.index', ['time_type' => 3, 'use_date_from' => $day.'/'.$month.'/'.$year]) }}">
                      {{ isset($arr['tong_tien']) ? number_format($arr['tong_tien']) : "-" }}
                    </a>
                  </th>                 

                  <!-- Số tiền từng người thu trong ngày -->
                  @if(!empty($tong_nguoi_thu))
                    @foreach($tong_nguoi_thu as $nguoiThuId => $val)
                      @php
                        $tienThuDaily = $arr['nguoi_thu'][$nguoiThuId] ?? 0;
                      @endphp
                      <td class="text-right">
                        {{ $tienThuDaily > 0 ? number_format($tienThuDaily) : '-' }}
                      </td>
                    @endforeach
                  @endif
                </tr>
                @endforeach            
                
                <!-- Dòng tổng cộng -->
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                  <td class="text-center">Tổng cộng</td>
                  <td class="text-right">
                    <span style="color:red; font-weight: bold; font-size:20px;">{{ number_format($tong_tien) }}</span>
                  </td>

                  @if(!empty($tong_nguoi_thu))
                    @foreach($tong_nguoi_thu as $nguoiThuId => $tongTien)
                      <td class="text-right" style="color: #008d4c; font-size:16px;">
                        {{ number_format($tongTien) }}
                      </td>
                    @endforeach
                  @endif
                </tr>  
              </tbody>
            </table>
          </div>
        </div>
      </div>      
    </div>
  </div> 
</section>
</div>

<style type="text/css">
  table a{
    color: #000;
    font-weight: bold;
  }
  .table-list-data td, .table-list-data th{
    border: 1px solid #000 !important;
    font-weight: bold;
    color: #000;
  }
</style>
<input type="hidden" id="table_name" value="articles">
@stop