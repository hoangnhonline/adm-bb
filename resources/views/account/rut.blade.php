@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Yêu cầu rút tiền
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'rut' ) }}"> Yêu cầu rút tiền</a></li>
    <li class="active">List</li>
  </ol>
</section>
<style type="text/css">
  .select2-container--default .select2-selection--single{height: 33px !important;}
</style>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif     
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Bộ lọc</h3>
        </div>
        <div class="panel-body">
         <form action="" method="{{ route('rut') }}" class="form-inline">    
            <div class="form-group">
              <select name="user_id" id="user_id" class="form-control select2">
                <option value="">Tất cả user</option>
                @foreach($userList as $usr)
                <option value="{{ $usr->id }}" {{ $user_id == $usr->id ? "selected" : "" }}>{!! $usr->name !!}</option>
                @endforeach
              </select>
            </div>
        <div class="form-group">
          <select name="status" id="status" class="form-control">
            <option value="">Status</option>
            <option value="2" {{ $status == 2 ? "selected" : "" }}>Chưa xử lý</option>
            <option value="1" {{ $status == 1 ? "selected" : "" }}>Đã xử lý</option>            
          </select>
        </div>             
        <div class="form-group">
          <input type="text" class="form-control datepicker search-date" name="from_date" value="{{ $from_date }}" placeholder="Từ ngày" autocomplete="off">
        </div>           
        <div class="form-group">
          <input type="text" class="form-control datepicker search-date" name="to_date" value="{{ $to_date }}" placeholder="Đến ngày" autocomplete="off">
        </div>       
        
            <button type="submit" class="btn btn-primary btn-sm">Xem</button>
          </form>         
        </div>
      </div>
      <div class="box">      
      <div class="box-header with-border">
          <h3 class="box-title">List ( <span class="value">{{ $amountList->total() }} records )</span></h3>
        </div>  
        <!-- /.box-header -->
        <div class="box-body">
           
           <div class="table-responsive">
        <table class="table table-hover media-list">
        <thead>
          <tr>
            <th class="text-left">User</th>            
            <th class="text-right">Số tiền</th>
            <th class="text-left">Ngân hàng</th>            
            <th class="text-right" >Status</th>
            @if($status != 2)
            <th class="text-center" >Đã xử lý</th>
            @endif
            <th class="text-right" >Ngày tạo</th>
          </tr>
        </thead>
       
          @if($amountList->count() > 0)
          @foreach($amountList as $amount)
          <tr>
            <td>
              {{ $amount->user->name }}
            </td>         
            <td class="text-right">{{ number_format($amount->amount) }}</td>
            <td>
              {{ $amount->bank->bank_name }} / {{ $amount->bank->branch_name }}<br>
              {{ $amount->bank->account_name }} - {{ $amount->bank->account_no }}
            </td>
            <td class="text-right">              
              @if($amount->status == 1)
              <span class="badge badge-success" style="background-color: #28a745; color:#FFF">Đã xử lý</span>
              @else
              <span class="badge badge-danger" style="background-color: #dc3545; color: #FFF">Chưa xử lý</span>
              @endif             
            </td>
            @if($status != 1)
            <td class="text-center">
              @if($amount->status == 2)
              <input type="checkbox" name="status" data-id="<?php echo $amount->id ?>" class="change_status" value="1" />
              @endif
            </td>
            @endif
            <td class="text-right">{{ date('d/m/Y H:i', strtotime($amount->created_at)) }}</td>
          </tr>
          @endforeach
          @else
          <tr>
            <td colspan="9" class="text-center">Không có dữ liệu.</td>
          </tr>
          @endif
     
        <tfoot>          
          <tr class="no-border">
            <td colspan="9" style="text-align: right">
              <paginator>
                <div class="pull-right">
                    {{ $amountList->appends(['type' => $type, 'from_date' => $from_date, 'to_date' => $to_date, 'status' => $status, 'user_id' => $user_id])->links() }}
                </div>
              </paginator>
            </td>
          </tr>
        </tfoot>
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

<style type="text/css">


</style>
@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    $('#status, #type, #user_id').change(function(){
      $(this).parents('form').submit();
    });
    $('.change_status').click(function(){
      var obj = $(this);      
      $.ajax({
        url : "{{ route('change-rut-status') }}",
        type : 'GET',
        data : {
          id : obj.data('id')
        },
        success: function(){
          window.location.reload();
        }
      });
    });
    $('.search-date').datepicker();
  });
</script>
@stop