@extends('layout')
@section('content')
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Gán quyền cho tài khoản') }}</h1>
  <ol class="breadcrumb"><li><a href="{{ route('phan-quyen.index') }}">{{ __('Phân quyền') }}</a></li><li class="active">{{ __('Tài khoản') }}</li></ol>
</section>
<section class="content">
  @if(Session::has('message'))<p class="alert alert-info">{{ Session::get('message') }}</p>@endif
  <div class="box">
    <div class="box-header with-border">
      <form class="form-inline" method="GET" action="{{ route('phan-quyen.users') }}">
        <input type="text" name="keyword" class="form-control input-sm" placeholder="{{ __('Tên / email / SĐT') }}" value="{{ request('keyword') }}">
        <select name="role_id" class="form-control input-sm">
          <option value="">{{ __('Tất cả') }}</option>
          <option value="none" {{ request('role_id') === 'none' ? 'selected' : '' }}>{{ __('— Quyền cũ (chưa gán nhóm) —') }}</option>
          @foreach($roles as $r)<option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>@endforeach
        </select>
        <button class="btn btn-info btn-sm">{{ __('Tìm') }}</button>
      </form>
      <p class="text-muted" style="margin:6px 0 0">{{ __('Nhóm quyền "— Quyền cũ —": tài khoản chạy như hiện tại. Chi nhánh: để trống thì dùng chi nhánh mặc định của tài khoản. Nhóm "thấy tất cả chi nhánh" thì bỏ qua phần chi nhánh.') }}</p>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-hover table-condensed">
        <tr style="background:#f4f4f4"><th>ID</th><th>{{ __('Tài khoản') }}</th><th>{{ __('Quyền cũ') }}</th><th>{{ __('Nhóm quyền') }}</th><th>{{ __('Chi nhánh được gán') }}</th><th></th></tr>
        @foreach($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td><b>{{ $u->name }}</b><br><small class="text-muted">{{ $u->email }} {{ $u->phone }}</small></td>
          <td><small>role {{ $u->role }} · level {{ $u->level }}<br>{{ __('CN mặc định') }}: {{ $branchNames[$u->branch_id] ?? '–' }}</small></td>
          @if(in_array((int) $u->id, config('permissions.super_admin_ids', [1])))
          <td colspan="3"><span class="label label-danger">{{ __('Quản trị chính – toàn quyền') }}</span></td>
          @else
          <td>
            <select name="role_id" form="uf{{ $u->id }}" class="form-control input-sm" style="min-width:170px">
              <option value="">{{ __('— Quyền cũ —') }}</option>
              @foreach($roles as $r)<option value="{{ $r->id }}" {{ $u->role_id == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>@endforeach
            </select>
          </td>
          <td>
            @foreach($branches as $b)
            <label style="font-weight:normal;margin-right:8px;white-space:nowrap"><input type="checkbox" name="branch_ids[]" form="uf{{ $u->id }}" value="{{ $b->id }}" {{ in_array($b->id, $ub[$u->id] ?? []) ? 'checked' : '' }}> {{ $b->name }}</label>
            @endforeach
          </td>
          <td style="white-space:nowrap">
            <form id="uf{{ $u->id }}" method="POST" action="{{ route('phan-quyen.user-save') }}" style="display:inline">
              {!! csrf_field() !!}<input type="hidden" name="user_id" value="{{ $u->id }}">
              <button class="btn btn-primary btn-xs">{{ __('Lưu') }}</button>
            </form>
            <a href="{{ route('phan-quyen.check', $u->id) }}" class="btn btn-default btn-xs">{{ __('Kiểm tra quyền') }}</a>
          </td>
          @endif
        </tr>
        @endforeach
      </table>
    </div>
    <div class="box-footer">{{ $users->links() }}</div>
  </div>
</section>
</div>
@stop
