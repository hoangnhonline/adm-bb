@extends('layout')
@section('content')
@php
  $nf = function($v){ return number_format((float) $v); };
  $dmy = function($d){ return date('d/m/Y', strtotime($d)); };
  $wd = [__('CN'), 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
  $bl = function($from, $to) use ($branchId, $dmy) {
    $a = ['time_type' => $from == $to ? 3 : 2, 'use_date_from' => $dmy($from), 'use_date_to' => $dmy($to)];
    if ($branchId) $a['branch_id'] = $branchId;
    return route('booking.index', $a);
  };
  $cl = function($from, $to, $cate = null) use ($branchId, $dmy) {
    $a = ['time_type' => $from == $to ? 3 : 2, 'use_date_from' => $dmy($from), 'use_date_to' => $dmy($to)];
    if ($cate) $a['cate_id'] = $cate;
    if ($branchId) $a['branch_ids'] = [$branchId];
    return route('cost.index', $a);
  };
  $pct = function($cur, $old){
    if (!$old) return '';
    $p = round(($cur - $old) * 100 / abs($old), 1);
    return '<span style="color:' . ($p >= 0 ? '#00a65a' : '#dd4b39') . '">' . ($p >= 0 ? '▲ ' : '▼ ') . abs($p) . '%</span>';
  };
  $link = function($p) use ($branchId) { return route('report.doanh-thu-thang', array_filter($p + ['branch_id' => $branchId])); };
  $today = date('Y-m-d');
  $margin = $total['revenue'] ? round($total['profit'] * 100 / $total['revenue'], 1) : 0;
  if ($mode == 'day') { $title = __('Thống kê ngày') . ' ' . $dmy($from) . ' (' . $wd[date('w', strtotime($from))] . ')'; }
  elseif ($mode == 'month') { $title = __('Thống kê tháng') . ' ' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year; }
  else { $title = __('Thống kê') . ' ' . $dmy($from) . ' – ' . $dmy($to); }
  // nút chuyển kỳ trước / sau
  if ($mode == 'day') {
    $prevLink = $link(['mode' => 'day', 'date' => date('Y-m-d', strtotime($from . ' -1 day'))]);
    $nextD = date('Y-m-d', strtotime($from . ' +1 day')); $nextLink = $nextD <= $today ? $link(['mode' => 'day', 'date' => $nextD]) : null;
  } elseif ($mode == 'month') {
    $pm = strtotime($from . ' -1 month'); $nm = strtotime($from . ' +1 month');
    $prevLink = $link(['mode' => 'month', 'month' => date('n', $pm), 'year' => date('Y', $pm)]);
    $nextLink = date('Y-m', $nm) <= date('Y-m') ? $link(['mode' => 'month', 'month' => date('n', $nm), 'year' => date('Y', $nm)]) : null;
  } else { $prevLink = $nextLink = null; }
  $rowLabel = function($k) use ($groupBy, $wd) {
    return $groupBy == 'day' ? date('d/m', strtotime($k)) . ' <small class="text-muted">' . $wd[date('w', strtotime($k))] . '</small>' : __('Tháng') . ' ' . date('m/Y', strtotime($k . '-01'));
  };
  $rowRange = function($k) use ($groupBy, $from, $to) {
    if ($groupBy == 'day') return [$k, $k];
    return [max($from, $k . '-01'), min($to, date('Y-m-t', strtotime($k . '-01')))];
  };
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{!! e($title) !!} @if($branchId)<small>{{ $branches[$branchId] ?? '' }}</small>@endif</h1>
</section>
<section class="content">
  <div class="panel panel-default"><div class="panel-body">
    <ul class="nav nav-pills" style="margin-bottom:8px">
      <li class="{{ $mode == 'day' ? 'active' : '' }}"><a href="#" onclick="setMode('day');return false">{{ __('Theo ngày') }}</a></li>
      <li class="{{ $mode == 'month' ? 'active' : '' }}"><a href="#" onclick="setMode('month');return false">{{ __('Theo tháng') }}</a></li>
      <li class="{{ $mode == 'range' ? 'active' : '' }}"><a href="#" onclick="setMode('range');return false">{{ __('Khoảng ngày') }}</a></li>
    </ul>
    <form class="form-inline" method="GET" action="{{ route('report.doanh-thu-thang') }}" id="periodForm">
      <input type="hidden" name="mode" id="modeInput" value="{{ $mode }}">
      <span class="m-day" style="{{ $mode == 'day' ? '' : 'display:none' }}">
        <input type="date" name="date" class="form-control input-sm" value="{{ $mode == 'day' ? $from : $today }}" max="{{ $today }}">
      </span>
      <span class="m-month" style="{{ $mode == 'month' ? '' : 'display:none' }}">
        <select name="month" class="form-control input-sm">
          @for($i = 1; $i <= 12; $i++)<option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ __('Tháng') }} {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>@endfor
        </select>
        <select name="year" class="form-control input-sm">
          @foreach($years as $y)<option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
        </select>
      </span>
      <span class="m-range" style="{{ $mode == 'range' ? '' : 'display:none' }}">
        <input type="date" name="from" class="form-control input-sm" value="{{ $from }}"> –
        <input type="date" name="to" class="form-control input-sm" value="{{ $to }}">
      </span>
      <select name="branch_id" class="form-control input-sm">
        <option value="">{{ __('Tất cả chi nhánh') }}</option>
        @foreach($branches as $id => $n)<option value="{{ $id }}" {{ $branchId == $id ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <button class="btn btn-info btn-sm">{{ __('Xem') }}</button>
      @if($prevLink)<a class="btn btn-default btn-sm" href="{{ $prevLink }}">«</a>@endif
      @if($nextLink)<a class="btn btn-default btn-sm" href="{{ $nextLink }}">»</a>@endif
    </form>
    <div style="margin-top:8px">
      <a class="btn btn-default btn-xs" href="{{ $link(['mode' => 'day', 'date' => $today]) }}">{{ __('Hôm nay') }}</a>
      <a class="btn btn-default btn-xs" href="{{ $link(['mode' => 'day', 'date' => date('Y-m-d', strtotime('-1 day'))]) }}">{{ __('Hôm qua') }}</a>
      <a class="btn btn-default btn-xs" href="{{ $link(['mode' => 'range', 'from' => date('Y-m-d', strtotime('-6 days')), 'to' => $today]) }}">{{ __('7 ngày qua') }}</a>
      <a class="btn btn-default btn-xs" href="{{ $link(['mode' => 'range', 'from' => date('Y-m-d', strtotime('-29 days')), 'to' => $today]) }}">{{ __('30 ngày qua') }}</a>
      <a class="btn btn-default btn-xs" href="{{ $link(['mode' => 'month']) }}">{{ __('Tháng này') }}</a>
      <a class="btn btn-default btn-xs" href="{{ $link(['mode' => 'month', 'month' => date('n', strtotime('first day of last month')), 'year' => date('Y', strtotime('first day of last month'))]) }}">{{ __('Tháng trước') }}</a>
      <a class="btn btn-default btn-xs" href="{{ $link(['mode' => 'range', 'from' => date('Y-01-01'), 'to' => $today]) }}">{{ __('Năm nay') }}</a>
    </div>
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
  <p class="text-muted" style="margin:-4px 0 10px"><small>% {{ __('so với') }} {{ $pFrom == $pTo ? $dmy($pFrom) : $dmy($pFrom) . ' – ' . $dmy($pTo) }}</small></p>

  @if(count($rows) > 1)
  <div class="box"><div class="box-body"><div id="chartMonth" style="height:280px"></div></div></div>
  @endif

  @if(!$branchId && count($byBranch) > 1)
  <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Theo chi nhánh') }}</h3></div>
  <div class="box-body table-responsive no-padding"><table class="table table-bordered table-condensed table-hover" style="margin:0">
    <thead><tr><th>{{ __('Chi nhánh') }}</th><th class="text-right">{{ __('Số bill') }}</th><th class="text-right">{{ __('Doanh thu') }}</th><th class="text-right">{{ __('Chi phí') }}</th>
      <th class="text-right">{{ __('Lợi nhuận') }}</th><th class="text-right">{{ __('Biên lợi nhuận') }}</th><th class="text-right">% {{ __('doanh thu') }}</th></tr></thead>
    @foreach($byBranch as $bid => $b)
    <tr><td><a href="{{ request()->fullUrlWithQuery(['branch_id' => $bid]) }}">{{ $branches[$bid] ?? ($bid ? '#' . $bid : __('Không rõ')) }}</a></td>
      <td class="text-right">{{ $b['n'] ?: '' }}</td><td class="text-right">{{ $nf($b['revenue']) }}</td><td class="text-right">{{ $nf($b['cost']) }}</td>
      <td class="text-right" style="font-weight:bold;color:{{ $b['profit'] < 0 ? '#dd4b39' : '#333' }}">{{ $nf($b['profit']) }}</td>
      <td class="text-right">{{ $b['revenue'] ? round($b['profit'] * 100 / $b['revenue'], 1) . '%' : '' }}</td>
      <td class="text-right">{{ $total['revenue'] ? round($b['revenue'] * 100 / $total['revenue'], 1) . '%' : '' }}</td></tr>
    @endforeach
  </table></div></div>
  @endif

  <div class="row">
  <div class="col-md-7">
  <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ $groupBy == 'day' ? __('Theo từng ngày') : __('Theo từng tháng') }}</h3></div>
  <div class="box-body table-responsive no-padding"><table class="table table-bordered table-striped table-condensed table-hover" style="margin:0">
    <thead><tr><th>{{ $groupBy == 'day' ? __('Ngày') : __('Tháng') }}</th><th class="text-right">{{ __('Số bill') }}</th><th class="text-right">{{ __('Doanh thu') }}</th><th class="text-right">{{ __('Giảm giá') }}</th>
      <th class="text-right">{{ __('Chi phí') }}</th><th class="text-right">{{ __('Lợi nhuận') }}</th></tr></thead>
    @foreach($rows as $k => $r)
      @php [$rf, $rt] = $rowRange($k); @endphp
      @if($rf > $today && !$r['n'] && !$r['cost']) @continue @endif
      <tr>
        <td>{!! $rowLabel($k) !!}</td>
        <td class="text-right">{{ $r['n'] ?: '' }}</td>
        <td class="text-right">@if($r['revenue'])<a href="{{ $bl($rf, $rt) }}" target="_blank">{{ $nf($r['revenue']) }}</a>@endif</td>
        <td class="text-right">{{ $r['discount'] ? $nf($r['discount']) : '' }}</td>
        <td class="text-right">@if($r['cost'])<a href="{{ $cl($rf, $rt) }}" target="_blank">{{ $nf($r['cost']) }}</a>@endif</td>
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
    <tr><td>@if($from != $to)<a href="#" onclick="toggleCate({{ (int) $cid }});return false" title="{{ __('Xem theo ngày') }}"><i class="fa fa-caret-right"></i></a>@endif
        <a href="{{ $cl($from, $to, $cid) }}" target="_blank">{{ $cateNames[$cid] ?? ('#' . $cid) }}</a></td>
      <td class="text-right">{{ $nf($m) }}</td>
      <td class="text-right">{{ $total['cost'] ? round($m * 100 / $total['cost'], 1) : 0 }}%</td>
      <td class="text-right">{{ $total['revenue'] ? round($m * 100 / $total['revenue'], 1) : 0 }}%</td></tr>
    @if($from != $to)
    <tr class="cate-days" id="cateDays{{ (int) $cid }}" style="display:none"><td colspan="4" style="background:#fafafa">
      @foreach($cateDays[$cid] ?? [] as $d => $v)
        <a href="{{ $cl($d, $d, $cid) }}" target="_blank" style="display:inline-block;margin:2px 10px 2px 0;font-weight:normal">{{ date('d/m', strtotime($d)) }}: <b>{{ $nf($v) }}</b></a>
      @endforeach
    </td></tr>
    @endif
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
function setMode(m){
  document.getElementById('modeInput').value = m;
  ['day', 'month', 'range'].forEach(function(x){ document.querySelectorAll('.m-' + x).forEach(function(el){ el.style.display = x == m ? '' : 'none'; }); });
  document.querySelectorAll('.nav-pills li').forEach(function(li, i){ li.className = ['day', 'month', 'range'][i] == m ? 'active' : ''; });
}
// chỉ gửi các ô của chế độ đang chọn
document.getElementById('periodForm').addEventListener('submit', function(){
  var m = document.getElementById('modeInput').value;
  ['day', 'month', 'range'].forEach(function(x){ if (x != m) document.querySelectorAll('.m-' + x + ' [name]').forEach(function(el){ el.disabled = true; }); });
});
</script>
@if(count($rows) > 1)
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
(function(){
  if (!window.Highcharts) return;
  @php
    $chartRows = array_filter($rows, function($r, $k) use ($today, $rowRange) { return $rowRange($k)[0] <= $today || $r['n'] || $r['cost']; }, ARRAY_FILTER_USE_BOTH);
    $chartCats = array_map(function($k) use ($groupBy) { return $groupBy == 'day' ? date('d/m', strtotime($k)) : date('m/Y', strtotime($k . '-01')); }, array_keys($chartRows));
  @endphp
  Highcharts.chart('chartMonth', {
    title: { text: null }, credits: { enabled: false },
    xAxis: { categories: {!! json_encode($chartCats) !!} },
    yAxis: { title: { text: null }, labels: { formatter: function(){ return Highcharts.numberFormat(this.value / 1000000, 1, ',', '.') + 'tr'; } } },
    tooltip: { shared: true, valueSuffix: ' đ' },
    series: [
      { type: 'column', name: {!! json_encode(__('Doanh thu')) !!}, color: '#00c0ef', data: {!! json_encode(array_values(array_map(function($r){ return $r['revenue']; }, $chartRows))) !!} },
      { type: 'column', name: {!! json_encode(__('Chi phí')) !!}, color: '#f39c12', data: {!! json_encode(array_values(array_map(function($r){ return $r['cost']; }, $chartRows))) !!} },
      { type: 'line', name: {!! json_encode(__('Lợi nhuận')) !!}, color: '#00a65a', data: {!! json_encode(array_values(array_map(function($r){ return $r['profit']; }, $chartRows))) !!} }
    ]
  });
})();
</script>
@endif
@stop
