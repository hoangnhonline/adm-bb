@extends('layout')
@section('content')
@php
  $periodNames = ['week' => 'Tuần', 'month' => 'Tháng', 'year' => 'Năm', 'custom' => 'Tùy chọn'];
  $fp = function($v){ return $v === null ? '–' : number_format($v, 1).'%'; };
  $diffPt = function($cur, $prev){
    if($cur === null || $prev === null) return '';
    $d = $cur - $prev;
    if(abs($d) < 0.05) return '<span class="text-muted">=</span>';
    return '<span class="'.($d > 0 ? 'text-red' : 'text-green').'">'.($d > 0 ? '▲' : '▼').' '.number_format(abs($d), 1).' điểm</span>';
  };
  $costLink = function($cateId) use ($from, $to, $branch_id){
    return route('cost.index', array_filter(['cate_id' => $cateId, 'branch_id' => $branch_id, 'time_type' => 2,
      'use_date_from' => date('d/m/Y', strtotime($from)), 'use_date_to' => date('d/m/Y', strtotime($to))]));
  };
  $baseParams = ['branch_id' => $branch_id];
  $pie = [];
  foreach($groups as $g){ if($g['money'] > 0) $pie[] = ['name' => $g['name'], 'y' => $g['money']]; }
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>Chi phí / Doanh thu</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Chi phí / Doanh thu</li>
  </ol>
</section>

