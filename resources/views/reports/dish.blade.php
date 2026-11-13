@extends('layout')
@section('content')
@php
  $fmtQty = function($v){ return rtrim(rtrim(number_format((float) $v, 2), '0'), '.'); };
  $baseParams = ['branch_id' => $branch_id, 'group' => $group, 'sort' => $sort];
  $periodNames = ['week' => 'Tuần', 'month' => 'Tháng', 'year' => 'Năm', 'custom' => 'Tùy chọn'];
  $diffTotal = $prevSumMoney > 0 ? round(($sumMoney - $prevSumMoney) / $prevSumMoney * 100, 1) : null;
  $top = array_slice($reports, 0, 10);
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>Món bán chạy</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Món bán chạy</li>
  </ol>
</section>

<section class="content">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="btn-group" style="margin-bottom:10px">
        @foreach(['week', 'month', 'year'] as $p)
          <a href="{{ route('report.dish', array_merge($baseParams, ['period' => $p])) }}" class="btn btn-sm {{ $period == $p ? 'btn-primary' : 'btn-default' }}">{{ $periodNames[$p] }}</a>
        @endforeach
        <a href="#" class="btn btn-sm {{ $period == 'custom' ? 'btn-primary' : 'btn-default' }}" onclick="$('#customRange').toggle();return false;">Tùy chọn</a>
      </div>

      <form method="GET" action="{{ route('report.dish') }}" class="form-inline" id="filterForm">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="date" value="{{ $from }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <span id="customRange" style="{{ $period == 'custom' ? '' : 'display:none' }}">
          <input type="date" class="form-control input-sm" name="from" value="{{ $from }}">
          <input type="date" class="form-control input-sm" name="to" value="{{ $to }}">
          <button type="submit" class="btn btn-sm btn-primary" onclick="this.form.period.value='custom'">Xem</button>
          &nbsp;
        </span>
        <select name="branch_id" class="form-control input-sm" onchange="this.form.submit()">
          <option value="">Tất cả chi nhánh</option>
          @foreach($branchList as $br)
            <option value="{{ $br->id }}" {{ $branch_id == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
          @endforeach
        </select>
        <select name="group" class="form-control input-sm" onchange="this.form.submit()">
          <option value="all" {{ $group == 'all' ? 'selected' : '' }}>Tất cả nhóm món</option>
          <option value="main" {{ $group == 'main' ? 'selected' : '' }}>Món chính</option>
          <option value="bread" {{ $group == 'bread' ? 'selected' : '' }}>Bánh</option>
          <option value="drink" {{ $group == 'drink' ? 'selected' : '' }}>Đồ uống</option>
        </select>
      </form>

      <h3 style="margin:15px 0 5px">
        @if($prevAnchor)
          <a href="{{ route('report.dish', array_merge($baseParams, ['period' => $period, 'date' => $prevAnchor])) }}" class="btn btn-default btn-sm" title="Kỳ trước"><i class="fa fa-chevron-left"></i></a>
        @endif
        {{ $label }}
        @if($nextAnchor)
          <a href="{{ route('report.dish', array_merge($baseParams, ['period' => $period, 'date' => $nextAnchor])) }}" class="btn btn-default btn-sm" title="Kỳ sau"><i class="fa fa-chevron-right"></i></a>
        @endif
      </h3>
      <div class="text-muted">So với kỳ trước: {{ date('d/m/Y', strtotime($prevFrom)) }} - {{ date('d/m/Y', strtotime($prevTo)) }}. Chỉ tính bill hợp lệ (bỏ bill hủy), gộp các món trùng tên.</div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-money"></i></span>
        <div class="info-box-content"><span class="info-box-text">Tổng doanh thu món</span>
          <span class="info-box-number">{{ number_format($sumMoney) }}</span>
          @if($diffTotal !== null)
            <span class="{{ $diffTotal >= 0 ? 'text-green' : 'text-red' }}">{{ $diffTotal >= 0 ? '▲' : '▼' }} {{ abs($diffTotal) }}% so với kỳ trước</span>
          @endif
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-cutlery"></i></span>
        <div class="info-box-content"><span class="info-box-text">Món chính / khác</span><span class="info-box-number">{{ number_format($totalOther) }}</span></div></div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box"><span class="info-box-icon bg-blue"><i class="fa fa-circle-o"></i></span>
        <div class="info-box-content"><span class="info-box-text">Bánh</span><span class="info-box-number">{{ number_format($totalBread) }}</span></div></div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-glass"></i></span>
        <div class="info-box-content"><span class="info-box-text">Đồ uống</span><span class="info-box-number">{{ number_format($totalDrink) }}</span></div></div>
    </div>
  </div>

  <div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">Top 10 theo {{ $sort == 'money' ? 'doanh thu' : 'số lượng' }}</h3></div>
    <div class="box-body">
      @if(count($top))
        <div id="topChart" style="height:{{ 60 + count($top) * 32 }}px"></div>
      @else
        <p class="text-muted">Không có dữ liệu trong kỳ này.</p>
      @endif
    </div>
  </div>

  <div class="box box-default">
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-striped table-hover">
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th>Tên món</th>
            <th>Nhóm</th>
            <th class="text-right"><a href="{{ route('report.dish', array_merge($baseParams, ['period' => $period, 'date' => $from, 'from' => $from, 'to' => $to, 'sort' => 'qty'])) }}">Số lượng {!! $sort == 'qty' ? '▼' : '' !!}</a></th>
            <th class="text-right"><a href="{{ route('report.dish', array_merge($baseParams, ['period' => $period, 'date' => $from, 'from' => $from, 'to' => $to, 'sort' => 'money'])) }}">Doanh thu {!! $sort == 'money' ? '▼' : '' !!}</a></th>
            <th class="text-right">% doanh thu</th>
            <th class="text-right">SL kỳ trước</th>
            <th class="text-right">Tăng/giảm SL</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reports as $i => $r)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><strong>{{ $r['name'] }}</strong></td>
              <td>{{ $r['type'] }}</td>
              <td class="text-right">{{ $fmtQty($r['qty']) }}</td>
              <td class="text-right">{{ number_format($r['money']) }}</td>
              <td class="text-right">{{ $sumMoney > 0 ? number_format($r['money'] / $sumMoney * 100, 1) : 0 }}%</td>
              <td class="text-right">{{ $fmtQty($r['prev_qty']) }}</td>
              <td class="text-right">
                @if($r['diff_pct'] === null)
                  <span class="label label-info">Mới</span>
                @else
                  <span class="{{ $r['diff_pct'] >= 0 ? 'text-green' : 'text-red' }}">{{ $r['diff_pct'] >= 0 ? '▲' : '▼' }} {{ abs($r['diff_pct']) }}%</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">Không có dữ liệu</td></tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr style="font-weight:bold">
            <td colspan="3">TỔNG CỘNG</td>
            <td class="text-right">{{ $fmtQty($sumQty) }}</td>
            <td class="text-right">{{ number_format($sumMoney) }}</td>
            <td class="text-right">100%</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</section>
</div>
@stop

@section('js')
@if(count($top))
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
(function(){
  var data = @json($top);
  var byMoney = {{ $sort == 'money' ? 'true' : 'false' }};
  Highcharts.chart('topChart', {
    chart: { type: 'bar' },
    title: { text: null },
    credits: { enabled: false },
    legend: { enabled: false },
    xAxis: { categories: data.map(function(r){ return r.name; }), title: { text: null } },
    yAxis: { title: { text: byMoney ? 'Doanh thu (đ)' : 'Số lượng' } },
    tooltip: {
      formatter: function(){
        var r = data[this.point.index];
        return '<b>' + r.name + '</b><br>SL: ' + Highcharts.numberFormat(r.qty, 0, ',', '.') +
               '<br>Doanh thu: ' + Highcharts.numberFormat(r.money, 0, ',', '.') + ' đ';
      }
    },
    plotOptions: { bar: { dataLabels: { enabled: true, formatter: function(){ return Highcharts.numberFormat(this.y, 0, ',', '.'); } } } },
    series: [{ name: byMoney ? 'Doanh thu' : 'Số lượng', color: '#3c8dbc',
               data: data.map(function(r){ return byMoney ? r.money : r.qty; }) }]
  });
})();
</script>
@endif
@stop
