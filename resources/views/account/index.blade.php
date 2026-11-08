@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Thành viên
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'account.index' ) }}">Thành viên</a></li>
    <li class="active">List</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      @if(Auth::user()->id != 333)
    	<a href="{{ route('account.create') }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new</a>
      @endif
      <a href="{{ route('account.create-tx') }}" class="btn btn-warning btn-sm" style="margin-bottom:5px">Tạo nhanh đối tác</a>
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif      

      @if(Auth::user()->role == 1)
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Bộ lọc</h3>
        </div>            
      <div class="panel-body">
        @php
       // $role = 4;
        @endphp
        <form class="form-inline" role="form" method="GET" action="{{ route('account.index') }}">
           @if(Auth::user()->id != 333)
        <div class="form-group">
            <label>Phân loại&nbsp;&nbsp;</label>
            <select class="form-control" name="role" id="role">      
              <option value="" >--Tất cả--</option>                       
              <option value="1" {{ $role == 1 ? "selected" : "" }}>Super Admin</option>  
              <option value="2" {{ $role == 2 ? "selected" : "" }}>Kế toán</option>  
              <option value="3" {{ $role == 3 ? "selected" : "" }}>Điều hành</option>  
              <option value="4" {{ $role == 4 ? "selected" : "" }}>Sales</option>  
              <option value="5" {{ $role == 5 ? "selected" : "" }}>HDV</option>  
              <option value="6" {{ $role == 6 ? "selected" : "" }}>Đối tác</option>                
            </select>
          </div>   
         
          <div class="form-group">
            <label>&nbsp;&nbsp;Level&nbsp;&nbsp;</label>
            <select class="form-control" name="level" id="level">      
              <option value="" >--Tất cả--</option>                       
              <option value="1" {{ $level == 1 ? "selected" : "" }}>Level 1 - 9</option>  
              <option value="2" {{ $level == 2 ? "selected" : "" }}>Level 2 - TX</option>  
              <option value="3" {{ $level == 3 ? "selected" : "" }}>Level 3 - 3949</option>
              <option value="4" {{ $level == 4 ? "selected" : "" }}>Level 4 - 3848</option>
              <option value="5" {{ $level == 5 ? "selected" : "" }}>Level 5 - 10</option>
              <option value="6" {{ $level == 6 ? "selected" : "" }}>Level 6 - 0</option>
              <option value="7" {{ $level == 7 ? "selected" : "" }}>Level 7 - BẾN</option>
            </select>
          </div> 
          <div class="form-group">
            <label>&nbsp;&nbsp;Người phụ trách</label>
            <select class="form-control" name="user_id_manage" id="user_id_manage">      
              <option value="" >--Tất cả--</option>                       
              <option value="84" {{ $user_id_manage == 84 ? "selected" : "" }}>Lâm Như</option>  
              <option value="219" {{ $user_id_manage == 219 ? "selected" : "" }}>Trang Tạ</option>  
              <option value="333" {{ $user_id_manage == 333 ? "selected" : "" }}>Group Tour</option>
              <option value="451" {{ $user_id_manage == 451 ? "selected" : "" }}>Thảo Lê</option>
            </select>
          </div>  
          <div class="form-group">
            <label>&nbsp;&nbsp;Loại công nợ</label>
            <select class="form-control" name="debt_type" id="debt_type">      
              <option value="" >--Tất cả--</option>                       
              <option value="1" {{ $debt_type == 1 ? "selected" : "" }}>Ngày</option>  
              <option value="2" {{ $debt_type == 2 ? "selected" : "" }}>Tuần</option>  
              <option value="3" {{ $debt_type == 3 ? "selected" : "" }}>Tháng</option>
            </select>
          </div> 
          @endif           
          <div class="form-group">
            <label>&nbsp;&nbsp;Phone number</label>
            <input type="text" name="phone" value="{{ $phone }}" class="form-control">
          </div>
          @if(Auth::user()->id != 333)
          <div class="form-group">
            &nbsp;&nbsp;&nbsp;<input type="checkbox" name="hdv" id="hdv" {{ $hdv == 1 ? "checked" : "" }} value="1">
            <label for="hdv">HDV&nbsp;&nbsp;&nbsp;&nbsp;</label>
          </div>   
          @endif
          <button class="btn btn-info" type="submit">Search</button>     
          </form>
      </div>
      </div>
      @endif

      <div class="box">

        <div class="box-header with-border">
          <h3 class="box-title">List ( {{ $items->total() }} )</h3>
        </div>
        @if(Auth::user()->role == 1 && Auth::user()->id != 333)
        <div class="form-inline" style="padding: 5px">
          <div class="form-group">            
            <select class="form-control select2 multi-change-column-value" data-column="user_id_manage">
                <option value="">--Người phụ trách--</option>
                <option value="84">Lâm Như</option>
                <option value="219">Trang Tạ</option>
                <option value="333">Group Tour</option>
                <option value="451">Thảo Lê</option>
              </select>                         
          </div>        
        
          <div class="form-group">            
            <select class="form-control select2 multi-change-column-value" data-column="debt_type">
                <option value="">--Loại công nợ--</option>
                <option value="1">Ngày</option>
                <option value="2">Tuần</option>
                <option value="3">Tháng</option>
              </select>                         
          </div>        
        </div>
        @endif
        <!-- /.box-header -->
        <div class="box-body">
          <div class="table-responsive">
            <table class="table table-bordered  table-hover" id="table-list-data">
              <tr>
                <th style="width: 1%"><input type="checkbox" id="check_all" value="1"></th>
                <th style="width: 1%">#</th>              
                <th>Tên hiển thị</th>
                <th>NV Phụ trách</th>
                <!-- <th>Ảnh avatar</th> -->
                <th>CODE</th>
                <th>Email truy cập</th>
                <th>Phone number</th>
                <th style="display: none;">Phân loại</th>
                <th>Level</th>
                <!-- <th>Status</th>
                <th>Ngày tạo</th> -->
                <th style="white-space:nowrap; width: 1%">Thao tác</th>
              </tr>
              <tbody>
              @if( $items->count() > 0 )
                <?php $i = 0; ?>
                @foreach( $items as $item )
                  <?php $i ++; ?>
                  <tr class="cost"  id="row-{{ $item->id }}">
                    <td>
                    <input type="checkbox" id="checked{{ $item->id }}" class="check_one" value="{{ $item->id }}">
                  </td> 
                    <td><span class="order">{{ $i }}</span></td>                   
                    <td>                  
                      <a href="{{ route( 'account.edit', [ 'id' => $item->id ]) }}">{{ $item->name }}</a>
                      <br>
                      Công nợ <b>
                        @if($item->debt_type == 1)
                        Ngày
                        @elseif($item->debt_type == 2)
                        Tuần
                        @else
                        Tháng
                        @endif

                      </b>
                    </td>
                    <td>
                      @if($item->userManage)
                      {{ $item->userManage->name }}
                      @endif
                    </td>
                    <!-- <td>
                      @if($item->image_url)
                      <img class="lazy" data-original="{{ Helper::showImage($item->image_url) }}" width="130px">
                      @endif
                    </td> -->
                    <td>
                      <a href="https://plantotravel.vn/book-tour/{{ Helper::mahoa('mahoa', $item->code) }}" target="_blank"> 
                      {{ $item->code }} 
                    </a> 
                    </td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->phone }}</td>
                    <td style="display: none;">
                      @if($item->role == 1)      
                      Super Admin         
                      @elseif($item->role == 2)
                      Kế toán
                      @elseif($item->role == 3)
                      Điều hành
                      @elseif($item->role == 4)
                      Sales
                      @elseif($item->role == 5)
                      HDV
                      @elseif($item->role == 6)
                      Đối tác
                      @endif
                    </td>
                    <td>
                      <select class="form-control change-column-value" data-table="users" data-column="level" data-id="{{ $item->id }}">
                          <option value="">--Level--</option>                      
                          <option value="1" {{ $item->level == 1 ? "selected" : "" }}>Level 1 - 9</option>  
                          <option value="2" {{ $item->level == 2 ? "selected" : "" }}>Level 2 - TX</option>  
                          <option value="3" {{ $item->level == 3 ? "selected" : "" }}>Level 3 - 3949</option>
                          <option value="4" {{ $item->level == 4 ? "selected" : "" }}>Level 4 - 3848</option>
                          <option value="5" {{ $item->level == 5 ? "selected" : "" }}>Level 5 - 10</option>
                          <option value="6" {{ $item->level == 6 ? "selected" : "" }}>Level 6 - 0</option>
                          <option value="7" {{ $item->level == 7 ? "selected" : "" }}>Level 7 - BẾN</option>
                        </select>
                    </td>
                    <!-- <td>{{ $item->status == 1 ? "Mở"  : "Khóa" }}</td>
                    <td>
                      {{ date('d/m/Y', strtotime($item->created_at)) }}
                    </td> -->
                    <td style="white-space:nowrap; text-align: right;">
                    @if($item->hdv == 1) 
                      <a class="btn btn-info btn-sm" target="_blank" href="https://plantotravel.vn/hdv-tour/{{ Helper::mahoa('mahoa', $item->code)}}">LINK TOUR</a>
                      @endif
                      @if($item->user_id_manage != 333)
                      <a class="btn btn-info btn-sm" target="_blank" href="https://plantotravel.vn/partner/{{ Helper::mahoa('mahoa', $item->id)}}">QR</a> 
                      @endif
                      <a href="{{ route( 'account.update-status', ['status' => $item->status == 1 ? 2 : 1 , 'id' => $item->id ])}}" class="btn btn-sm {{ $item->status == 1 ? "btn-warning" : "btn-info" }}" 
                      @if( $item->status == 2)
                      onclick="return confirm('Bạn chắc chắn muốn MỞ khóa tài khoản này? '); "
                      @else
                      onclick="return confirm('Bạn chắc chắn muốn KHÓA tài khoản này? '); "
                      @endif
                      >{{ $item->status == 1 ? "Khóa TK" : "Mở khóa TK" }}</a>                
                      <a href="{{ route( 'account.edit', [ 'id' => $item->id ]) }}" class="btn-sm btn btn-primary"><span class="glyphicon glyphicon-pencil"></span></a>                 
                   
                      <a onclick="return callDelete('{{ $item->fullname }}','{{ route( 'account.destroy', [ 'id' => $item->id ]) }}');" class="btn-sm btn btn-danger"><span class="glyphicon glyphicon-trash"></span></a>
                   
                      
                    </td>
                  </tr> 
                @endforeach
              @else
              <tr>
                <td colspan="9">Không có dữ liệu.</td>
              </tr>
              @endif

            </tbody>
            </table>
          </div>
          
        </div>        
      </div>
      <!-- /.box -->     
    </div>
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>
@stop
@section('js')
<script type="text/javascript">
function callDelete(name, url){  
  swal({
    title: 'Bạn muốn xóa "' + name +'"?',
    text: "Dữ liệu sẽ không thể phục hồi.",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes'
  }).then(function() {
    location.href= url;
  })
  return flag;
}
$(document).ready(function(){
   $('tr.cost').click(function(){
      $(this).find('.check_one').attr('checked', 'checked');
    });
    $("#check_all").click(function(){
        $('input.check_one').not(this).prop('checked', this.checked);
    });
    $('.multi-change-column-value').change(function(){
      var obj = $(this);      
      $('.check_one:checked').each(function(){
          $.ajax({
            url : "{{ route('change-value-by-column-chung') }}",
            type : 'GET',
            data : {
              id : $(this).val(),
              col : obj.data('column'),
              value: obj.val(),
              table: 'users'
            },
            success: function(data){

            }
          });
      });
      
   });
  $('#role, #level, #user_id_manage, #debt_type').change(function(){
    $(this).parents('form').submit();
  });
  $('.change-column-value').change(function(){
          var obj = $(this);      
          $.ajax({
            url : "{{ route('change-value-by-column-chung') }}",
            type : 'GET',
            data : {
              id : obj.data('id'),
              col : obj.data('column'),
              value: obj.val(),
              table : obj.data('table')
            },
            success: function(data){
                console.log(data);
            }
          });
       });
  $('#table-list-data tbody').sortable({
        placeholder: 'placeholder',
        handle: ".move",
        start: function (event, ui) {
                ui.item.toggleClass("highlight");
        },
        stop: function (event, ui) {
                ui.item.toggleClass("highlight");
        },          
        axis: "y",
        update: function() {
            var rows = $('#table-list-data tbody tr');
            var strOrder = '';
            var strTemp = '';
            for (var i=0; i<rows.length; i++) {
                strTemp = rows[i].id;
                strOrder += strTemp.replace('row-','') + ";";
            }     
            updateOrder("loai_sp", strOrder);
        }
    });
});
function updateOrder(table, strOrder){
  $.ajax({
      url: $('#route_update_order').val(),
      type: "POST",
      async: false,
      data: {          
          str_order : strOrder,
          table : table
      },
      success: function(data){
          var countRow = $('#table-list-data tbody tr span.order').length;
          for(var i = 0 ; i < countRow ; i ++ ){
              $('span.order').eq(i).html(i+1);
          }                        
      }
  });
}
</script>
@stop