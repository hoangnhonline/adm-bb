@extends('layout')
@section('content')
<div class="content-wrapper">


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 style="text-transform: uppercase;">
            Đặt tour : Mã QR <span style="color: red">PTT{{ $detail->id }}</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <a class="btn btn-default btn-sm" href="{{ route('booking.index', ['type' => $detail->type]) }}"
            style="margin-bottom:5px">Back</a>
        <a class="btn btn-success btn-sm" href="{{ route('booking.index', ['type' => $detail->type]) }}"
            style="margin-bottom:5px">Xem danh sách booking</a>
        <a href="{{ route( 'booking-payment.index', ['booking_id' => $detail->id] ) }}" class="btn btn-danger btn-sm"
            style="margin-bottom:5px">Lịch sử thanh toán</a>
        <a href="{{ route( 'booking-qrcode', ['booking_id' => $detail->id] ) }}" class="btn btn-info btn-sm"
            style="margin-bottom:5px">QR Code</a>
        
        <div class="box box-primary">
            <div class="qr-code">
                {{-- <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->merge('logo-white.png', 0.4, true)
                ->size(200)->errorCorrection('H')
                ->generate($link)) !!} "> --}}
                {!! QrCode::size(200)->generate($link) !!}
            </div>
        </div>

        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>

@stop
@section('js')
<script type="text/javascript">
    var levelLogin = {
        {
            Auth::user() - > level
        }
    };

</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('img.img-unc').click(function () {
            $('#unc_img').attr('src', $(this).attr('src'));
            $('#uncModal').modal('show');
        });
    });

</script>
@stop
