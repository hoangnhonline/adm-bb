@extends('layout')
@section('content')
@php
  $nf = function($v){ return number_format((float) $v); };
  $dmy = function($d){ return date('d/m/Y', strtotime($d)); };
  $wd = [__('CN'), 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
  $bl = function($from, $to) use ($branchId, $dmy) {
    $a = ['time_type' => 3, 'use_date_from' => $dmy($from), 'use_date_to' => $dmy($to)];
    if ($branchId) $a['branch_id'] = $branchId;
    return route('booking.index', $a);
  };
  $cl = function($from, $to, $cate = null) use ($branchId, $dmy) {
    $a = ['time_type' => 3, 'use_date_from' => $dmy($from), 'use_date_to' => $dmy($to)];
    if ($cate) $a['cate_id'] = $cate;
    if ($branchId) $a['branch_ids'] = [$branchId];
    return route('cost.index', $a);
  };
  $pct = function($cur, $old){
    if (!$old) return '';
    $p = round(($cur - $old) * 100 / abs($old), 1);
    return '<span style="color:' . ($p >= 0 ? '#00a65a' : '#dd4b39') . '">' . ($p >= 0 ? '▲ ' : '▼ ') . abs($p) . '%</span>';
  };
  $today = date('Y-m-d');
  $margin = $total['revenue'] ? round($total['profit'] * 100 / $total['revenue'], 1) : 0;
  $prevLabel = __('so với') . ' ' . $dmy($pFrom) . ' – ' . $dmy($pTo);
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Thống kê tháng') }} {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }} @if($branchId)<small>{{ $branches[$branchId] ?? '' }}</small>@endif</h1>
</section>
<section class="content">
  <div class="panel panel-default"><div class="panel-body">
    <form class="form-inline" method="GET" action="{{ route('report.doanh-thu-thang') }}">
      <select name="month" class="form-control input-sm">
        @for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ __('Tháng') }} {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>@endfor
      </select>
      <select name="year" class="form-control input-sm">
        @foreach($years as $y)<option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
      </select>
      <select name="branch_id" class="form-control input-sm">
        <option value="">{{ __('Tất cả chi nhánh') }}</option>
        @foreach($branches as $id => $n)<option value="{{ $id }}" {{ $branchId == $id ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <button class="btn btn-info btn-sm">{{ __('Xem') }}</button>
      @php $pm = strtotime($from . ' -1 month'); $nm = strtotime($from . ' +1 month'); @endphp
      <a class="btn btn-default btn-sm" href="{{ route('report.doanh-thu-thang', array_filter(['month' => date('n', $pm), 'year' => date('Y', $pm), 'branch_id' => $branchId])) }}">« {{ __('Tháng trước') }}</a>
      @if(date('Y-m', $nm) <= date('Y-m'))
      <a class="btn btn-default btn-sm" href="{{ route('report.doanh-thu-thang', array_filter(['month' => date('n', $nm), 'year' => date('Y', $nm), 'branch_id' => $branchId])) }}">{{ __('Tháng sau') }} »</a>
      @endif
    </form>
    <p class="text-muted" style="margin:6px 0 0">{{ __('Doanh thu = tổng bill − giảm giá (chưa VAT). Lợi nhuận = doanh thu − chi phí. Hoa hồng đối tác đã chi nằm trong chi phí.') }}</p>
  </div></div>

  <div class="row">
    @foreach([
      [__('Doanh thu'), $total['revenue'], $total['n'] . ' ' . __('bill') . ' · ' . __('giảm giá') . ' ' . $nf($total['discount']), 'aqua', $prev['revenue']],
      [__('Chi phí'), $total['cost'], __('Vận hành') . ' ' . $nf($total['cost_vh']) . ' · ' . __('Khác') . ' ' . $nf($total['cost_khac']), 'yellow', $prev['cost']],
      [__('Lợi nhuận'), $total['profit'], __('Biên lợi nhuận') . ' ' . $margin . '%', $total['profit'] >= 0 ? 'green' : 'red', $prev['profit']],
      [__('Tiền VAT'), $total['vat'], __('Ngoài doanh thu, thu thêm của khách'), 'gray', $prev['vat']],
    ] as $c)
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-{{ $c[3] }}" style="margin-bottom:10px"><div class="inner" style="padding:8px 12px">
        <h3 style="font-size:24px;margin:0 0 4px">{{ $nf($c[1]) }}</h3>
        <p style="margin:0;font-weight:bold">{{ $c[0] }} <span style="background:#fff;border-radius:3px;padding:0 4px;font-size:12px">{!! $pct($c[1], $c[4]) ?: '–' !!}</span></p>
        <small>{{ $c[2] }}</small>
      </div></div>
    </div>
    @endforeach
  </div>
  <p class="text-muted" style="margin:-4px 0 10px"><small>% {{ $prevLabel }}</small></p>

  <div class="box"><div class="box-body"><div id="chartMonth" style="height:280px"></div></div></div>

  <div class="row">
  <div class="col-md-7">
  <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Theo từng ngày') }}</h3></div>
  <div class="box-body table-responsive no-padding"><table class="table table-bordered table-striped table-condensed table-hover" style="margin:0">
    <thead><tr><th>{{ __('Ngày') }}</th><th class="text-right">{{ __('Số bill') }}</th><th class="text-right">{{ __('Doanh thu') }}</th><th class="text-right">{{ __('Giảm giá') }}</th>
      <th class="text-right">{{ __('Chi phí') }}</th><th class="text-right">{{ __('Lợi nhuận') }}</th></tr></thead>
    @foreach($days as $d => $r)
      @if($d > $today && !$r['n'] && !$r['cost']) @continue @endif
      <tr>
        <td>{{ date('d', strtotime($d)) }} <small class="text-muted">{{ $wd[date('w', strtotime($d))] }}</small></td>
        <td class="text-right">{{ $r['n'] ?: '' }}</td>
        <td class="text-right">@if($r['revenue'])<a href="{{ $bl($d, $d) }}" target="_blank">{{ $nf($r['revenue']) }}</a>@endif</td>
        <td class="text-right">{{ $r['discount'] ? $nf($r['discount']) : '' }}</td>
        <td class="text-right">@if($r['cost'])<a href="{{ $cl($d, $d) }}" target="_blank">{{ $nf($r['cost']) }}</a>@endif</td>
        <td class="text-right" style="font-weight:bold;color:{{ $r['profit'] < 0 ? '#dd4b39' : '#333' }}">{{ ($r['revenue'] || $r['cost']) ? $nf($r['profit']) : '' }}</td>
      </tr>
    @endforeach
    <tr style="font-weight:bold;background:#f4f4f4"><td>{{ __('Tổng') }}</td><td class="text-right">{{ $total['n'] }}</td>
      <td class="text-right"><a href="{{ $bl($from, $to) }}" target="_blank">{{ $nf($total['revenue']) }}</a></td><td class="text-right">{{ $nf($total['discount']) }}</td>
      <td class="text-right"><a href="{{ $cl($from, $to) }}" target="_blank">{{ $nf($total['cost']) }}</a></td>
      <td class="text-right" style="color:{{ $total['profit'] < 0 ? '#dd4b39' : '#c0392b' }};font-size:16px">{{ $nf($total['profit']) }}</td></tr>
  </table></div></div>
  </div>

  <div class="col-md-5">
  <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Chi phí theo danh mục') }}</h3></div>
  <div class="box-body table-responsive no-padding"><table class="table table-bordered table-condensed table-hover" style="margin:0">
    <thead><tr><th>{{ __('Danh mục') }}</th><th class="text-right">{{ __('Số tiền') }}</th><th class="text-right">% {{ __('chi phí') }}</th><th class="text-right">% {{ __('doanh thu') }}</th></tr></thead>
    @forelse($cates as $cid => $m)
    <tr><td><a href="#" onclick="toggleCate({{ (int) $cid }});return false" title="{{ __('Xem theo ngày') }}"><i class="fa fa-caret-right"></i></a>
        <a href="{{ $cl($from, $to, $cid) }}" target="_blank">{{ $cateNames[$cid] ?? ('#' . $cid) }}</a></td>
      <td class="text-right">{{ $nf($m) }}</td>
      <td class="text-right">{{ $total['cost'] ? round($m * 100 / $total['cost'], 1) : 0 }}%</td>
      <td class="text-right">{{ $total['revenue'] ? round($m * 100 / $total['revenue'], 1) : 0 }}%</td></tr>
    <tr class="cate-days" id="cateDays{{ (int) $cid }}" style="display:none"><td colspan="4" style="background:#fafafa">
      @foreach($cateDays[$cid] ?? [] as $d => $v)
        <a href="{{ $cl($d, $d, $cid) }}" target="_blank" style="display:inline-block;margin:2px 10px 2px 0;font-weight:normal">{{ date('d/m', strtotime($d)) }}: <b>{{ $nf($v) }}</b></a>
      @endforeach
    </td></tr>
    @empty
    <tr><td colspan="4" class="text-muted">{{ __('Không có dữ liệu') }}</td></tr>
    @endforelse
    @if($cates)
    <tr style="font-weight:bold;background:#f4f4f4"><td>{{ __('Tổng') }}</td><td class="text-right">{{ $nf($total['cost']) }}</td><td class="text-right">100%</td>
      <td class="text-right">{{ $total['revenue'] ? round($total['cost'] * 100 / $total['revenue'], 1) : 0 }}%</td></tr>
    @endif
  </table></div></div>
  </div>
  </div>
</section>
</div>
<script>
function toggleCate(id){ var r = document.getElementById('cateDays' + id); if (r) r.style.display = r.style.display == 'none' ? '' : 'none'; }
</script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
(function(){
  if (!window.Highcharts) return;
  @php $chartDays = array_filter($days, function($r, $d) use ($today) { return $d <= $today || $r['n'] || $r['cost']; }, ARRAY_FILTER_USE_BOTH); @endphp
  Highcharts.chart('chartMonth', {
    title: { text: null }, credits: { enabled: false },
    xAxis: { categories: {!! json_encode(array_map(function($d){ return date('d', strtotime($d)); }, array_keys($chartDays))) !!} },
    yAxis: { title: { text: null }, labels: { formatter: function(){ return Highcharts.numberFormat(this.value / 1000000, 1, ',', '.') + 'tr'; } } },
    tooltip: { shared: true, valueSuffix: ' đ' },
    series: [
      { type: 'column', name: {!! json_encode(__('Doanh thu')) !!}, color: '#00c0ef', data: {!! json_encode(array_values(array_map(function($r){ return $r['revenue']; }, $chartDays))) !!} },
      { type: 'column', name: {!! json_encode(__('Chi phí')) !!}, color: '#f39c12', data: {!! json_encode(array_values(array_map(function($r){ return $r['cost']; }, $chartDays))) !!} },
      { type: 'line', name: {!! json_encode(__('Lợi nhuận')) !!}, color: '#00a65a', data: {!! json_encode(array_values(array_map(function($r){ return $r['profit']; }, $chartDays))) !!} }
    ]
  });
})();
</script>
@stop
