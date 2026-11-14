@extends('layout')
@section('content')
@php
  $nf = function($v){ return number_format((float) $v); };
  $q = function($extra) use ($f, $view){ return route('report.vat', array_filter(array_merge($f, ['view' => $view], $extra), function($v){ return $v !== null && $v !== ''; })); };
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Báo cáo VAT & hoa hồng') }} <small>VAT {{ rtrim(rtrim(number_format($vatRate, 2), '0'), '.') }}% {{ __('trên (tổng bill − giảm giá)') }}</small></h1>
  <ol class="breadcrumb"><li><a href="{{ route('booking.index') }}">{{ __('Quản lý bill') }}</a></li><li class="active">{{ __('Báo cáo VAT & hoa hồng') }}</li></ol>
</section>
<section class="content">
  @if(Session::has('message'))<div class="alert alert-info">{{ Session::get('message') }}</div>@endif
  <div class="panel panel-default"><div class="panel-body">
    <form class="form-inline" method="GET" action="{{ route('report.vat') }}">
      <input type="hidden" name="view" value="{{ $view }}">
      <input type="date" name="from" class="form-control input-sm" value="{{ $f['from'] }}">
      <input type="date" name="to" class="form-control input-sm" value="{{ $f['to'] }}">
      <select name="branch_id" class="form-control input-sm">
        <option value="">{{ __('Tất cả chi nhánh') }}</option>
        @foreach($branches as $id => $n)<option value="{{ $id }}" {{ $f['branch_id'] == $id ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <select name="vat" class="form-control input-sm">
        <option value="">{{ __('Có VAT + KHÔNG VAT') }}</option>
        <option value="1" {{ $f['vat'] === '1' ? 'selected' : '' }}>{{ __('Chỉ bill có VAT') }}</option>
        <option value="0" {{ $f['vat'] === '0' ? 'selected' : '' }}>{{ __('Chỉ bill KHÔNG VAT') }}</option>
      </select>
      <select name="doi_tac" class="form-control input-sm">
        <option value="">{{ __('Tất cả khách') }}</option>
        <option value="0" {{ $f['doi_tac'] === 0 ? 'selected' : '' }}>{{ __('Khách lẻ (không đối tác)') }}</option>
        @foreach($partners as $id => $n)<option value="{{ $id }}" {{ $f['doi_tac'] === $id ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <select name="com" class="form-control input-sm">
        <option value="">{{ __('Hoa hồng: tất cả') }}</option>
        <option value="1" {{ $f['com'] === '1' ? 'selected' : '' }}>{{ __('Có hoa hồng') }}</option>
        <option value="0" {{ $f['com'] === '0' ? 'selected' : '' }}>{{ __('Không hoa hồng') }}</option>
      </select>
      <input type="text" name="q" class="form-control input-sm" value="{{ $f['q'] }}" placeholder="{{ __('Mã bill / bàn / ghi chú') }}" style="width:170px">
      <button class="btn btn-info btn-sm">{{ __('Xem') }}</button>
      <a href="{{ $q(['export' => 1]) }}" class="btn btn-success btn-sm"><i class="fa fa-download"></i> {{ __('Xuất CSV') }}</a>
    </form>
  </div></div>

  <div class="row">
    @foreach([
      [__('Doanh thu chưa VAT'), $total['net'], $total['n'] . ' ' . __('bill') . ' · ' . __('giảm giá') . ' ' . $nf($total['discount']), 'aqua'],
      [__('Tiền VAT'), $total['vat'], $total['n_vat'] . ' ' . __('bill có VAT') . ' · ' . __('doanh thu') . ' ' . $nf($total['net_vat']), 'red'],
      [__('KHÔNG VAT'), $total['net_novat'], $total['n_novat'] . ' ' . __('bill'), 'gray'],
      [__('Tổng thanh toán (gồm VAT)'), $total['pay'], '', 'green'],
      [__('Hoa hồng đối tác'), $total['com'], $total['n_com'] . ' ' . __('bill có hoa hồng'), 'yellow'],
    ] as $c)
    <div class="col-md-2 col-sm-4 col-xs-6" style="{{ $loop->index == 3 ? 'width:22%' : ($loop->index == 0 ? 'width:22%' : '') }}">
      <div class="small-box bg-{{ $c[3] }}" style="margin-bottom:10px"><div class="inner" style="padding:8px 10px">
        <h4 style="margin:0 0 4px;font-weight:bold">{{ $nf($c[1]) }}</h4><p style="margin:0">{{ $c[0] }}</p><small>{{ $c[2] }}</small>
      </div></div>
    </div>
    @endforeach
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Theo chi nhánh') }}</h3></div>
      <div class="box-body table-responsive no-padding"><table class="table table-bordered table-condensed" style="margin:0">
        <tr><th>{{ __('Chi nhánh') }}</th><th class="text-right">{{ __('Bill') }}</th><th class="text-right">{{ __('Chưa VAT') }}</th><th class="text-right">VAT</th><th class="text-right">{{ __('KHÔNG VAT') }}</th><th class="text-right">{{ __('Hoa hồng') }}</th></tr>
        @foreach($byBranch as $bid => $s)
        <tr><td><a href="{{ $q(['branch_id' => $bid]) }}">{{ $branches[$bid] ?? $bid }}</a></td><td class="text-right">{{ $s['n'] }}</td><td class="text-right">{{ $nf($s['net']) }}</td>
          <td class="text-right" style="color:#c0392b">{{ $nf($s['vat']) }}</td><td class="text-right">{{ $nf($s['net_novat']) }}</td><td class="text-right">{{ $nf($s['com']) }}</td></tr>
        @endforeach
      </table></div></div>
    </div>
    <div class="col-md-6">
      <div class="box"><div class="box-header with-border"><h3 class="box-title">{{ __('Hoa hồng theo đối tác') }}</h3></div>
      <div class="box-body table-responsive no-padding" style="max-height:260px;overflow:auto"><table class="table table-bordered table-condensed" style="margin:0">
        <tr><th>{{ __('Đối tác') }}</th><th class="text-right">{{ __('Bill') }}</th><th class="text-right">{{ __('Chưa VAT') }}</th><th class="text-right">{{ __('Hoa hồng') }}</th></tr>
        @forelse($byPartner as $pid => $s)
        <tr><td><a href="{{ $q(['doi_tac' => $pid]) }}">{{ $partners[$pid] ?? ('#' . $pid) }}</a></td><td class="text-right">{{ $s['n'] }}</td><td class="text-right">{{ $nf($s['net']) }}</td><td class="text-right" style="color:#d68910">{{ $nf($s['com']) }}</td></tr>
        @empty
        <tr><td colspan="4" class="text-muted">{{ __('Không có bill đối tác') }}</td></tr>
        @endforelse
      </table></div></div>
    </div>
  </div>

  <ul class="nav nav-tabs">
    <li class="{{ $view == 'detail' ? 'active' : '' }}"><a href="{{ $q(['view' => 'detail']) }}">{{ __('Chi tiết từng bill') }} ({{ $total['n'] }})</a></li>
    <li class="{{ $view == 'day' ? 'active' : '' }}"><a href="{{ $q(['view' => 'day']) }}">{{ __('Theo ngày') }}</a></li>
  </ul>
  <div class="box" style="border-top:0">
  @if($view == 'day')
    <div class="box-body table-responsive no-padding"><table class="table table-bordered table-striped table-condensed" style="margin:0">
      <thead><tr><th>{{ __('Ngày') }}</th><th class="text-right">{{ __('Bill') }}</th><th class="text-right">{{ __('Tổng tiền') }}</th><th class="text-right">{{ __('Giảm giá') }}</th><th class="text-right">{{ __('Chưa VAT') }}</th>
        <th class="text-right">VAT</th><th class="text-right">{{ __('KHÔNG VAT') }}</th><th class="text-right">{{ __('Tổng thanh toán') }}</th><th class="text-right">{{ __('Hoa hồng') }}</th></tr></thead>
      @foreach($byDay as $d => $s)
      <tr><td>{{ date('d/m/Y', strtotime($d)) }}</td><td class="text-right">{{ $s['n'] }}</td><td class="text-right">{{ $nf($s['total']) }}</td><td class="text-right">{{ $nf($s['discount']) }}</td>
        <td class="text-right">{{ $nf($s['net']) }}</td><td class="text-right" style="color:#c0392b">{{ $nf($s['vat']) }}</td><td class="text-right">{{ $nf($s['net_novat']) }}</td>
        <td class="text-right"><b>{{ $nf($s['pay']) }}</b></td><td class="text-right">{{ $nf($s['com']) }}</td></tr>
      @endforeach
      <tr style="font-weight:bold;background:#f4f4f4"><td>{{ __('Tổng') }}</td><td class="text-right">{{ $total['n'] }}</td><td class="text-right">{{ $nf($total['total']) }}</td><td class="text-right">{{ $nf($total['discount']) }}</td>
        <td class="text-right">{{ $nf($total['net']) }}</td><td class="text-right">{{ $nf($total['vat']) }}</td><td class="text-right">{{ $nf($total['net_novat']) }}</td><td class="text-right">{{ $nf($total['pay']) }}</td><td class="text-right">{{ $nf($total['com']) }}</td></tr>
    </table></div>
  @else
    <form method="POST" action="{{ route('report.vat.no-vat') }}" id="noVatForm">
      {!! csrf_field() !!}
      <input type="hidden" name="value" id="noVatValue" value="1">
      @if($canEdit)
      <div class="box-header" style="padding:8px 10px">
        <button type="submit" class="btn btn-default btn-sm" onclick="document.getElementById('noVatValue').value=1;return confirmNoVat()"><i class="fa fa-ban"></i> {{ __('Đánh dấu KHÔNG VAT') }}</button>
        <button type="submit" class="btn btn-default btn-sm" onclick="document.getElementById('noVatValue').value=0;return confirmNoVat()"><i class="fa fa-check"></i> {{ __('Bỏ KHÔNG VAT (tính VAT)') }}</button>
        <span class="text-muted" style="margin-left:8px">{{ __('Tick chọn bill bên dưới để đổi trạng thái VAT. Chi nhánh Vui Fest (bill Sapo) luôn KHÔNG VAT.') }}</span>
      </div>
      @endif
      <div class="box-body table-responsive no-padding"><table class="table table-bordered table-striped table-condensed table-hover" style="margin:0">
        <thead><tr>
          @if($canEdit)<th style="width:28px"><input type="checkbox" onclick="var c=this.checked;document.querySelectorAll('.nv-id').forEach(function(x){x.checked=c})"></th>@endif
          <th>{{ __('Mã bill') }}</th><th>{{ __('Ngày') }}</th><th>{{ __('Chi nhánh') }}</th><th>{{ __('Bàn') }}</th><th>{{ __('Đối tác') }}</th>
          <th class="text-right">{{ __('Tổng tiền') }}</th><th class="text-right">{{ __('Giảm giá') }}</th><th class="text-right">{{ __('Chưa VAT') }}</th>
          <th class="text-right">VAT</th><th class="text-right">{{ __('Tổng thanh toán') }}</th><th class="text-right">{{ __('Hoa hồng') }}</th><th>{{ __('Ghi chú') }}</th></tr></thead>
        @foreach($rows as $r)
        <tr>
          @if($canEdit)<td><input type="checkbox" class="nv-id" name="ids[]" value="{{ $r->id }}"></td>@endif
          <td><a href="{{ route('booking.edit', $r->id) }}" target="_blank">#{{ $r->id }}</a></td>
          <td>{{ date('d/m/Y', strtotime($r->use_date)) }}</td>
          <td>{{ $branches[$r->branch_id] ?? $r->branch_id }}</td>
          <td>{{ $r->table_no }}</td>
          <td>{{ $r->doi_tac_id ? ($partners[$r->doi_tac_id] ?? '#' . $r->doi_tac_id) : '' }}</td>
          <td class="text-right">{{ $nf($r->total_price) }}</td>
          <td class="text-right">{{ $r->discount ? $nf($r->discount) : '' }}</td>
          <td class="text-right">{{ $nf($r->net) }}</td>
          <td class="text-right">@if($r->no_vat)<span class="label label-default">{{ __('KHÔNG VAT') }}</span>@else<span style="color:#c0392b">{{ $nf($r->vat) }}</span>@endif</td>
          <td class="text-right"><b>{{ $nf($r->pay) }}</b></td>
          <td class="text-right">@if($r->com){{ $nf($r->com) }}<br><small class="text-muted">{{ $r->bill_type == 1 ? $r->pax . ' ' . __('khách') : ($r->com_pct !== null ? (float) $r->com_pct . '%' : 'Alacarte') }}</small>@endif</td>
          <td style="max-width:220px"><small>{{ \Illuminate\Support\Str::limit($r->notes, 80) }}</small></td>
        </tr>
        @endforeach
        <tr style="font-weight:bold;background:#f4f4f4">
          @if($canEdit)<td></td>@endif
          <td colspan="5">{{ __('Tổng') }} ({{ $total['n'] }} {{ __('bill') }})</td>
          <td class="text-right">{{ $nf($total['total']) }}</td><td class="text-right">{{ $nf($total['discount']) }}</td><td class="text-right">{{ $nf($total['net']) }}</td>
          <td class="text-right">{{ $nf($total['vat']) }}</td><td class="text-right">{{ $nf($total['pay']) }}</td><td class="text-right">{{ $nf($total['com']) }}</td><td></td></tr>
      </table></div>
    </form>
  @endif
  </div>
</section>
</div>
<script>
function confirmNoVat(){
  var n = document.querySelectorAll('.nv-id:checked').length;
  if(!n){ alert({!! json_encode(__('Chưa chọn bill nào')) !!}); return false; }
  return confirm({!! json_encode(__('Cập nhật trạng thái VAT cho')) !!} + ' ' + n + ' ' + {!! json_encode(__('bill')) !!} + '?');
}
</script>
@endsection
