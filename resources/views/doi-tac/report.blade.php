@extends('layout')
@section('content')
@php
  $nf = function($v){ return $v ? number_format((int) $v) : ''; };
  $periodNames = ['week' => __('Tuần'), 'month' => __('Tháng'), 'year' => __('Năm')];
  $base = ['type' => $type];
  $dtDoanhThu = $tong['doanh_thu'];
  $leDoanhThu = (int) ($khachLe->doanh_thu ?? 0);
  $allDt = $dtDoanhThu + $leDoanhThu;
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Báo cáo đối tác & công nợ') }}</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('doi-tac.index') }}">{{ __('Đối tác') }}</a></li>
    <li class="active">{{ __('Báo cáo') }}</li>
  </ol>
</section>
<section class="content">
  <div class="panel panel-default"><div class="panel-body">
    <div class="btn-group">
      @foreach($periodNames as $p => $n)
      <a href="{{ route('doi-tac.report', array_merge($base, ['period' => $p])) }}" class="btn btn-sm {{ $period == $p ? 'btn-primary' : 'btn-default' }}">{{ $n }}</a>
      @endforeach
    </div>
    <form class="form-inline" method="GET" action="{{ route('doi-tac.report') }}" style="display:inline-block;margin-left:10px">
      <input type="hidden" name="period" value="custom">
      <input type="date" name="from" class="form-control input-sm" value="{{ date('Y-m-d', strtotime(str_replace('/', '-', $fromStr))) }}">
      <input type="date" name="to" class="form-control input-sm" value="{{ date('Y-m-d', strtotime(str_replace('/', '-', $toStr))) }}">
      <select name="type" class="form-control input-sm">
        <option value="">{{ __('Tất cả loại') }}</option>
        @foreach($types as $k => $v)<option value="{{ $k }}" {{ $type == $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
      </select>
      <button class="btn btn-sm btn-primary">{{ __('Xem') }}</button>
    </form>
    <h3 style="margin:15px 0 5px">
      @if($period != 'custom')<a href="{{ route('doi-tac.report', array_merge($base, ['period' => $period, 'date' => $prevAnchor])) }}" class="btn btn-default btn-sm"><i class="fa fa-chevron-left"></i></a>@endif
      {{ $label }}
      @if($period != 'custom')<a href="{{ route('doi-tac.report', array_merge($base, ['period' => $period, 'date' => $nextAnchor])) }}" class="btn btn-default btn-sm"><i class="fa fa-chevron-right"></i></a>@endif
    </h3>
  </div></div>

  <div class="row">
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-handshake-o"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Doanh thu từ đối tác') }}</span><span class="info-box-number">{{ number_format($dtDoanhThu) }}</span>
      <span class="progress-description">{{ $allDt ? number_format($dtDoanhThu * 100 / $allDt, 1) : 0 }}% {{ __('tổng') }} · {{ number_format($tong['so_bill']) }} {{ __('bill') }}</span></div></div></div>
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-gray"><i class="fa fa-user"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Doanh thu khách lẻ') }}</span><span class="info-box-number">{{ number_format($leDoanhThu) }}</span>
      <span class="progress-description">{{ number_format((int) ($khachLe->so_bill ?? 0)) }} {{ __('bill') }}</span></div></div></div>
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-percent"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Hoa hồng phát sinh trong kỳ') }}</span><span class="info-box-number">{{ number_format($tong['hh']) }}</span>
      <span class="progress-description">{{ $dtDoanhThu ? number_format($tong['hh'] * 100 / $dtDoanhThu, 1) : 0 }}% {{ __('doanh thu đối tác') }} · {{ number_format($tong['pax']) }} {{ __('khách buffet') }}</span></div></div></div>
    <div class="col-md-3 col-xs-6"><div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Nợ quá hạn (hiện tại)') }}</span><span class="info-box-number">{{ number_format($tong['qua_han']) }}</span>
      <span class="progress-description">{{ __('Tổng phải thu') }} {{ number_format($tong['phai_thu']) }}</span></div></div></div>
  </div>

  <div class="box">
    <div class="box-header with-border"><h3 class="box-title">{{ __('Theo đối tác') }}</h3>
      <small class="text-muted">{{ __('— cột "Trong kỳ" theo kỳ đang chọn; cột "Số dư" và "Tuổi nợ" tính đến hôm nay') }}</small></div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-hover table-condensed">
        <tr style="background:#f4f4f4">
          <th rowspan="2">{{ __('Đối tác') }}</th><th rowspan="2">{{ __('Loại') }}</th>
          <th colspan="5" class="text-center">{{ __('Trong kỳ') }}</th>
          <th colspan="3" class="text-center">{{ __('Số dư hiện tại') }}</th>
          <th colspan="5" class="text-center">{{ __('Tuổi nợ phải thu') }}</th>
        </tr>
        <tr style="background:#f4f4f4">
          <th class="text-right">{{ __('Bill') }}</th><th class="text-right">{{ __('Khách buffet') }}</th><th class="text-right">{{ __('Doanh thu') }}</th><th class="text-right">{{ __('Ghi nợ') }}</th><th class="text-right">{{ __('Hoa hồng') }}</th>
          <th class="text-right">{{ __('Phải thu') }}</th><th class="text-right">{{ __('Phải trả HH') }}</th><th class="text-right">{{ __('Ròng') }}</th>
          <th class="text-right">0–30</th><th class="text-right">31–60</th><th class="text-right">61–90</th><th class="text-right">&gt;90</th><th class="text-right">{{ __('Quá hạn') }}</th>
        </tr>
        @forelse($rows as $r)
        <tr>
          <td><a href="{{ route('doi-tac.show', $r['p']->id) }}">{{ $r['p']->name }}</a>{!! $r['p']->status != 1 ? ' <small class="text-muted">'.e(__('(đã ẩn)')).'</small>' : '' !!}</td>
          <td>{{ $types[$r['p']->type] ?? $r['p']->type }}</td>
          <td class="text-right">{{ $nf($r['so_bill']) }}</td><td class="text-right">{{ $nf($r['pax']) }}</td>
          <td class="text-right">{{ $nf($r['doanh_thu']) }}</td><td class="text-right">{{ $nf($r['ghi_no']) }}</td><td class="text-right">{{ $nf($r['hh']) }}</td>
          <td class="text-right" style="color:#c0392b">{{ $nf($r['phai_thu']) }}</td><td class="text-right" style="color:#d68910">{{ $nf($r['phai_tra']) }}</td><td class="text-right"><b>{{ $nf($r['rong']) }}</b></td>
          <td class="text-right">{{ $nf($r['0_30']) }}</td><td class="text-right">{{ $nf($r['31_60']) }}</td><td class="text-right">{{ $nf($r['61_90']) }}</td><td class="text-right">{{ $nf($r['tren_90']) }}</td>
          <td class="text-right" style="color:#c0392b"><b>{{ $nf($r['qua_han']) }}</b></td>
        </tr>
        @empty
        <tr><td colspan="15" class="text-center text-muted">{{ __('Chưa có dữ liệu') }}</td></tr>
        @endforelse
        <tr style="background:#f9f9f9;font-weight:bold">
          <td colspan="2">{{ __('TỔNG') }}</td>
          <td class="text-right">{{ $nf($tong['so_bill']) }}</td><td class="text-right">{{ $nf($tong['pax']) }}</td><td class="text-right">{{ $nf($tong['doanh_thu']) }}</td>
          <td class="text-right">{{ $nf($tong['ghi_no']) }}</td><td class="text-right">{{ $nf($tong['hh']) }}</td>
          <td class="text-right">{{ $nf($tong['phai_thu']) }}</td><td class="text-right">{{ $nf($tong['phai_tra']) }}</td><td class="text-right">{{ $nf($tong['rong']) }}</td>
          <td class="text-right">{{ $nf($tong['0_30']) }}</td><td class="text-right">{{ $nf($tong['31_60']) }}</td><td class="text-right">{{ $nf($tong['61_90']) }}</td><td class="text-right">{{ $nf($tong['tren_90']) }}</td><td class="text-right">{{ $nf($tong['qua_han']) }}</td>
        </tr>
      </table>
    </div>
  </div>
</section>
</div>
@stop
