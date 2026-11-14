@extends('layout')
@section('content')
@php $nf = function($v){ return number_format((int) $v); }; @endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Đối tác & Công nợ') }}</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> {{ __('Trang chủ') }}</a></li>
    <li class="active">{{ __('Đối tác') }}</li>
  </ol>
</section>
<section class="content">
  @if(Session::has('message'))
  <p class="alert alert-info">{{ Session::get('message') }}</p>
  @endif

  <div class="row">
    <div class="col-md-4 col-xs-12"><div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-arrow-down"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Đối tác đang nợ nhà hàng') }}</span><span class="info-box-number">{{ $nf($tong['phai_thu']) }}</span></div></div></div>
    <div class="col-md-4 col-xs-12"><div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-arrow-up"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Hoa hồng nhà hàng còn phải trả') }}</span><span class="info-box-number">{{ $nf($tong['phai_tra']) }}</span></div></div></div>
    <div class="col-md-4 col-xs-12"><div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-balance-scale"></i></span>
      <div class="info-box-content"><span class="info-box-text">{{ __('Ròng (+ đối tác nợ / − mình nợ)') }}</span><span class="info-box-number">{{ $nf($tong['rong']) }}</span></div></div></div>
  </div>

  @if(\App\Services\Perm::can('partner.edit'))
  <div class="box box-default collapsed-box" id="comSettings">
    <div class="box-header with-border"><h3 class="box-title" style="font-size:15px"><i class="fa fa-cog"></i> {{ __('Cài đặt hoa hồng mặc định') }}:
      {{ __('alacarte') }} <b>{{ rtrim(rtrim(number_format((float) \App\Services\AppSetting::get('com_alacarte_pct_default'), 2), '0'), '.') }}%</b> ·
      {{ __('buffet') }} <b>{{ number_format((int) \App\Services\AppSetting::get('com_buffet_per_pax_default')) }}</b>/{{ __('khách') }}</h3>
      <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i> {{ __('Sửa') }}</button></div></div>
    <div class="box-body">
      <form class="form-inline" method="POST" action="{{ route('doi-tac.settings') }}">
        {!! csrf_field() !!}
        <label>{{ __('Hoa hồng alacarte (%)') }}</label>
        <input type="text" name="com_alacarte_pct_default" class="form-control input-sm" style="width:80px" value="{{ rtrim(rtrim(number_format((float) \App\Services\AppSetting::get('com_alacarte_pct_default'), 2, '.', ''), '0'), '.') }}">
        &nbsp; <label>{{ __('Hoa hồng buffet (đ / khách)') }}</label>
        <input type="text" name="com_buffet_per_pax_default" class="form-control input-sm number" style="width:110px" value="{{ (int) \App\Services\AppSetting::get('com_buffet_per_pax_default') }}">
        &nbsp; <label style="font-weight:normal"><input type="checkbox" name="apply_all" value="1"> {{ __('Áp dụng cho tất cả đối tác hiện có (và tính lại bill cũ)') }}</label>
        <button class="btn btn-primary btn-sm" onclick="return !this.form.apply_all.checked || confirm({{ json_encode(__('Đổi mức hoa hồng cho tất cả đối tác và tính lại hoa hồng các bill cũ?')) }})">{{ __('Lưu') }}</button>
      </form>
      <p class="text-muted" style="margin:6px 0 0">{{ __('Mức mặc định dùng cho đối tác mới. Từng đối tác vẫn sửa mức riêng được; trên từng bill cũng nhập được % riêng.') }}</p>
    </div>
  </div>
  @endif
  <div class="box">
    <div class="box-header with-border">
      <form class="form-inline" method="GET" action="{{ route('doi-tac.index') }}">
        <select name="type" class="form-control input-sm" onchange="this.form.submit()">
          <option value="">{{ __('Tất cả loại') }}</option>
          @foreach($types as $k => $v)
          <option value="{{ $k }}" {{ $type == $k ? 'selected' : '' }}>{{ $v }}</option>
          @endforeach
        </select>
        <input type="text" name="name" class="form-control input-sm" placeholder="{{ __('Tên đối tác') }}" value="{{ $name }}">
        <button class="btn btn-info btn-sm">{{ __('Tìm') }}</button>
        <a href="{{ route('doi-tac.create') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> {{ __('Thêm đối tác') }}</a>
        <a href="{{ route('doi-tac.report') }}" class="btn btn-default btn-sm"><i class="fa fa-bar-chart"></i> {{ __('Báo cáo công nợ') }}</a>
      </form>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-hover">
        <tr style="background:#f4f4f4">
          <th>#</th><th>{{ __('Đối tác') }}</th><th>{{ __('Loại') }}</th><th>{{ __('Liên hệ') }}</th>
          <th class="text-right">{{ __('HH buffet/khách') }}</th><th class="text-right">{{ __('HH alacarte') }}</th><th class="text-center">{{ __('Hạn nợ') }}</th>
          <th class="text-right">{{ __('Số bill') }}</th>
          <th class="text-right">{{ __('Đang nợ mình') }}</th><th class="text-right">{{ __('Mình nợ HH') }}</th><th class="text-right">{{ __('Ròng') }}</th><th></th>
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
          <td class="text-center">{{ $p->pay_term_days > 0 ? __(':n ngày', ['n' => $p->pay_term_days]) : __('Trả ngay') }}</td>
          <td class="text-right">{{ $nf($b['so_bill']) }}</td>
          <td class="text-right" style="color:#c0392b">{{ $b['phai_thu'] ? $nf($b['phai_thu']) : '' }}</td>
          <td class="text-right" style="color:#d68910">{{ $b['phai_tra'] ? $nf($b['phai_tra']) : '' }}</td>
          <td class="text-right"><strong>{{ $b['rong'] ? $nf($b['rong']) : '' }}</strong></td>
          <td style="white-space:nowrap">
            <a href="{{ route('doi-tac.show', $p->id) }}" class="btn btn-info btn-xs">{{ __('Sổ công nợ') }}</a>
            <a href="{{ route('doi-tac.edit', $p->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
          </td>
        </tr>
        @empty
        <tr><td colspan="12" class="text-center text-muted">{{ __('Chưa có đối tác. Bấm "Thêm đối tác".') }}</td></tr>
        @endforelse
      </table>
    </div>
  </div>
</section>
</div>
@stop
