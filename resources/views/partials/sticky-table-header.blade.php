{{-- Giữ cố định dòng tiêu đề của bảng khi cuộn xuống (áp dụng chung cho mọi trang danh sách).
     - Tự nhận dòng tiêu đề: <thead>, hoặc các dòng đầu bảng chỉ gồm <th>.
     - Chỉ áp dụng cho bảng >= 8 dòng, không áp dụng bảng lồng trong bảng.
     - Tắt cho 1 bảng: thêm thuộc tính data-no-sticky vào <table>. --}}
<style>
  .sth-float{position:fixed;z-index:1020;overflow:hidden;display:none;pointer-events:auto;box-shadow:0 2px 4px rgba(0,0,0,.15)}
  .sth-float table{margin:0 !important;table-layout:fixed;background:#fff}
  .sth-float th,.sth-float td{background:#f4f4f4;box-sizing:border-box}
</style>
<script>
(function () {
  var MIN_ROWS = 8, items = [];

  function headerRows(table) {
    if (table.tHead && table.tHead.rows.length) return Array.prototype.slice.call(table.tHead.rows);
    var rows = [], all = table.rows;
    for (var i = 0; i < all.length && i < 3; i++) {
      var cells = all[i].cells;
      if (!cells.length) break;
      var allTh = true;
      for (var j = 0; j < cells.length; j++) if (cells[j].tagName !== 'TH') { allTh = false; break; }
      if (!allTh) break;
      rows.push(all[i]);
    }
    return rows;
  }

  function topOffset() {
    var h = document.querySelector('.main-header');
    if (h && getComputedStyle(h).position === 'fixed') return h.getBoundingClientRect().bottom;
    return 0;
  }

  function scrollParentX(el) {
    var p = el.parentElement;
    while (p && p !== document.body) {
      var ox = getComputedStyle(p).overflowX;
      if ((ox === 'auto' || ox === 'scroll') && p.scrollWidth > p.clientWidth) return p;
      p = p.parentElement;
    }
    return null;
  }

  function build(table, rows) {
    var wrap = document.createElement('div');
    wrap.className = 'sth-float';
    var clone = table.cloneNode(false);
    clone.removeAttribute('id');
    var body = document.createElement('thead');
    var origCells = [];
    rows.forEach(function (r) {
      var c = r.cloneNode(true);
      // không để trùng id / name với bảng gốc
      Array.prototype.forEach.call(c.querySelectorAll('[id]'), function (e) { e.removeAttribute('id'); });
      Array.prototype.forEach.call(c.querySelectorAll('[name]'), function (e) { e.removeAttribute('name'); });
      body.appendChild(c);
      origCells = origCells.concat(Array.prototype.slice.call(r.cells));
    });
    clone.appendChild(body);
    wrap.appendChild(clone);
    document.body.appendChild(wrap);
    // bấm checkbox / nút trên tiêu đề nổi -> chuyển sang phần tử thật của bảng gốc
    wrap.addEventListener('click', function (e) {
      var t = e.target.closest('input,button,select');
      if (!t) return;
      var fake = Array.prototype.slice.call(wrap.querySelectorAll('input,button,select'));
      var real = [];
      rows.forEach(function (r) { real = real.concat(Array.prototype.slice.call(r.querySelectorAll('input,button,select'))); });
      var idx = fake.indexOf(t);
      if (idx >= 0 && real[idx]) { e.preventDefault(); real[idx].click(); setTimeout(function () { sync(item, true); }, 0); }
    });
    var item = { table: table, rows: rows, wrap: wrap, clone: clone, origCells: origCells };
    return item;
  }

  function sync(it, force) {
    var t = it.table, r = t.getBoundingClientRect(), top = topOffset();
    var headH = it.rows.reduce(function (s, row) { return s + row.getBoundingClientRect().height; }, 0);
    var firstTop = it.rows[0].getBoundingClientRect().top;
    var visible = t.offsetParent !== null && firstTop < top && r.bottom > top + headH + 20;
    if (!visible) { it.wrap.style.display = 'none'; return; }
    var sp = scrollParentX(t);
    var box = sp ? sp.getBoundingClientRect() : r;
    var left = Math.max(r.left, box.left), right = Math.min(r.right, box.right);
    it.wrap.style.display = 'block';
    it.wrap.style.top = top + 'px';
    it.wrap.style.left = left + 'px';
    it.wrap.style.width = Math.max(0, right - left) + 'px';
    it.clone.style.width = r.width + 'px';
    it.clone.style.marginLeft = (r.left - left) + 'px';
    if (force || it.wrap.getAttribute('data-w') !== String(r.width)) {
      it.wrap.setAttribute('data-w', String(r.width));
      var cc = it.clone.querySelectorAll('th,td');
      for (var i = 0; i < cc.length && i < it.origCells.length; i++) {
        var w = it.origCells[i].getBoundingClientRect().width;
        cc[i].style.width = w + 'px'; cc[i].style.minWidth = w + 'px'; cc[i].style.maxWidth = w + 'px';
      }
      // đồng bộ trạng thái checkbox
      var fi = it.clone.querySelectorAll('input[type=checkbox]'), ri = [];
      it.rows.forEach(function (row) { ri = ri.concat(Array.prototype.slice.call(row.querySelectorAll('input[type=checkbox]'))); });
      for (var k = 0; k < fi.length && k < ri.length; k++) fi[k].checked = ri[k].checked;
    }
  }

  function scan() {
    items.forEach(function (it) { if (it.wrap.parentNode) it.wrap.parentNode.removeChild(it.wrap); });
    items = [];
    var tables = document.querySelectorAll('.content-wrapper table');
    Array.prototype.forEach.call(tables, function (t) {
      if (t.hasAttribute('data-no-sticky') || t.parentElement.closest('table') || t.closest('.modal')) return;
      if (t.rows.length < MIN_ROWS) return;
      var rows = headerRows(t);
      if (!rows.length || rows.length >= t.rows.length - 1) return;
      items.push(build(t, rows));
    });
    update(true);
  }

  var ticking = false;
  function update(force) {
    items.forEach(function (it) { sync(it, force === true); });
  }
  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () { ticking = false; update(false); });
  }

  function init() {
    scan();
    document.addEventListener('scroll', onScroll, true); // bắt cả cuộn của .wrapper / .table-responsive
    window.addEventListener('resize', function () { update(true); });
    // giao diện có thể thay đổi sau khi tải (select2, ajax...) -> quét lại nhẹ nhàng
    var t = null;
    if (window.MutationObserver) {
      new MutationObserver(function (muts) {
        for (var i = 0; i < muts.length; i++) {
          var n = muts[i].target;
          if (n.closest && n.closest('.sth-float')) continue;
          if (n.closest && n.closest('.content-wrapper')) { clearTimeout(t); t = setTimeout(scan, 400); return; }
        }
      }).observe(document.body, { childList: true, subtree: true });
    }
    setTimeout(function () { update(true); }, 800);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
