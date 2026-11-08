@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    CANO THÁNG {{ $month }}/{{ $year }}
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
          <form class="form-inline" role="form" method="GET" action="{{ route('report.cano') }}" id="searchForm">
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
                  <option value="2020" {{ $year == 2020 ? "selected" : "" }}>2020</option>
                  <option value="2021" {{ $year == 2021 ? "selected" : "" }}>2021</option>
                  <option value="2022" {{ $year == 2022 ? "selected" : "" }}>2022</option>
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
            <!-- <p style="color: red; font-weight: bold">CHI PHÍ THÁNG {{ $month }}</p> -->
            <table class="table table-bordered table-hover table-list-data">
              <tr>
                <th width="1%" class="text-center">STT</th>
                <th>Tên cano</th>
                <th class="text-center">Số lần thực tế</th>
                <th class="text-center">Số lần chi phí</th>
                <th class="text-right">Số tiền chi phí</th>
              </tr>
              @php  $i = 0; @endphp
              @foreach($countCano as $cano_id => $solanchay)
              @php $i++; @endphp
              <tr @if(in_array($cano_id, $arrCanoCty)) style="background-color: #e8c45f" @endif>
                <td class="text-center">{{ $i }}</td>
                <td class="text-left">
                  <a href="{{ route('report.cano-detail', ['cano_id' => $cano_id, 'month' => $month, 'year' => $year])}}" target="_blank" >
                  {{ isset($partnerArr[$cano_id]) ? $partnerArr[$cano_id] : "Không xác định" }}
                </a>
                </td>
                <td class="text-center">
                  <a target="_blank" href="{{ route('booking.index', ['tour_id' => 1, 'type' => 1, 'time_type' => 1, 'month' => $month , 'year' => $year, 'cano_id'=> $cano_id])}}">
                  {{ $solanchay }}
                </a>
                </td>
                <?php 
                if(in_array($cano_id, [9,10,11])){
                  $cost_type_id = 2;
                }else{
                  $cost_type_id = 1;
                }
                ?>
                <td class="text-center">
                  <a target="_blank" href="{{ route('cost.index', ['cate_id' => $cost_type_id, 'partner_id'=> $cano_id, 'use_date_from' => "01/".$month.'/'.$year, 'use_date_to' => $maxDay."/".$month.'/'.$year])}}">{{ isset($arrCost[$cano_id]) ? number_format($arrCost[$cano_id]['amount']) : "" }}</a>
                </td>
                <td class="text-right">
                  <a target="_blank" href="{{ route('cost.index', ['cate_id' => $cost_type_id, 'partner_id'=> $cano_id, 'use_date_from' => "01/".$month.'/'.$year, 'use_date_to' => $maxDay."/".$month.'/'.$year])}}">
                  {{ isset($arrCost[$cano_id]) ? number_format($arrCost[$cano_id]['total_money']) : "" }}
                  </a>
                </td>
              </tr>
              @endforeach
            </table>
          </div>
        </div>
      </div>  
      <div class="box">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <tr>
              <th class="text-center" width="1%">STT</th>
              <th class="text-center">Ngày</th>
              <th>Loại tour</th>
              <th>Cano</th>
              <th>HDV</th>
              <th class="text-center">NL</th>
              <th class="text-center">TE</th>
              <th class="text-center">ĂN NL</th>
              <th class="text-center">ĂN TE</th>
              <th class="text-center">CÁP NL</th>
              <th class="text-center">CÁP TE</th>
            </tr>
            @php $i = 0;@endphp
            @foreach($arrCanoCount as $day => $arrTourType)
            @foreach($arrTourType as $tour_type => $arrCanoId)
            @foreach($arrCanoId as $cano_id => $arr)
            @php $i++; 
            if($tour_type == 2){
                $class = "vip";
              }elseif($tour_type == 3){
              $class = "thue-cano";
            }else{
            $class = "ghep";
          }
            @endphp
            <tr class="{{ $class }}" @if(!isset($userArr[$arr['hdv_id']])) style="background-color:red; font-weight: bold; color:#FFF" @endif>
              <td class="text-center">{{ $i }}</td>
              <td class="text-center">{{ $day }}</td>
              <td>{{ $tour_type == 1 ? "TOUR GHÉP" : ($tour_type == 2 ? "TOUR VIP" : "THUÊ CANO") }}</td>
              <td>{{ isset($partnerArr[$cano_id]) ? $partnerArr[$cano_id] : "Không xác định" }}</td>
              <td>{{ isset($userArr[$arr['hdv_id']]) ? $userArr[$arr['hdv_id']] : "Không xác định" }}</td>
              <td class="text-center">{{ $arr['adults'] }}</td>
              <td class="text-center">{{ $arr['childs'] > 0 ? $arr['childs'] : '' }}</td>
              <td class="text-center">{{ $arr['meals'] > 0 ? $arr['meals'] : '' }}</td>
              <td class="text-center">{{ $arr['meals_te'] > 0 ? $arr['meals_te'] : '' }}</td>
              <td class="text-center">{{ $arr['cap_nl'] > 0 ? $arr['cap_nl'] : '' }}</td>
              <td class="text-center">{{ $arr['cap_te'] > 0 ? $arr['cap_te'] : '' }}</td>
            </tr>
            @endforeach
            @endforeach
            @endforeach
          </table>
          </div>
      </div>
      <!-- /.box -->     
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