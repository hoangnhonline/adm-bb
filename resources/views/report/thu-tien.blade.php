@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    REPORT {{ $month }}/{{ $year }}
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
          <form class="form-inline" role="form" method="GET" action="{{ route('report.thu-tien') }}" id="searchForm">
             <div class="form-group  chon-thang">      
                <label for="month">THÁNG</label>          
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">--CHỌN--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>    
              <div class="form-group  chon-thang">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--Năm--</option>                 
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                </select>
              </div>       
            <button type="submit" class="btn btn-info btn-sm">Search</button>
          </form>         
        </div>
      </div>      
      
      <div class="box">
      <div class="box-body">
      <table class="table table-bordered table-hover" >
       
            <tr style="background-color:blue; color:#fff">
            <td>
                Total Revenue
              </td>
              <td class="text-right">
                {{ number_format($tong_thuc_thu) }}
              </td>
            </tr>
            @foreach($collecterNameArr as $id_nguoi_thu => $nguoi_thu)
            <tr>
              <td>{{ $nguoi_thu }}</td>
              <td class="text-right">{{ isset($arrNguoiThu[$id_nguoi_thu]) ? number_format($arrNguoiThu[$id_nguoi_thu]) : $id_nguoi_thu }}</td>
            </tr>
            @endforeach
          </table>
          </div>
        </div>
        <div class="box">
      <div class="box-body">
      <table class="table table-bordered table-hover" >
       
            <tr style="background-color:red; color:#fff">
            <td>
                Total Cost
              </td>
              <td class="text-right">
                {{ number_format($tong_chi) }}
              </td>
            </tr>
            @foreach($collecterNameArr as $id_nguoi_chi => $nguoi_chi)
            @if(isset($totalChi[$id_nguoi_chi]))
            <tr>
              <td>{{ $nguoi_chi }}</td>
              <td class="text-right">{{ number_format($totalChi[$id_nguoi_chi]) }}</td>
            </tr>
            @endif
            @endforeach
          </table>
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
                
                <th class="text-right">Tổng cọc</th>
                <th class="text-right">Tổng giảm</th>
                <th class="text-right">Tổng chiết khấu</th>
                <th class="text-right">Chi 5%</th>
                <th class="text-right">CP vận hành</th>
                <th class="text-right">Cost khác</th>
                <th class="text-right">Tổng chi phí</th>
                <th class="text-right">Tổng còn lại</th>
            
              </tr>
              @php
              $tong_chi_phi_theo_ngay  = 0;
              $tong_con_lai = 0;
              ksort($arrCost);
              @endphp
              
              @foreach($arrDay as $day => $arr)

              <?php 
              $chi_phi = isset($arrCost[$day]['total']) ? $arrCost[$day]['total'] : 0;
              ?>
              <tr>
                <th class="text-center">{{ $day }}</th>
                <th class="text-right"><a target="_blank" href="{{ route('booking.index', ['time_type' => 3, 'use_date_from' => $day.'/'.$month.'/'.$year]) }}">{{ isset($arrDay[$day]['tong_tien']) ? number_format($arrDay[$day]['tong_tien']) : "-" }}</a></th>
                
                <th class="text-right">{{ isset($arrDay[$day]['tong_coc']) ? number_format($arrDay[$day]['tong_coc']) : "-" }}</th>
                <th class="text-right">{{ isset($arrDay[$day]['tong_giam']) ? number_format($arrDay[$day]['tong_giam']) : "-" }}</th>
                <th class="text-right">{{ isset($arrDay[$day]['tong_chiet_khau']) ? number_format($arrDay[$day]['tong_chiet_khau']) : "-" }}</th>
                <th class="text-right">{{ isset($arrDay[$day]['tong_chi_5']) ? number_format($arrDay[$day]['tong_chi_5']) : "-" }}</th>
                
                <th class="text-right"><a target="_blank" href="{{ route('cost.index', ['type' => 1,'time_type' => 3, 'use_date_from' => $day.'/'.$month.'/'.$year]) }}">{{ isset($arrCost[$day]['tong_chi_phi_van_hanh']) ? number_format($arrCost[$day]['tong_chi_phi_van_hanh']) : "-" }}</a></th>
                <th class="text-right"><a target="_blank" href="{{ route('cost.index', ['type' => 2,'time_type' => 3, 'use_date_from' => $day.'/'.$month.'/'.$year]) }}">{{ isset($arrCost[$day]['tong_chi_phi_khac']) ? number_format($arrCost[$day]['tong_chi_phi_khac']) : "-" }}</a></th>
                <th class="text-right"><a target="_blank" href="{{ route('cost.index', ['time_type' => 3, 'use_date_from' => $day.'/'.$month.'/'.$year]) }}">{{ isset($arrCost[$day]['total']) ? number_format($arrCost[$day]['total']) : "-" }}</a></th>
                <th class="text-right">
                  <?php 
                  $con_lai = isset($arrDay[$day]['tong_con_lai_chua_tru_chi_phi']) ? ($arrDay[$day]['tong_con_lai_chua_tru_chi_phi'] - $chi_phi) : 0;
                  ?>
                  {{  number_format($con_lai) }}

                </th>        
                <?php 
                $tong_con_lai += $con_lai;
                ?>                     
              </tr>
             
              @endforeach            
              <tr>
                <td colspan="9" class="text-right">Tổng lợi nhuận</td>
                <td  class="text-right"><span style="color:red; font-weight: bold;font-size:24px;">{{ number_format($tong_con_lai) }}</span></td>
              </tr>  
            </table>
          </div>
        </div>
      </div>        
      <!-- /.box -->  
        <div class="box">       
        <!-- /.box-header -->
        <div class="box-body">
          <div class="table-responsive">
            <p style="color: red; font-weight: bold">CHI PHÍ THÁNG {{ $month }}</p>
            <table class="table table-bordered table-hover table-list-data">
              <tr style="background-color: #06b7a4;color:#FFF">
                <th class="text-center">Ngày</th>
                @foreach($cateList as $cate)
                <th class="text-right">
                  {{ $cate->name }}
                </th>
                @endforeach
                <th class="text-right" style="background-color: red">CP vận hành</th>
                <th class="text-right" style="background-color: yellow">Cost khác</th>
                <th class="text-right">Tổng</th>
              </tr>
              @php
              $tong_chi_phi_theo_ngay  = 0;ksort($arrCost);
              @endphp
              
              @foreach($arrCost as $day => $arr)
              <?php ?>
              <tr>
                <th class="text-center">{{ $day }}</th>
                @foreach($cateList as $cate)
                <?php 
                $chi_phi = isset($arr[$cate->id]) ? $arr[$cate->id]['total'] : 0;             
                ?>
                <td class="text-right">
                  <a target="_blank" href="{{ route('cost.index', ['time_type' => 3,'cate_id'=> $cate->id, 'use_date_from' => $day."/".$month.'/'.$year])}}">
                  {{ $chi_phi > 0 ? number_format($chi_phi) : '' }}
                  </a>
                </td>
                @endforeach                
                <td class="text-right">{{ isset($arrCost[$day]) ? number_format($arrCost[$day]['tong_chi_phi_van_hanh']) : "" }}</td>
                <td class="text-right">{{ isset($arrCost[$day]) ? number_format($arrCost[$day]['tong_chi_phi_khac']) : "" }}</td>
                <th class="text-right">
                  {{ isset($arrCost[$day]) ? number_format($arrCost[$day]['total']) : "" }}
                </th>

              </tr>

              @php
              $tong_chi_phi_theo_ngay += isset($arrCost[$day]) ? $arrCost[$day]['total'] : 0;
              @endphp
              @endforeach
              <tr>
                <th colspan="{{ $cateList->count() + 1 }}" class="text-right"><h4>Tổng chi phí</h4></th>
                <td class="text-right">{{ number_format($tong_chi_phi_van_hanh) }}</td>
                <td class="text-right">{{ number_format($tong_chi_phi_khac) }}</td>
                <td class="text-right" >
                  <h4 style="color: red; font-weight: bold;">{{ number_format($tong_chi_phi_theo_ngay) }}</h4>

                </td>
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