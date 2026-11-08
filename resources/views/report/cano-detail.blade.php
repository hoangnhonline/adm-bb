@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1 style="text-transform: uppercase;">
    <span style="color:#06b7a4 ">{{ $canoDetail->name ?? "Không xác định" }}</span> THÁNG {{ $month }}/{{ $year }}
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
          <form class="form-inline" role="form" method="GET" action="{{ route('report.cano-detail') }}" id="searchForm">
            <input type="hidden" name="cano_id" value="{{ $canoDetail->id ?? "" }}">
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
       
          <div class="table-responsive col-md-8">
            <!-- <p style="color: red; font-weight: bold">CHI PHÍ THÁNG {{ $month }}</p> -->
            <table class="table table-bordered table-hover table-list-data" width="600">
              <tr>                
                <th width="1%" class="text-center">Ngày</th>
                <th class="text-center">
                @if(in_array($cano_id, [9,10,11]))
                Số can xăng
                @else
                Số lần thuê
                @endif
                </th>
                <!-- <th class="text-center">Số lần chạy</th> -->
                <th class="text-center">Tour ghép</th>
                <th class="text-center">Tour riêng</th>
                <th class="text-center">Thuê cano</th>
                <th class="text-center">Tổng lần chạy</th>
              </tr>
              @php  $i = 0; @endphp
              @for($day = 1; $day <= $maxDay; $day++)
              @php $i++; 
              $key = str_pad($day, 2, "0", STR_PAD_LEFT);
              @endphp

              <tr data-day="{{ $year }}-{{ $month }}-{{ $key }}" class="cost" data-id="{{ $cano_id }}">
                
                <td class="text-center">
                  {{ $day }}
                </td>
                <td class="text-center">
                  @php
                  $cate_id = in_array($cano_id, [9,10,11]) ? 2 : 1;
                  @endphp
                  <a target="_blank" href="{{ route('cost.index', ['cate_id' => $cate_id, 'partner_id'=> $cano_id, 'use_date_from' => $key."/".$month.'/'.$year, 'time_type' => 3])}}">
                    {{ isset($arrCostByDay[$key]) ? $arrCostByDay[$key]: "" }}
                  </a>
                </td>                
                <td class="text-center">
                  <a target="_blank" href="{{ route('booking.index', ['tour_id' => 1, 'type' => 1, 'time_type' => 3, 'month' => $month , 'year' => $year, 'cano_id'=> $cano_id, 'tour_type[]' => 1, 'use_date_from' => $key."/".$month.'/'.$year])}}">
                    {{ isset($arrCanoCount[$key]) && isset($arrCanoCount[$key][1]) ? 1 : "" }}
                  </a>
                </td>
                <td class="text-center">
                  <a target="_blank" href="{{ route('booking.index', ['tour_id' => 1, 'type' => 1, 'time_type' => 3, 'month' => $month , 'year' => $year, 'cano_id'=> $cano_id, 'tour_type[]' => 2, 'use_date_from' => $key."/".$month.'/'.$year])}}">
                    {{ isset($arrCanoCount[$key]) && isset($arrCanoCount[$key][2]) ? 1 : "" }}
                  </a>
              </td>    
                <td class="text-center">
                <a target="_blank" href="{{ route('booking.index', ['tour_id' => 1, 'type' => 1, 'time_type' => 3, 'month' => $month , 'year' => $year, 'cano_id'=> $cano_id, 'tour_type[]' => 3, 'use_date_from' => $key."/".$month.'/'.$year])}}">
                {{ isset($arrCanoCount[$key]) && isset($arrCanoCount[$key][3]) ? 1 : "" }}
                </a>
              </td>                
                <td class="text-center">
                  <a target="_blank" href="{{ route('booking.index', ['tour_id' => 1, 'type' => 1, 'time_type' => 3, 'month' => $month , 'year' => $year, 'cano_id'=> $cano_id, 'use_date_from' => $key."/".$month.'/'.$year])}}">
                    {{ isset($arrCanoCount[$key]) ? count($arrCanoCount[$key]) : "" }}
                  </a>
                </td>
              </tr>
              @endfor
              <tr>
                
                <td class="text-center">
                  <strong>Tổng</strong>
                </td>
                <td class="text-center">
                    <strong style="color: red; font-size: 17px">{{ number_format($totalAmount) }}</strong>
                </td> 
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </table>
          </div>
        </div>
      </div>  
      <div class="box">
   
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
</style>
<input type="hidden" id="table_name" value="articles">

@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    $('tr.cost').each(function(){
      $.ajax({
        url : '{{ route('report.ajax-detail-cost')}}',
        type : 'GET',
        data: {
          id : $(this).data('id'),
          date_use : $(this).data('day')
        },
        success: function(data){
          
        }
      });
    });
      
  });
</script>
@stop