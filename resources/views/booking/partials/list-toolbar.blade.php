{{-- Thanh nút trên cùng của danh sách bill (16/11/2026) --}}
<div class="bk-toolbar">
  @if($notNH)
  <a href="{{ route('booking.create') }}{{ $time_type == 3 ? '?use_date=' . $arrSearch['use_date_from'] : '' }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> {{ __('TẠO BILL') }}</a>
  @endif
  @if(\App\Http\Controllers\BookingController::canUseKitchen())
  <a href="{{ route('booking.orders') }}" class="btn btn-default btn-sm"><i class="fa fa-cutlery"></i> {{ __('BẾP') }}</a>
  <a href="{{ route('booking.curry') }}" class="btn btn-default btn-sm">{{ __('CURRY') }}</a>
  <a href="{{ route('booking.tandoor') }}" class="btn btn-default btn-sm">{{ __('BÁNH') }}</a>
  @endif
  @if(empty($isView) && !empty($showExcel) && \App\Services\Perm::can('bill.export'))
  <a href="javascript:;" class="btn btn-default btn-sm" id="btnExcel"><i class="fa fa-file-excel-o"></i> {{ __('Excel') }}</a>
  @endif
</div>
