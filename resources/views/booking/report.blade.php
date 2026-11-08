@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    REPORT
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
    
      <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
          <div class="table-responsive">
            <h3>TẤT CẢ</h3>            
            <table class="table table-bordered table-hover table-list-data">
              <tr style="background-color: #d2d6de; color: #002795">
                <th width="3%" class="text-center">STT</th>
                <th width="7%" class="text-center">Ngày</th>
                <th width="10%" class="text-center">Đối tác</th>
                <th width="15%" class="text-center">HDV</th>
                <th width="7%" class="text-center">Số DV</th>
                <th width="20%" class="text-right">Tổng DT</th>
                <th width="17%" class="text-right">CK</th>
                <th width="20%" class="text-right">DT sau CK</th>
              </tr>
              @php  $i = 0; 
              $tong_dich_vu = $tong_doanh_thu = $tong_chiet_khau = $tong_sau_chiet_khau = 0;

              @endphp
              @foreach($contents as $arr)
              @php $i++; 
              $tong_dich_vu += $arr['so_dich_vu'];
              $tong_doanh_thu += $arr['tong_doanh_thu'];
              $tong_chiet_khau += $arr['chiet_khau'];
              $tong_sau_chiet_khau += $arr['sau_chiet_khau'];              
              @endphp
              <tr>
                <td class="text-center">{{ $i }}</td>
                <td class="text-center">
                  {{ $arr['ngay'] }}
                </a>
                </td>
                <td class="text-center">
                  {{ $arr['doi_tac'] }}
                </td>                
                <td class="text-center">
                  {{ $arr['hdv'] }}
                </td>
                <td class="text-center">
                  {{ $arr['so_dich_vu'] }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['tong_doanh_thu']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['chiet_khau']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['sau_chiet_khau']) }}
                </td>
               
              </tr>
              @endforeach
              <tr >
                <td colspan="4" class="text-right" style="font-size: 16px; font-weight: bold; color: #0E8172 !important;">Tổng</td>
                <td class="text-center" style="background-color: #0E8172; color: #fff">{{ $tong_dich_vu }}</td>
                <td class="text-right"  style="background-color: #0E8172; color: #fff">{{ number_format($tong_doanh_thu) }}</td>
                <td class="text-right" style="background-color: #0E8172; color: #fff">{{ number_format($tong_chiet_khau) }}</td>
                <td class="text-right" style="background-color: #0E8172; color: #fff">{{ number_format($tong_sau_chiet_khau) }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
          <div class="table-responsive">
            <h3>JOHN'S TOURS</h3>            
            <table class="table table-bordered table-hover table-list-data">
              <tr style="background-color: #d2d6de; color: #002795">
                <th width="3%" class="text-center">STT</th>
                <th width="7%" class="text-center">Ngày</th>
                <th width="10%" class="text-center">Đối tác</th>
                <th width="15%" class="text-center">HDV + ghi chú</th>
                <th width="7%" class="text-center">Số dịch vụ</th>
                <th width="20%" class="text-right">Tổng doanh thu</th>
                <th width="17%" class="text-right">Discount rate / Discount (%)</th>
                <th width="20%" class="text-right">DT sau chiết khấu</th>
              </tr>
              @php  $i = 0; 
              $tong_dich_vu = $tong_doanh_thu = $tong_chiet_khau = $tong_sau_chiet_khau = 0;

              @endphp
              @foreach($contentsJohn as $arr)
              @php $i++; 
              $tong_dich_vu += $arr['so_dich_vu'];
              $tong_doanh_thu += $arr['tong_doanh_thu'];
              $tong_chiet_khau += $arr['chiet_khau'];
              $tong_sau_chiet_khau += $arr['sau_chiet_khau'];              
              @endphp
              <tr>
                <td class="text-center">{{ $i }}</td>
                <td class="text-left">
                  {{ $arr['ngay'] }}
                </a>
                </td>
                <td class="text-center">
                  {{ $arr['doi_tac'] }}
                </td>                
                <td class="text-center">
                  {{ $arr['hdv'] }}
                </td>
                <td class="text-center">
                  {{ $arr['so_dich_vu'] }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['tong_doanh_thu']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['chiet_khau']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['sau_chiet_khau']) }}
                </td>
               
              </tr>
              @endforeach
              <tr style="font-size: 17px; font-weight: bold;">
                 <td colspan="4" class="text-right" style="font-size: 16px; font-weight: bold; color: #0E8172 !important;">Tổng</td>
                <td class="text-center"  style="background-color: #0E8172; color: #fff">{{ $tong_dich_vu }}</td>
                <td class="text-right"  style="background-color: #0E8172; color: #fff">{{ number_format($tong_doanh_thu) }}</td>
                <td class="text-right"  style="background-color: #0E8172; color: #fff">{{ number_format($tong_chiet_khau) }}</td>
                <td class="text-right" style="background-color: #0E8172; color: #fff">{{ number_format($tong_sau_chiet_khau) }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div> 
      <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
          <div class="table-responsive">
            <h3>ĐỐI TÁC KHÁC</h3>            
            <table class="table table-bordered table-hover table-list-data">
              <tr style="background-color: #d2d6de; color: #002795">
                <th width="3%" class="text-center">STT</th>
                <th width="7%" class="text-center">Ngày</th>
                <th width="10%" class="text-center">Đối tác</th>
                <th width="15%" class="text-center">HDV + ghi chú</th>
                <th width="7%" class="text-center">Số dịch vụ</th>
                <th width="20%" class="text-right">Tổng doanh thu</th>
                <th width="17%" class="text-right">Discount rate / Discount (%)</th>
                <th width="20%" class="text-right">DT sau chiết khấu</th>
              </tr>
              @php  $i = 0; 
              $tong_dich_vu = $tong_doanh_thu = $tong_chiet_khau = $tong_sau_chiet_khau = 0;

              @endphp
              @foreach($contentsOther as $arr)
              @php $i++; 
              $tong_dich_vu += $arr['so_dich_vu'];
              $tong_doanh_thu += $arr['tong_doanh_thu'];
              $tong_chiet_khau += $arr['chiet_khau'];
              $tong_sau_chiet_khau += $arr['sau_chiet_khau'];              
              @endphp
              <tr>
                <td class="text-center">{{ $i }}</td>
                <td class="text-left">
                  {{ $arr['ngay'] }}
                </a>
                </td>
                <td class="text-center">
                  {{ $arr['doi_tac'] }}
                </td>                
                <td class="text-center">
                  {{ $arr['hdv'] }}
                </td>
                <td class="text-center">
                  {{ $arr['so_dich_vu'] }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['tong_doanh_thu']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['chiet_khau']) }}
                </td>
                <td class="text-right">
                  {{ number_format($arr['sau_chiet_khau']) }}
                </td>
               
              </tr>
              @endforeach
              <tr style="font-size: 17px; font-weight: bold;">
                 <td colspan="4" class="text-right" style="font-size: 16px; font-weight: bold; color: #0E8172 !important;">Tổng</td>
                <td class="text-center" style="background-color: #0E8172; color: #fff">{{ $tong_dich_vu }}</td>
                <td class="text-right"  style="background-color: #0E8172; color: #fff">{{ number_format($tong_doanh_thu) }}</td>
                <td class="text-right" style="background-color: #0E8172; color: #fff">{{ number_format($tong_chiet_khau) }}</td>
                <td class="text-right"  style="background-color: #0E8172; color: #fff">{{ number_format($tong_sau_chiet_khau) }}</td>
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
	.table-list-data td, .table-list-data th {
    border: 1px solid #ede3e3 !important;
    font-weight: bold;
    color: #000;
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