<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>HÓA ĐƠN</title>
    <!-- Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Optional: Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<style type="text/css">
    p div td tr th span{
        font-size: 12px !important;
    }   
</style>
<br>
<div style="font-size: 12px; width: 100%; max-width: 420px; border: 1px solid #ddd; margin: 0 auto; padding: 10px; border-radius: 5px; color: black !important; font-weight: bold !important;">
<p style="font-size:12px; text-align: center; font-weight: bold;">BOMBAY INDIAN RESTAURANT</p>
   <p style="font-size: 11px; color: black; font-weight: bold;"> 
    @if($detail->branch_id == 1)
07 Le Loi street, Duong Dong, Phu Quoc<br>
@elseif($detail->branch_id == 2)
Alley 124 Tran Hung Dao street, Duong Dong, Phu Quoc<br>
@else
Kiosk P12 Vui-Fest night market, Sunset Town, Phu Quoc
@endif
Hotline: +84981478544 </p>

<p style="text-align: right; width: 100%; max-width: 400px; margin: 0 auto; "><span style="font-weight: bold; color: black;">Invoice: #{{ str_pad($detail->id,5,"0",STR_PAD_LEFT) }} {{ date('m/d/y H:i', strtotime($detail->created_at)) }}</p>
<table cellpadding="5" cellspacing="0" border="0" style="margin: 0 auto;width: 100%; max-width: 420px;">
    <tr style="font-size:12px;"> 
        <td style="border-bottom: 1px dotted #000 !important; font-weight: bold">DISH NAME</td>
        <td style="text-align: right;border-bottom: 1px dotted #000 !important; font-weight: bold">DETAIL</td>
    </tr>

    @php
        // Tổng bill vẫn lấy theo details gốc để chắc chắn đúng
        $total = $detail->details->sum('total_price');

        // Gộp món theo dish_id + price (tránh trường hợp cùng món nhưng khác giá)
        $grouped = $detail->details
            ->groupBy(function($item){
                return $item->dish_id . '|' . (int)$item->price;
            })
            ->map(function($rows){
                $first = $rows->first();
                $amount = $rows->sum('amount');
                $totalPrice = $rows->sum('total_price');
                $price = (int)$first->price; // cùng group nên price giống nhau

                return (object)[
                    'dish_name' => optional($first->dish)->name,
                    'price' => $price,
                    'amount' => $amount,
                    'total_price' => $totalPrice,
                ];
            })
            ->values();
    @endphp

    @foreach($grouped as $g)
        <tr style="font-size:12px;color: black; font-weight: bold;">
            <td style="text-transform: capitalize !important;border-bottom: 1px dotted #000 !important;">
                {{ ucwords($g->dish_name) }}
            </td>
            <td style="text-align: right;border-bottom: 1px dotted #000 !important;">
                {{ number_format($g->price) }} x {{ $g->amount }} = {{ number_format($g->total_price) }}
            </td>
        </tr>
    @endforeach

    
    @if($detail->discount > 0)
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">SUBTOTAL</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($total) }}
     
        </td>
    </tr>
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">DISCOUNT</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($detail->discount) }}</td>
    </tr>
     @if($detail->no_vat == 0)
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">VAT ({{ config('bombay.vat_rate', 8) }}%)</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($detail->con_lai*config('bombay.vat_rate', 8)/100) }}
     
        </td>
    </tr>
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">TOTAL</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($detail->con_lai*(1+config('bombay.vat_rate', 8)/100)) }}
            <!-- <br>
            @php
            $fx = app(\App\Services\CurrencyService::class);
            $inr = $fx->vndToInr($total, 0);
            @endphp
            RUPEES: <strong style="color:red">{{ $inr }}</strong> -->
        </td>
    </tr>
    @else
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">TOTAL</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($detail->con_lai) }}
            <!-- <br>
            @php
            $fx = app(\App\Services\CurrencyService::class);
            $inr = $fx->vndToInr($total, 0);
            @endphp
            RUPEES: <strong style="color:red">{{ $inr }}</strong> -->
        </td>
    </tr>
    @endif
    @else 
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">SUB TOTAL</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($total) }}
     
        </td>
    </tr> 
    @if($detail->no_vat == 0)
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">VAT ({{ config('bombay.vat_rate', 8) }}%)</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($total*config('bombay.vat_rate', 8)/100) }}
     
        </td>
    </tr>  

    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">TOTAL</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($total*(1+config('bombay.vat_rate', 8)/100)) }}
     
        </td>
    </tr>
    @else
    <tr style="font-size:12px !important;">
        <td style="text-align: right; font-weight: bold">TOTAL</td>
        <td style="text-align: right; font-weight: bold">{{ number_format($total) }}
     
        </td>
    </tr>

    @endif
    <!-- <tr>
        <td colspan="2" class="text-align:center">            
            <img src="{{ asset('images/harj.png') }}" width="100%" style="margin: 0 auto;">
        </td>
    </tr> -->
    @endif
</table>

<p style="text-align:center; font-weight: bold; margin-top: 20px">*** THANK YOU ***</p>

<a style="margin-left: 10px; margin-top:10px; display:block;" href="{{ route('booking.index') }}"><< Back</a>
</div>
 <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>
</html>
