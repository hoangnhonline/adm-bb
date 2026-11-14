{{-- Menu cho tài khoản đã gán nhóm quyền: chỉ hiện mục được phép xem (config/permissions.php -> menu) --}}
<aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="pull-left image">
        <img src="{{ asset('admin/dist/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p>{{ Auth::user()->display_name ?: Auth::user()->name }}</p>
        <a href="#"><i class="fa fa-circle text-success"></i> {{ __('Online') }}</a>
      </div>
    </div>
    <ul class="sidebar-menu">
      @foreach(config('permissions.menu') as $mi)
        @if(\App\Services\Perm::can($mi[1]) && Route::has($mi[0]))
        <li {{ ($routeName ?? '') == $mi[0] ? 'class=active' : '' }}>
          <a href="{{ route($mi[0]) }}"><i class="fa {{ $mi[2] }}"></i> <span>{{ __($mi[3]) }}</span></a>
        </li>
        @endif
      @endforeach
      <li><a href="{{ route('account.change-pass') }}"><i class="fa fa-key"></i> <span>{{ __('Đổi mật khẩu') }}</span></a></li>
    </ul>
  </section>
</aside>
