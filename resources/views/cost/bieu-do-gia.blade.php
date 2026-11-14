@extends('layout')
@section('content')
@php
  $fmtQty = function($v){ return rtrim(rtrim(number_format((float) $v, 3), '0'), '.'); };
  $nccInHistory = [];
  foreach($history as $h){ $nccInHistory[$h['ncc_id']] = $h['ncc']; }
  asort($nccInHistory);
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Biểu đồ giá nguyên liệu') }}</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> {{ __('Trang chủ') }}</a></li>
    <li><a href="{{ route('cost.nhap-hang') }}">{{ __('Nhập hàng') }}</a></li>
    <li class="active">{{ __('Biểu đồ giá') }}</li>
  </ol>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{{ __('Bộ lọc') }}</h3></div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('cost.bieu-do-gia') }}" id="searchForm">
            <div class="form-group" style="width:100%;max-width:440px">
              <select class="form-control" name="ingredient_ids[]" id="ingredient_ids" multiple style="width:100%">
                @foreach($ingredientList as $ing)
                  <option value="{{ $ing->id }}" data-aliases="{{ $ing->aliases }}" {{ in_array($ing->id, $arrSearch['ingredient_ids']) ? "selected" : "" }}>{{ $ing->name }}{{ $ing->unit ? ' ('.$ing->unit.')' : '' }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="{{ __('Từ ngày') }}" value="{{ $arrSearch['use_date_from'] }}" style="width:110px">
            </div>
            <div class="form-group">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="{{ __('Đến ngày') }}" value="{{ $arrSearch['use_date_to'] }}" style="width:110px">
            </div>
            <div class="form-group">
              <select class="form-control" name="ncc_id" id="ncc_id" style="min-width:160px">
                <option value="">{{ __('--Nhà cung cấp--') }}</option>
                @foreach($nccList as $ncc)
                  <option value="{{ $ncc->id }}" {{ $arrSearch['ncc_id'] == $ncc->id ? "selected" : "" }}>{{ $ncc->name }}</option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="btn btn-info btn-sm" style="margin-top:-5px">{{ __('Xem') }}</button>
            <div style="margin-top:8px">
              <div class="btn-group btn-group-sm" style="margin-right:12px">
                <label class="btn btn-default {{ $arrSearch['mode'] == 'lan' ? 'active btn-primary' : '' }}">
                  <input type="radio" name="mode" value="lan" style="display:none" {{ $arrSearch['mode'] == 'lan' ? 'checked' : '' }}> {{ __('Theo từng lần nhập') }}
                </label>
                <label class="btn btn-default {{ $arrSearch['mode'] == 'ngay' ? 'active btn-primary' : '' }}">
                  <input type="radio" name="mode" value="ngay" style="display:none" {{ $arrSearch['mode'] == 'ngay' ? 'checked' : '' }}> {{ __('Bình quân theo ngày') }}
                </label>
              </div>
              <div class="form-group" title="{{ __('Chỉ dùng khi chọn 1 nguyên liệu') }}">
                <input type="checkbox" name="by_ncc" id="by_ncc" value="1" {{ $arrSearch['by_ncc'] ? "checked" : "" }} {{ count($arrSearch['ingredient_ids']) == 1 ? '' : 'disabled' }}>
                <label for="by_ncc">{{ __('Tách theo nhà cung cấp') }}</label>
              </div>
              &nbsp;&nbsp;
              <div class="form-group">
                <input type="checkbox" name="include_review" id="include_review" value="1" {{ $arrSearch['include_review'] ? "checked" : "" }}>
                <label for="include_review">{{ __('Gồm dòng cần kiểm tra') }}</label>
              </div>
            </div>
            <div style="margin-top:4px">
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

      @if(!$arrSearch['ingredient_ids'])
        <div class="callout callout-info">
          <p>{{ __('Chọn 1–5 nguyên liệu ở ô trên (gõ không dấu cũng tìm được, ví dụ "ca chua") rồi bấm') }} <b>{{ __('Xem') }}</b>.</p>
        </div>
      @else

      {{-- Biểu đồ --}}
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            {{ $arrSearch['mode'] == 'lan' ? __('Giá theo từng lần nhập') : __('Giá bình quân theo ngày') }}
            ({{ $arrSearch['use_date_from'] }} – {{ $arrSearch['use_date_to'] }})
          </h3>
        </div>
        <div class="box-body">
          @if(count($series))
            <div id="price_chart" style="height:460px"></div>
            <p class="text-muted" style="margin:6px 0 0"><i>{{ __('Rê chuột vào điểm để xem chi tiết, bấm vào điểm để mở bill.') }}</i></p>
          @else
            <p class="text-center">{{ __('Không có lần nhập nào khớp bộ lọc.') }}</p>
          @endif
          @if($skipped['unit'] || $skipped['zero'] || $skipped['review'])
            <p class="text-muted" style="margin:4px 0 0"><i>
              {{ __('Không đưa vào biểu đồ') }}:
              @if($skipped['unit']) {{ $skipped['unit'] }} {{ __('dòng khác đơn vị chuẩn') }}; @endif
              @if($skipped['zero']) {{ $skipped['zero'] }} {{ __('dòng không có số lượng/tiền (hàng tặng, bù…)') }}; @endif
              @if($skipped['review']) {{ $skipped['review'] }} {{ __('dòng cần kiểm tra (tick "Gồm dòng cần kiểm tra" để xem).') }} @endif
            </i></p>
          @endif
        </div>
      </div>

      {{-- Tóm tắt --}}
      <div class="box">
        <div class="box-header with-border"><h3 class="box-title">{{ __('Tóm tắt') }}</h3></div>
        <div class="box-body table-responsive">
          <table class="table table-bordered table-condensed">
            <thead>
              <tr style="background:#f4f4f4">
                <th>{{ __('Nguyên liệu') }}</th>
                <th class="text-center">{{ __('Số lần nhập') }}</th>
                <th class="text-right">{{ __('Tổng SL') }}</th>
                <th class="text-right">{{ __('Giá thấp nhất') }}</th>
                <th class="text-right">{{ __('Giá cao nhất') }}</th>
                <th class="text-right">{{ __('Giá TB') }}</th>
                <th class="text-right">{{ __('Lần đầu kỳ') }}</th>
                <th class="text-right">{{ __('Lần gần nhất') }}</th>
                <th class="text-right">{{ __('Thay đổi') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($arrSearch['ingredient_ids'] as $ingId)
                @php $ing = $ingArr->get($ingId); $sm = $summary[$ingId] ?? null; @endphp
                <tr>
                  <td><b>{{ optional($ing)->name }}</b> <span class="text-muted">/{{ optional($ing)->unit }}</span></td>
                  @if($sm)
                    <td class="text-center">{{ $sm['count'] }}</td>
                    <td class="text-right">{{ $fmtQty($sm['qty']) }}</td>
                    <td class="text-right">{{ number_format($sm['min']['price']) }}<br><small class="text-muted">{{ date('d/m', strtotime($sm['min']['date'])) }} · {{ $sm['min']['ncc'] }}</small></td>
                    <td class="text-right">{{ number_format($sm['max']['price']) }}<br><small class="text-muted">{{ date('d/m', strtotime($sm['max']['date'])) }} · {{ $sm['max']['ncc'] }}</small></td>
                    <td class="text-right">{{ $sm['avg'] !== null ? number_format($sm['avg']) : '' }}</td>
                    <td class="text-right">{{ number_format($sm['first']['price']) }}<br><small class="text-muted">{{ date('d/m/Y', strtotime($sm['first']['date'])) }}</small></td>
                    <td class="text-right">{{ number_format($sm['last']['price']) }}<br><small class="text-muted">{{ date('d/m/Y', strtotime($sm['last']['date'])) }}</small></td>
                    <td class="text-right">
                      @if($sm['change_pct'] !== null)
                        <span style="color:{{ $sm['change_pct'] > 0 ? '#dd4b39' : ($sm['change_pct'] < 0 ? '#00a65a' : '#333') }}">
                          {{ $sm['change_pct'] > 0 ? '+' : '' }}{{ number_format($sm['change_pct'], 1) }}%
                        </span>
                      @endif
                    </td>
                  @else
                    <td colspan="8" class="text-center text-muted">{{ __('Không có lần nhập trong kỳ') }}</td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>
          <p class="text-muted" style="margin:0"><i>{{ __('Giá TB = tổng tiền ÷ tổng số lượng. "Thay đổi" = lần gần nhất so với lần đầu kỳ.') }}</i></p>
        </div>
      </div>

      {{-- Lịch sử từng lần nhập --}}
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">{{ __('Lịch sử từng lần nhập') }} ({{ count($history) }})</h3>
          <div class="box-tools" style="top:8px">
            <select id="historyNcc" class="form-control input-sm" style="width:200px">
              <option value="">{{ __('Tất cả nhà cung cấp') }}</option>
              @foreach($nccInHistory as $nid => $nname)
                <option value="{{ $nid }}">{{ $nname }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="box-body table-responsive">
          <table class="table table-bordered table-condensed table-hover" id="historyTable">
            <thead>
              <tr style="background:#f4f4f4">
                <th class="sortable" data-type="num" style="cursor:pointer">{{ __('Ngày') }} <i class="fa fa-sort"></i></th>
                @if(count($arrSearch['ingredient_ids']) > 1)
                  <th class="sortable" data-type="text" style="cursor:pointer">{{ __('Nguyên liệu') }} <i class="fa fa-sort"></i></th>
                @endif
                <th>{{ __('Bill') }}</th>
                <th class="sortable" data-type="text" style="cursor:pointer">{{ __('Nhà cung cấp') }} <i class="fa fa-sort"></i></th>
                <th>{{ __('Chi nhánh') }}</th>
                <th class="text-right sortable" data-type="num" style="cursor:pointer">{{ __('SL') }} <i class="fa fa-sort"></i></th>
                <th class="text-right sortable" data-type="num" style="cursor:pointer">{{ __('Đơn giá') }} <i class="fa fa-sort"></i></th>
                <th class="text-right sortable" data-type="num" style="cursor:pointer">{{ __('Thành tiền') }} <i class="fa fa-sort"></i></th>
                <th class="text-right sortable" data-type="num" style="cursor:pointer">{{ __('So lần trước') }} <i class="fa fa-sort"></i></th>
              </tr>
            </thead>
            <tbody>
              @foreach($history as $h)
                @php $ing = $ingArr->get($h['ingredient_id']); @endphp
                <tr data-ncc="{{ $h['ncc_id'] }}">
                  <td data-sort="{{ strtotime($h['date']) * 100000 + $h['cost_id'] % 100000 }}" style="white-space:nowrap">{{ date('d/m/Y', strtotime($h['date'])) }}</td>
                  @if(count($arrSearch['ingredient_ids']) > 1)
                    <td>{{ optional($ing)->name }}</td>
                  @endif
                  <td><a href="{{ route('cost.edit', $h['cost_id']) }}" target="_blank">CP{{ $h['cost_id'] }}</a>
                    @if($h['need_review']) <span class="label label-warning">{{ __('Cần KT') }}</span> @endif
                  </td>
                  <td>{{ $h['ncc'] }}</td>
                  <td>{{ $h['branch'] }}</td>
                  <td class="text-right" data-sort="{{ $h['qty'] }}">{{ $fmtQty($h['qty']) }} {{ optional($ing)->unit }}</td>
                  <td class="text-right" data-sort="{{ $h['price'] }}"><b>{{ number_format($h['price']) }}</b></td>
                  <td class="text-right" data-sort="{{ $h['amount'] }}">{{ number_format($h['amount']) }}</td>
                  <td class="text-right" data-sort="{{ $h['diff_pct'] ?? 0 }}">
                    @if($h['diff'] !== null && round($h['diff']) != 0)
                      <span style="color:{{ $h['diff'] > 0 ? '#dd4b39' : '#00a65a' }}">
                        {{ $h['diff'] > 0 ? '▲ +' : '▼ ' }}{{ number_format($h['diff']) }}
                        @if($h['diff_pct'] !== null) ({{ $h['diff_pct'] > 0 ? '+' : '' }}{{ number_format($h['diff_pct'], 1) }}%) @endif
                      </span>
                    @elseif($h['diff'] !== null)
                      <span class="text-muted">=</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <p class="text-muted" style="margin:0"><i>{{ __('Nhiều dòng cùng nguyên liệu trong 1 bill được gộp thành 1 lần nhập. "So lần trước" so với lần nhập ngay trước của cùng nguyên liệu (mọi nhà cung cấp).') }}</i></p>
        </div>
      </div>
      @endif

    </div>
  </div>
</section>
</div>
@stop
@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
    function norm(s) {
      s = (s || '').toString().toLowerCase();
      if (s.normalize) s = s.normalize('NFD').replace(/[̀-ͯ]/g, '');
      return s.replace(/đ/g, 'd');
    }
    function matcher(params, data) {
      if ($.trim(params.term) === '') return data;
      var hay = norm(data.text + ' ' + ($(data.element).data('aliases') || ''));
      return hay.indexOf(norm(params.term)) > -1 ? data : null;
    }
    $('#ingredient_ids').select2({ matcher: matcher, maximumSelectionLength: 5, placeholder: {!! json_encode(__('Chọn nguyên liệu (tối đa 5)')) !!}, width: '100%' });
    $('#ncc_id').select2();
    $('#ingredient_ids').on('change', function () {
      var one = ($(this).val() || []).length === 1;
      $('#by_ncc').prop('disabled', !one);
      if (!one) $('#by_ncc').prop('checked', false);
    });
    $('#searchForm input[name="mode"]').change(function () { $('#searchForm').submit(); });
    $('#searchForm input[name="branch_ids[]"], #include_review, #by_ncc').change(function () {
      $('#searchForm').submit();
    });

    // Lọc lịch sử theo NCC
    $('#historyNcc').change(function () {
      var v = $(this).val();
      $('#historyTable tbody tr').each(function () {
        $(this).toggle(v === '' || String($(this).data('ncc')) === v);
      });
    });
    // Sắp xếp cột
    $('#historyTable th.sortable').click(function () {
      var $th = $(this), idx = $th.index(), type = $th.data('type');
      var asc = !$th.data('asc');
      $('#historyTable th.sortable').data('asc', null);
      $th.data('asc', asc);
      var rows = $('#historyTable tbody tr').get();
      rows.sort(function (a, b) {
        var $a = $(a).children().eq(idx), $b = $(b).children().eq(idx);
        var va = $a.data('sort') !== undefined ? $a.data('sort') : $a.text().trim();
        var vb = $b.data('sort') !== undefined ? $b.data('sort') : $b.text().trim();
        if (type === 'num') { va = parseFloat(va) || 0; vb = parseFloat(vb) || 0; return asc ? va - vb : vb - va; }
        return asc ? String(va).localeCompare(String(vb), 'vi') : String(vb).localeCompare(String(va), 'vi');
      });
      $.each(rows, function (i, r) { $('#historyTable tbody').append(r); });
    });

    @if(count($series))
    var series = @json($series);
    var editUrl = "{{ route('cost.edit', '__ID__') }}";
    function fmt(n) { return Highcharts.numberFormat(n, 0, ',', '.'); }
    Highcharts.setOptions({ lang: { months: {!! json_encode(app()->getLocale() == 'en' ? ['January','February','March','April','May','June','July','August','September','October','November','December'] : ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12']) !!},
      shortMonths: ['Th1','Th2','Th3','Th4','Th5','Th6','Th7','Th8','Th9','Th10','Th11','Th12'], thousandsSep: '.' } });
    Highcharts.chart('price_chart', {
      chart: { type: 'line', zoomType: 'x' },
      title: { text: null },
      credits: { enabled: false },
      xAxis: { type: 'datetime', dateTimeLabelFormats: { day: '%d/%m', week: '%d/%m', month: '%m/%Y' } },
      yAxis: { title: { text: {!! json_encode(__('Đơn giá (đ)')) !!} }, labels: { formatter: function () { return fmt(this.value); } } },
      legend: { enabled: true },
      tooltip: {
        useHTML: true,
        formatter: function () {
          var p = this.point, s = this.series;
          var unit = s.userOptions.unit || '';
          return '<b>' + s.name + '</b><br>' +
            Highcharts.dateFormat('%d/%m/%Y', p.x) + (p.bill ? ' · ' + p.bill : '') + '<br>' +
            {!! json_encode(__('Đơn giá')) !!} + ': <b>' + fmt(p.y) + 'đ/' + unit + '</b><br>' +
            {!! json_encode(__('SL')) !!} + ': ' + p.qty + ' ' + unit + ' · ' + {!! json_encode(__('Tiền')) !!} + ': ' + fmt(p.amount) + 'đ<br>' +
            (p.ncc ? 'NCC: ' + p.ncc + '<br>' : '') +
            (p.branch ? {!! json_encode(__('Chi nhánh')) !!} + ': ' + p.branch : '');
        }
      },
      plotOptions: {
        series: {
          marker: { enabled: true, radius: 3 },
          cursor: 'pointer',
          point: { events: { click: function () { if (this.cost_id) window.open(editUrl.replace('__ID__', this.cost_id), '_blank'); } } }
        }
      },
      series: series
    });
    @endif
  });
</script>
@stop
