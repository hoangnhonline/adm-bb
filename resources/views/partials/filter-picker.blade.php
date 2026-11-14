{{-- [BỘ LỌC TÙY CHỌN] (port từ Plan To Travel 13/11/2026) Cho user tick chọn các ô filter cần hiển thị, lưu theo tài khoản.
     - Mặc định (chưa lưu): chỉ hiện ngày + tìm kiếm cơ bản + nhóm trạng thái.
     - Ô filter đang có giá trị luôn được hiển thị (tránh lọc ngầm mà user không thấy).
     - Cấu hình lưu ở bảng user_filter_prefs, theo route name của trang. --}}
@auth
@php
    $fpRoute = Route::currentRouteName() ?? '';
    // Bombay: bật cho các trang danh sách bill và chi phí
    $fpEnabled = in_array($fpRoute, ['booking.index', 'cost.index', 'cost.nhap-hang']);
    $fpSaved = $fpEnabled ? \App\Http\Controllers\FilterPrefController::getForPage($fpRoute) : null;
@endphp
@if($fpEnabled)
<style>
  #fpPanel{position:absolute;z-index:1060;background:#fff;border:1px solid #d2d6de;border-radius:4px;box-shadow:0 4px 16px rgba(0,0,0,.15);padding:10px 12px;width:560px;max-width:95vw;display:none}
  #fpPanel .fp-title{font-weight:bold;margin-bottom:4px}
  #fpPanel .fp-note{font-size:12px;color:#777;margin-bottom:8px}
  #fpPanel .fp-list{max-height:320px;overflow:auto;column-count:2;column-gap:16px}
  #fpPanel .fp-list label{display:block;font-weight:normal;margin:0 0 6px 0;break-inside:avoid;cursor:pointer}
  #fpPanel .fp-list label.fp-active{color:#999}
  #fpPanel .fp-actions{margin-top:10px;text-align:right;border-top:1px solid #eee;padding-top:8px}
  @media (max-width:600px){#fpPanel .fp-list{column-count:1}}
</style>
<script>
(function () {
  var FP = {
    page: @json($fpRoute),
    saved: @json($fpSaved),
    url: @json(route('filter-prefs.save')),
    t: {
      btn: @json(__('Bộ lọc')),
      title: @json(__('Chọn bộ lọc hiển thị')),
      note: @json(__('Bộ lọc đang có giá trị luôn được hiển thị.')),
      save: @json(__('Lưu')),
      reset: @json(__('Mặc định')),
      close: @json(__('Đóng')),
      all: @json(__('Chọn tất cả')),
      dateRange: @json(__('Khoảng ngày')),
      err: @json(__('Không lưu được cấu hình bộ lọc'))
    }
  };
  // Bombay: mặc định hiện ngày, tìm ID/bàn, trạng thái, loại chi phí, chi nhánh. Các ô khác user tự bật.
  var DEFAULT_NAME = /^(phone|keyword|id_search|table_no|status|cate_id|branch_ids|branch_id|period|sort)$/;
  var DEFAULT_DATE = /(date|range|month|year|time_type|created_at)/i;

  function clean(s) { return (s || '').replace(/ /g, ' ').replace(/\s+/g, ' ').replace(/^[\s\-–]+|[\s\-–]+$/g, '').trim(); }

  function init() {
    var form = document.getElementById('searchForm');
    if (!form || form.getAttribute('data-fp') === '1') return;
    form.setAttribute('data-fp', '1');

    var items = {}, order = [];
    var groups = Array.prototype.filter.call(form.querySelectorAll('.form-group'), function (g) {
      return !(g.parentElement && g.parentElement.closest('.form-group'));
    });
    groups.forEach(function (g) {
      var fields = Array.prototype.filter.call(g.querySelectorAll('input, select, textarea'), function (f) {
        var tp = (f.getAttribute('type') || '').toLowerCase();
        return ['hidden', 'button', 'submit', 'reset'].indexOf(tp) < 0 && !f.closest('.select2-container') && (f.name || f.id);
      });
      if (!fields.length) return;
      var f = fields[0];
      var tp = (f.getAttribute('type') || '').toLowerCase();
      var name = (f.name || f.id).replace(/\[\]$/, '');
      var key, label, isDefault;
      if (tp === 'checkbox' || tp === 'radio') {
        key = 'cb:' + name;
        var lb = f.id ? form.querySelector('label[for="' + f.id + '"]') : null;
        label = clean(lb ? lb.textContent : g.textContent);
        isDefault = DEFAULT_NAME.test(name);
      } else {
        key = 'f:' + name;
        if (f.tagName === 'SELECT') {
          label = clean(f.options.length ? f.options[0].text : '');
        } else {
          label = clean(f.getAttribute('placeholder') || '');
          if (!label && f.classList.contains('daterange')) label = FP.t.dateRange;
        }
        if (!label) label = name;
        isDefault = DEFAULT_NAME.test(name) || DEFAULT_DATE.test(name) || f.classList.contains('daterange') || f.classList.contains('datepicker');
      }
      if (!items[key]) { items[key] = { key: key, labels: [], groups: [], fields: [], isDefault: isDefault }; order.push(key); }
      items[key].groups.push(g);
      items[key].fields = items[key].fields.concat(fields);
      if (label && items[key].labels.indexOf(label) < 0) items[key].labels.push(label);
    });
    if (!order.length) return;

    function isActive(it) {
      return it.fields.some(function (f) {
        var tp = (f.getAttribute('type') || '').toLowerCase();
        if (tp === 'checkbox' || tp === 'radio') return f.checked;
        if (f.tagName === 'SELECT') {
          if (f.multiple) return Array.prototype.some.call(f.options, function (o) { return o.selected && o.value !== ''; });
          return f.value !== '' && f.value !== null;
        }
        return (f.value || '').trim() !== '';
      });
    }
    function labelOf(it) { var s = it.labels.join(' / '); return s.length > 70 ? s.slice(0, 67) + '…' : s; }
    function wantVisible(it) { return FP.saved ? FP.saved.indexOf(it.key) >= 0 : it.isDefault; }

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-default btn-sm';
    btn.style.marginTop = '-5px';
    btn.style.marginLeft = '4px';

    function apply() {
      var shown = 0;
      order.forEach(function (k) {
        var it = items[k], vis = wantVisible(it) || isActive(it);
        if (vis) shown++;
        it.groups.forEach(function (g) { g.style.display = vis ? '' : 'none'; });
      });
      btn.innerHTML = '<i class="fa fa-sliders"></i> ' + FP.t.btn + ' (' + shown + '/' + order.length + ')';
    }

    var submit = form.querySelector('button[type="submit"], input[type="submit"]');
    if (submit) {
      var host = submit.closest('.form-group');
      if (host && host.parentNode === form) host.appendChild(btn); else submit.parentNode.insertBefore(btn, submit.nextSibling);
    } else {
      form.appendChild(btn);
    }

    var panel = document.createElement('div');
    panel.id = 'fpPanel';
    document.body.appendChild(panel);

    function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; }

    function openPanel() {
      panel.innerHTML = '';
      panel.appendChild(el('div', 'fp-title', FP.t.title));
      panel.appendChild(el('div', 'fp-note', FP.t.note));
      var allLb = el('label'); allLb.style.marginBottom = '8px';
      var allCb = document.createElement('input'); allCb.type = 'checkbox';
      allLb.appendChild(allCb); allLb.appendChild(document.createTextNode(' ' + FP.t.all));
      panel.appendChild(allLb);
      var list = el('div', 'fp-list');
      var boxes = [];
      order.forEach(function (k) {
        var it = items[k], active = isActive(it);
        var lb = el('label', active ? 'fp-active' : '');
        var cb = document.createElement('input'); cb.type = 'checkbox'; cb.value = k;
        cb.checked = wantVisible(it) || active;
        if (active) { cb.disabled = true; }
        boxes.push(cb);
        lb.appendChild(cb); lb.appendChild(document.createTextNode(' ' + labelOf(it)));
        list.appendChild(lb);
      });
      allCb.checked = boxes.every(function (b) { return b.checked; });
      allCb.onchange = function () { boxes.forEach(function (b) { if (!b.disabled) b.checked = allCb.checked; }); };
      panel.appendChild(list);

      var act = el('div', 'fp-actions');
      var bReset = el('button', 'btn btn-default btn-sm pull-left', FP.t.reset); bReset.type = 'button';
      var bClose = el('button', 'btn btn-default btn-sm', FP.t.close); bClose.type = 'button';
      var bSave = el('button', 'btn btn-primary btn-sm', FP.t.save); bSave.type = 'button'; bSave.style.marginLeft = '6px';
      act.appendChild(bReset); act.appendChild(bClose); act.appendChild(bSave);
      panel.appendChild(act);

      bClose.onclick = closePanel;
      bSave.onclick = function () {
        var keys = boxes.filter(function (b) { return b.checked; }).map(function (b) { return b.value; });
        send({ page: FP.page, keys: keys }, function () { FP.saved = keys; apply(); closePanel(); });
      };
      bReset.onclick = function () {
        send({ page: FP.page, reset: 1 }, function () { FP.saved = null; apply(); closePanel(); });
      };

      var r = btn.getBoundingClientRect();
      panel.style.display = 'block';
      var left = r.left + window.pageXOffset;
      var maxLeft = window.pageXOffset + document.documentElement.clientWidth - panel.offsetWidth - 8;
      panel.style.left = Math.max(8, Math.min(left, maxLeft)) + 'px';
      panel.style.top = (r.bottom + window.pageYOffset + 4) + 'px';
    }
    function closePanel() { panel.style.display = 'none'; }

    function send(data, done) {
      var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
      var body = new URLSearchParams();
      body.append('_token', token);
      body.append('page', data.page);
      if (data.reset) body.append('reset', '1');
      (data.keys || []).forEach(function (k) { body.append('keys[]', k); });
      if (data.keys && !data.keys.length) body.append('keys', '');
      fetch(FP.url, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: body })
        .then(function (res) { return res.json().then(function (j) { if (!res.ok || !j.ok) throw new Error(j.message || FP.t.err); }); })
        .then(done)
        .catch(function (e) { alert(e.message || FP.t.err); });
    }

    btn.onclick = function (e) { e.stopPropagation(); if (panel.style.display === 'block') closePanel(); else openPanel(); };
    panel.addEventListener('click', function (e) { e.stopPropagation(); });
    document.addEventListener('click', closePanel);

    apply();
  }

  if (window.jQuery) { jQuery(function () { setTimeout(init, 0); }); }
  else if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
  else { init(); }
})();
</script>
@endif
@endauth
