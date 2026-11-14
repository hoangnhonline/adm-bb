@extends('layout')
@section('content')
@php
  $nf = function($v){ return number_format((int) $v); };
  $maxBuTru = max(0, min($bal['phai_thu'], $bal['phai_tra']));
  $statusNames = [1 => __('Mới'), 2 => __('Hoàn tất'), 4 => __('Đã tiếp nhận'), 5 => __('Đang triển khai')];
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ $detail->name }} <small>{{ $types[$detail->type] ?? $detail->type }}
    · {{ __('HH buffet') }} {{ $nf($detail->com_buffet_per_pax) }}/{{ __('khách') }} · {{ __('alacarte') }} {{ rtrim(rtrim(number_format($detail->com_alacarte_pct, 2), '0'), '.') }}%
    · {{ $detail->pay_term_days > 0 ? __('nợ :n ngày', ['n' => $detail->pay_term_days]) : __('trả ngay') }}</small></h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('doi-tac.index') }}">{{ __('Đối tác') }}</a></li>
    <li class="active">{{ __('Sổ công nợ') }}</li>
  </ol>
</section>
<section class="content">
  @if(Session::has('message'))
  <p class="alert alert-info">{{ Session::get('message') }}</p>
  @endif
  @if(count($errors) > 0)
  <div class="alert alert-danger"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  {{-- Số dư hiện tại: tách riêng phải thu / phải trả / bù trừ --}}
  <div class="row">
    <div class="col-md-4">
      <div class="box box-danger"><div class="box-header with-border"><h3 class="box-title">{{ __('Đối tác nợ nhà hàng (phải thu)') }}</h3></div>
        <table class="table table-condensed" style="margin:0">
          <tr><td>{{ __('Bill ghi nợ phát sinh') }}</td><td class="text-right">{{ $nf($bal['no_phat_sinh']) }}</td></tr>
          <tr><td>{{ __('− Đối tác đã thanh toán') }}</td><td class="text-right">{{ $nf($bal['da_thu']) }}</td></tr>
          <tr><td>{{ __('− Đã bù trừ với hoa hồng') }}</td><td class="text-right">{{ $nf($bal['bu_tru']) }}</td></tr>
          <tr style="font-size:16px"><th>{{ __('Còn phải thu') }}</th><th class="text-right" style="color:#c0392b">{{ $nf($bal['phai_thu']) }}</th></tr>
        </table></div>
    </div>
    <div class="col-md-4">
      <div class="box box-warning"><div class="box-header with-border"><h3 class="box-title">{{ __('Nhà hàng nợ hoa hồng (phải trả)') }}</h3></div>
        <table class="table table-condensed" style="margin:0">
          <tr><td>{{ __('Hoa hồng phát sinh') }}</td><td class="text-right">{{ $nf($bal['hh_phat_sinh']) }}</td></tr>
          <tr><td>{{ __('− Đã chi hoa hồng') }}</td><td class="text-right">{{ $nf($bal['da_chi']) }}</td></tr>
          <tr><td>{{ __('− Đã bù trừ với công nợ') }}</td><td class="text-right">{{ $nf($bal['bu_tru']) }}</td></tr>
          <tr style="font-size:16px"><th>{{ __('Còn phải trả') }}</th><th class="text-right" style="color:#d68910">{{ $nf($bal['phai_tra']) }}</th></tr>
        </table></div>
    </div>
    <div class="col-md-4">
      <div class="box box-info"><div class="box-header with-border"><h3 class="box-title">{{ __('Tổng hợp') }}</h3></div>
        <table class="table table-condensed" style="margin:0">
          <tr><td>{{ __('Tổng số bill / khách buffet') }}</td><td class="text-right">{{ $nf($bal['so_bill']) }} / {{ $nf($bal['pax']) }}</td></tr>
          <tr><td>{{ __('Tổng doanh thu từ đối tác') }}</td><td class="text-right">{{ $nf($bal['doanh_thu']) }}</td></tr>
          <tr><td>{{ __('Có thể bù trừ tối đa') }}</td><td class="text-right">{{ $nf($maxBuTru) }}</td></tr>
          <tr style="font-size:16px"><th>{{ __('Ròng') }} {{ $bal['rong'] >= 0 ? __('(đối tác nợ mình)') : __('(mình nợ đối tác)') }}</th><th class="text-right">{{ $nf(abs($bal['rong'])) }}</th></tr>
        </table></div>
    </div>
  </div>

  {{-- Ghi giao dịch --}}
  <div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">{{ __('Ghi nhận thanh toán / chi hoa hồng / bù trừ') }}</h3></div>
    <form method="POST" action="{{ route('doi-tac.transaction.store', $detail->id) }}" class="box-body" id="transForm">
      {!! csrf_field() !!}
      <div class="row">
        <div class="form-group col-md-3"><label>{{ __('Loại giao dịch') }}</label>
          <select name="type" id="t_type" class="form-control">
            <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>{{ __('Đối tác thanh toán cho mình') }}</option>
            <option value="2" {{ old('type') == 2 ? 'selected' : '' }}>{{ __('Chi hoa hồng cho đối tác') }}</option>
            <option value="3" {{ old('type') == 3 ? 'selected' : '' }}>{{ __('Bù trừ (trừ HH vào nợ)') }}</option>
          </select></div>
        <div class="form-group col-md-2"><label>{{ __('Ngày') }}</label>
          <input type="text" name="date" class="form-control datepicker" autocomplete="off" value="{{ old('date', date('d/m/Y')) }}"></div>
        <div class="form-group col-md-2"><label>{{ __('Số tiền') }} <a href="#" id="t_fill" style="font-weight:normal">{{ __('(điền tối đa)') }}</a></label>
          <input type="text" name="amount" id="t_amount" class="form-control number" value="{{ old('amount') }}"></div>
        <div class="form-group col-md-2" id="t_pay_wrap"><label id="t_pay_label">{{ __('Quỹ') }}</label>
          <select name="pay_method" class="form-control">
            @foreach($collecterList as $c)
            <option value="{{ $c->id }}" {{ old('pay_method', 2) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
          </select></div>
        <div class="form-group col-md-3"><label>{{ __('Ghi chú') }}</label>
          <input type="text" name="notes" class="form-control" value="{{ old('notes') }}"></div>
      </div>
      <p class="text-muted" id="t_hint" style="margin:0"></p>
      <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm({{ json_encode(__('Ghi giao dịch này?')) }})">{{ __('Ghi nhận') }}</button>
    </form>
  </div>

  {{-- Tuổi nợ --}}
  @if(count($aging['open']) > 0)
  <div class="box box-danger">
    <div class="box-header with-border"><h3 class="box-title">{{ __('Bill ghi nợ chưa thu (trừ dần bill cũ nhất trước)') }}</h3>
      <div class="box-tools" style="padding-top:4px">
        {{ __('0–30 ngày:') }} <b>{{ $nf($aging['buckets']['0_30']) }}</b> · 31–60: <b>{{ $nf($aging['buckets']['31_60']) }}</b>
        · 61–90: <b>{{ $nf($aging['buckets']['61_90']) }}</b> · &gt;90: <b>{{ $nf($aging['buckets']['tren_90']) }}</b>
        · <span style="color:#c0392b">{{ __('Quá hạn:') }} <b>{{ $nf($aging['buckets']['qua_han']) }}</b></span>
      </div></div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-condensed table-bordered">
        <tr style="background:#f4f4f4"><th>{{ __('Ngày') }}</th><th>{{ __('Bill') }}</th><th class="text-right">{{ __('Tiền bill') }}</th><th class="text-right">{{ __('Còn nợ') }}</th><th class="text-center">{{ __('Tuổi nợ') }}</th><th>{{ __('Hạn') }}</th></tr>
        @foreach($aging['open'] as $o)
        <tr class="{{ $o->overdue ? 'danger' : '' }}">
          <td>{{ date('d/m/Y', strtotime($o->use_date)) }}</td>
          <td><a href="{{ route('booking.edit', $o->id) }}" target="_blank">#{{ $o->id }}</a> {{ $o->table_no ? __('Bàn').' '.$o->table_no : '' }}</td>
          <td class="text-right">{{ $nf($o->amount) }}</td><td class="text-right"><b>{{ $nf($o->remain) }}</b></td>
          <td class="text-center">{{ __(':n ngày', ['n' => $o->age]) }}</td>
          <td>{{ date('d/m/Y', strtotime($o->due)) }} {!! $o->overdue ? '<span class="label label-danger">'.e(__('Quá hạn')).'</span>' : '' !!}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>
  @endif

  {{-- Lọc kỳ --}}
  <div class="box">
    <div class="box-header with-border">
      <form class="form-inline" method="GET" action="{{ route('doi-tac.show', $detail->id) }}">
        <input type="hidden" name="period" value="custom">
        {{ __('Kỳ:') }} <input type="text" name="from" class="form-control input-sm datepicker" autocomplete="off" value="{{ $fromStr }}" style="width:110px">
        – <input type="text" name="to" class="form-control input-sm datepicker" autocomplete="off" value="{{ $toStr }}" style="width:110px">
        <button class="btn btn-info btn-sm">{{ __('Xem') }}</button>
        <a href="{{ route('doi-tac.export', [$detail->id, 'period' => 'custom', 'from' => $fromStr, 'to' => $toStr]) }}" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> {{ __('Xuất đối soát') }}</a>
        <a href="{{ route('doi-tac.edit', $detail->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> {{ __('Sửa đối tác') }}</a>
        <a href="{{ route('doi-tac.recalc', $detail->id) }}" class="btn btn-default btn-sm" onclick="return confirm({{ json_encode(__('Tính lại hoa hồng tất cả bill của đối tác này theo mức hiện tại?')) }})"><i class="fa fa-refresh"></i> {{ __('Tính lại HH') }}</a>
      </form>
    </div>
    <div class="box-body">
      <p>{{ __('Trong kỳ:') }} <b>{{ $nf($ky['so_bill']) }}</b> {{ __('bill') }} · <b>{{ $nf($ky['pax']) }}</b> {{ __('khách buffet') }} · {{ __('doanh thu') }} <b>{{ $nf($ky['doanh_thu']) }}</b>
        · {{ __('ghi nợ') }} <b style="color:#c0392b">{{ $nf($ky['no']) }}</b> · {{ __('hoa hồng') }} <b style="color:#d68910">{{ $nf($ky['hh']) }}</b></p>
      <div class="table-responsive">
      <table class="table table-condensed table-bordered table-hover">
        <tr style="background:#f4f4f4"><th>{{ __('Ngày') }}</th><th>{{ __('Bill') }}</th><th>{{ __('Chi nhánh') }}</th><th>{{ __('Loại') }}</th><th class="text-right">{{ __('Khách') }}</th>
          <th class="text-right">{{ __('Tổng') }}</th><th class="text-right">{{ __('Giảm') }}</th><th class="text-right">{{ __('Thực thu') }}</th><th class="text-center">{{ __('CK') }}</th><th class="text-center">{{ __('Ghi nợ') }}</th><th class="text-right">{{ __('Hoa hồng') }}</th></tr>
        @forelse($bills as $b)
        <tr>
          <td>{{ date('d/m/Y', strtotime($b->use_date)) }}</td>
          <td><a href="{{ route('booking.edit', $b->id) }}" target="_blank">#{{ $b->id }}</a> {{ $b->table_no ? __('Bàn').' '.$b->table_no : '' }} <small class="text-muted">{{ $statusNames[$b->status] ?? '' }}</small></td>
          <td>{{ $branchArr[$b->branch_id] ?? '' }}</td>
          <td>{{ $b->bill_type == 1 ? 'Buffet' : 'Alacarte' }}</td>
          <td class="text-right">{{ $b->bill_type == 1 ? $b->pax : '' }}</td>
          <td class="text-right">{{ $nf($b->total_price) }}</td>
          <td class="text-right">{{ $b->discount ? $nf($b->discount) : '' }}</td>
          <td class="text-right">{{ $nf($b->total_price - $b->discount) }}</td>
          <td class="text-center">{!! $b->co_chiet_khau ? '<i class="fa fa-check text-green"></i>' : '' !!}</td>
          <td class="text-center">{!! $b->ghi_no ? '<i class="fa fa-check text-red"></i>' : '' !!}</td>
          <td class="text-right">{{ $b->com_amount ? $nf($b->com_amount) : '' }}</td>
        </tr>
        @empty
        <tr><td colspan="11" class="text-center text-muted">{{ __('Không có bill trong kỳ') }}</td></tr>
        @endforelse
      </table>
      </div>

      <h4>{{ __('Giao dịch trong kỳ') }}</h4>
      <table class="table table-condensed table-bordered">
        <tr style="background:#f4f4f4"><th>{{ __('Ngày') }}</th><th>{{ __('Loại') }}</th><th class="text-right">{{ __('Số tiền') }}</th><th>{{ __('Quỹ') }}</th><th>{{ __('Ghi chú') }}</th><th>{{ __('Chi phí') }}</th><th>{{ __('Người ghi') }}</th><th></th></tr>
        @forelse($trans as $t)
        <tr style="{{ $t->status == 0 ? 'text-decoration:line-through;color:#999' : '' }}">
          <td>{{ date('d/m/Y', strtotime($t->date)) }}</td>
          <td>{{ $transTypes[$t->type] ?? $t->type }}</td>
          <td class="text-right">{{ $nf($t->amount) }}</td>
          <td>{{ $t->pay_method ? ($collecterNameArr[$t->pay_method] ?? $t->pay_method) : '' }}</td>
          <td>{{ $t->notes }}</td>
          <td>@if($t->cost_id)<a href="{{ route('cost.edit', $t->cost_id) }}" target="_blank">#{{ $t->cost_id }}</a>@endif</td>
          <td>{{ $t->createdUser->name ?? '' }}</td>
          <td>@if($t->status == 1)<a href="{{ route('doi-tac.transaction.cancel', $t->id) }}" class="btn btn-xs btn-default" onclick="return confirm({{ json_encode(__('Hủy giao dịch này? Chi phí liên quan (nếu có) cũng bị xóa.')) }})">{{ __('Hủy') }}</a>@else <small>{{ __('Đã hủy') }}</small> @endif</td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted">{{ __('Chưa có giao dịch trong kỳ') }}</td></tr>
        @endforelse
      </table>
    </div>
  </div>
</section>
</div>
@stop
@section('js')
<script>
  var DT_MAX = {1: {{ max(0, $bal['phai_thu']) }}, 2: {{ max(0, $bal['phai_tra']) }}, 3: {{ $maxBuTru }}};
  function tTypeChange(){
    var t = $('#t_type').val();
    $('#t_pay_label').text(t == 1 ? {!! json_encode(__('Thu vào quỹ')) !!} : (t == 2 ? {!! json_encode(__('Chi từ quỹ')) !!} : {!! json_encode(__('Người chi ghi trên chi phí')) !!}));
    var h = {1: {!! json_encode(__('Giảm số đối tác nợ.')) !!}, 2: {!! json_encode(__('Tạo 1 chi phí loại "Hoa hồng đối tác".')) !!}, 3: {!! json_encode(__('Không thu/chi tiền: trừ hoa hồng vào nợ, ghi nhận chi phí hoa hồng.')) !!}};
    $('#t_hint').text(h[t] + ' ' + {!! json_encode(__('Tối đa')) !!} + ': ' + DT_MAX[t].toLocaleString('en-US'));
  }
  $(function(){
    $('#t_type').change(tTypeChange); tTypeChange();
    $('#t_fill').click(function(){ $('#t_amount').val(DT_MAX[$('#t_type').val()]); return false; });
  });
</script>
@stop
