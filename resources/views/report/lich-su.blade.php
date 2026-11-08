@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Lịch sử nạp tiền
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'report.index' ) }}"> Lịch sử nạp tiền</a></li>
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
         <form action="" method="{{ route('report.index') }}" class="form-inline">    
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
                <option value="1" {{ $status == 1 ? "selected" : "" }}>Thành công</option>
                <option value="2" {{ $status == 2 ? "selected" : "" }}>Thất bại</option>
              </select>
            </div>
            <div class="form-group">
              <select name="loaithe_id" id="loaithe_id" class="form-control">
                <option value="">Loại thẻ</option>
                @foreach($loaiThe as $loai)
                <option value="{{ $loai->id }}" {{ $loaithe_id == $loai->id ? "selected" : "" }}>{!! $loai->name !!}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <select name="menhgia_id" id="menhgia_id" class="form-control">
                <option value="">Mệnh giá</option>
                @foreach($menhgiaList as $mg)
                <option value="{{ $mg->id }}" {{ $menhgia_id == $mg->id ? "selected" : "" }}>{!! $mg->name !!}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <input type="text" class="form-control code-card" name="code" value="{{ $code }}" placeholder="Mã thẻ" autocomplete="off">
            </div>           
            <div class="form-group">
              <input type="text" class="form-control code-card" name="serial" value="{{ $serial }}" placeholder="Serial thẻ" autocomplete="off">
            </div>
            <div class="form-group">
              <input type="text" class="form-control datepicker search-date" data-date-format="dd/mm/yyyy" name="from_date" value="{{ $from_date }}" placeholder="Từ ngày" autocomplete="off">
            </div>           
            <div class="form-group">
              <input type="text" class="form-control datepicker search-date" data-date-format="dd/mm/yyyy" name="to_date" value="{{ $to_date }}" placeholder="Đến ngày" autocomplete="off">
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
           
           <table class="table table-hover media-list">
        <thead>
          <tr>
            <th class="text-left">User</th>
            <th class="text-left">Mã thẻ</th>
            <th class="text-left">Seri thẻ</th>
            <th class="text-center">Status</th>
            <th class="text-center">Loại thẻ</th>
            <th class="text-right" >Mệnh giá</th>
            <th class="text-right" >Thực nhận</th>
            <th class="text-right" >Thời gian</th>
          </tr>
        </thead>
       
          @if($amountList->count() > 0)
          @foreach($amountList as $item)
          <tr>
            <td>
              {{ $item->user->name }}
            </td>
            <td class="text-left">{{ $item->code }}</td>
            <td class="text-left">{{ $item->serial }}</td>
            <td class="text-center">
              @if($item->status == 1)
              <span class="badge badge-success" style="background-color: #28a745; color:#FFF">Thành công</span>
              @else
              <span class="badge badge-danger" style="background-color: #dc3545; color: #FFF">Thất bại</span>
              @endif
            <td class="text-center">{{ $item->loai->name }}</td>
            <td class="text-right">{{ $item->gia->name }}</td>            
            <td class="text-right">
              @if($item->status == 1)
              {{ number_format($item->thuc_nhan) }}
              @endif
            </td>
            <td class="text-right">{{ date('d/m/Y H:i', strtotime($item->created_at)) }}</td>
          </tr>
          @endforeach
          @else
          <tr>
            <td colspan="8" class="text-center">Không có dữ liệu.</td>
          </tr>
          @endif
     
        <tfoot>          
          <tr class="no-border">
            <td colspan="8" style="text-align: right">
              <paginator>
                <div class="pull-right">
                    {{ $amountList->appends(['menhgia_id' => $menhgia_id, 'loaithe_id' => $loaithe_id, 'status' => $status, 'serial' => $serial, 'code' => $code, 'from_date' => $from_date, 'to_date' => $to_date, 'user_id' => $user_id])->links() }}
                </div>
              </paginator>
            </td>
          </tr>
        </tfoot>
      </table>
         
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
    $('#status, #loaithe_id, #menhgia_id, #user_id').change(function(){
      $(this).parents('form').submit();
    });
    $('.search-date').datepicker();
  });
</script>
@stop