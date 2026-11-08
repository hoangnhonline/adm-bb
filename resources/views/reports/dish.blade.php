@extends('layout')

@section('content')
<div class="container">
    <h4 class="mb-3">📊 Báo cáo món đã bán</h4>

    {{-- Filter --}}
    
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Tên món</th>
                <th class="text-right">Số lượng</th>
                <th class="text-right">Doanh thu</th>
            </tr>
        </thead>

        <tbody>
            @php
                $sumQty = 0;
                $sumMoney = 0;
            @endphp

            @foreach ($reports as $i => $item)
                @php
                    $sumQty += $item->total_qty;
                    $sumMoney += $item->total_money;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">{{ number_format($item->total_qty) }}</td>
                    <td class="text-right">{{ number_format($item->total_money) }} đ</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot class="table-secondary fw-bold">
            <tr>
                <td colspan="3">Tổng doanh thu Bánh</td>
                <td class="text-right text-primary">
                    {{ number_format($totalBread) }} đ
                </td>
            </tr>
            <tr>
                <td colspan="3">Tổng doanh thu Nước uống</td>
                <td class="text-right text-success">
                    {{ number_format($totalDrink) }} đ
                </td>
            </tr>
            <tr>
                <td colspan="3">Tổng doanh thu món khác</td>
                <td class="text-right text-warning">
                    {{ number_format($totalOther) }} đ
                </td>
            </tr>
            <tr>
                <td colspan="3">TỔNG CỘNG</td>
                <td class="text-right text-danger">
                    {{ number_format($sumMoney) }} đ
                </td>
            </tr>
        </tfoot>
    </table>

    
</div>
@endsection
