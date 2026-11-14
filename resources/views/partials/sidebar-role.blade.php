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
      @php
        // gom mục menu theo nhóm (phần tử thứ 5), giữ thứ tự xuất hiện đầu tiên của nhóm
        $grpIcons = ['TỔNG QUAN' => 'fa-dashboard', 'ĐƠN HÀNG' => 'fa-ship', 'BẾP' => 'fa-cutlery', 'CHI PHÍ & NHẬP HÀNG' => 'fa-usd',
          'BÁO CÁO' => 'fa-bar-chart', 'ĐỐI TÁC & CÔNG NỢ' => 'fa-handshake-o', 'QUẢN TRỊ' => 'fa-cogs'];
        $roleGroups = [];
        foreach (config('permissions.menu') as $mi) {
          if (!\App\Services\Perm::can($mi[1]) || !Route::has($mi[0])) continue;
          $roleGroups[$mi[4] ?? $mi[3]][] = $mi;
        }
        $curRoute = $routeName ?? '';
      @endphp
      @foreach($roleGroups as $gName => $items)
        @if(count($items) == 1)
        <li {{ $curRoute == $items[0][0] ? 'class=active' : '' }}>
          <a href="{{ route($items[0][0]) }}"><i class="fa {{ $items[0][2] }}"></i> <span>{{ __($items[0][3]) }}</span></a>
        </li>
        @else
        @php $gActive = in_array($curRoute, array_column($items, 0)); @endphp
        <li class="treeview {{ $gActive ? 'active menu-open' : '' }}">
          <a href="#"><i class="fa {{ $grpIcons[$gName] ?? $items[0][2] }}"></i> <span>{{ __($gName) }}</span>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
          <ul class="treeview-menu">
            @foreach($items as $mi)
            <li {{ $curRoute == $mi[0] ? 'class=active' : '' }}><a href="{{ route($mi[0]) }}"><i class="fa {{ $mi[2] }}"></i> {{ __($mi[3]) }}</a></li>
            @endforeach
          </ul>
        </li>
        @endif
      @endforeach
      <li><a href="{{ route('account.change-pass') }}"><i class="fa fa-key"></i> <span>{{ __('Đổi mật khẩu') }}</span></a></li>
    </ul>
  </section>
</aside>
