@extends('layout')
@section('content')
@php $modules = config('permissions.modules'); $actions = config('permissions.actions'); @endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Kiểm tra quyền') }}: {{ $user->name }}</h1>
  <ol class="breadcrumb"><li><a href="{{ route('phan-quyen.users') }}">{{ __('Gán quyền cho tài khoản') }}</a></li><li class="active">{{ __('Kiểm tra quyền') }}</li></ol>
</section>
<section class="content">
  <div class="box"><div class="box-body">
    @if(!$active)
      <p class="alert alert-warning">{{ in_array((int) $user->id, config('permissions.super_admin_ids', [1])) ? __('Quản trị chính – toàn quyền') : __('Tài khoản đang dùng quyền cũ (chưa gán nhóm quyền hoặc nhóm đã tạm khóa).') }}</p>
    @else
      <p><b>{{ __('Nhóm quyền') }}:</b> {{ $role->name }} &nbsp; · &nbsp; <b>{{ __('Chi nhánh') }}:</b>
        @if($branchIds === null) {{ __('Tất cả chi nhánh') }}
        @elseif(!$branchIds) <span class="text-red">{{ __('Chưa gán chi nhánh – không vào được dữ liệu') }}</span>
        @else {{ implode(', ', array_map(function($b) use ($allBranches){ return $allBranches[$b] ?? $b; }, $branchIds)) }} @endif
      </p>
      <table class="table table-bordered table-condensed" style="max-width:900px">
        <tr style="background:#f4f4f4"><th>{{ __('Chức năng') }}</th><th>{{ __('Được phép') }}</th></tr>
        @foreach($modules as $m => $cfg)
        @php $ok = array_filter($cfg['actions'], function($a) use ($m, $perms){ return in_array("$m.$a", $perms); }); @endphp
        <tr><td>{{ __($cfg['label']) }}</td>
          <td>@forelse($ok as $a)<span class="label label-success" style="display:inline-block;margin:1px">{{ __($actions[$a]) }}</span> @empty <span class="text-muted">{{ __('Không có') }}</span> @endforelse</td></tr>
        @endforeach
      </table>
      <p><b>{{ __('Menu hiển thị') }}:</b> @foreach($menu as $mi){{ __($mi[3]) }}@if(!$loop->last), @endif @endforeach</p>
    @endif
  </div></div>
</section>
</div>
@stop
