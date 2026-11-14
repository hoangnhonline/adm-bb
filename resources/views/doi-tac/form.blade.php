@extends('layout')
@section('content')
@php $isEdit = $detail->exists; @endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ $isEdit ? 'Sửa đối tác: '.$detail->name : 'Thêm đối tác' }}</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('doi-tac.index') }}">Đối tác</a></li>
    <li class="active">{{ $isEdit ? 'Sửa' : 'Thêm' }}</li>
  </ol>
</section>
<section class="content">
  <form method="POST" action="{{ $isEdit ? route('doi-tac.update') : route('doi-tac.store') }}">
    {!! csrf_field() !!}
    @if($isEdit)<input type="hidden" name="id" value="{{ $detail->id }}">@endif
    <div class="box box-primary"><div class="box-body">
      @if(count($errors) > 0)
      <div class="alert alert-danger"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      <div class="row">
        <div class="form-group col-md-6"><label>Tên đối tác <span class="red-star">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $detail->name) }}"></div>
        <div class="form-group col-md-3"><label>Loại <span class="red-star">*</span></label>
          <select name="type" class="form-control">
            @foreach($types as $k => $v)
            <option value="{{ $k }}" {{ old('type', $detail->type) == $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
          </select></div>
        <div class="form-group col-md-3"><label>Chi nhánh</label>
          <select name="branch_id" class="form-control">
            <option value="">Tất cả chi nhánh</option>
            @foreach($branchList as $br)
            <option value="{{ $br->id }}" {{ old('branch_id', $detail->branch_id) == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
            @endforeach
          </select></div>
      </div>
      <div class="row">
        <div class="form-group col-md-4"><label>Người liên hệ</label>
          <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $detail->contact_name) }}"></div>
        <div class="form-group col-md-4"><label>Điện thoại</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone', $detail->phone) }}"></div>
        <div class="form-group col-md-4"><label>Email</label>
          <input type="text" name="email" class="form-control" value="{{ old('email', $detail->email) }}"></div>
      </div>
      <div class="row">
        <div class="form-group col-md-4"><label>Mã số thuế</label>
          <input type="text" name="tax_code" class="form-control" value="{{ old('tax_code', $detail->tax_code) }}"></div>
        <div class="form-group col-md-8"><label>Địa chỉ</label>
          <input type="text" name="address" class="form-control" value="{{ old('address', $detail->address) }}"></div>
      </div>
      <div class="form-group"><label>Tài khoản ngân hàng (để chi hoa hồng)</label>
        <input type="text" name="bank_info" class="form-control" value="{{ old('bank_info', $detail->bank_info) }}"></div>
      <hr>
      <div class="row">
        <div class="form-group col-md-4"><label>Hoa hồng buffet (đ / khách)</label>
          <input type="text" name="com_buffet_per_pax" class="form-control number" value="{{ old('com_buffet_per_pax', (int) $detail->com_buffet_per_pax) }}"></div>
        <div class="form-group col-md-4"><label>Hoa hồng alacarte (% tổng bill sau giảm giá)</label>
          <input type="text" name="com_alacarte_pct" class="form-control" value="{{ old('com_alacarte_pct', rtrim(rtrim((string) $detail->com_alacarte_pct, '0'), '.')) }}"></div>
        <div class="form-group col-md-4"><label>Hạn thanh toán bill ghi nợ (ngày, 0 = trả ngay)</label>
          <input type="number" min="0" name="pay_term_days" class="form-control" value="{{ old('pay_term_days', (int) $detail->pay_term_days) }}"></div>
      </div>
      @if($isEdit)
      <div class="checkbox"><label><input type="checkbox" name="recalc_old" value="1"> Nếu đổi mức hoa hồng: tính lại cả các bill cũ của đối tác này theo mức mới</label></div>
      @endif
      <div class="form-group"><label>Ghi chú</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $detail->description) }}</textarea></div>
    </div>
    <div class="box-footer">
      <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
      <a href="{{ route('doi-tac.index') }}" class="btn btn-default btn-sm">Hủy</a>
      @if($isEdit)
      <a href="{{ route('doi-tac.destroy', $detail->id) }}" class="btn btn-danger btn-sm pull-right" onclick="return confirm('Ẩn đối tác này? Dữ liệu bill và công nợ vẫn giữ nguyên.')">Ẩn đối tác</a>
      @endif
    </div></div>
  </form>
</section>
</div>
@stop
