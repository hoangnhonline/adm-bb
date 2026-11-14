@extends('layout')
@section('content')
@php
  $nf = function($v){ return number_format((float) $v); };
  $chg = function($cur, $prev){
    if(!$prev) return '';
    $p = ($cur - $prev) / $prev * 100;
    return '<span class="'.($p >= 0 ? 'text-green' : 'text-red').'">'.($p >= 0 ? '▲' : '▼').' '.number_format(abs($p), 1).'%</span>';
  };
  $pct = function($v){ return $v === null ? '–' : number_format($v, 1).'%'; };
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Tổng quan') }} <small>{{ date('d/m/Y H:i') }}</small></h1>
  <form method="GET" action="{{ route('tong-quan.index') }}" class="form-inline" style="position:absolute;right:15px;top:12px">
    <select name="branch_id" class="form-control input-sm" onchange="this.form.submit()">
      <option value="">{{ __('Tất cả chi nhánh') }}</option>
      @foreach($branchList as $br)<option value="{{ $br->id }}" {{ $branchId == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>@endforeach
    </select>
  </form>
</section>
<section class="content">
  {{-- Doanh thu --}}
  <div class="row">
    <div class="col-md-3 col-sm-6"><div class="small-box bg-aqua"><div class="inner">
      <h3 style="font-size:26px">{{ $nf($kpi['today']['net']) }}</h3>
      <p>{{ __('Hôm nay (đến giờ)') }} · {{ $kpi['today']['bills'] }} {{ __('bill') }}</p></div>
      <div class="icon"><i class="fa fa-clock-o"></i></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="small-box bg-green"><div class="inner">
      <h3 style="font-size:26px">{{ $nf($kpi['yesterday']['net']) }}</h3>
      <p>{{ __('Hôm qua') }} · {{ $kpi['yesterday']['bills'] }} {{ __('bill') }} · {!! $chg($kpi['yesterday']['net'], $kpi['same_day_last_week']['net']) !!} <small style="display:inline">{{ __('so với cùng thứ tuần trước') }}</small></p></div>
      <div class="icon"><i class="fa fa-calendar"></i></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="small-box bg-blue"><div class="inner">
      <h3 style="font-size:26px">{{ $nf($kpi['month']['net']) }}</h3>
      <p>{{ __('Tháng này') }} · {{ $kpi['month']['bills'] }} {{ __('bill') }} · {!! $chg($kpi['month']['net'], $kpi['prev_month_same']['net']) !!} <small style="display:inline">{{ __('so với cùng kỳ tháng trước') }}</small></p></div>
      <div class="icon"><i class="fa fa-line-chart"></i></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="small-box bg-purple"><div class="inner">
      <h3 style="font-size:26px">{{ $nf($kpi['month']['avg']) }}</h3>
      <p>{{ __('Trung bình mỗi bill (tháng này)') }} · {!! $chg($kpi['month']['avg'], $kpi['prev_month_same']['avg']) !!}</p></div>
      <div class="icon"><i class="fa fa-file-text-o"></i></div></div></div>
  </div>

  <div class="row">
    {{-- Cần chú ý --}}
    <div class="col-md-4">
      <div class="box box-danger">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bell"></i> {{ __('Cần chú ý') }}</h3></div>
        <ul class="list-group" style="margin:0">
          @if(isset($attention['anomaly']))
          <a href="{{ route('bill-anomaly.index', ['branch_id' => $branchId]) }}" class="list-group-item">
            <span class="badge {{ $attention['anomaly']['total'] ? 'bg-red' : '' }}">{{ $attention['anomaly']['total'] }}</span>
            <i class="fa fa-exclamation-circle text-red"></i> {{ __('Bill bất thường chưa xem (7 ngày)') }}</a>
          @endif
          @if(isset($attention['purchase']))
          <a href="{{ route('cost.canh-bao-nhap', ['branch_ids' => $branchId ? [$branchId] : null]) }}" class="list-group-item">
            <span class="badge {{ $attention['purchase'] ? 'bg-orange' : '' }}">{{ $attention['purchase'] }}</span>
            <i class="fa fa-shopping-cart text-orange"></i> {{ __('Nguyên liệu nhập tăng bất thường (tháng này)') }}</a>
          @endif
          @if(isset($attention['overdue']))
          <a href="{{ route('doi-tac.report') }}" class="list-group-item">
            <span class="badge {{ $attention['overdue'] ? 'bg-red' : '' }}">{{ $nf($attention['overdue']) }}</span>
            <i class="fa fa-handshake-o text-red"></i> {{ __('Nợ đối tác quá hạn') }}</a>
          @endif
          @if(isset($attention['inbox']))
          <a href="{{ route('cost.bill-upload') }}" class="list-group-item">
            <span class="badge {{ $attention['inbox'] ? 'bg-yellow' : '' }}">{{ $attention['inbox'] }}</span>
            <i class="fa fa-picture-o text-yellow"></i> {{ __('Ảnh bill chi phí đang chờ nhập') }}</a>
          @endif
        </ul>
      </div>

      {{-- Top món --}}
      <div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-trophy"></i> {{ __('Top 5 món tuần này') }}</h3>
          <div class="box-tools"><a href="{{ route('report.dish', ['period' => 'week', 'branch_id' => $branchId]) }}" class="btn btn-box-tool">{{ __('Xem') }}</a></div></div>
        <table class="table table-condensed" style="margin:0">
          @forelse($topDishes as $i => $d)
          <tr><td>{{ $i + 1 }}. {{ $d->name }}</td><td class="text-right">{{ $d->qty + 0 }}</td><td class="text-right">{{ $nf($d->money) }}</td></tr>
          @empty <tr><td class="text-muted text-center">{{ __('Không có dữ liệu') }}</td></tr> @endforelse
        </table>
      </div>
    </div>

    {{-- Biểu đồ 30 ngày --}}
    <div class="col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">{{ __('Doanh thu 30 ngày gần nhất') }}</h3></div>
        <div class="box-body"><div id="rev30" style="height:300px"></div></div>
      </div>

      {{-- Chi phí tháng này --}}
      <div class="box box-warning">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-money"></i> {{ __('Chi phí tháng này') }}</h3>
          <div class="box-tools"><a href="{{ route('report.chi-phi-doanh-thu', ['branch_id' => $branchId]) }}" class="btn btn-box-tool">{{ __('Chi tiết') }}</a></div></div>
        <div class="box-body">
          <div class="row text-center">
            <div class="col-xs-6 col-sm-3"><div class="description-block"><h5 class="description-header">{{ $nf($costKpi['total']) }}</h5><span class="description-text">{{ __('Tổng chi phí vận hành') }}</span><br><small>{{ $pct($costKpi['cost_pct']) }} {{ __('doanh thu') }}</small></div></div>
            <div class="col-xs-6 col-sm-3"><div class="description-block"><h5 class="description-header">{{ $pct($costKpi['food_pct']) }}</h5><span class="description-text">Food cost</span><br><small>{{ $nf($costKpi['food']) }}</small></div></div>
            <div class="col-xs-6 col-sm-3"><div class="description-block"><h5 class="description-header">{{ $pct($costKpi['labor_pct']) }}</h5><span class="description-text">Labor cost</span><br><small>{{ $nf($costKpi['labor']) }}</small></div></div>
            <div class="col-xs-6 col-sm-3"><div class="description-block"><h5 class="description-header">{{ $nf($costKpi['invest']) }}</h5><span class="description-text">{{ __('Đầu tư (tách riêng)') }}</span></div></div>
          </div>
          <h5 style="margin-top:12px"><b>{{ __('5 khoản chi lớn nhất') }}</b></h5>
          <table class="table table-condensed" style="margin:0">
            @forelse($topCosts as $c)
            <tr><td style="white-space:nowrap">{{ date('d/m', strtotime($c->date_use)) }}</td>
              <td><a href="{{ route('cost.edit', $c->id) }}" target="_blank">{{ $c->cate_name }}</a> <small class="text-muted">{{ \Illuminate\Support\Str::limit($c->content ?: strip_tags($c->notes), 50) }}</small></td>
              <td><small>{{ $bName[$c->branch_id] ?? '' }}</small></td>
              <td class="text-right"><b>{{ $nf($c->total_money) }}</b></td></tr>
            @empty <tr><td class="text-muted text-center">{{ __('Không có dữ liệu') }}</td></tr> @endforelse
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
</div>
@stop
@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
  Highcharts.chart('rev30', {
    chart: { type: 'column' }, title: { text: null }, credits: { enabled: false },
    xAxis: { categories: @json($chart['days']) },
    yAxis: { title: { text: null }, labels: { formatter: function(){ return Highcharts.numberFormat(this.value / 1000000, 1, ',', '.') + 'tr'; } }, stackLabels: { enabled: false } },
    tooltip: { shared: true, pointFormat: '<span style="color:{series.color}">●</span> {series.name}: <b>{point.y:,.0f}</b><br>' },
    plotOptions: { column: { stacking: 'normal' } },
    lang: { thousandsSep: ',' },
    series: @json($chart['series'])
  });
</script>
@stop
