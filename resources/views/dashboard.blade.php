@extends('layout') @section('content')
<div class="content-wrapper" style="min-height: 926px;">
    <!-- Content Header (Page header) -->

    <!-- Main content -->
    <section class="content" style="padding-top: 50px;">
        <!-- /.row -->

        <div class="row">
            <div class="col-md-12" id="content_alert"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-superpowers"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">TOUR HÔM NAY</span>
                        <a href="{{ route('booking.index', ['type' => 1]) }}">
                            <span class="info-box-number">{{ count($allTour) }}</span>
                        </a>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            @if(Auth::user()->is_limit == 0)
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-building-o"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">BOOKING KS CHECK-IN HÔM NAY</span>
                        <a href="{{ route('booking-hotel.index', ['checkin_from' => date('d/m/Y')]) }}">
                            <span class="info-box-number">{{ count($allHotel) }}</span>
                        </a>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            @endif
            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>
            @if(Auth::user()->is_limit == 0)
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-ticket"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">BOOKING VÉ GIAO HÔM NAY</span>
                        <a href="{{ route('booking-ticket.index') }}">
                            <span class="info-box-number">{{ count($allTicket) }}</span>
                        </a>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-cab"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">ĐẶT XE HÔM NAY</span>
                        <a href="{{ route('booking-car.index') }}">
                            <span class="info-box-number">{{ count($allCar) }}</span>
                        </a>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-pencil-square-o"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">QUẢN LÝ CÔNG VIỆC</span>
                        <a href="{{ route('task-detail.index') }}">
                            <span class="info-box-number">{{ $taskCount }}</span>
                        </a>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>  
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa-camera fa"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">CHỤP GRAND WORLD</span>
                        <a href="{{ route('task-detail.index') }}">
                            <span class="info-box-number">{{ $totalGrand }}</span>
                        </a>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            @if(Auth::user()->role < 3)
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-user"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">QUẢN LÝ NHÂN VIÊN</span>
                        <a href="{{ route('staff.index') }}">
                            <span class="info-box-number">{{ $nvCount }}</span>
                        </a>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            @endif
            @endif
            <!-- /.col -->
        </div>
    </section>
    <!-- /.content -->
    <section class="content">
  <div class="row">
    <div class="col-md-12">
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif     
      <div class="panel panel-default">       
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('dashboard') }}" id="searchForm">
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
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button>
          </form>         
        </div>
      </div>
      <div class="box">
        <!-- /.box-header -->
        <div class="box-body">          
          <div class="table-responsive">
            <!-- <p style="color: red; font-weight: bold">CHI PHÍ THÁNG {{ $month }}</p> -->
            <table class="table table-hover">
                <thead>
              <tr>
                <th width="1%" class="text-center">STT</th>
                <th class="text-center">Ngày</th>
                <th class="text-center">Phần ăn</th>
                <th class="text-center">Khách ghép</th>
                <th class="text-center">VIP</th>
                <th class="text-center">Thuê cano</th>
              </tr>
              </thead>
              <tbody>
              @php 
              $i = 0;
              @endphp
               @foreach($arrResult as $day => $arr)
               @php $i++; @endphp
              <tr>
                <td class="text-center">{{ $i }}</td>
               
                <td  class="text-center">
                    {{ $day }}
                </td>
                <td  class="text-center">
                    {{ $arr['meals'] }}
                </td>
                <td class="text-center">
                     {{ isset($arr[1]) ? $arr[1] : "" }}
                </td>                
                <td class="text-center">
                    {{ $arr[2] ?? "" }}
                </td>
                <td class="text-center">
                    {{ $arr[3] ?? "" }}
                </td>
              </tr>
               @endforeach
                </tbody>
            </table>
          </div>
        </div>
      </div>          
    </div>
    <!-- /.col -->  
  </div> 
</section>
</div>
@stop 
@section('js') 

@stop
