@extends('layout')
@section('content')
@php
  $dec = function($s){ $a = json_decode((string) $s, true); return is_array($a) ? $a : []; };
  $show = function($v){
    if($v === null) return '<i class="text-muted">null</i>';
    if(is_array($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
    $v = (string) $v;
    return e(mb_strlen($v) > 300 ? mb_substr($v, 0, 300).'…' : $v);
  };
  $q = function($extra) use ($f, $tab){ return route('activity-log.index', array_filter(array_merge(['tab' => $tab] + $f, $extra), function($v){ return $v !== null && $v !== ''; })); };
  $actColor = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger'];
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>Lịch sử thao tác</h1>
  <ol class="breadcrumb"><li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li><li class="active">Lịch sử thao tác</li></ol>
</section>
<section class="content">
  <ul class="nav nav-tabs" style="margin-bottom:0">
    <li class="{{ $tab == 'data' ? 'active' : '' }}"><a href="{{ route('activity-log.index', ['tab' => 'data']) }}">Thay đổi dữ liệu <small>(lưu vĩnh viễn)</small></a></li>
    <li class="{{ $tab == 'action' ? 'active' : '' }}"><a href="{{ route('activity-log.index', ['tab' => 'action']) }}">Nhật ký thao tác <small>(giữ 12 tháng)</small></a></li>
  </ul>
  <div class="box" style="border-top:0">
    <div class="box-header with-border">
      <form class="form-inline" method="GET" action="{{ route('activity-log.index') }}">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="date" name="from" class="form-control input-sm" value="{{ $f['from'] }}">
        <input type="date" name="to" class="form-control input-sm" value="{{ $f['to'] }}">
        <select name="user_id" class="form-control input-sm">
          <option value="">Tất cả người dùng</option>
          @foreach($userArr as $id => $nm)<option value="{{ $id }}" {{ $f['user_id'] == $id ? 'selected' : '' }}>{{ $nm }}</option>@endforeach
        </select>
        @if($tab == 'data')
        <select name="model" class="form-control input-sm">
          <option value="">Tất cả dữ liệu</option>
          @foreach($modelNames as $k => $v)<option value="{{ $k }}" {{ $f['model'] == $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
        </select>
        <input type="text" name="model_id" class="form-control input-sm" placeholder="ID" value="{{ $f['model_id'] }}" style="width:80px">
        <select name="action" class="form-control input-sm">
          <option value="">Thêm / Sửa / Xóa</option>
          @foreach($actionNames as $k => $v)<option value="{{ $k }}" {{ $f['action'] == $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
        </select>
        @endif
        <input type="text" name="keyword" class="form-control input-sm" placeholder="{{ $tab == 'data' ? 'Tìm trong giá trị' : 'Tìm trong URL / dữ liệu gửi' }}" value="{{ $f['keyword'] }}">
        <button class="btn btn-info btn-sm">Xem</button>
        <a href="{{ route('activity-log.index', ['tab' => $tab]) }}" class="btn btn-default btn-sm">Bỏ lọc</a>
      </form>
      @if($f['request_uid'])<p style="margin:6px 0 0">Đang xem theo 1 thao tác: <code>{{ $f['request_uid'] }}</code></p>@endif
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-condensed">
      @if($tab == 'data')
        <tr style="background:#f4f4f4"><th style="width:140px">Thời gian</th><th style="width:130px">Người</th><th style="width:170px">Dữ liệu</th><th style="width:60px">Thao tác</th><th>Chi tiết thay đổi</th></tr>
        @forelse($items as $it)
        @php
          $old = $dec($it->old_values); $new = $dec($it->new_values);
          $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
          $link = \App\Http\Controllers\ActivityLogController::objectUrl($it->model, $it->model_id);
        @endphp
        <tr>
          <td>{{ date('d/m/Y H:i:s', strtotime($it->created_at)) }}<br><small class="text-muted">{{ $it->ip }}</small></td>
          <td>{{ $userArr[$it->user_id] ?? ($it->user_id ? '#'.$it->user_id : 'Hệ thống') }}</td>
          <td>{{ $modelNames[$it->model] ?? $it->model }}
            @if($link)<a href="{{ $link }}" target="_blank">#{{ $it->model_id }}</a>@else #{{ $it->model_id }}@endif
            <br><a href="{{ $q(['model' => $it->model, 'model_id' => $it->model_id, 'from' => null, 'action' => null, 'user_id' => null, 'keyword' => null]) }}"><small>toàn bộ lịch sử</small></a>
          </td>
          <td><span class="label label-{{ $actColor[$it->action] ?? 'default' }}">{{ $actionNames[$it->action] ?? $it->action }}</span></td>
          <td style="font-size:12px">
            <table class="table table-condensed" style="margin:0;background:transparent">
              @foreach($keys as $k)
              <tr>
                <td style="width:160px;border:0;padding:1px 4px"><code>{{ $k }}</code></td>
                @if($it->action == 'updated')
                <td style="border:0;padding:1px 4px;color:#c0392b">{!! $show($old[$k] ?? null) !!}</td>
                <td style="border:0;padding:1px 4px;width:20px">→</td>
                <td style="border:0;padding:1px 4px;color:#1e8449">{!! $show($new[$k] ?? null) !!}</td>
                @else
                <td colspan="3" style="border:0;padding:1px 4px">{!! $show($it->action == 'deleted' ? ($old[$k] ?? null) : ($new[$k] ?? null)) !!}</td>
                @endif
              </tr>
              @endforeach
            </table>
            @if($it->request_uid)<a href="{{ route('activity-log.index', ['tab' => 'action', 'request_uid' => $it->request_uid]) }}"><small>xem thao tác gây ra thay đổi này</small></a>@endif
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted">Không có dữ liệu</td></tr>
        @endforelse
      @else
        <tr style="background:#f4f4f4"><th style="width:140px">Thời gian</th><th style="width:130px">Người</th><th style="width:60px">Kiểu</th><th>Thao tác / URL</th><th>Dữ liệu gửi</th><th style="width:60px">KQ</th></tr>
        @forelse($items as $it)
        <tr>
          <td>{{ date('d/m/Y H:i:s', strtotime($it->created_at)) }}<br><small class="text-muted">{{ $it->ip }}</small></td>
          <td>{{ $userArr[$it->user_id] ?? ($it->user_id ? '#'.$it->user_id : 'Khách') }}</td>
          <td>{{ $it->method }}</td>
          <td style="word-break:break-all"><b>{{ $it->route }}</b><br><small>{{ $it->url }}</small><br>
            <a href="{{ route('activity-log.index', ['tab' => 'data', 'request_uid' => $it->request_uid]) }}"><small>dữ liệu bị thay đổi</small></a></td>
          <td style="font-size:11px;max-width:520px;word-break:break-all"><div style="max-height:120px;overflow:auto">{{ $it->params }}</div></td>
          <td><span class="label label-{{ $it->status_code >= 400 ? 'danger' : 'default' }}">{{ $it->status_code }}</span><br><small>{{ $it->duration_ms }}ms</small></td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted">Không có dữ liệu</td></tr>
        @endforelse
      @endif
      </table>
    </div>
    <div class="box-footer">{{ $items->links() }}</div>
  </div>
</section>
</div>
@stop
