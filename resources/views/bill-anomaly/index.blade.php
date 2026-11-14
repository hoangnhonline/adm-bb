@extends('layout')
@section('content')
@php
  $nf = function($v){ return number_format((float) $v); };
  $typeCls = ['discount' => 'warning', 'cancel' => 'danger', 'reduce' => 'danger', 'receiver' => 'primary', 'old' => 'info', 'many' => 'default'];
  $canReview = \App\Services\Perm::can('bill_alert.edit');
  $q = function($extra) use ($f){ return route('bill-anomaly.index', array_filter(array_merge($f, $extra), function($v){ return $v !== null && $v !== ''; })); };
@endphp
<div class="content-wrapper">
<section class="content-header">
  <h1>{{ __('Bill bất thường') }}</h1>
  <ol class="breadcrumb"><li><a href="{{ route('tong-quan.index') }}">{{ __('Tổng quan') }}</a></li><li class="active">{{ __('Bill bất thường') }}</li></ol>
</section>
<section class="content">
  <div class="panel panel-default"><div class="panel-body">
    <form class="form-inline" method="GET" action="{{ route('bill-anomaly.index') }}">
      <input type="date" name="from" class="form-control input-sm" value="{{ $f['from'] }}">
      <input type="date" name="to" class="form-control input-sm" value="{{ $f['to'] }}">
      <select name="branch_id" class="form-control input-sm">
        <option value="">{{ __('Tất cả chi nhánh') }}</option>
        @foreach($branches as $id => $n)<option value="{{ $id }}" {{ $f['branch_id'] == $id ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <select name="type" class="form-control input-sm">
        <option value="">{{ __('Tất cả loại cảnh báo') }}</option>
        @foreach($typeNames as $k => $n)<option value="{{ $k }}" {{ $f['type'] == $k ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <select name="user_id" class="form-control input-sm">
        <option value="">{{ __('Tất cả người dùng') }}</option>
        @foreach($allUsers as $id => $n)<option value="{{ $id }}" {{ $f['user_id'] == $id ? 'selected' : '' }}>{{ $n }}</option>@endforeach
      </select>
      <select name="reviewed" class="form-control input-sm">
        <option value="0" {{ $f['reviewed'] === '0' ? 'selected' : '' }}>{{ __('Chưa xem') }}</option>
        <option value="1" {{ $f['reviewed'] === '1' ? 'selected' : '' }}>{{ __('Đã xem') }}</option>
        <option value="" {{ $f['reviewed'] === '' ? 'selected' : '' }}>{{ __('Tất cả') }}</option>
      </select>
      <button class="btn btn-info btn-sm">{{ __('Xem') }}</button>
    </form>
    <div style="margin-top:10px">
      @foreach($typeNames as $k => $n)
        <a href="{{ $q(['type' => $f['type'] == $k ? null : $k]) }}" class="label label-{{ $typeCls[$k] }}" style="display:inline-block;margin:2px;padding:5px 8px;font-size:12px;{{ $f['type'] == $k ? 'outline:2px solid #333' : '' }}">{{ $n }}: {{ $counts[$k] ?? 0 }}</a>
      @endforeach
    </div>
    <p class="text-muted" style="margin:8px 0 0;font-size:12px">{{ __('Giảm giá cao = giảm trên 15% tổng bill. Sửa nhiều lần = từ 3 lần. Các cảnh báo về sửa bill dựa trên lịch sử thao tác, chỉ có dữ liệu từ khi bật lịch sử thao tác.') }}</p>
  </div></div>

  <div class="box">
    <div class="box-header with-border"><h3 class="box-title">{{ __('Danh sách cảnh báo') }} ({{ count($items) }})</h3>
      @if($canReview && count($items))
      <div class="box-tools" style="top:6px">
        <button type="button" class="btn btn-sm btn-success" id="btnReviewSel"><i class="fa fa-check"></i> {{ __('Đánh dấu đã xem các dòng đã chọn') }}</button>
      </div>
      @endif
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-bordered table-hover table-condensed">
        <tr style="background:#f4f4f4">
          @if($canReview)<th style="width:30px"><input type="checkbox" id="chkAll"></th>@endif
          <th>{{ __('Thời gian') }}</th><th>{{ __('Loại') }}</th><th>{{ __('Bill') }}</th><th>{{ __('Chi nhánh') }}</th>
          <th>{{ __('Người thực hiện') }}</th><th>{{ __('Chi tiết') }}</th><th>{{ __('Trạng thái') }}</th>
        </tr>
        @forelse($items as $i)
        @php $d = $i->detail; @endphp
        <tr class="{{ $i->reviewed ? 'text-muted' : '' }}">
          @if($canReview)<td>@if(!$i->reviewed)<input type="checkbox" class="chk" value="{{ $i->key }}">@endif</td>@endif
          <td style="white-space:nowrap">{{ $i->at ? date('d/m/Y H:i', strtotime($i->at)) : '' }}</td>
          <td><span class="label label-{{ $typeCls[$i->type] }}">{{ $typeNames[$i->type] }}</span></td>
          <td style="white-space:nowrap"><a href="{{ route('booking.edit', $i->booking_id) }}" target="_blank">#{{ $i->booking_id }}</a>
            <br><small>{{ date('d/m/Y', strtotime($i->use_date)) }}{{ $i->table_no ? ' · '.__('Bàn').' '.$i->table_no : '' }}</small></td>
          <td>{{ $branches[$i->branch_id] ?? '' }}</td>
          <td>{{ $i->user_id ? ($users[$i->user_id] ?? '#'.$i->user_id) : '' }}</td>
          <td style="font-size:12px">
            @if($i->type == 'discount')
              {{ __('Giảm') }} <b>{{ $nf($d['discount']) }}</b> / {{ $nf($d['total']) }} = <b class="text-red">{{ $d['pct'] }}%</b>
            @elseif($i->type == 'cancel')
              {{ $d['status'] == 0 ? __('Đã xóa') : __('Đã hủy') }} · {{ __('Giá trị') }} {{ $nf($d['total']) }}
            @elseif($i->type == 'reduce')
              @if(isset($d['total_price'])){{ __('Tổng tiền') }}: {{ $nf($d['total_price'][0]) }} → <b class="text-red">{{ $nf($d['total_price'][1]) }}</b><br>@endif
              @if(isset($d['discount'])){{ __('Giảm giá') }}: {{ $nf($d['discount'][0]) }} → <b class="text-red">{{ $nf($d['discount'][1]) }}</b><br>@endif
              @if(isset($d['item']))
                {{ $dishes[$d['dish_id'] ?? 0] ?? __('Món') }}:
                @if($d['item'] === 'deleted') <b class="text-red">{{ __('bị xóa khỏi bill') }}</b>
                @else {{ __('SL') }} {{ $d['item'][0] + 0 }} → <b class="text-red">{{ $d['item'][1] + 0 }}</b> @endif
              @endif
            @elseif($i->type == 'receiver')
              {{ __('Người thu') }}: {{ $collecters[$d['from']] ?? $d['from'] ?? '–' }} → <b>{{ $collecters[$d['to']] ?? $d['to'] }}</b>
            @elseif($i->type == 'old')
              {{ __('Sửa ngày :day (:n thay đổi)', ['day' => date('d/m/Y', strtotime($d['day'])), 'n' => $d['changes']]) }}
            @elseif($i->type == 'many')
              {{ __('Đã sửa :n lần', ['n' => $d['times']]) }}
            @endif
            <br><a href="{{ route('activity-log.index', ['tab' => 'data', 'model' => 'booking', 'model_id' => $i->booking_id]) }}" target="_blank"><small>{{ __('lịch sử bill') }}</small></a>
          </td>
          <td style="white-space:nowrap">
            @if($i->reviewed)
              <small>{{ __('Đã xem') }}: {{ $users[$i->reviewed_by] ?? '' }}<br>{{ $i->reviewed_at ? date('d/m H:i', strtotime($i->reviewed_at)) : '' }}</small>
              @if($canReview)<br><a href="#" class="undo" data-key="{{ $i->key }}"><small>{{ __('Bỏ đánh dấu') }}</small></a>@endif
            @elseif($canReview)
              <button type="button" class="btn btn-xs btn-default rv" data-key="{{ $i->key }}"><i class="fa fa-check"></i> {{ __('Đánh dấu đã xem') }}</button>
            @else <span class="text-red">{{ __('Chưa xem') }}</span> @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted">{{ __('Không có cảnh báo nào.') }}</td></tr>
        @endforelse
      </table>
    </div>
  </div>
</section>
</div>
@stop
@section('js')
<script>
  $(function(){
    function send(keys, undo){
      $.ajax({ url: @json(route('bill-anomaly.review')), type: 'POST', dataType: 'json',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), keys: keys, undo: undo ? 1 : 0 },
        success: function(){ location.reload(); }, error: function(){ alert({!! json_encode(__('Có lỗi xảy ra')) !!}); } });
    }
    $('.rv').click(function(){ send([$(this).data('key')], false); });
    $('.undo').click(function(e){ e.preventDefault(); send([$(this).data('key')], true); });
    $('#chkAll').change(function(){ $('.chk').prop('checked', this.checked); });
    $('#btnReviewSel').click(function(){
      var keys = $('.chk:checked').map(function(){ return this.value; }).get();
      if(keys.length) send(keys, false);
    });
  });
</script>
@stop
