<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>email</title>
    </head>

    <body>
        <div class="container">
            <p style="font-size: 15px;">Kính gửi <b>Quý khách,</b></p>
            <p style="font-size: 15px;"><b>Plan To Travel</b> trân trọng cảm ơn quý khách đã tin tưởng và sử dụng dịch vụ đặt phòng của chúng tôi</p>
            <p style="font-size: 15px;"><b>Thông tin đặt phòng</b> của quý khách được thể hiện chi tiết như bên dưới:</p>
            <br />
            <table class="table table-bordered" width="700" style="color: #1f497d;">
                <tr>
                    <td colspan="2">
                        <img src="{{ asset('images/logo-plan-to-travel.png') }}" alt="logo" width="160px" align="left" />
                        <div style="float: right; text-align: right; font-size: 14px; color: #5f6368;">
                            <p style="font-weight: bold; margin-top: 5px; margin-bottom: 5px;">CTY TNHH TMDV & DU LỊCH PLAN TO TRAVEL</p>
                            ĐC: <b>124 Trần Hưng Đạo, Dương Tơ, Phú Quốc, KG</b>
                            <br />
                            MST: <b>0315788585</b>
                            <br />
                            Hotline: <b>0911 380 111</b>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center;">
                        <h3>XÁC NHẬN ĐẶT PHÒNG</h3>
                    </td>
                </tr>
            </table>
            <table width="700" border="1" cellspacing="0" cellpadding="10" style="color: #1f497d;">
                <tr>
                    <td width="170">
                        Mã đặt phòng
                    </td>
                    <td style="color: red;">
                        <b style="text-transform: uppercase;">{{ $detail->booking_code }}</b>
                    </td>
                </tr>
                <tr>
                    <td width="170">
                        Khách sạn/resort
                    </td>
                    <td>
                        <b>{!! $detailHotel->name !!}</b>
                    </td>
                </tr>
                <tr>
                    <td width="170">
                        Customer name
                    </td>
                    <td>
                        <b>{!! $detail->name !!}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Số điện thoại
                    </td>
                    <td>
                        <b>{!! $detail->phone !!}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Ngày đến
                    </td>
                    <td>
                        <b>{!! date('d/m/Y', strtotime($detail->checkin)) !!}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Ngày đi
                    </td>
                    <td>
                        <b>{!! date('d/m/Y', strtotime($detail->checkout)) !!}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Người lớn/Trẻ em/Em bé
                    </td>
                    <td>
                        <b>{{ $detail->adults }}/{{ $detail->childs }}/{{ $detail->infants }}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Loại phòng
                    </td>
                    <td style="font-weight: bold;">
                        @foreach($detail->rooms as $room) - {{ $room->room_name }}: {{ $room->room_amount }} phòng {{ $room->nights }} đêm <br />
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td>
                        Notes
                    </td>
                    <td style="color: red; font-weight: bold;">
                        {!! nl2br($detail->notes_hotel) !!}
                    </td>
                </tr>
            </table>
            <br />
            <p style="font-size: 15px;">
                <b>Quý khách</b> cần bất kỳ sự hỗ trợ nào vui lòng gọi Hotline <b style="color: red;">0911 380 111</b>. Kính chúc <b>quý khách</b> có kỳ nghỉ tuyệt vời tại đảo ngọc Phú Quốc.<br />
                Trân trọng cảm ơn.
            </p>
            <br />
            <br />
            <br />

            <div>
                <div dir="ltr" data-smartmail="gmail_signature">
                    <div dir="ltr">
                        <div>
                            <div dir="ltr">
                                <div dir="ltr">
                                    <h4 style="line-height: 1.1; margin-top: 10px; margin-bottom: 10px;">
                                        <font face="Helvetica Neue, Helvetica, Arial, sans-serif">Thanks and best regards</font><i style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 18px;">,</i>
                                        <font color="#0b5394" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 500;"><br /></font>
                                    </h4>
                                    <h4 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.1; margin-top: 10px; margin-bottom: 10px;">
                                        <font color="#0b5394">CÔNG TY TNHH THƯƠNG MẠI DỊCH VỤ VÀ DU LỊCH PLAN TO TRAVEL</font>
                                    </h4>
                                    <div>
                                        <b>Chuyên:</b> Booking phòng khách sạn Phú Quốc, Book tour 4 đảo giá tốt uy tín, cho thuê xe 4C,7C,16C, vé tham quan Vinpearl, cáp treo, hỗ trợ <b>lên lịch tham quan chuyên nghiệp miễn phí</b> giúp
                                        tiết kiệm rất nhiều chi phí tại Phú Quốc.
                                    </div>
                                    <span style="color: rgb(0, 0, 0); font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;"><b>Mã số thuế</b>:&nbsp;</span>0315788585
                                    <br style="color: rgb(0, 0, 0); font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;" />
                                    <span style="color: rgb(0, 0, 0); font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;"><b>Địa chỉ:</b> 124 Trần Hưng Đạo, Dương Tơ, Phú Quốc, KG</span>
                                    <span style="color: rgb(0, 0, 0); font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">
                                        <br />
                                        <b>Hotline:</b>
                                    </span>
                                    <span style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">
                                        <font color="#ff0000"><b>0911 380 111</b></font>
                                    </span>
                                    <div class="yj6qo"></div>
                                    <div class="adL"><br /></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
