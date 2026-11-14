<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitchen Screen</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Màn hình bếp (thiết kế lại 14/11/2026) */
        :root { --ok:#16a34a; --warn:#f59e0b; --late:#dc2626; }
        body { background:#1f2430; color:#111; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
        .kds-top { position:sticky; top:0; z-index:10; background:#111827; color:#fff; padding:8px 12px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; box-shadow:0 2px 6px rgba(0,0,0,.4); }
        .kds-top .brand { font-weight:800; font-size:20px; letter-spacing:.5px; }
        .kds-top .brand small { color:#9ca3af; font-weight:600; font-size:14px; margin-left:6px; }
        .kds-top .stat { background:#374151; border-radius:8px; padding:3px 10px; font-weight:700; }
        .kds-top .stat b { color:#fbbf24; font-size:18px; }
        .kds-top .clock { margin-left:auto; font-size:22px; font-weight:800; font-variant-numeric:tabular-nums; }
        .kds-top a.back { color:#9ca3af; text-decoration:none; font-weight:600; }
        .kds-top a.back:hover { color:#fff; }
        .live-dot { width:10px; height:10px; border-radius:50%; background:var(--ok); display:inline-block; }
        .live-dot.off { background:var(--late); }

        .kds-sum { background:#2b3242; padding:8px 12px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .kds-sum .lbl { color:#9ca3af; font-weight:700; font-size:13px; text-transform:uppercase; margin-right:4px; }
        .kds-sum .chip { background:#fff; border-radius:20px; padding:3px 4px 3px 12px; font-weight:700; font-size:14px; text-transform:uppercase; display:inline-flex; align-items:center; gap:8px; border-left:6px solid #999; }
        .kds-sum .chip .q { background:#111827; color:#fff; border-radius:14px; padding:1px 9px; font-size:15px; }

        .kds-board { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:12px; padding:12px; align-items:start; }
        .kds-card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 3px 10px rgba(0,0,0,.35); border-top:8px solid var(--ok); }
        .kds-card.warn { border-top-color:var(--warn); }
        .kds-card.late { border-top-color:var(--late); animation:pulse 1.6s ease-in-out infinite; }
        @keyframes pulse { 50% { box-shadow:0 0 0 4px rgba(220,38,38,.55); } }
        .kds-head { display:flex; align-items:center; gap:8px; padding:8px 12px; background:#f3f4f6; border-bottom:1px solid #e5e7eb; }
        .kds-head .tbl { font-size:28px; font-weight:900; line-height:1; }
        .kds-head .tbl small { font-size:13px; color:#6b7280; font-weight:700; display:block; text-transform:uppercase; }
        .kds-head .meta { margin-left:auto; text-align:right; }
        .kds-head .age { font-size:20px; font-weight:900; font-variant-numeric:tabular-nums; }
        .kds-card.warn .age { color:#b45309; } .kds-card.late .age { color:var(--late); }
        .kds-head .time { font-size:12px; color:#6b7280; font-weight:600; }
        .kds-tags { padding:6px 12px 0; display:flex; gap:6px; flex-wrap:wrap; }
        .tag { font-size:12px; font-weight:800; padding:2px 8px; border-radius:6px; text-transform:uppercase; }
        .tag-ta { background:#dbeafe; color:#1d4ed8; } .tag-ni { background:#fee2e2; color:#b91c1c; }
        .kds-note { margin:6px 12px 0; padding:5px 8px; background:#fff7ed; border-left:4px solid #f97316; color:#9a3412; font-weight:700; border-radius:4px; }

        .kds-items { padding:8px; display:grid; gap:6px; }
        .dish-row { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; border-left:8px solid #999; background:#f9fafb; cursor:pointer; user-select:none; transition:transform .08s, opacity .2s; }
        .dish-row:active { transform:scale(.98); }
        .dish-row.is-loading { opacity:.5; }
        .dish-row .q { min-width:46px; text-align:center; font-size:22px; font-weight:900; background:#111827; color:#fff; border-radius:8px; padding:2px 6px; }
        .dish-row .q small { font-size:12px; font-weight:700; color:#9ca3af; }
        .dish-row .nm { font-weight:800; font-size:17px; text-transform:uppercase; line-height:1.2; }
        .dish-row .dn { color:#dc2626; font-weight:800; text-transform:uppercase; font-size:15px; }
        .dish-row .tm { font-size:12px; color:#6b7280; }
        .dish-row .boxes { margin-left:auto; display:flex; gap:4px; flex-wrap:wrap; justify-content:flex-end; max-width:45%; }
        .dish-row .bx { width:22px; height:22px; border-radius:5px; border:2px solid #9ca3af; background:#fff; }
        .dish-row .bx.on { background:var(--ok); border-color:var(--ok); }
        .dish-row.dish-done { display:none; }
        .kds-done-toggle .dish-row.dish-done { display:flex; opacity:.45; cursor:default; }
        .kds-foot { padding:0 12px 8px; font-size:12px; color:#6b7280; }
        .kds-empty { color:#9ca3af; text-align:center; padding:80px 20px; font-size:22px; font-weight:700; }
        .btn-showdone { background:#374151; color:#e5e7eb; border:0; border-radius:8px; padding:3px 10px; font-weight:600; }
        .btn-showdone.on { background:#2563eb; color:#fff; }
    </style>
</head>
<body>
@php
    if (!function_exists('kdsDishHue')) {
        function kdsDishHue($dishId) { return crc32((string) $dishId) % 360; }
    }
    $branchName = optional(\App\Models\Branch::find(Auth::user()->branch_id))->name;
    $pendingDishes = $pendingSummary->sum('qty');
@endphp

<div class="kds-top">
    <span class="live-dot" id="liveDot" title="Auto refresh"></span>
    <span class="brand">KITCHEN @if($branchName)<small>{{ $branchName }}</small>@endif</span>
    <span class="stat">Tables <b id="statTables">{{ $blocks->count() }}</b></span>
    <span class="stat">Dishes to cook <b id="statDishes">{{ $pendingDishes }}</b></span>
    <button type="button" class="btn-showdone" id="btnShowDone">Show done</button>
    <span class="clock" id="clock">{{ date('H:i') }}</span>
    <a class="back" href="{{ route('booking.index') }}">&laquo; Back</a>
</div>

<div id="kdsLive">
    @if($pendingSummary->count())
    <div class="kds-sum">
        <span class="lbl">To cook:</span>
        @foreach($pendingSummary as $s)
        <span class="chip" style="border-left-color:hsl({{ kdsDishHue($s['dish_id']) }},70%,50%)">{{ $s['name'] }} <span class="q">{{ $s['qty'] }}</span></span>
        @endforeach
    </div>
    @endif

    <div class="kds-board">
        @forelse($blocks as $t)
            @php
                $pendingRows = 0; $oldest = null;
                foreach ($t['details'] as $d) {
                    $q = max(1, (int) ($d->amount ?? 1));
                    if ((int) ($d->done_qty ?? 0) < $q) {
                        $pendingRows++;
                        $ts = $d->created_at ? $d->created_at->timestamp : null;
                        if ($ts && (!$oldest || $ts < $oldest)) $oldest = $ts;
                    }
                }
                $bookingTime = '';
                if (!empty($t['booking_created_at'])) { $tsb = strtotime($t['booking_created_at']); $bookingTime = $tsb ? date('H:i', $tsb) : $t['booking_created_at']; }
            @endphp
            <div class="kds-card" data-since="{{ $oldest ?: '' }}" data-booking-id="{{ $t['booking_id'] }}">
                <div class="kds-head">
                    <div class="tbl"><small>Table</small>{{ $t['table_no'] }}</div>
                    <div class="meta">
                        <div class="age js-age">{{ $oldest ? floor((time() - $oldest) / 60) . "'" : '' }}</div>
                        <div class="time">#{{ $t['booking_id'] }} · {{ $bookingTime }} · <span class="js-left">{{ $pendingRows }}</span> left</div>
                    </div>
                </div>
                @if($t['take_away'] == 1 || $t['indian'] == 2)
                <div class="kds-tags">
                    @if($t['take_away'] == 1)<span class="tag tag-ta">Take away</span>@endif
                    @if($t['indian'] == 2)<span class="tag tag-ni">Not Indian</span>@endif
                </div>
                @endif
                @if($t['notes'])<div class="kds-note">{{ $t['notes'] }}</div>@endif

                <div class="kds-items">
                    @foreach($t['details'] as $d)
                        @php
                            $qty = max(1, (int) ($d->amount ?? 1));
                            $doneQty = min($qty, (int) ($d->done_qty ?? 0));
                            $isDone = $doneQty >= $qty;
                            $hue = kdsDishHue($d->dish_id);
                        @endphp
                        <div class="dish-row js-dish {{ $isDone ? 'dish-done' : '' }}"
                             style="border-left-color:hsl({{ $hue }},70%,50%);background:hsl({{ $hue }},70%,95%)"
                             data-detail-id="{{ $d->id }}" data-url="{{ route('booking.dish-done', $d->id) }}"
                             data-qty="{{ $qty }}" data-done-qty="{{ $doneQty }}" title="Tap = 1 done">
                            <div class="q"><span class="js-pending">{{ $qty - $doneQty }}</span>@if($qty > 1)<small>/{{ $qty }}</small>@endif</div>
                            <div>
                                <div class="nm">{{ optional($d->dish)->name ?? ('Dish #' . $d->dish_id) }}</div>
                                @if($d->notes)<div class="dn">{{ $d->notes }}</div>@endif
                                <div class="tm">{{ optional($d->created_at)->format('H:i') }}</div>
                            </div>
                            <div class="boxes">
                                @for($i = 1; $i <= $qty; $i++)<span class="bx {{ $i <= $doneQty ? 'on' : '' }}"></span>@endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="kds-empty" style="grid-column:1/-1">No orders waiting 👍</div>
        @endforelse
    </div>
</div>

<script>
(function () {
    var WARN_MIN = 10, LATE_MIN = 20;   // phút chờ: vàng / đỏ
    var busy = 0, lastTap = 0;

    function csrf() { var el = document.querySelector('meta[name="csrf-token"]'); return el ? el.getAttribute('content') : ''; }
    function pad(n) { return (n < 10 ? '0' : '') + n; }

    function tick() {
        var now = new Date();
        document.getElementById('clock').textContent = pad(now.getHours()) + ':' + pad(now.getMinutes());
        var sec = Math.floor(Date.now() / 1000);
        document.querySelectorAll('.kds-card').forEach(function (c) {
            var since = parseInt(c.getAttribute('data-since'), 10);
            c.classList.remove('warn', 'late');
            var ageEl = c.querySelector('.js-age');
            if (!since) { if (ageEl) ageEl.textContent = ''; return; }
            var m = Math.max(0, Math.floor((sec - since) / 60));
            if (ageEl) ageEl.textContent = m + "'";
            if (m >= LATE_MIN) c.classList.add('late'); else if (m >= WARN_MIN) c.classList.add('warn');
        });
    }

    function recount() {
        var dishes = 0;
        document.querySelectorAll('.kds-card').forEach(function (c) {
            var left = 0;
            c.querySelectorAll('.js-dish').forEach(function (r) {
                var p = parseInt(r.getAttribute('data-qty'), 10) - parseInt(r.getAttribute('data-done-qty'), 10);
                if (p > 0) { left++; dishes += p; }
            });
            var l = c.querySelector('.js-left'); if (l) l.textContent = left;
        });
        document.getElementById('statTables').textContent = document.querySelectorAll('.kds-card').length;
        document.getElementById('statDishes').textContent = dishes;
    }

    async function donePlusOne(row) {
        if (row.classList.contains('is-loading')) return;
        var qty = parseInt(row.getAttribute('data-qty'), 10) || 1;
        var done = parseInt(row.getAttribute('data-done-qty'), 10) || 0;
        if (done >= qty) return;
        row.classList.add('is-loading'); busy++; lastTap = Date.now();
        try {
            var res = await fetch(row.getAttribute('data-url'), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' } });
            if (!res.ok) { alert('Update failed: ' + res.status); return; }
            var data = await res.json();
            var nd = parseInt(data.done_qty, 10); if (isNaN(nd)) nd = done + 1;
            var nq = parseInt(data.amount, 10) || qty;
            row.setAttribute('data-done-qty', nd);
            var pe = row.querySelector('.js-pending'); if (pe) pe.textContent = Math.max(0, nq - nd);
            var bx = row.querySelectorAll('.bx'); for (var i = 0; i < bx.length; i++) bx[i].classList.toggle('on', i < nd);
            if (data.is_done || nd >= nq) row.classList.add('dish-done');
            recount();
        } catch (e) {
            alert('Error: ' + (e && e.message ? e.message : e));
        } finally {
            row.classList.remove('is-loading'); busy--;
        }
    }

    document.addEventListener('click', function (e) {
        var row = e.target.closest('.js-dish');
        if (row && !row.classList.contains('dish-done')) { e.preventDefault(); donePlusOne(row); }
    });

    var btnDone = document.getElementById('btnShowDone');
    function applyShowDone() {
        var on = sessionStorageGet('kdsShowDone') === '1';
        document.body.classList.toggle('kds-done-toggle', on);
        btnDone.classList.toggle('on', on);
        btnDone.textContent = on ? 'Hide done' : 'Show done';
    }
    function sessionStorageGet(k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } }
    function sessionStorageSet(k, v) { try { sessionStorage.setItem(k, v); } catch (e) {} }
    btnDone.addEventListener('click', function () { sessionStorageSet('kdsShowDone', sessionStorageGet('kdsShowDone') === '1' ? '0' : '1'); applyShowDone(); });

    // Làm mới dữ liệu mỗi 5 giây không nháy trang; bỏ qua khi đang bấm món
    async function refresh() {
        if (busy > 0 || Date.now() - lastTap < 1500) return;
        try {
            var res = await fetch(location.href, { headers: { 'Accept': 'text/html' }, cache: 'no-store' });
            if (!res.ok) throw new Error(res.status);
            var html = await res.text();
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var live = doc.getElementById('kdsLive');
            if (!live) { location.reload(); return; }   // bị đăng xuất / trang khác
            if (busy > 0) return;
            document.getElementById('kdsLive').innerHTML = live.innerHTML;
            document.getElementById('liveDot').classList.remove('off');
            recount(); tick();
        } catch (e) {
            document.getElementById('liveDot').classList.add('off');
        }
    }

    applyShowDone(); tick(); recount();
    setInterval(tick, 15000);
    setInterval(refresh, 5000);
})();
</script>
</body>
</html>
