@extends('layout')
@section('content')
@php
  $modules = config('permissions.modules');
  $actions = config('permissions.actions');
  $inUse = [];
  foreach($modules as $m) foreach($m['actions'] as $a) $inUse[$a] = true;
  $usedActions = array_values(array_filter(array_keys($actions), function($a) use ($inUse){ return isset($inUse[$a]); }));
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ $role->id ? __('Sửa nhóm quyền').': '.$role->name : __('Thêm nhóm quyền') }}</h1>
  <ol class="breadcrumb"><li><a href="{{ route('phan-quyen.index') }}">{{ __('Phân quyền') }}</a></li><li class="active">{{ $role->id ? __('Sửa') : __('Thêm') }}</li></ol>
</section>
<section class="content">
  <form method="POST" action="{{ route('phan-quyen.save') }}">
    {!! csrf_field() !!}
    @if($role->id)<input type="hidden" name="id" value="{{ $role->id }}">@endif
    <div class="box box-primary"><div class="box-body">
      @if(count($errors) > 0)<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
      <div class="row">
        <div class="form-group col-md-4"><label>{{ __('Tên nhóm') }} <span class="red-star">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}"></div>
        <div class="form-group col-md-6"><label>{{ __('Mô tả') }}</label>
          <input type="text" name="description" class="form-control" value="{{ old('description', $role->description) }}"></div>
        <div class="form-group col-md-2"><label>{{ __('Trạng thái') }}</label>
          <select name="status" class="form-control"><option value="1" {{ $role->status ? 'selected' : '' }}>{{ __('Hoạt động') }}</option><option value="0" {{ !$role->status ? 'selected' : '' }}>{{ __('Tạm khóa') }}</option></select></div>
      </div>
      <div class="checkbox"><label><input type="checkbox" name="all_branches" value="1" {{ $role->all_branches ? 'checked' : '' }}>
        {{ __('Thấy tất cả chi nhánh (không giới hạn theo chi nhánh được gán)') }}</label></div>

      <h4>{{ __('Quyền theo chức năng') }}</h4>
      <div class="table-responsive">
      <table class="table table-bordered table-condensed" id="permMatrix" data-no-sticky>
        <tr style="background:#f4f4f4">
          <th>{{ __('Chức năng') }}</th>
          @foreach($usedActions as $a)
          <th class="text-center"><label style="font-weight:600;cursor:pointer"><input type="checkbox" class="col-all" data-a="{{ $a }}"> {{ __($actions[$a]) }}</label></th>
          @endforeach
          <th class="text-center">{{ __('Cả dòng') }}</th>
        </tr>
        @foreach($modules as $m => $cfg)
        <tr>
          <td><b>{{ __($cfg['label']) }}</b></td>
          @foreach($usedActions as $a)
          <td class="text-center">
            @if(in_array($a, $cfg['actions']))
            <input type="checkbox" name="perms[]" value="{{ $m }}.{{ $a }}" class="p p-{{ $a }} r-{{ $m }}" {{ in_array("$m.$a", old('perms', $perms)) ? 'checked' : '' }}>
            @else <span class="text-muted">–</span> @endif
          </td>
          @endforeach
          <td class="text-center"><input type="checkbox" class="row-all" data-m="{{ $m }}"></td>
        </tr>
        @endforeach
      </table>
      </div>
      <p class="text-muted">{{ __('"Xem" là điều kiện để mục hiện trên menu. "Sửa/xóa bill ngày cũ": không tick thì chỉ được sửa / xóa bill trong ngày. Trang Phân quyền và Lịch sử thao tác chỉ dành cho tài khoản quản trị chính.') }}</p>
    </div>
    <div class="box-footer">
      <button type="submit" class="btn btn-primary btn-sm">{{ __('Lưu') }}</button>
      <a href="{{ route('phan-quyen.index') }}" class="btn btn-default btn-sm">{{ __('Hủy') }}</a>
    </div></div>
  </form>
</section>
</div>
@stop
@section('js')
<script>
  $(function(){
    $('.col-all').change(function(){ $('.p-' + $(this).data('a')).prop('checked', this.checked); });
    $('.row-all').change(function(){ $('.r-' + $(this).data('m')).prop('checked', this.checked); });
  });
</script>
@stop
