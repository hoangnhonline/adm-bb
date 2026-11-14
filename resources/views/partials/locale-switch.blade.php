{{-- Đa ngôn ngữ (port từ Plan To Travel): nút chuyển giao diện VI / EN, lưu theo tài khoản --}}
@auth
@php($curLocale = app()->getLocale())
<li class="locale-switch" style="padding: 13px 8px 0 8px; white-space: nowrap;">
  <span style="display:inline-block;background:#f4f4f4;border:1px solid #d2d6de;border-radius:4px;padding:2px;line-height:1;">
    <a href="{{ route('locale.change', 'vi') }}" title="Tiếng Việt" style="display:inline-block;padding:4px 8px;border-radius:3px;font-size:12px;font-weight:bold;text-decoration:none;{{ $curLocale == 'vi' ? 'background:#3c8dbc;color:#fff;' : 'background:transparent;color:#444;' }}">VI</a>
    <a href="{{ route('locale.change', 'en') }}" title="English" style="display:inline-block;padding:4px 8px;border-radius:3px;font-size:12px;font-weight:bold;text-decoration:none;{{ $curLocale == 'en' ? 'background:#3c8dbc;color:#fff;' : 'background:transparent;color:#444;' }}">EN</a>
  </span>
</li>
@endauth