<section class="content">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="btn-group" style="margin-bottom:10px">
        @foreach(['week', 'month', 'year'] as $p)
          <a href="{{ route('report.chi-phi-doanh-thu', array_merge($baseParams, ['period' => $p])) }}" class="btn btn-sm {{ $period == $p ? 'btn-primary' : 'btn-default' }}">{{ $periodNames[$p] }}</a>
        @endforeach
        <a href="#" class="btn btn-sm {{ $period == 'custom' ? 'btn-primary' : 'btn-default' }}" onclick="$('#customRange').toggle();return false;">Tùy chọn</a>
      </div>
      <form method="GET" action="{{ route('report.chi-phi-doanh-thu') }}" class="form-inline">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="date" value="{{ $from }}">
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
      </form>
      <h3 style="margin:15px 0 5px">
        @if($prevAnchor)
          <a href="{{ route('report.chi-phi-doanh-thu', array_merge($baseParams, ['period' => $period, 'date' => $prevAnchor])) }}" class="btn btn-default btn-sm" title="Kỳ trước"><i class="fa fa-chevron-left"></i></a>
        @endif
        {{ $label }}
        @if($nextAnchor)
          <a href="{{ route('report.chi-phi-doanh-thu', array_merge($baseParams, ['period' => $period, 'date' => $nextAnchor])) }}" class="btn btn-default btn-sm" title="Kỳ sau"><i class="fa fa-chevron-right"></i></a>
        @endif
      </h3>
      <div class="text-muted">
        Doanh thu = tổng tiền bill − giảm giá (bỏ bill hủy). So với kỳ trước: {{ date('d/m/Y', strtotime($prevFrom)) }} - {{ date('d/m/Y', strtotime($prevTo)) }}.
        Chi phí Setup/mở chi nhánh và Visa/vé máy bay NV được tách riêng, không tính vào tỷ lệ.
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-aqua"><div class="inner">
        <h3 style="font-size:22px">{{ number_format($rev['net']) }}</h3>
        <p>Doanh thu thuần ({{ number_format($rev['bills']) }} bill, giảm giá {{ number_format($rev['discount']) }})<br>
          <small>Kỳ trước: {{ number_format($prevRev['net']) }}</small></p>
      </div></div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-yellow"><div class="inner">
        <h3 style="font-size:22px">{{ $fp($kpi['food'][0]) }}</h3>
        <p>Food cost (giá vốn thực phẩm + đồ uống)<br><small>Kỳ trước: {{ $fp($kpi['food'][1]) }}</small></p>
      </div></div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-purple"><div class="inner">
        <h3 style="font-size:22px">{{ $fp($kpi['labor'][0]) }}</h3>
        <p>Labor cost (nhân sự, không gồm visa/vé)<br><small>Kỳ trước: {{ $fp($kpi['labor'][1]) }}</small></p>
      </div></div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-red"><div class="inner">
        <h3 style="font-size:22px">{{ $fp($kpi['prime'][0]) }}</h3>
        <p>Prime cost (food + labor)<br><small>Kỳ trước: {{ $fp($kpi['prime'][1]) }}</small></p>
      </div></div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="box box-primary">
        <div class="box-body table-responsive no-padding">
          <table class="table table-bordered table-condensed table-hover">
            <thead>
              <tr style="background:#f4f4f4">
                <th>Loại chi phí</th>
                <th class="text-center">Số khoản</th>
                <th class="text-right">Số tiền</th>
                <th class="text-right">% doanh thu</th>
                <th class="text-right">% tổng chi phí</th>
                <th class="text-right">% DT kỳ trước</th>
                <th class="text-right">Thay đổi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($groups as $g)
                @php
                  $gPct = $rev['net'] > 0 ? $g['money'] / $rev['net'] * 100 : null;
                  $gPrevPct = $prevRev['net'] > 0 ? $g['prev_money'] / $prevRev['net'] * 100 : null;
                @endphp
                <tr style="background:#eef5fb;font-weight:bold">
                  <td>{{ $g['name'] }}</td>
                  <td></td>
                  <td class="text-right">{{ number_format($g['money']) }}</td>
                  <td class="text-right">{{ $fp($gPct) }}</td>
                  <td class="text-right">{{ $totals['cost'] > 0 ? number_format($g['money'] / $totals['cost'] * 100, 1).'%' : '–' }}</td>
                  <td class="text-right">{{ $fp($gPrevPct) }}</td>
                  <td class="text-right">{!! $diffPt($gPct, $gPrevPct) !!}</td>
                </tr>
                @foreach($g['rows'] as $r)
                  <tr>
                    <td style="padding-left:25px">{{ $r['name'] }}</td>
                    <td class="text-center">{{ $r['count'] ?: '' }}</td>
                    <td class="text-right">
                      @if($r['money'] > 0)
                        <a href="{{ $costLink($r['cate_id']) }}" target="_blank" title="Xem các khoản chi">{{ number_format($r['money']) }}</a>
                      @else
                        0
                      @endif
                    </td>
                    <td class="text-right">{{ $fp($r['pct_rev']) }}</td>
                    <td class="text-right">{{ $totals['cost'] > 0 ? number_format($r['money'] / $totals['cost'] * 100, 1).'%' : '–' }}</td>
                    <td class="text-right">{{ $fp($r['prev_pct_rev']) }}</td>
                    <td class="text-right">{!! $diffPt($r['pct_rev'], $r['prev_pct_rev']) !!}</td>
                  </tr>
                @endforeach
              @empty
                <tr><td colspan="7" class="text-center text-muted">Không có chi phí trong kỳ này.</td></tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr style="background:#f4f4f4;font-weight:bold">
                <td>TỔNG CHI PHÍ VẬN HÀNH</td>
                <td></td>
                <td class="text-right">{{ number_format($totals['cost']) }}</td>
                <td class="text-right">{{ $fp($kpi['cost'][0]) }}</td>
                <td class="text-right">100%</td>
                <td class="text-right">{{ $fp($kpi['cost'][1]) }}</td>
                <td class="text-right">{!! $diffPt($kpi['cost'][0], $kpi['cost'][1]) !!}</td>
              </tr>
              <tr style="font-weight:bold" class="{{ $remain >= 0 ? 'text-green' : 'text-red' }}">
                <td>DOANH THU CÒN LẠI (sau chi phí vận hành)</td>
                <td></td>
                <td class="text-right">{{ number_format($remain) }}</td>
                <td class="text-right">{{ $rev['net'] > 0 ? number_format($remain / $rev['net'] * 100, 1).'%' : '–' }}</td>
                <td></td>
                <td class="text-right">{{ $prevRev['net'] > 0 ? number_format($prevRemain / $prevRev['net'] * 100, 1).'%' : '–' }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="box box-warning">
        <div class="box-header with-border"><h3 class="box-title">Chi phí đầu tư (tách riêng, không tính vào tỷ lệ)</h3></div>
        <div class="box-body table-responsive no-padding">
          <table class="table table-bordered table-condensed">
            <thead><tr style="background:#f4f4f4"><th>Loại</th><th class="text-center">Số khoản</th><th class="text-right">Số tiền</th><th class="text-right">Kỳ trước</th></tr></thead>
            <tbody>
              @forelse($invest as $r)
                <tr>
                  <td>{{ $r['name'] }}</td>
                  <td class="text-center">{{ $r['count'] ?: '' }}</td>
                  <td class="text-right">
                    @if($r['money'] > 0)
                      <a href="{{ $costLink($r['cate_id']) }}" target="_blank">{{ number_format($r['money']) }}</a>
                    @else 0 @endif
                  </td>
                  <td class="text-right">{{ number_format($r['prev_money']) }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted">Không có</td></tr>
              @endforelse
            </tbody>
            <tfoot><tr style="font-weight:bold;background:#f4f4f4"><td colspan="2">Tổng đầu tư</td><td class="text-right">{{ number_format($totals['invest']) }}</td><td class="text-right">{{ number_format($totals['prev_invest']) }}</td></tr></tfoot>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="box box-default">
        <div class="box-header with-border"><h3 class="box-title">Tỷ trọng nhóm chi phí</h3></div>
        <div class="box-body">
          @if(count($pie))
            <div id="pieChart" style="height:360px"></div>
          @else
            <p class="text-muted">Không có dữ liệu.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
</div>
@stop

@section('js')
@if(count($pie))
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
(function(){
  var rev = {{ (float) $rev['net'] }};
  Highcharts.chart('pieChart', {
    chart: { type: 'pie' }, title: { text: null }, credits: { enabled: false },
    tooltip: { formatter: function(){
      return '<b>' + this.point.name + '</b><br>' + Highcharts.numberFormat(this.y, 0, ',', '.') + ' đ' +
             '<br>' + Highcharts.numberFormat(this.percentage, 1, ',', '.') + '% tổng chi phí' +
             (rev > 0 ? '<br>' + Highcharts.numberFormat(this.y / rev * 100, 1, ',', '.') + '% doanh thu' : '');
    } },
    plotOptions: { pie: { dataLabels: { enabled: true, format: '{point.name}: {point.percentage:.1f}%', style: { fontSize: '11px' } } } },
    series: [{ name: 'Chi phí', data: @json($pie) }]
  });
})();
</script>
@endif
@stop
