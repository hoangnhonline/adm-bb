@extends('layout')
@section('content')
@php $nf = function($v){ return number_format((int) $v); }; @endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>Đối tác &amp; Công nợ</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Đối tác</li>
  </ol>
</section>
<section class="content">
  @if(Session::has('message'))
  <p class="alert alert-info">{{ Session::get('message') }}</p>
  @endif

  <div class="row">
    <div class="col-md-4 col-xs-12"><div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-arrow-down"></i></span>
      <div class="info-box-content"><span class="info-box-text">Đối tác đang nợ nhà hàng</span><span class="info-box-number">{{ $nf($tong['phai_thu']) }}</span></div></div></div>
    <div class="col-md-4 col-xs-12"><div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-arrow-up"></i></span>
      <div class="info-box-content"><span class="info-box-text">Hoa hồng nhà hàng còn phải trả</span><span class="info-box-number">{{ $nf($tong['phai_tra']) }}</span></div></div></div>
    <div class="col-md-4 col-xs-12"><div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-balance-scale"></i></span>
      <div class="info-box-content"><span class="info-box-text">Ròng (+ đối tác nợ / − mình nợ)</span><span class="info-box-number">{{ $nf($tong['rong']) }}</span></div></div></div>
  </div>

  <div class="box">
    <div class="box-header with-border">
      <form class="form-inline" method="GET" action="{{ route('doi-tac.index') }}">
        <select name="type" class="form-control input-sm" onchange="this.form.submit()">
          <option value="">Tất cả loại</option>
          @foreach($types as $k => $v)
          <option value="{{ $k }}" {{ $type == $k ? 'selected' : '' }}>{{ $v }}</option>
          @endforeach
        </select>
        <input type="text" name="name" class="form-control input-sm" placeholder="Tên đối tác" value="{{ $name }}">
        <button class="btn btn-info btn-sm">Tìm</button>
        <a href="{{ route('doi-tac.create') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Thêm đối tác</a>
        <a href="{{ route('doi-tac.report') }}" class="btn btn-default btn-sm"><i class="fa fa-bar-chart"></i> Báo cáo công nợ</a>
      </form>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-hover">
        <tr style="background:#f4f4f4">
          <th>#</th><th>Đối tác</th><th>Loại</th><th>Liên hệ</th>
          <th class="text-right">HH buffet/khách</th><th class="text-right">HH alacarte</th><th class="text-center">Hạn nợ</th>
          <th class="text-right">Số bill</th>
          <th class="text-right">Đang nợ mình</th><th class="text-right">Mình nợ HH</th><th class="text-right">Ròng</th><th></th>
        </tr>
        @forelse($items as $i => $p)
        @php $b = $balances[$p->id]; @endphp
        <tr>
          <td>{{ $i + 1 }}</td>
          <td><a href="{{ route('doi-tac.show', $p->id) }}"><strong>{{ $p->name }}</strong></a></td>
          <td>{{ $types[$p->type] ?? $p->type }}</td>
          <td>{{ $p->contact_name }} {{ $p->phone }}</td>
          <td class="text-right">{{ $nf($p->com_buffet_per_pax) }}</td>
          <td class="text-right">{{ rtrim(rtrim(number_format($p->com_alacarte_pct, 2), '0'), '.') }}%</td>
          <td class="text-center">{{ $p->pay_term_days > 0 ? $p->pay_term_days.' ngày' : 'Trả ngay' }}</td>
          <td class="text-right">{{ $nf($b['so_bill']) }}</td>
          <td class="text-right" style="color:#c0392b">{{ $b['phai_thu'] ? $nf($b['phai_thu']) : '' }}</td>
          <td class="text-right" style="color:#d68910">{{ $b['phai_tra'] ? $nf($b['phai_tra']) : '' }}</td>
          <td class="text-right"><strong>{{ $b['rong'] ? $nf($b['rong']) : '' }}</strong></td>
          <td style="white-space:nowrap">
            <a href="{{ route('doi-tac.show', $p->id) }}" class="btn btn-info btn-xs">Sổ công nợ</a>
            <a href="{{ route('doi-tac.edit', $p->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
          </td>
        </tr>
        @empty
        <tr><td colspan="12" class="text-center text-muted">Chưa có đối tác. Bấm "Thêm đối tác".</td></tr>
        @endforelse
      </table>
    </div>
  </div>
</section>
</div>
@stop
