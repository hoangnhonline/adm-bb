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
  @if(!empty($kiotStat) && $kiotStat['base_count'] > 0)
  @php $kPct = $totalRevenue ? round($kiotStat['revenue'] * 100 / $totalRevenue, 1) : 0; @endphp
  <div class="bk-sum-card kiot" id="kiotCard" data-revenue="{{ (int) $kiotStat['revenue'] }}" data-count="{{ (int) $kiotStat['count'] }}"
       data-total="{{ (int) $totalRevenue }}" data-base="{{ (int) $kiotStat['base_revenue'] }}" data-base-count="{{ (int) $kiotStat['base_count'] }}">
    <div class="v"><span class="js-kiot-rev">{{ number_format($kiotStat['revenue']) }}</span> <small class="js-kiot-pct">{{ $kPct }}%</small></div>
    <div class="l">{{ __('Kiot') }} · <span class="js-kiot-count">{{ $kiotStat['count'] }}</span>/{{ $kiotStat['base_count'] }} {{ __('bill') }} ·
      <span class="js-kiot-pct2">{{ $kiotStat['base_revenue'] ? round($kiotStat['revenue'] * 100 / $kiotStat['base_revenue'], 1) : 0 }}%</span> {{ __('của NM + THD') }}</div>
  </div>
  @endif
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
  .bk-sum-card.kiot { border-color:#605ca8; background:#f5f4fb; } .bk-sum-card.kiot .v { color:#605ca8; } .bk-sum-card.kiot small { font-size:13px; }
  .bk-sum-link { align-self:center; font-size:13px; margin-left:4px; }
  .bk-branch { display:inline-block; margin:0 4px 0 0; }
  .bk-branch input { display:none; }
  .bk-branch span { display:inline-block; padding:3px 10px; border:1px solid #ccc; border-radius:14px; font-size:12px; color:#666; background:#fff; cursor:pointer; }
  .bk-branch input:checked + span { background:#3c8dbc; border-color:#3c8dbc; color:#fff; }
  .bk-badge { display:inline-block; font-size:11px; padding:1px 6px; border-radius:3px; margin:2px 2px 0 0; background:#eef1f4; color:#444; }
  .bk-badge.blue { background:#e3f0fa; color:#1d6fa5; } .bk-badge.red { background:#fbe9e7; color:#c0392b; } .bk-badge.orange { background:#fdf2e3; color:#b9770e; }
</style>
<script>
// Tick / bỏ tick KIOT trên danh sách => cập nhật ngay tổng tiền Kiot và % doanh thu (19/11/2026)
document.addEventListener('change', function (e) {
  var cb = e.target;
  if (!cb.matches || !cb.matches('input[data-column="is_kiot"]')) return;
  var card = document.getElementById('kiotCard'); if (!card) return;
  var rev = parseInt(cb.getAttribute('data-revenue'), 10) || 0, sign = cb.checked ? 1 : -1;
  var kRev = (parseInt(card.getAttribute('data-revenue'), 10) || 0) + sign * rev;
  var kCnt = (parseInt(card.getAttribute('data-count'), 10) || 0) + sign;
  var total = parseInt(card.getAttribute('data-total'), 10) || 0, base = parseInt(card.getAttribute('data-base'), 10) || 0;
  card.setAttribute('data-revenue', kRev); card.setAttribute('data-count', kCnt);
  var fmt = function (n) { return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); };
  var pct = function (a, b) { return b ? (Math.round(a * 1000 / b) / 10) : 0; };
  card.querySelector('.js-kiot-rev').textContent = fmt(kRev);
  card.querySelector('.js-kiot-count').textContent = kCnt;
  card.querySelector('.js-kiot-pct').textContent = pct(kRev, total) + '%';
  card.querySelector('.js-kiot-pct2').textContent = pct(kRev, base) + '%';
});
</script>
