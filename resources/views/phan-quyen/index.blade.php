@extends('layout')
@section('content')
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Phân quyền') }}</h1>
  <ol class="breadcrumb"><li class="active">{{ __('Nhóm quyền') }}</li></ol>
</section>
<section class="content">
  @if(Session::has('message'))<p class="alert alert-info">{{ Session::get('message') }}</p>@endif
  <div class="box">
    <div class="box-header with-border">
      <a href="{{ route('phan-quyen.create') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> {{ __('Thêm nhóm quyền') }}</a>
      <a href="{{ route('phan-quyen.users') }}" class="btn btn-primary btn-sm"><i class="fa fa-users"></i> {{ __('Gán quyền cho tài khoản') }}</a>
      <span class="text-muted" style="margin-left:10px">{{ __('Tài khoản chưa gán nhóm vẫn dùng quyền cũ như hiện tại.') }}</span>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-hover">
        <tr style="background:#f4f4f4"><th>#</th><th>{{ __('Nhóm quyền') }}</th><th>{{ __('Mô tả') }}</th><th class="text-center">{{ __('Chi nhánh') }}</th>
          <th class="text-center">{{ __('Số quyền') }}</th><th class="text-center">{{ __('Số người dùng') }}</th><th class="text-center">{{ __('Trạng thái') }}</th><th></th></tr>
        @forelse($roles as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td><a href="{{ route('phan-quyen.edit', $r->id) }}"><b>{{ $r->name }}</b></a></td>
          <td>{{ $r->description }}</td>
          <td class="text-center">{{ $r->all_branches ? __('Tất cả chi nhánh') : __('Theo chi nhánh được gán') }}</td>
          <td class="text-center">{{ $permCount[$r->id] ?? 0 }}</td>
          <td class="text-center"><a href="{{ route('phan-quyen.users', ['role_id' => $r->id]) }}">{{ $userCount[$r->id] ?? 0 }}</a></td>
          <td class="text-center">{!! $r->status ? '<span class="label label-success">'.e(__('Hoạt động')).'</span>' : '<span class="label label-default">'.e(__('Tạm khóa')).'</span>' !!}</td>
          <td style="white-space:nowrap">
            <a href="{{ route('phan-quyen.edit', $r->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> {{ __('Sửa') }}</a>
            <a href="{{ route('phan-quyen.create', ['copy' => $r->id]) }}" class="btn btn-default btn-xs"><i class="fa fa-copy"></i> {{ __('Sao chép') }}</a>
            @if(empty($userCount[$r->id]))
            <a href="{{ route('phan-quyen.destroy', $r->id) }}" class="btn btn-danger btn-xs" onclick="return confirm({{ json_encode(__('Xóa nhóm quyền này?')) }})"><i class="fa fa-trash"></i></a>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted">{{ __('Chưa có nhóm quyền. Hãy chạy file SQL phân quyền.') }}</td></tr>
        @endforelse
      </table>
    </div>
  </div>
</section>
</div>
@stop
