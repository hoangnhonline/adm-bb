@extends('layout')
@section('content')
@php
  $nf = function($v, $d = 0){ return number_format((float) $v, $d); };
  $qf = function($v){ return rtrim(rtrim(number_format((float) $v, 2), '0'), '.'); };
  $pctHtml = function($v){
    if($v === null) return '<span class="text-muted">–</span>';
    $cls = $v >= 100 ? 'text-red' : ($v >= 50 ? 'text-orange' : ($v > 0 ? 'text-yellow' : 'text-green'));
    return '<b class="'.$cls.'">'.($v > 0 ? '+' : '').number_format($v, 0).'%</b>';
  };
  $fmtD = function($d){ return date('d/m/Y', strtotime($d)); };
  $base = array_filter(['compare' => $p['compare'], 'pct' => $p['pct'], 'min_amount' => $p['min_amount'], 'basis' => $p['basis'],
    'branch_ids' => $p['branch_ids'], 'cate_id' => $p['cate_id'], 'ncc_id' => $p['ncc_id'], 'include_review' => $p['include_review'] ?: null,
    'same_days' => $p['same_days'], 'include_new' => $p['include_new'], 'f' => 1], function($v){ return $v !== null && $v !== [] && $v !== ''; });
  $levelCls = ['high' => 'danger', 'medium' => 'warning', 'low' => '', 'new' => 'info'];
  $reasonNames = ['qty' => __('Tăng số lượng'), 'amount' => __('Tăng tiền nhập'), 'price' => __('Tăng giá'), 'new' => __('Mới phát sinh')];
  $reasonCls = ['qty' => 'danger', 'amount' => 'warning', 'price' => 'primary', 'new' => 'info'];
  $cnt = ['high' => 0, 'medium' => 0, 'low' => 0, 'new' => 0];
  foreach($res['rows'] as $r){ $cnt[$r->level]++; }
  $extraTotal = array_sum(array_map(function($r){ return max(0, $r->extra_amount); }, $res['rows']));
  $baseLabel = $p['compare'] == 'avg3' ? __('TB 3 kỳ trước') : __('Kỳ trước');
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Cảnh báo nhập hàng tăng bất thường') }}</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('cost.nhap-hang') }}">{{ __('Nhập hàng') }}</a></li>
    <li class="active">{{ __('Cảnh báo') }}</li>
  </ol>
