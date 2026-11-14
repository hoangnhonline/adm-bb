@extends('layout')
@section('content')
@php
  $nf = function($v){ return number_format((int) $v); };
  $maxBuTru = max(0, min($bal['phai_thu'], $bal['phai_tra']));
  $statusNames = [1 => 'Mới', 2 => 'Hoàn tất', 4 => 'Đã tiếp nhận', 5 => 'Đang triển khai'];
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ $detail->name }} <small>{{ $types[$detail->type] ?? $detail->type }}
    · HH buffet {{ $nf($detail->com_buffet_per_pax) }}/khách · alacarte {{ rtrim(rtrim(number_format($detail->com_alacarte_pct, 2), '0'), '.') }}%
    · {{ $detail->pay_term_days > 0 ? 'nợ '.$detail->pay_term_days.' ngày' : 'trả ngay' }}</small></h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('doi-tac.index') }}">Đối tác</a></li>
    <li class="active">Sổ công nợ</li>
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
      <div class="box box-danger"><div class="box-header with-border"><h3 class="box-title">Đối tác nợ nhà hàng (phải thu)</h3></div>
        <table class="table table-condensed" style="margin:0">
          <tr><td>Bill ghi nợ phát sinh</td><td class="text-right">{{ $nf($bal['no_phat_sinh']) }}</td></tr>
          <tr><td>− Đối tác đã thanh toán</td><td class="text-right">{{ $nf($bal['da_thu']) }}</td></tr>
          <tr><td>− Đã bù trừ với hoa hồng</td><td class="text-right">{{ $nf($bal['bu_tru']) }}</td></tr>
          <tr style="font-size:16px"><th>Còn phải thu</th><th class="text-right" style="color:#c0392b">{{ $nf($bal['phai_thu']) }}</th></tr>
        </table></div>
    </div>
    <div class="col-md-4">
      <div class="box box-warning"><div class="box-header with-border"><h3 class="box-title">Nhà hàng nợ hoa hồng (phải trả)</h3></div>
        <table class="table table-condensed" style="margin:0">
          <tr><td>Hoa hồng phát sinh</td><td class="text-right">{{ $nf($bal['hh_phat_sinh']) }}</td></tr>
          <tr><td>− Đã chi hoa hồng</td><td class="text-right">{{ $nf($bal['da_chi']) }}</td></tr>
          <tr><td>− Đã bù trừ với công nợ</td><td class="text-right">{{ $nf($bal['bu_tru']) }}</td></tr>
          <tr style="font-size:16px"><th>Còn phải trả</th><th class="text-right" style="color:#d68910">{{ $nf($bal['phai_tra']) }}</th></tr>
        </table></div>
    </div>
    <div class="col-md-4">
      <div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Tổng hợp</h3></div>
        <table class="table table-condensed" style="margin:0">
          <tr><td>Tổng số bill / khách buffet</td><td class="text-right">{{ $nf($bal['so_bill']) }} / {{ $nf($bal['pax']) }}</td></tr>
          <tr><td>Tổng doanh thu từ đối tác</td><td class="text-right">{{ $nf($bal['doanh_thu']) }}</td></tr>
          <tr><td>Có thể bù trừ tối đa</td><td class="text-right">{{ $nf($maxBuTru) }}</td></tr>
          <tr style="font-size:16px"><th>Ròng {{ $bal['rong'] >= 0 ? '(đối tác nợ mình)' : '(mình nợ đối tác)' }}</th><th class="text-right">{{ $nf(abs($bal['rong'])) }}</th></tr>
        </table></div>
    </div>
  </div>

  {{-- Ghi giao dịch --}}
  <div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">Ghi nhận thanh toán / chi hoa hồng / bù trừ</h3></div>
    <form method="POST" action="{{ route('doi-tac.transaction.store', $detail->id) }}" class="box-body" id="transForm">
      {!! csrf_field() !!}
      <div class="row">
        <div class="form-group col-md-3"><label>Loại giao dịch</label>
          <select name="type" id="t_type" class="form-control">
            <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>Đối tác thanh toán cho mình</option>
            <option value="2" {{ old('type') == 2 ? 'selected' : '' }}>Chi hoa hồng cho đối tác</option>
            <option value="3" {{ old('type') == 3 ? 'selected' : '' }}>Bù trừ (trừ HH vào nợ)</option>
          </select></div>
        <div class="form-group col-md-2"><label>Ngày</label>
          <input type="text" name="date" class="form-control datepicker" autocomplete="off" value="{{ old('date', date('d/m/Y')) }}"></div>
        <div class="form-group col-md-2"><label>Số tiền <a href="#" id="t_fill" style="font-weight:normal">(điền tối đa)</a></label>
          <input type="text" name="amount" id="t_amount" class="form-control number" value="{{ old('amount') }}"></div>
        <div class="form-group col-md-2" id="t_pay_wrap"><label id="t_pay_label">Quỹ</label>
          <select name="pay_method" class="form-control">
            @foreach($collecterList as $c)
            <option value="{{ $c->id }}" {{ old('pay_method', 2) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
          </select></div>
        <div class="form-group col-md-3"><label>Ghi chú</label>
          <input type="text" name="notes" class="form-control" value="{{ old('notes') }}"></div>
      </div>
      <p class="text-muted" id="t_hint" style="margin:0"></p>
      <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Ghi giao dịch này?')">Ghi nhận</button>
    </form>
  </div>

  {{-- Tuổi nợ --}}
  @if(count($aging['open']) > 0)
  <div class="box box-danger">
    <div class="box-header with-border"><h3 class="box-title">Bill ghi nợ chưa thu (trừ dần bill cũ nhất trước)</h3>
      <div class="box-tools" style="padding-top:4px">
        0–30 ngày: <b>{{ $nf($aging['buckets']['0_30']) }}</b> · 31–60: <b>{{ $nf($aging['buckets']['31_60']) }}</b>
        · 61–90: <b>{{ $nf($aging['buckets']['61_90']) }}</b> · &gt;90: <b>{{ $nf($aging['buckets']['tren_90']) }}</b>
        · <span style="color:#c0392b">Quá hạn: <b>{{ $nf($aging['buckets']['qua_han']) }}</b></span>
      </div></div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-condensed table-bordered">
        <tr style="background:#f4f4f4"><th>Ngày</th><th>Bill</th><th class="text-right">Tiền bill</th><th class="text-right">Còn nợ</th><th class="text-center">Tuổi nợ</th><th>Hạn</th></tr>
        @foreach($aging['open'] as $o)
        <tr class="{{ $o->overdue ? 'danger' : '' }}">
          <td>{{ date('d/m/Y', strtotime($o->use_date)) }}</td>
          <td><a href="{{ route('booking.edit', $o->id) }}" target="_blank">#{{ $o->id }}</a> {{ $o->table_no ? 'Bàn '.$o->table_no : '' }}</td>
          <td class="text-right">{{ $nf($o->amount) }}</td><td class="text-right"><b>{{ $nf($o->remain) }}</b></td>
          <td class="text-center">{{ $o->age }} ngày</td>
          <td>{{ date('d/m/Y', strtotime($o->due)) }} {!! $o->overdue ? '<span class="label label-danger">Quá hạn</span>' : '' !!}</td>
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
        Kỳ: <input type="text" name="from" class="form-control input-sm datepicker" autocomplete="off" value="{{ $fromStr }}" style="width:110px">
        – <input type="text" name="to" class="form-control input-sm datepicker" autocomplete="off" value="{{ $toStr }}" style="width:110px">
        <button class="btn btn-info btn-sm">Xem</button>
        <a href="{{ route('doi-tac.export', [$detail->id, 'period' => 'custom', 'from' => $fromStr, 'to' => $toStr]) }}" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Xuất đối soát</a>
        <a href="{{ route('doi-tac.edit', $detail->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> Sửa đối tác</a>
        <a href="{{ route('doi-tac.recalc', $detail->id) }}" class="btn btn-default btn-sm" onclick="return confirm('Tính lại hoa hồng tất cả bill của đối tác này theo mức hiện tại?')"><i class="fa fa-refresh"></i> Tính lại HH</a>
      </form>
    </div>
    <div class="box-body">
      <p>Trong kỳ: <b>{{ $nf($ky['so_bill']) }}</b> bill · <b>{{ $nf($ky['pax']) }}</b> khách buffet · doanh thu <b>{{ $nf($ky['doanh_thu']) }}</b>
        · ghi nợ <b style="color:#c0392b">{{ $nf($ky['no']) }}</b> · hoa hồng <b style="color:#d68910">{{ $nf($ky['hh']) }}</b></p>
      <div class="table-responsive">
      <table class="table table-condensed table-bordered table-hover">
        <tr style="background:#f4f4f4"><th>Ngày</th><th>Bill</th><th>Chi nhánh</th><th>Loại</th><th class="text-right">Khách</th>
          <th class="text-right">Tổng</th><th class="text-right">Giảm</th><th class="text-right">Thực thu</th><th class="text-center">CK</th><th class="text-center">Ghi nợ</th><th class="text-right">Hoa hồng</th></tr>
        @forelse($bills as $b)
        <tr>
          <td>{{ date('d/m/Y', strtotime($b->use_date)) }}</td>
          <td><a href="{{ route('booking.edit', $b->id) }}" target="_blank">#{{ $b->id }}</a> {{ $b->table_no ? 'Bàn '.$b->table_no : '' }} <small class="text-muted">{{ $statusNames[$b->status] ?? '' }}</small></td>
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
        <tr><td colspan="11" class="text-center text-muted">Không có bill trong kỳ</td></tr>
        @endforelse
      </table>
      </div>

      <h4>Giao dịch trong kỳ</h4>
      <table class="table table-condensed table-bordered">
        <tr style="background:#f4f4f4"><th>Ngày</th><th>Loại</th><th class="text-right">Số tiền</th><th>Quỹ</th><th>Ghi chú</th><th>Chi phí</th><th>Người ghi</th><th></th></tr>
        @forelse($trans as $t)
        <tr style="{{ $t->status == 0 ? 'text-decoration:line-through;color:#999' : '' }}">
          <td>{{ date('d/m/Y', strtotime($t->date)) }}</td>
          <td>{{ $transTypes[$t->type] ?? $t->type }}</td>
          <td class="text-right">{{ $nf($t->amount) }}</td>
          <td>{{ $t->pay_method ? ($collecterNameArr[$t->pay_method] ?? $t->pay_method) : '' }}</td>
          <td>{{ $t->notes }}</td>
          <td>@if($t->cost_id)<a href="{{ route('cost.edit', $t->cost_id) }}" target="_blank">#{{ $t->cost_id }}</a>@endif</td>
          <td>{{ $t->createdUser->name ?? '' }}</td>
          <td>@if($t->status == 1)<a href="{{ route('doi-tac.transaction.cancel', $t->id) }}" class="btn btn-xs btn-default" onclick="return confirm('Hủy giao dịch này? Chi phí liên quan (nếu có) cũng bị xóa.')">Hủy</a>@else <small>Đã hủy</small> @endif</td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted">Chưa có giao dịch trong kỳ</td></tr>
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
    $('#t_pay_label').text(t == 1 ? 'Thu vào quỹ' : (t == 2 ? 'Chi từ quỹ' : 'Người chi ghi trên chi phí'));
    var h = {1: 'Giảm số đối tác nợ. Tối đa: ', 2: 'Tạo 1 chi phí loại "Hoa hồng đối tác". Tối đa: ', 3: 'Không thu/chi tiền: trừ hoa hồng vào nợ, ghi nhận chi phí hoa hồng. Tối đa: '};
    $('#t_hint').text(h[t] + DT_MAX[t].toLocaleString('en-US'));
  }
  $(function(){
    $('#t_type').change(tTypeChange); tTypeChange();
    $('#t_fill').click(function(){ $('#t_amount').val(DT_MAX[$('#t_type').val()]); return false; });
  });
</script>
@stop
