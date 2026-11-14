{{-- Phân quyền: ẩn các link / nút dẫn tới chức năng tài khoản không có quyền (server vẫn chặn 403) --}}
@if(\App\Services\Perm::active())
@php
  $permDenied = [];
  foreach (config('permissions.routes') as $rn => $pm) {
      if (substr($rn, -1) === '*' || \App\Services\Perm::can($pm)) continue;
      $r = \Illuminate\Support\Facades\Route::getRoutes()->getByName($rn);
      if (!$r) continue;
      $uri = '/' . ltrim($r->uri(), '/');
      $permDenied[] = '^' . preg_replace('#\\\\\{[^/]+?\\\\\}#', '[^/]+', preg_quote($uri, '#')) . '/?$';
  }
@endphp
<script>
(function () {
  var denied = @json(array_values(array_unique($permDenied))).map(function (p) { return new RegExp(p); });
  function hide(root) {
    (root || document).querySelectorAll('a[href]').forEach(function (a) {
      if (a.closest('.main-sidebar')) return;
      var u; try { u = new URL(a.getAttribute('href'), location.href); } catch (e) { return; }
      if (u.origin !== location.origin) return;
      for (var i = 0; i < denied.length; i++) if (denied[i].test(u.pathname)) { a.style.display = 'none'; break; }
    });
  }
  document.addEventListener('DOMContentLoaded', function () { hide(); });
})();
</script>
@endif
