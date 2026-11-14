@extends('layout')
@section('content')
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Thống kê nhập hàng theo nguyên liệu') }}</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> {{ __('Trang chủ') }}</a></li>
    <li><a href="{{ route('cost.index') }}">{{ __('Chi phí') }}</a></li>
    <li class="active">{{ __('Nhập hàng') }}</li>
  </ol>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{{ __('Bộ lọc') }}</h3></div>
        <div class="panel-body">
          @php
            $navParams = array_merge($arrSearch, ['ingredient_id' => null, 'use_date_from' => null, 'use_date_to' => null]);
            $periodNames = ['week' => __('Tuần'), 'month' => __('Tháng'), 'year' => __('Năm'), 'custom' => __('Tùy chọn')];
          @endphp
          <div style="margin-bottom:10px">
            <div class="btn-group">
              @foreach(['week', 'month', 'year'] as $p)
                <a href="{{ route('cost.nhap-hang', array_merge($navParams, ['period' => $p, 'date' => null])) }}" class="btn btn-sm {{ $period == $p ? 'btn-primary' : 'btn-default' }}">{{ $periodNames[$p] }}</a>
              @endforeach
              <span class="btn btn-sm {{ $period == 'custom' ? 'btn-primary' : 'btn-default' }}" style="cursor:default">{{ __('Tùy chọn (chọn ngày bên dưới)') }}</span>
            </div>
            <span style="font-size:16px;margin-left:10px;vertical-align:middle">
              @if($prevAnchor)
                <a href="{{ route('cost.nhap-hang', array_merge($navParams, ['period' => $period, 'date' => $prevAnchor])) }}" class="btn btn-default btn-xs" title="{{ __('Kỳ trước') }}"><i class="fa fa-chevron-left"></i></a>
              @endif
              <b>{{ $periodLabel }}</b>
              @if($nextAnchor)
                <a href="{{ route('cost.nhap-hang', array_merge($navParams, ['period' => $period, 'date' => $nextAnchor])) }}" class="btn btn-default btn-xs" title="{{ __('Kỳ sau') }}"><i class="fa fa-chevron-right"></i></a>
              @endif
            </span>
            <span class="text-muted" style="margin-left:10px">{{ __('So với kỳ trước') }}: {{ date('d/m/Y', strtotime($prevFrom)) }} - {{ date('d/m/Y', strtotime($prevTo)) }}</span>
          </div>
          <form class="form-inline" role="form" method="GET" action="{{ route('cost.nhap-hang') }}" id="searchForm">
            <input type="hidden" name="period" id="f_period" value="{{ $period }}">
            <input type="hidden" name="date" value="{{ $period != 'custom' ? $fromDate : '' }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <div class="form-group">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="{{ __('Từ ngày') }}" value="{{ $arrSearch['use_date_from'] }}" style="width:110px">
            </div>
            <div class="form-group">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="{{ __('Đến ngày') }}" value="{{ $arrSearch['use_date_to'] }}" style="width:110px">
            </div>
            <div class="form-group">
              <select class="form-control select2" name="cate_id" style="min-width:200px">
                <option value="">{{ __('--Nhóm nguyên liệu--') }}</option>
                @include('cost._cate-options', ['selectedCate' => $arrSearch['cate_id']])
              </select>
            </div>
            <div class="form-group">
              <select class="form-control select2" name="ncc_id" style="min-width:160px">
                <option value="">{{ __('--Nhà cung cấp--') }}</option>
                @foreach($nccList as $ncc)
                  <option value="{{ $ncc->id }}" {{ $arrSearch['ncc_id'] == $ncc->id ? "selected" : "" }}>{{ $ncc->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <select class="form-control select2" name="ingredient_id" style="min-width:180px">
                <option value="">{{ __('--Nguyên liệu (xem chi tiết)--') }}</option>
                <option value="none" {{ $arrSearch['ingredient_id'] == 'none' ? "selected" : "" }}>{{ __('(Chưa gắn nguyên liệu)') }}</option>
                @foreach($ingredientList as $ing)
                  <option value="{{ $ing->id }}" {{ (string) $arrSearch['ingredient_id'] === (string) $ing->id ? "selected" : "" }}>{{ $ing->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              &nbsp;<input type="checkbox" name="include_review" id="include_review" value="1" {{ $arrSearch['include_review'] ? "checked" : "" }}>
              <label for="include_review">{{ __('Tính cả dòng cần kiểm tra') }}</label>
            </div>
            <button type="submit" class="btn btn-info btn-sm" style="margin-top:-5px">{{ __('Xem') }}</button>
            <div>
              @foreach($branchList as $beach)
                <div class="form-group">
                  &nbsp;&nbsp;&nbsp;<input type="checkbox" name="branch_ids[]" id="branch_{{ $beach->id }}" value="{{ $beach->id }}" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }}>
                  <label for="branch_{{ $beach->id }}">{{ $beach->name }}</label>
                </div>
              @endforeach
            </div>
          </form>
        </div>
      </div>

      {{-- Độ phủ dữ liệu --}}
      @php
        $covBills = (int) ($coverage->bills ?? 0);
        $covMoney = (float) ($coverage->money ?? 0);
        $covPct = $covMoney > 0 ? round(($coverage->money_detail ?? 0) / $covMoney * 100) : 0;
      @endphp
      <div class="row">
        <div class="col-md-3 col-xs-6">
          <div class="small-box bg-aqua"><div class="inner">
            <h3 style="font-size:24px">{{ number_format($totalAmount) }}</h3>
            <p>{{ __('Tiền hàng có chi tiết (theo bộ lọc)') }}</p>
          </div></div>
        </div>
        <div class="col-md-3 col-xs-6">
          <div class="small-box {{ $covPct >= 80 ? 'bg-green' : 'bg-yellow' }}"><div class="inner">
            <h3 style="font-size:24px">{{ $covPct }}%</h3>
            <p>{{ __('Độ phủ') }}: {{ number_format($coverage->bills_detail ?? 0) }}/{{ number_format($covBills) }} {{ __('bill giá vốn có chi tiết') }}</p>
          </div></div>
        </div>
        <div class="col-md-3 col-xs-6">
          <div class="small-box bg-red"><div class="inner">
            <h3 style="font-size:24px">{{ number_format($coverage->bills_check ?? 0) }}</h3>
            <p><a href="{{ route('cost.index', ['detail_check' => 1, 'time_type' => 2, 'use_date_from' => $arrSearch['use_date_from'], 'use_date_to' => $arrSearch['use_date_to']]) }}" style="color:#fff;text-decoration:underline">{{ __('Bill lệch tiền chi tiết') }}</a></p>
          </div></div>
        </div>
        <div class="col-md-3 col-xs-6">
          <div class="small-box bg-gray"><div class="inner">
            <h3 style="font-size:24px">{{ number_format($reviewRows) }}</h3>
            <p>{{ __('Dòng cần kiểm tra') }} {{ $arrSearch['include_review'] ? __('(đang tính vào)') : __('(chưa tính vào)') }}</p>
          </div></div>
        </div>
      </div>

      {{-- Top 10 --}}
      @php
        $top = $items->filter(function ($it) { return $it->ingredient_id; })->take(10)->map(function ($it) {
          return ['name' => $it->name, 'unit' => $it->unit, 'qty' => (float) $it->qty, 'amount' => (float) $it->amount];
        })->values();
        $diffTotal = $prevTotal > 0 ? round(($totalAmount - $prevTotal) / $prevTotal * 100, 1) : null;
      @endphp
      @if($top->count())
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">{{ $sort == 'qty' ? __('Top 10 nguyên liệu nhập nhiều nhất theo số lượng') : __('Top 10 nguyên liệu nhập nhiều nhất theo tiền') }} – {{ $periodLabel }}</h3>
          @if($diffTotal !== null)
            <span style="margin-left:10px" class="{{ $diffTotal >= 0 ? 'text-red' : 'text-green' }}">{{ __('Tổng tiền nhập') }} {{ $diffTotal >= 0 ? '▲' : '▼' }} {{ abs($diffTotal) }}% {{ __('so với kỳ trước') }} ({{ number_format($prevTotal) }})</span>
          @endif
        </div>
        <div class="box-body"><div id="topChart" style="height:{{ 60 + $top->count() * 32 }}px"></div></div>
      </div>
      @endif

      {{-- Tổng hợp theo nguyên liệu --}}
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">{{ __('Tổng hợp theo nguyên liệu') }} ({{ $arrSearch['use_date_from'] }} – {{ $arrSearch['use_date_to'] }})</h3>
          <div class="box-tools" style="top:8px">
            <a href="{{ route('cost.bieu-do-gia') }}" class="btn btn-sm btn-primary"><i class="fa fa-line-chart"></i> {{ __('Biểu đồ giá') }}</a>
          </div>
        </div>
        <div class="box-body table-responsive">
          <table class="table table-bordered table-hover table-condensed">
            <thead>
              <tr style="background:#f4f4f4">
                <th style="width:1%">#</th>
                <th>{{ __('Nguyên liệu') }}</th>
                <th>{{ __('Nhóm') }}</th>
                <th class="text-right"><a href="{{ route('cost.nhap-hang', array_merge($arrSearch, ['ingredient_id' => null, 'sort' => 'qty', 'date' => $period != 'custom' ? $fromDate : null])) }}">{{ __('Số lượng') }} {!! $sort == 'qty' ? '▼' : '' !!}</a></th>
                <th>{{ __('ĐVT') }}</th>
                <th class="text-right">{{ __('Đơn giá TB') }}</th>
                <th class="text-right"><a href="{{ route('cost.nhap-hang', array_merge($arrSearch, ['ingredient_id' => null, 'sort' => 'amount', 'date' => $period != 'custom' ? $fromDate : null])) }}">{{ __('Tiền') }} {!! $sort == 'amount' ? '▼' : '' !!}</a></th>
                <th class="text-right">{{ __('% tiền') }}</th>
                <th class="text-center">{{ __('Số lần nhập') }}</th>
                <th class="text-right">{{ __('SL kỳ trước') }}</th>
                <th class="text-right">{{ __('Tiền kỳ trước') }}</th>
                <th class="text-right">{{ __('± tiền') }}</th>
                <th class="text-center">{{ __('Dòng cần KT') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($items as $k => $it)
                @php
                  $avg = ($it->qty_has_amount ?? 0) > 0 ? $it->amount_has_qty / $it->qty_has_amount : null;
                  $ingKey = $it->ingredient_id ?: 'none';
                  $pv = $prevMap[$ingKey] ?? null;
                  $pvAmount = $pv ? (float) $pv->amount : 0;
                  $diffPct = $pvAmount > 0 ? round(($it->amount - $pvAmount) / $pvAmount * 100, 1) : null;
                @endphp
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>
                    <a href="{{ route('cost.nhap-hang', array_merge($arrSearch, ['ingredient_id' => $ingKey])) }}">
                      {{ $it->name ?: __('(Chưa gắn nguyên liệu)') }}
                    </a>
                    @if($it->ingredient_id)
                      <a href="{{ route('cost.bieu-do-gia', ['ingredient_ids' => [$it->ingredient_id], 'branch_ids' => $arrSearch['branch_ids'], 'ncc_id' => $arrSearch['ncc_id']]) }}" title="Biểu đồ giá {{ $it->name }}" style="margin-left:4px">📈</a>
                    @endif
                  </td>
                  <td>{{ $it->cost_type_id && isset($cateArr[$it->cost_type_id]) ? $cateArr[$it->cost_type_id] : '' }}</td>
                  <td class="text-right">{{ $it->qty !== null ? rtrim(rtrim(number_format($it->qty, 2), '0'), '.') : '' }}</td>
                  <td>{{ $it->unit }}</td>
                  <td class="text-right">{{ $avg !== null ? number_format($avg) : '' }}</td>
                  <td class="text-right"><b>{{ number_format($it->amount) }}</b></td>
                  <td class="text-right">{{ $totalAmount > 0 ? number_format($it->amount / $totalAmount * 100, 1) : 0 }}%</td>
                  <td class="text-center">{{ $it->bills }}</td>
                  <td class="text-right">{{ $pv && $pv->qty !== null ? rtrim(rtrim(number_format($pv->qty, 2), '0'), '.') : '' }}</td>
                  <td class="text-right">{{ $pv ? number_format($pvAmount) : '' }}</td>
                  <td class="text-right">
                    @if($diffPct === null)
                      <span class="label label-info">{{ __('Mới') }}</span>
                    @else
                      <span class="{{ $diffPct >= 0 ? 'text-red' : 'text-green' }}">{{ $diffPct >= 0 ? '▲' : '▼' }} {{ abs($diffPct) }}%</span>
                    @endif
                  </td>
                  <td class="text-center">{!! $it->review_lines > 0 ? '<span class="label label-warning">'.$it->review_lines.'</span>' : '' !!}</td>
                </tr>
              @empty
                <tr><td colspan="13" class="text-center">{{ __('Chưa có dữ liệu chi tiết hàng trong kỳ này.') }}</td></tr>
              @endforelse
            </tbody>
            @if($items->count())
            <tfoot>
              <tr style="background:#f4f4f4">
                <th colspan="6" class="text-right">{{ __('Tổng') }}</th>
                <th class="text-right">{{ number_format($totalAmount) }}</th>
                <th colspan="3"></th>
                <th class="text-right">{{ number_format($prevTotal) }}</th>
                <th></th>
              </tr>
            </tfoot>
            @endif
          </table>
          <p class="text-muted" style="margin:0"><i>{{ __('Số lượng và đơn giá TB chỉ cộng các dòng cùng ĐVT chuẩn của nguyên liệu. Bấm vào tên nguyên liệu để xem từng lần nhập, bấm 📈 để xem biểu đồ giá.') }}</i></p>
        </div>
      </div>

      {{-- Chi tiết từng lần nhập của 1 nguyên liệu --}}
      @if($arrSearch['ingredient_id'])
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Chi tiết từng lần nhập:
            {{ $arrSearch['ingredient_id'] == 'none' ? __('(Chưa gắn nguyên liệu)') : optional($ingredientList->firstWhere('id', (int) $arrSearch['ingredient_id']))->name }}
          </h3>
        </div>
        <div class="box-body table-responsive">
          <table class="table table-bordered table-condensed">
            <thead>
              <tr style="background:#f4f4f4">
                <th>{{ __('Ngày') }}</th>
                <th>{{ __('Bill') }}</th>
                <th>{{ __('Chi nhánh') }}</th>
                <th>{{ __('NCC') }}</th>
                <th class="text-right">{{ __('SL') }}</th>
                <th>{{ __('ĐVT') }}</th>
                <th class="text-right">{{ __('Đơn giá') }}</th>
                <th class="text-right">{{ __('Thành tiền') }}</th>
                <th>{{ __('Ghi chú') }}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @php $lineSum = 0; @endphp
              @forelse($lines as $ln)
                @php $lineSum += (int) $ln->amount; @endphp
                <tr>
                  <td style="white-space:nowrap">{{ date('d/m/Y', strtotime($ln->date_use)) }}</td>
                  <td><a href="{{ route('cost.edit', $ln->cost_id) }}" target="_blank">CP{{ $ln->cost_id }}</a>
                    @if($ln->detail_check) <span class="label label-danger" title="{{ __('Bill lệch tiền chi tiết') }}">{{ __('Lệch') }}</span> @endif
                  </td>
                  <td>{{ $beachArr[$ln->branch_id] ?? '' }}</td>
                  <td>{{ $nccArr[$ln->ncc_id] ?? '' }}</td>
                  <td class="text-right">{{ $ln->quantity !== null ? rtrim(rtrim(number_format($ln->quantity, 3), '0'), '.') : '' }}</td>
                  <td>{{ $ln->unit ?: $ln->ingredient_unit }}</td>
                  <td class="text-right">{{ $ln->unit_price !== null ? number_format($ln->unit_price) : '' }}</td>
                  <td class="text-right">{{ $ln->amount !== null ? number_format($ln->amount) : '' }}</td>
                  <td>{{ $ln->notes }}</td>
                  <td>
                    @if($ln->need_review) <span class="label label-warning">{{ __('Cần KT') }}</span> @endif
                    @if($ln->source == 2) <span class="label label-default" title="{{ __('Bóc tách từ ghi chú cũ') }}">{{ __('Ghi chú cũ') }}</span> @endif
                    @if($ln->source == 3) <span class="label label-info" title="{{ __('Đọc từ ảnh hóa đơn') }}">{{ __('Đọc từ ảnh') }}</span> @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="10" class="text-center">{{ __('Không có dòng nào.') }}</td></tr>
              @endforelse
            </tbody>
            @if($lines->count())
            <tfoot>
              <tr style="background:#f4f4f4">
                <th colspan="7" class="text-right">{{ __('Tổng') }}</th>
                <th class="text-right">{{ number_format($lineSum) }}</th>
                <th colspan="2"></th>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
      </div>
      @endif

    </div>
  </div>
</section>
</div>
@stop
@section('js')
@if($top->count())
<script src="https://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript">
(function(){
  var data = @json($top);
  var byQty = {{ $sort == 'qty' ? 'true' : 'false' }};
  Highcharts.chart('topChart', {
    chart: { type: 'bar' }, title: { text: null }, credits: { enabled: false }, legend: { enabled: false },
    xAxis: { categories: data.map(function(r){ return r.name + (byQty && r.unit ? ' (' + r.unit + ')' : ''); }), title: { text: null } },
    yAxis: { title: { text: byQty ? {!! json_encode(__('Số lượng')) !!} : {!! json_encode(__('Tiền nhập (đ)')) !!} } },
    tooltip: { formatter: function(){ var r = data[this.point.index];
      return '<b>' + r.name + '</b><br>SL: ' + Highcharts.numberFormat(r.qty, 2, ',', '.') + ' ' + (r.unit || '') +
             '<br>' + {!! json_encode(__('Tiền')) !!} + ': ' + Highcharts.numberFormat(r.amount, 0, ',', '.') + ' đ'; } },
    plotOptions: { bar: { dataLabels: { enabled: true, formatter: function(){ return Highcharts.numberFormat(this.y, byQty ? 1 : 0, ',', '.'); } } } },
    series: [{ color: '#3c8dbc', data: data.map(function(r){ return byQty ? r.qty : r.amount; }) }]
  });
})();
</script>
@endif
<script type="text/javascript">
  $(document).ready(function () {
    $('#searchForm input[name="use_date_from"], #searchForm input[name="use_date_to"]').on('change', function () {
      $('#f_period').val('custom');
    });
    $('#searchForm input[name="branch_ids[]"], #include_review').change(function () {
      $('#searchForm').submit();
    });
  });
</script>
@stop