</section>
<section class="content">
  <div class="panel panel-default"><div class="panel-body">
    <div class="btn-group" style="margin-bottom:8px">
      @foreach(['week', 'month', 'year'] as $per)
      <a href="{{ route('cost.canh-bao-nhap', array_merge($base, ['period' => $per])) }}" class="btn btn-sm {{ $p['period'] == $per ? 'btn-primary' : 'btn-default' }}">{{ $labels[$per] }}</a>
      @endforeach
      <a href="#" class="btn btn-sm {{ $p['period'] == 'custom' ? 'btn-primary' : 'btn-default' }}" onclick="$('#cbCustom').toggle();return false;">{{ $labels['custom'] }}</a>
    </div>
    <form method="GET" action="{{ route('cost.canh-bao-nhap') }}" class="form-inline" id="cbForm">
      <input type="hidden" name="f" value="1">
      <input type="hidden" name="period" value="{{ $p['period'] }}">
      @if($p['period'] != 'custom')<input type="hidden" name="date" value="{{ $res['from'] }}">@endif
      <span id="cbCustom" style="{{ $p['period'] == 'custom' ? '' : 'display:none' }}">
        <input type="date" name="from" class="form-control input-sm" value="{{ $p['from'] }}">
        <input type="date" name="to" class="form-control input-sm" value="{{ $p['to'] }}">
        <button type="submit" class="btn btn-sm btn-primary" onclick="this.form.period.value='custom'">{{ __('Xem') }}</button> &nbsp;
      </span>
      <select name="compare" class="form-control input-sm" title="{{ __('So sánh với') }}">
        <option value="prev" {{ $p['compare'] == 'prev' ? 'selected' : '' }}>{{ __('So với kỳ trước') }}</option>
        <option value="avg3" {{ $p['compare'] == 'avg3' ? 'selected' : '' }}>{{ __('So với TB 3 kỳ trước') }}</option>
      </select>
      <select name="basis" class="form-control input-sm">
        <option value="any" {{ $p['basis'] == 'any' ? 'selected' : '' }}>{{ __('Tăng số lượng hoặc tiền') }}</option>
        <option value="qty" {{ $p['basis'] == 'qty' ? 'selected' : '' }}>{{ __('Chỉ xét số lượng') }}</option>
        <option value="amount" {{ $p['basis'] == 'amount' ? 'selected' : '' }}>{{ __('Chỉ xét tiền nhập') }}</option>
      </select>
      <div class="input-group input-group-sm" style="width:120px">
        <span class="input-group-addon">≥</span>
        <input type="number" min="1" name="pct" class="form-control" value="{{ $p['pct'] }}" title="{{ __('Ngưỡng tăng (%)') }}">
        <span class="input-group-addon">%</span>
      </div>
      <div class="input-group input-group-sm" style="width:190px" title="{{ __('Bỏ qua nguyên liệu có tiền nhập kỳ này nhỏ hơn') }}">
        <span class="input-group-addon">{{ __('Tối thiểu') }}</span>
        <input type="text" name="min_amount" class="form-control number" value="{{ $nf($p['min_amount']) }}">
      </div>
      <select name="cate_id" class="form-control input-sm">
        <option value="">{{ __('--Nhóm nguyên liệu--') }}</option>
        @foreach($cateList as $c)<option value="{{ $c->id }}" {{ $p['cate_id'] == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
      </select>
      <select name="ncc_id" class="form-control input-sm">
        <option value="">{{ __('--Nhà cung cấp--') }}</option>
        @foreach($nccList as $n)<option value="{{ $n->id }}" {{ $p['ncc_id'] == $n->id ? 'selected' : '' }}>{{ $n->name }}</option>@endforeach
      </select>
      <button class="btn btn-sm btn-info">{{ __('Xem') }}</button>
      <div style="margin-top:6px">
        @foreach($branchList as $br)
        <label style="font-weight:normal;margin-right:10px"><input type="checkbox" name="branch_ids[]" value="{{ $br->id }}" {{ in_array($br->id, $p['branch_ids']) ? 'checked' : '' }}> {{ $br->name }}</label>
        @endforeach
        <label style="font-weight:normal;margin-right:10px"><input type="checkbox" name="same_days" value="1" {{ $p['same_days'] ? 'checked' : '' }}> {{ __('Kỳ đang chạy: so cùng số ngày') }}</label>
        <label style="font-weight:normal;margin-right:10px"><input type="checkbox" name="include_new" value="1" {{ $p['include_new'] ? 'checked' : '' }}> {{ __('Gồm nguyên liệu mới phát sinh') }}</label>
        <label style="font-weight:normal"><input type="checkbox" name="include_review" value="1" {{ $p['include_review'] ? 'checked' : '' }}> {{ __('Gồm dòng cần kiểm tra') }}</label>
      </div>
    </form>

    <h3 style="margin:14px 0 4px">
      @if($prevAnchor)<a href="{{ route('cost.canh-bao-nhap', array_merge($base, ['period' => $p['period'], 'date' => $prevAnchor])) }}" class="btn btn-default btn-sm" title="{{ __('Kỳ trước') }}"><i class="fa fa-chevron-left"></i></a>@endif
      {{ $fmtD($res['from']) }} – {{ $fmtD($res['to']) }}
      @if($nextAnchor)<a href="{{ route('cost.canh-bao-nhap', array_merge($base, ['period' => $p['period'], 'date' => $nextAnchor])) }}" class="btn btn-default btn-sm" title="{{ __('Kỳ sau') }}"><i class="fa fa-chevron-right"></i></a>@endif
    </h3>
    <div class="text-muted">
      {{ $baseLabel }}:
      @foreach($res['base_ranges'] as $i => $r){{ $i ? '; ' : '' }}{{ $fmtD($r[0]) }} – {{ $fmtD($r[1]) }}@endforeach
      @if($res['partial']) · <i>{{ __('Kỳ đang chạy: so sánh cùng :n ngày đầu kỳ', ['n' => $res['elapsed']]) }}</i>@endif
    </div>
  </div></div>

  <div class="row">
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Tăng ≥ 100%') }}</span><span class="info-box-number">{{ $cnt['high'] }}</span></div></div></div>
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-orange"><i class="fa fa-arrow-up"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Tăng 50–100%') }}</span><span class="info-box-number">{{ $cnt['medium'] }}</span>
      <span class="progress-description">{{ __('Dưới 50%') }}: {{ $cnt['low'] }}</span></div></div></div>
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-plus"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Mới phát sinh') }}</span><span class="info-box-number">{{ $cnt['new'] }}</span></div></div></div>
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Tiền nhập vượt') }}</span><span class="info-box-number">{{ $nf($extraTotal) }}</span>
      <span class="progress-description">{{ __('Tổng kỳ này') }} {{ $nf($res['total_cur']) }} / {{ $baseLabel }} {{ $nf($res['total_base']) }}</span></div></div></div>
  </div>

  <div class="box">
    <div class="box-header with-border"><h3 class="box-title">{{ __('Nguyên liệu cần chú ý') }} ({{ count($res['rows']) }})</h3>
      <small class="text-muted"> — {{ __('xếp theo số tiền nhập vượt so với kỳ so sánh') }}</small></div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-hover table-condensed">
        <tr style="background:#f4f4f4">
          <th rowspan="2">{{ __('Nguyên liệu') }}</th><th rowspan="2">{{ __('Nhóm') }}</th>
          <th colspan="3" class="text-center">{{ __('Số lượng') }}</th>
          <th colspan="3" class="text-center">{{ __('Tiền nhập') }}</th>
          <th colspan="2" class="text-center">{{ __('Đơn giá TB') }}</th>
          <th rowspan="2" class="text-center">{{ __('Số lần nhập') }}</th>
          <th rowspan="2">{{ __('Lý do') }}</th>
        </tr>
        <tr style="background:#f4f4f4">
          <th class="text-right">{{ __('Kỳ này') }}</th><th class="text-right">{{ $baseLabel }}</th><th class="text-right">±</th>
          <th class="text-right">{{ __('Kỳ này') }}</th><th class="text-right">{{ $baseLabel }}</th><th class="text-right">±</th>
          <th class="text-right">{{ __('Kỳ này') }}</th><th class="text-right">±</th>
        </tr>
        @forelse($res['rows'] as $r)
        <tr class="{{ $levelCls[$r->level] }}">
          <td>
            <a href="{{ route('cost.nhap-hang', ['period' => 'custom', 'use_date_from' => $fmtD($res['from']), 'use_date_to' => $fmtD($res['to']), 'ingredient_id' => $r->id, 'branch_ids' => $p['branch_ids']]) }}" title="{{ __('Xem từng lần nhập') }}"><b>{{ $r->name }}</b></a>
            <a href="{{ route('cost.bieu-do-gia', ['ingredient_ids' => [$r->id], 'branch_ids' => $p['branch_ids']]) }}" title="{{ __('Biểu đồ giá') }}">📈</a>
          </td>
          <td><small>{{ $cateArr[$r->cost_type_id] ?? '' }}</small></td>
          <td class="text-right">{{ $qf($r->qty) }} {{ $r->unit }}</td>
          <td class="text-right">{{ $qf($r->base_qty) }}</td>
          <td class="text-right">{!! $pctHtml($r->qty_pct) !!}</td>
          <td class="text-right">{{ $nf($r->amount) }}</td>
          <td class="text-right">{{ $nf($r->base_amount) }}</td>
          <td class="text-right">{!! $pctHtml($r->amount_pct) !!}<br><small>{{ $r->extra_amount > 0 ? '+' : '' }}{{ $nf($r->extra_amount) }}</small></td>
          <td class="text-right">{{ $r->price ? $nf($r->price) : '–' }}</td>
          <td class="text-right">{!! $pctHtml($r->price_pct) !!}</td>
          <td class="text-center">{{ $r->bills }} <small class="text-muted">/ {{ $qf($r->base_bills) }}</small></td>
          <td>@foreach($r->reasons as $rs)<span class="label label-{{ $reasonCls[$rs] }}" style="display:inline-block;margin:1px">{{ $reasonNames[$rs] }}</span> @endforeach</td>
        </tr>
        @empty
        <tr><td colspan="12" class="text-center text-muted">{{ __('Không có nguyên liệu nào tăng vượt ngưỡng.') }}</td></tr>
        @endforelse
      </table>
    </div>
    <div class="box-footer text-muted" style="font-size:12px">
      {{ __('Số lượng và đơn giá TB chỉ tính các dòng cùng ĐVT chuẩn của nguyên liệu; chỉ tính chi phí có nhập chi tiết hàng. Nếu tiền tăng nhưng số lượng không tăng, nhiều khả năng do tăng giá chứ không phải nhập nhiều.') }}
    </div>
  </div>
</section>
</div>
@stop
