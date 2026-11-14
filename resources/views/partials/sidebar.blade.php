<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
      <div class="pull-left image">
        <img src="{{ asset('admin/dist/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p>{{ Auth::user()->display_name }}</p>
        <a href="#"><i class="fa fa-circle text-success"></i> {{ __('Online') }}</a>
      </div>
    </div>
    <!-- /.search form -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    @if(Auth::user()->id==519)
    <ul class="sidebar-menu">
      <li class="treeview {{ in_array($routeName, ['booking-bbc.index', 'booking-bbc.create', 'booking-bbc.edit']) && (isset($type) && $type == 1) ? 'active' : '' }}"" >
        <a href="#">
          <i class="fa fa-superpowers"></i> 
          <span>{{ __('ĐẶT DỊCH VỤ') }}</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        
        <ul class="treeview-menu">
          <li {{ in_array($routeName, ['booking-bbc.index', 'booking-bbc.edit']) ? "class=active" : "" }}><a href="{{ route('booking-bbc.index') }}"><i class="fa fa-circle-o"></i> {{ __('Danh sách') }}</a></li>
       
          <li {{ in_array($routeName, ['booking-bbc.create']) ? "class=active" : "" }}><a href="{{ route('booking-bbc.create') }}"><i class="fa fa-circle-o"></i> {{ __('Thêm mới') }}</a></li>
         
        </ul>        
      </li>
    </ul>
    @else
    <ul class="sidebar-menu">
      {{-- Menu gom nhóm (14/11/2026): giữ nguyên điều kiện hiển thị cũ của từng mục, nhóm chỉ có 1 mục thì hiện thẳng --}}
      @php
        $u = Auth::user();
        $isAdm = $u->role == 1 && $u->branch_id != 7;   // trước đây: branch_id != 7 && role == 1
        $mi = function ($route, $label, $icon, $show = true, $routes = []) {
          return ['route' => $route, 'label' => $label, 'icon' => $icon, 'show' => $show && Route::has($route), 'routes' => array_merge([$route], $routes)];
        };
        $menuGroups = [
          ['label' => 'TỔNG QUAN', 'icon' => 'fa-dashboard', 'items' => [
            $mi('tong-quan.index', 'Tổng quan', 'fa-dashboard', $isAdm),
          ]],
          ['label' => 'ĐƠN HÀNG', 'icon' => 'fa-ship', 'items' => [
            $mi('booking.index', 'Danh sách bill', 'fa-list', true, ['booking.edit']),
            $mi('booking.create', 'Thêm bill mới', 'fa-plus', $u->role == 1 && !($u->branch_id > 0)),
            $mi('bill-anomaly.index', 'Bill bất thường', 'fa-exclamation-circle', $isAdm),
          ]],
          ['label' => 'CHI PHÍ & NHẬP HÀNG', 'icon' => 'fa-usd', 'items' => [
            $mi('cost.index', 'Chi phí', 'fa-usd', $isAdm, ['cost.create', 'cost.edit']),
            $mi('cost.nhap-hang', 'Nhập hàng', 'fa-shopping-basket', $isAdm),
            $mi('cost.canh-bao-nhap', 'Cảnh báo nhập hàng', 'fa-exclamation-triangle', $isAdm),
            $mi('cost.bieu-do-gia', 'Biểu đồ giá', 'fa-line-chart', $isAdm),
          ]],
          ['label' => 'ĐỐI TÁC & CÔNG NỢ', 'icon' => 'fa-handshake-o', 'items' => [
            $mi('doi-tac.index', 'Danh sách đối tác', 'fa-handshake-o', $isAdm, ['doi-tac.create', 'doi-tac.edit', 'doi-tac.show']),
            $mi('doi-tac.report', 'Báo cáo công nợ đối tác', 'fa-line-chart', $isAdm),
          ]],
          ['label' => 'BÁO CÁO', 'icon' => 'fa-bar-chart', 'items' => [
            $mi('report.doanh-thu-thang', 'Thống kê doanh thu', 'fa-bar-chart', $u->branch_id != 7 && $u->role < 3),
            $mi('report.thong-ke-bai-bien', 'Thống kê', 'fa-bar-chart', $u->branch_id != 7 && $u->role == 4),
            $mi('report.thu-tien-nguoi', 'Thu tiền theo người', 'fa-user-circle', $isAdm),
            $mi('report.vat', 'VAT & hoa hồng', 'fa-percent', $isAdm),
            $mi('report.chi-phi-doanh-thu', 'Chi phí / doanh thu', 'fa-pie-chart', $isAdm),
            $mi('report.dish', 'Món bán chạy', 'fa-trophy', $isAdm),
          ]],
          ['label' => 'QUẢN TRỊ', 'icon' => 'fa-cogs', 'items' => [
            $mi('dish.index', 'Món ăn', 'fa-list-alt', $u->branch_id != 7 && $u->role < 3, ['dish.create', 'dish.edit']),
            $mi('ung-luong.index', 'Ứng lương', 'fa-money', $u->branch_id != 7 && $u->id != 3 && $u->role == 1, ['ung-luong.create', 'ung-luong.edit']),
            $mi('phan-quyen.index', 'Phân quyền', 'fa-lock', $isAdm && $u->id == 1),
            $mi('activity-log.index', 'Lịch sử thao tác', 'fa-history', $isAdm && $u->id == 1),
          ]],
        ];
        $curRoute = $routeName ?? (Route::currentRouteName() ?: '');
      @endphp
      @foreach($menuGroups as $grp)
        @php
          $vis = array_values(array_filter($grp['items'], function ($i) { return $i['show']; }));
          $grpActive = false; foreach ($vis as $i) if (in_array($curRoute, $i['routes'])) $grpActive = true;
        @endphp
        @if(count($vis) == 1)
          @php $i = $vis[0]; @endphp
          <li class="{{ in_array($curRoute, $i['routes']) ? 'active' : '' }}">
            <a href="{{ route($i['route']) }}"><i class="fa {{ $grp['icon'] }}"></i> <span>{{ count($grp['items']) == 1 ? __($grp['label']) : mb_strtoupper(__($i['label'])) }}</span></a>
          </li>
        @elseif(count($vis) > 1)
          <li class="treeview {{ $grpActive ? 'active menu-open' : '' }}">
            <a href="#"><i class="fa {{ $grp['icon'] }}"></i> <span>{{ __($grp['label']) }}</span>
              <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
            <ul class="treeview-menu">
              @foreach($vis as $i)
              <li class="{{ in_array($curRoute, $i['routes']) ? 'active' : '' }}"><a href="{{ route($i['route']) }}"><i class="fa {{ $i['icon'] }}"></i> {{ __($i['label']) }}</a></li>
              @endforeach
            </ul>
          </li>
        @endif
      @endforeach
      @if(Auth::user()->id == 494)
      <li {{ in_array($routeName, ['cost.index', 'cost.create', 'cost.edit']) ? "class=active" : "" }}>
        <a href="{{ route('cost.index') }}">
          <i class="glyphicon glyphicon-usd"></i> <span>{{ __('CHI PHÍ') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['dish.index', 'dish.create', 'dish.edit']) ? "class=active" : "" }}>
        <a href="{{ route('dish.index') }}">
          <i class="fa fa-list-alt" aria-hidden="true"></i><span>{{ __('Món') }}</span>
        </a>
      </li> 
       <li {{ in_array($routeName, ['ntv.index', 'ntv.create', 'ntv.edit']) ? "class=active" : "" }}>
        <a href="{{ route('ntv.index') }}">
          <i class="fa fa-user-circle" aria-hidden="true"></i> <span>{{ __('Người tư vấn') }}</span>
        </a>
      </li> 
      @endif
      
    </ul>
    @endif
  </section>
  <!-- /.sidebar -->
</aside>
<style type="text/css">
  .skin-blue .sidebar-menu>li>.treeview-menu{
    padding-left: 15px !important;
  }
</style>