<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitchen Screen</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .table-block { border: 1px solid #e9ecef; border-radius: 14px; padding: 14px; background: #ddd; }
        .dish-pill { border-radius: 5px; padding: 3px 5px; display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .dish-left { display:flex; flex-direction:column; }
        .dish-name { font-weight: 700; }
        .dish-meta { font-size: 12px; opacity: .75; }
        .done-badge { font-size: 12px; }
        .btn-done { white-space: nowrap; }

        /* ✅ DONE: nền xám tối hẳn */
        .dish-done {
            display: none;
            background: #ccc !important;
            border: 2px solid #ccc !important;
            color: #f8f9fa;
        }
        .dish-done .dish-meta { opacity: 0.85; }
        .text-bg-dark {
            color: red !important;
            background-color: #fff !important;
            color: blue !important;
            font-size: 20px;
        }
        .js-qty-checks input.form-check-input {
          width: 20px;
          height: 20px;
        }
        .js-qty-checks.is-loading {
          opacity: 0.6;
        }

    </style>
</head>

<body class="bg-light">

@php
    function dishColor($dishId) {
        $hash = crc32((string)$dishId);
        $h = $hash % 360;
        $s = 70;
        $l = 88;
        return "hsl($h, $s%, $l%)";
    }

    function dishBorder($dishId) {
        $hash = crc32((string)$dishId);
        $h = $hash % 360;
        return "hsl($h, 70%, 55%)";
    }
@endphp


    {{-- ✅ Tables --}}
    <div class="row g-3" style="padding: 5px">
        @foreach($blocks as $t)
            <div class="col-12 col-md-4 col-xl-4">
                <div class="table-block">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        @php
                            // booking_created_at có thể là "H:i:s" hoặc datetime string
                            $bookingTime = '';
                            if (!empty($t['booking_created_at'])) {
                                $ts = strtotime($t['booking_created_at']);
                                $bookingTime = $ts ? date('H:i', $ts) : $t['booking_created_at'];
                            }
                        @endphp

                        <div class="fs-4 fw-bold">
                            Table {{ $t['table_no'] }} @if($bookingTime) - {{ $bookingTime }} @endif
                           <span style="color:orange; font-weight: bold">{{ $t['notes'] }} </span>
                             @if($t['take_away'] == 1)
                    <span style="color: blue">TAKE AWAY</span>
                    @endif
                    @if($t['indian'] == 2)
                    <span style="color: red">NOT INDIAN</span>
                    @endif
                        </div>                        
                        <span class="badge bg-secondary js-items-badge" data-count="{{ $t['details']->count() }}">
  {{ $t['details']->count() }} items</span>
                    </div>

                    <div class="d-grid gap-2">
                        @foreach($t['details'] as $d)
                            @php
                                $dishId   = $d->dish_id;
                                $dishName = optional($d->dish)->name ?? ('Dish #' . $dishId);
                                $notes = $d->notes ?? "";
                                $qty      = (int)($d->amount ?? 1);
                                $doneQty  = (int)($d->done_qty ?? 0);
                                $pending  = max(0, $qty - $doneQty);

                                // ✅ done theo done_qty/amount
                                $isDone   = ($pending === 0);

                                $time = optional($d->created_at)->format('H:i:s') ?? '';

                                $bg = dishColor($dishId);
                                $bd = dishBorder($dishId);
                            @endphp

                            <div class="dish-pill {{ $isDone ? 'dish-done' : '' }}"

                                data-detail-id="{{ $d->id }}"
                                data-dish-id="{{ $dishId }}"

                                style="{{ $isDone ? '' : 'background:' . $bg . '; border:1px solid ' . $bd }}">

                                <div class="dish-left">
                                    <div class="dish-name" style="text-transform: uppercase;">
                                        {{ $dishName }}
                                        <span class="ms-2 badge text-bg-dark js-done-ratio" data-detail-id="{{ $d->id }}">
    {{ $doneQty ."/". $qty }}
</span>

                                        <span
                                          class="ms-2 js-qty-checks"
                                          data-detail-id="{{ $d->id }}"
                                          data-dish-id="{{ $dishId }}"
                                          data-booking-id="{{ $t['booking_id'] }}"
                                          data-url="{{ route('booking.dish-done', $d->id) }}"
                                          data-qty="{{ $qty }}"
                                          data-done-qty="{{ $doneQty }}"
                                          style="display:inline-flex; gap:6px; align-items:center; cursor:pointer;"
                                          title="Click to mark Done +1"
                                        >
                                          @for($i=1; $i<=$qty; $i++)
                                              <input
                                                type="checkbox"
                                                class="form-check-input m-0"
                                                style="pointer-events:none;"  {{-- chỉ cho checked tăng dần bằng click container --}}
                                                {{ $i <= $doneQty ? 'checked' : '' }}
                                              >
                                          @endfor
                                        </span>




<span class="ms-2 badge text-bg-success js-done-label" style="{{ $isDone ? '' : 'display:none' }}">Done</span>
            @if($notes)
                                        <span style="color: red; font-weight: bold; padding-left: 10px; text-transform: uppercase; font-size: 17px">{{ $notes }}</span>
                                        @endif
                                    </div>

                                  
                                </div>                               
                                
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        @endforeach
    </div>
<a style="margin-left: 10px; margin-top:10px; display:block;" href="{{ route('booking.index') }}"><< Back</a>
</div>

<script>
  // ✅ Auto refresh mỗi 5 giây
    setInterval(function () {
      window.location.reload();
   }, 5000);
</script>
<script>
(function () {
  function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
  }
  function toInt(v, d=0) {
    const n = parseInt(v, 10);
    return Number.isFinite(n) ? n : d;
  }

  async function donePlusOne(container) {
    if (container.classList.contains('is-loading')) return;

    const qty = toInt(container.getAttribute('data-qty'), 1);
    const doneQty = toInt(container.getAttribute('data-done-qty'), 0);

    if (doneQty >= qty) return;

    container.classList.add('is-loading');

    const url = container.getAttribute('data-url');
    const detailId = container.getAttribute('data-detail-id');

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        }
      });

      if (!res.ok) {
        alert('Update failed: ' + res.status);
        container.classList.remove('is-loading');
        return;
      }

      const data = await res.json();

      const newDoneQty = toInt(data.done_qty, doneQty + 1);
      const qtyFinal   = toInt(data.amount, qty);
      const isDone     = !!data.is_done || (newDoneQty >= qtyFinal);

      // cập nhật data
      container.setAttribute('data-done-qty', String(newDoneQty));

      // ✅ update badge done/qty
      const ratioEl = document.querySelector('.js-done-ratio[data-detail-id="' + detailId + '"]');
      if (ratioEl) ratioEl.textContent = newDoneQty + '/' + qtyFinal;

      // check thêm checkbox tương ứng
      const checks = container.querySelectorAll('input[type="checkbox"]');
      if (checks[newDoneQty - 1]) checks[newDoneQty - 1].checked = true;

      // done đủ -> ẩn dòng
      if (isDone) {
        const pill = document.querySelector(`.dish-pill[data-detail-id="${detailId}"]`);
        if (pill) pill.classList.add('dish-done');
      }

    } catch (e) {
      alert('Error: ' + (e && e.message ? e.message : e));
    } finally {
      container.classList.remove('is-loading');
    }
  }

  document.addEventListener('click', function (e) {
    const box = e.target.closest('.js-qty-checks');
    if (!box) return;
    e.preventDefault();
    donePlusOne(box);
  });
})();
</script>



</body>
</html>
