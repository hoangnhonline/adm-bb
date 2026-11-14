<header class="main-header">
  <!-- Logo -->
  <a href="{{ route('booking.index')}}" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini">
      <img src="{{ asset('images/logo-small.jpg')}}" width="45" style="margin-top: 5px;">
    </span>
    @if(Auth::user()->is_view == 1)
    <span class="logo-lg"><b>BOMBAY EXPRESS</b></span>
    @else
    <span class="logo-lg"><b>BOMBAY</b></span>
    @endif
  
  </a>
  <!-- Header Navbar: style can be found in header.less -->
  @if(!isset($codeUser))
  <nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      <span class="sr-only">{{ __('Mở/đóng menu') }}</span>
    </a>
      @if(Auth::check() && Auth::user()->chup_anh == 0 && $notNH)
     <div class="input-group input-group-sm " style="width: 150px;position: absolute;left: 45px; top: 10px;" id="div_search_fast">
  
          <input type="text" class="form-control" id="keyword" name="keyword" placeholder="{{ __('ID/Số điện thoại') }}" value="{{ isset($keyword) ? $keyword : "" }}">
          <span class="input-group-btn">
              <button type="button" id="btnQuickSearch" class="btn btn-danger btn-flat btn-preview">{{ __('Tìm') }}</button>
          </span>
      </div>
      <h3 style="color: blue; position: absolute;top: -10px; left: 200px;">{{ $branchDetail->name }}</h3>
      @endif
   
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        @include('partials.locale-switch')
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="setting_top_1">            
            <i class="fa fa-gears" id="setting_top_2"></i><span class="hidden-xs">{{ __('Chào') }} {{ Auth::check() ? Auth::user()->name : __('Khách') }}</span>
          </a>
          <ul class="dropdown-menu">            
            <li class="user-footer">
            <div class="pull-left">
                <a href="{{ route('account.change-pass') }}" class="btn btn-success btn-flat">{{ __('Đổi mật khẩu') }}</a>
              </div>             
              <div class="pull-right">

                <a href="{{ route('logout') }}" class="btn btn-danger btn-flat" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">{{ __('Thoát') }}</a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
              </div>
            </li>
          </ul>
        </li>         

      </ul>
    </div>
  </nav>
  @else
  <div style="clear:both;"></div>
   @endif

</header>