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
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>   
    <ul class="sidebar-menu">     
      <li {{ in_array($routeName, ['booking.index', 'booking.create', 'booking.edit']) ? "class=active" : "" }}>
        <a href="{{ route('booking.index') }}">
          <i class="fa fa-ship"></i> <span>THỂ THAO BIỂN</span>
        </a>
      </li>
      <li {{ in_array($routeName, ['cost.index', 'cost.create', 'cost.edit']) ? "class=active" : "" }}>
        <a href="{{ route('cost.index') }}">
          <i class="glyphicon glyphicon-usd"></i> <span>CHI PHÍ</span>
        </a>
      </li>      
    </ul>
 
  </section>
  <!-- /.sidebar -->
</aside>
<style type="text/css">
  .skin-blue .sidebar-menu>li>.treeview-menu{
    padding-left: 15px !important;
  }
</style>