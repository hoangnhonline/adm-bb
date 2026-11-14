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
      @if(Auth::user()->branch_id > 0)
      <li {{ in_array($routeName, ['booking.index', 'booking.create', 'booking.edit']) ? "class=active" : "" }}>
        <a href="{{ route('booking.index') }}">
          <i class="fa fa-ship" aria-hidden="true"></i><span>{{ __('ĐƠN HÀNG') }}</span>
        </a>
      </li> 
      @else
      <li class="treeview {{ in_array($routeName, ['booking.index', 'booking.create', 'booking.edit']) && (isset($type) && $type == 1) ? 'active' : '' }}"" >
        <a href="#">
          <i class="fa fa-ship"></i> 
          <span>{{ __('ĐƠN HÀNG') }}</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        
        <ul class="treeview-menu">
          <li {{ in_array($routeName, ['booking.index', 'booking.edit']) ? "class=active" : "" }}><a href="{{ route('booking.index') }}"><i class="fa fa-circle-o"></i> {{ __('Danh sách') }}</a></li>
          
                    
          @if(Auth::user()->role == 1)
          <li {{ in_array($routeName, ['booking.create']) ? "class=active" : "" }}><a href="{{ route('booking.create') }}"><i class="fa fa-circle-o"></i> {{ __('Thêm mới') }}</a></li>
          @endif
        </ul>        
      </li>
      @endif
      
      @if(Auth::user()->branch_id == 7 || Auth::user()->role == 1)
     <!--  <li {{ in_array($routeName, ['booking-bbc.index', 'booking-bbc.create', 'booking-bbc.edit']) ? "class=active" : "" }}>
        <a href="{{ route('booking-bbc.index') }}">
          <i class="fa fa-camera" aria-hidden="true"></i><span>{{ __('KHU CHỤP ẢNH') }}</span>
        </a>
      </li>  -->
      @endif
      @if(Auth::user()->branch_id != 7)
      @if(Auth::user()->role == 1)
      <li {{ in_array($routeName, ['cost.index', 'cost.create', 'cost.edit']) ? "class=active" : "" }}>
        <a href="{{ route('cost.index') }}">
          <i class="glyphicon glyphicon-usd"></i> <span>{{ __('CHI PHÍ') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['cost.nhap-hang']) ? "class=active" : "" }}>
        <a href="{{ route('cost.nhap-hang') }}">
          <i class="fa fa-shopping-basket"></i> <span>{{ __('NHẬP HÀNG') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['cost.canh-bao-nhap']) ? "class=active" : "" }}>
        <a href="{{ route('cost.canh-bao-nhap') }}">
          <i class="fa fa-exclamation-triangle"></i> <span>{{ __('CẢNH BÁO NHẬP HÀNG') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['cost.bieu-do-gia']) ? "class=active" : "" }}>
        <a href="{{ route('cost.bieu-do-gia') }}">
          <i class="fa fa-line-chart"></i> <span>{{ __('BIỂU ĐỒ GIÁ') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['report.dish']) ? "class=active" : "" }}>
        <a href="{{ route('report.dish') }}">
          <i class="fa fa-trophy"></i> <span>{{ __('MÓN BÁN CHẠY') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['report.chi-phi-doanh-thu']) ? "class=active" : "" }}>
        <a href="{{ route('report.chi-phi-doanh-thu') }}">
          <i class="fa fa-pie-chart"></i> <span>{{ __('CHI PHÍ / DOANH THU') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['doi-tac.index', 'doi-tac.show', 'doi-tac.create', 'doi-tac.edit']) ? "class=active" : "" }}>
        <a href="{{ route('doi-tac.index') }}">
          <i class="fa fa-handshake-o"></i> <span>{{ __('ĐỐI TÁC & CÔNG NỢ') }}</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['doi-tac.report']) ? "class=active" : "" }}>
        <a href="{{ route('doi-tac.report') }}">
          <i class="fa fa-line-chart"></i> <span>{{ __('BÁO CÁO CÔNG NỢ ĐỐI TÁC') }}</span>
        </a>
      </li>
      @if(Auth::user()->id == 1)
      <li {{ in_array($routeName, ['activity-log.index']) ? "class=active" : "" }}>
        <a href="{{ route('activity-log.index') }}">
          <i class="fa fa-history"></i> <span>{{ __('LỊCH SỬ THAO TÁC') }}</span>
        </a>
      </li>
      @endif
      @endif
      @if(Auth::user()->id != 3 && Auth::user()->role == 1)

      <li {{ in_array($routeName, ['ung-luong.index', 'ung-luong.create', 'ung-luong.edit']) ? "class=active" : "" }}>
        <a href="{{ route('ung-luong.index') }}">
         <i class="fa fa-money" aria-hidden="true"></i></i><span>{{ __('Ứng lương') }}</span>
        </a>
      </li> 
     
     
    <!--   <li {{ in_array($routeName, ['staff.index', 'staff.edit', 'staff.create']) ? "class=active" : "" }}>
        <a href="{{ route('staff.index') }}">
          <i class="fa fa-user-circle" aria-hidden="true"></i>
          <span>{{ __('NHÂN VIÊN') }}</span>          
        </a>       
      </li>  
       -->
      
     
      
      @endif
      @if(Auth::user()->role < 3)
     
      <li {{ in_array($routeName, ['report.doanh-thu-thang']) ? "class=active" : "" }}>
        <a href="{{ route('report.doanh-thu-thang') }}">
          <i class="fa fa-bar-chart" aria-hidden="true"></i><span>{{ __('THỐNG KÊ') }}</span>
        </a>
      </li>     
      <li {{ in_array($routeName, ['dish.index', 'dish.create', 'dish.edit']) ? "class=active" : "" }}>
        <a href="{{ route('dish.index') }}">
          <i class="fa fa-list-alt" aria-hidden="true"></i><span>{{ __('Món') }}</span>
        </a>
      </li> 

      @elseif(Auth::user()->role == 4)
      <li {{ in_array($routeName, ['report.thong-ke-bai-bien']) ? "class=active" : "" }}>
        <a href="{{ route('report.thong-ke-bai-bien') }}">
          <i class="fa fa-bar-chart" aria-hidden="true"></i><span>{{ __('THỐNG KÊ') }}</span>
        </a>
      </li> 
      @else
      
      @endif

      @endif
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