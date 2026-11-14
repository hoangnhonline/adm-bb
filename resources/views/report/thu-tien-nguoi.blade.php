@extends('layout')
@section('content')
@php
  $nf = function($v){ return $v ? number_format((float) $v) : ''; };
  $dmy = function($d){ return date('d/m/Y', strtotime($d)); };
  $wd = [__('CN'), 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
  $link = function($from, $to, $p) use ($f, $dmy) {
    $a = ['use_date_from' => $dmy($from), 'use_date_to' => $dmy($to), 'time_type' => 3];
    if ($p) $a['nguoi_thu_tien'] = $p;
    if ($f['branch_id']) $a['branch_id'] = $f['branch_id'];
    return route('booking.index', $a);
  };
  $q = function($extra) use ($f){ return route('report.thu-tien-nguoi', array_filter(array_merge($f, $extra), function($v){ return $v !== null && $v !== '' && $v !== 0; })); };
  $today = date('Y-m-d'); $yest = date('Y-m-d', strtotime('-1 day'));
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Thu tiền theo người nhận') }} <small>{{ $dmy($f['from']) }} – {{ $dmy($f['to']) }}</small></h1>
  <ol class="breadcrumb"><li><a href="{{ route('booking.index') }}">{{ __('Quản lý bill') }}</a></li><li class="active">{{ __('Thu tiền theo người nhận') }}</li></ol>
</section>
<section class="content">
  <div class="panel panel-default"><div class="panel-body">
    <form class="form-inline" method="GET" action="{{ route('report.thu-tien-nguoi') }}">
      <input type="date" name="from" class="form-control input-sm" value="{{ $f['from'] }}">
      <input type="date" name="to" class="form-control input-sm" value="{{ $f['to'] }}">
      <select name="branch_id" class="form-control input-sm">
        <option value="">{{ __('Tất cả chi nhánh') }}</option>
        @foreach($branches as $id => $n)<option value="{{ $id }}" {{ $f['branch_id'] == $id ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <label style="font-weight:normal;margin:0 6px"><input type="checkbox" name="vat" value="1" {{ $f['vat'] ? 'checked' : '' }}> {{ __('Gồm VAT') }}</label>
      <button class="btn btn-info btn-sm">{{ __('Xem') }}</button>
      <span style="margin-left:8px">
        <a class="btn btn-default btn-sm" href="{{ $q(['from' => $today, 'to' => $today]) }}">{{ __('Hôm nay') }}</a>
        <a class="btn btn-default btn-sm" href="{{ $q(['from' => $yest, 'to' => $yest]) }}">{{ __('Hôm qua') }}</a>
        <a class="btn btn-default btn-sm" href="{{ $q(['from' => date('Y-m-01'), 'to' => $today]) }}">{{ __('Tháng này') }}</a>
        <a class="btn btn-default btn-sm" href="{{ $q(['from' => date('Y-m-01', strtotime('first day of last month')), 'to' => date('Y-m-t', strtotime('first day of last month'))]) }}">{{ __('Tháng trước') }}</a>
      </span>
    </form>
    <p class="text-muted" style="margin:6px 0 0">{{ __('Tiền bill (còn lại) tính cho người thu tiền, tiền cọc tính cho người thu cọc.') }} @if($f['vat']){{ __('Đã cộng VAT của bill vào người thu tiền.') }}@endif</p>
  </div></div>

  {{-- Tổng theo từng người --}}
  <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Tổng tiền theo từng người') }}</h3>
    <span class="pull-right" style="font-size:16px">{{ __('Tổng') }}: <b style="color:#c0392b">{{ number_format($grand) }}</b></span></div>
  <div class="box-body table-responsive no-padding"><table class="table table-bordered table-condensed" style="margin:0">
    <thead><tr><th>{{ __('Người nhận tiền') }}</th><th class="text-right">{{ __('Số bill') }}</th><th class="text-right">{{ __('Tiền bill') }}</th><th class="text-right">{{ __('Tiền cọc') }}</th>
      @if($f['vat'])<th class="text-right">VAT</th>@endif<th class="text-right">{{ __('Tổng') }}</th><th class="text-right">%</th></tr></thead>
    @foreach($persons as $p => $name)
      @php $s = $sum[$p] ?? null; @endphp
      @if($s)
      <tr><td><a href="{{ $link($f['from'], $f['to'], $p) }}" target="_blank">{{ $name }}</a></td>
        <td class="text-right">{{ $s['n'] ?: '' }}</td><td class="text-right">{{ $nf($s['bill']) }}</td><td class="text-right">{{ $nf($s['coc']) }}</td>
        @if($f['vat'])<td class="text-right">{{ $nf($s['vat']) }}</td>@endif
        <td class="text-right"><b>{{ number_format($s['total']) }}</b></td>
        <td class="text-right">{{ $grand ? round($s['total'] * 100 / $grand, 1) : 0 }}%</td></tr>
      @endif
    @endforeach
    @if(!$sum)<tr><td colspan="7" class="text-muted">{{ __('Không có dữ liệu') }}</td></tr>@endif
  </table></div></div>

  @if($sum)
  <div class="row">
    <div class="col-md-4"><div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Tỷ trọng theo người') }}</h3></div>
      <div class="box-body"><div id="chartShare" style="height:280px"></div></div></div></div>
    <div class="col-md-8"><div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Thu tiền theo ngày') }}</h3></div>
      <div class="box-body"><div id="chartDays" style="height:280px"></div></div></div></div>
  </div>
  @endif

  @if(!$f['branch_id'] && count($branchSum) > 1)
  <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Theo chi nhánh') }}</h3></div>
  <div class="box-body table-responsive no-padding"><table class="table table-bordered table-condensed" style="margin:0">
    <thead><tr><th>{{ __('Chi nhánh') }}</th>@foreach($persons as $p => $name)@if(isset($sum[$p]))<th class="text-right">{{ $name }}</th>@endif @endforeach<th class="text-right">{{ __('Tổng') }}</th></tr></thead>
    @foreach($branchSum as $bid => $bs)
    <tr><td><a href="{{ $q(['branch_id' => $bid]) }}">{{ $branches[$bid] ?? $bid }}</a></td>
      @foreach($persons as $p => $name)@if(isset($sum[$p]))<td class="text-right">{{ $nf($bs[$p] ?? 0) }}</td>@endif @endforeach
      <td class="text-right"><b>{{ number_format(array_sum($bs)) }}</b></td></tr>
    @endforeach
  </table></div></div>
  @endif

  {{-- Từng ngày --}}
  <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Theo từng ngày') }}</h3></div>
  <div class="box-body table-responsive no-padding"><table class="table table-bordered table-striped table-condensed table-hover" style="margin:0">
    <thead><tr><th>{{ __('Ngày') }}</th><th class="text-right">{{ __('Số bill') }}</th>
      @foreach($persons as $p => $name)@if(isset($sum[$p]))<th class="text-right">{{ $name }}</th>@endif @endforeach
      <th class="text-right">{{ __('Tổng') }}</th></tr></thead>
    @foreach($days as $d => $per)
    <tr><td><a href="{{ $link($d, $d, null) }}" target="_blank">{{ $dmy($d) }}</a> <small class="text-muted">{{ $wd[date('w', strtotime($d))] }}</small></td>
      <td class="text-right">{{ $billCount[$d] ?? '' }}</td>
      @foreach($persons as $p => $name)@if(isset($sum[$p]))
        <td class="text-right">@if(!empty($per[$p]))<a href="{{ $link($d, $d, $p) }}" target="_blank" style="color:inherit">{{ number_format($per[$p]) }}</a>@endif</td>
      @endif @endforeach
      <td class="text-right"><b>{{ number_format($dayTotal[$d]) }}</b></td></tr>
    @endforeach
    @if($days)
    <tr style="font-weight:bold;background:#f4f4f4"><td>{{ __('Tổng') }}</td><td class="text-right">{{ array_sum($billCount) }}</td>
      @foreach($persons as $p => $name)@if(isset($sum[$p]))<td class="text-right">{{ number_format($sum[$p]['total']) }}</td>@endif @endforeach
      <td class="text-right">{{ number_format($grand) }}</td></tr>
    @endif
  </table></div></div>
</section>
</div>
@if($sum)
@php
  $cPersons = []; foreach ($persons as $p => $name) if (isset($sum[$p])) $cPersons[$p] = $name;
  $cDays = array_reverse(array_keys($days));
  if ((strtotime($f['to']) - strtotime($f['from'])) / 86400 <= 62) { // đủ từng ngày trong kỳ (tối đa ~2 tháng)
    $cDays = []; for ($d = $f['from']; $d <= min($f['to'], date('Y-m-d')); $d = date('Y-m-d', strtotime($d . ' +1 day'))) $cDays[] = $d;
  }
  $cSeries = []; foreach ($cPersons as $p => $name) $cSeries[] = ['name' => $name, 'data' => array_map(function ($d) use ($days, $p) { return (int) ($days[$d][$p] ?? 0); }, $cDays)];
  $cShare = []; foreach ($cPersons as $p => $name) $cShare[] = ['name' => $name, 'y' => (int) $sum[$p]['total']];
@endphp
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
(function(){
  if (!window.Highcharts) return;
  var colors = ['#3c8dbc', '#00a65a', '#f39c12', '#605ca8', '#d81b60', '#39cccc', '#777777', '#dd4b39'];
  var money = function(v){ return Highcharts.numberFormat(v, 0, '.', ','); };
  Highcharts.setOptions({ colors: colors, credits: { enabled: false }, lang: { thousandsSep: ',' } });
  Highcharts.chart('chartShare', {
    chart: { type: 'pie' }, title: { text: null },
    tooltip: { pointFormatter: function(){ return '<b>' + money(this.y) + '</b> (' + Highcharts.numberFormat(this.percentage, 1) + '%)'; } },
    plotOptions: { pie: { innerSize: '55%', showInLegend: true, dataLabels: { enabled: true, distance: -22, format: '{point.percentage:.0f}%', filter: { property: 'percentage', operator: '>', value: 5 }, style: { fontWeight: 'bold', textOutline: 'none', color: '#fff' } } } },
    legend: { labelFormatter: function(){ return this.name + ': ' + money(this.y); } },
    series: [{ name: {!! json_encode(__('Tổng')) !!}, data: {!! json_encode($cShare, JSON_UNESCAPED_UNICODE) !!} }]
  });
  Highcharts.chart('chartDays', {
    chart: { type: 'column' }, title: { text: null },
    xAxis: { categories: {!! json_encode(array_map(function ($d) { return date('d/m', strtotime($d)); }, $cDays)) !!} },
    yAxis: { title: { text: null }, stackLabels: { enabled: false }, labels: { formatter: function(){ return Highcharts.numberFormat(this.value / 1000000, 1, ',', '.') + 'tr'; } } },
    tooltip: { shared: true, pointFormatter: function(){ return this.y ? '<span style="color:' + this.color + '">●</span> ' + this.series.name + ': <b>' + money(this.y) + '</b><br/>' : ''; },
      footerFormat: {!! json_encode(__('Tổng')) !!} + ': <b>{point.total:,.0f}</b>' },
    plotOptions: { column: { stacking: 'normal', borderWidth: 0 } },
    series: {!! json_encode($cSeries, JSON_UNESCAPED_UNICODE) !!}
  });
})();
</script>
@endif
@endsection
