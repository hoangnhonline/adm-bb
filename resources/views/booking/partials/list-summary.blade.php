{{-- Hàng thẻ tổng của danh sách bill (16/11/2026) --}}
@php
  $sumCards = [
    [__('Số bill'), number_format($items->total()), ''],
    [__('Tổng tiền'), number_format($arrData['tong_tien'] ?? 0), ''],
    [__('Giảm giá'), number_format($arrData['tong_giam'] ?? 0), ''],
    [__('Doanh thu'), number_format($totalRevenue), 'main'],
  ];
  if (isset($totalVat)) {
    $sumCards[] = ['VAT', number_format($totalVat), ''];
    $sumCards[] = [__('Tổng thanh toán'), number_format($totalNet + $totalVat), ''];
    if ($totalCom > 0) $sumCards[] = [__('Hoa hồng đối tác'), number_format($totalCom), ''];
  }
  if ($totalDrinks > 0) $sumCards[] = [__('Đồ uống'), number_format($totalDrinks), ''];
  if ($totalRupees > 0) $sumCards[] = [__('Rupees'), number_format($totalRupees), ''];
@endphp
<div class="bk-sum">
  @foreach($sumCards as $c)
  <div class="bk-sum-card {{ $c[2] }}"><div class="v">{{ $c[1] }}</div><div class="l">{{ $c[0] }}</div></div>
  @endforeach
  @if(isset($totalVat) && \App\Services\Perm::can('report.view'))
  <a class="bk-sum-link" href="{{ route('report.vat') }}">{{ __('Báo cáo VAT & hoa hồng') }} &raquo;</a>
  @endif
</div>
<style>
  .bk-toolbar { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
  .bk-sum { display:flex; flex-wrap:wrap; gap:8px; align-items:stretch; margin-bottom:10px; }
  .bk-sum-card { background:#fff; border:1px solid #e3e6ea; border-radius:6px; padding:6px 12px; min-width:110px; }
  .bk-sum-card .v { font-size:17px; font-weight:700; color:#222; white-space:nowrap; }
  .bk-sum-card .l { font-size:12px; color:#777; }
  .bk-sum-card.main { border-color:#3c8dbc; background:#f2f8fc; }
  .bk-sum-card.main .v { color:#3c8dbc; }
  .bk-sum-link { align-self:center; font-size:13px; margin-left:4px; }
  .bk-branch { display:inline-block; margin:0 4px 0 0; }
  .bk-branch input { display:none; }
  .bk-branch span { display:inline-block; padding:3px 10px; border:1px solid #ccc; border-radius:14px; font-size:12px; color:#666; background:#fff; cursor:pointer; }
  .bk-branch input:checked + span { background:#3c8dbc; border-color:#3c8dbc; color:#fff; }
  .bk-badge { display:inline-block; font-size:11px; padding:1px 6px; border-radius:3px; margin:2px 2px 0 0; background:#eef1f4; color:#444; }
  .bk-badge.blue { background:#e3f0fa; color:#1d6fa5; } .bk-badge.red { background:#fbe9e7; color:#c0392b; } .bk-badge.orange { background:#fdf2e3; color:#b9770e; }
</style>
