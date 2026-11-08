@extends('layout')
@section('content')
<div class="content-wrapper">
  
<!-- Content Header (Page header) -->
<section class="content-header" style="padding-top: 15px;">
  <h1 style="text-transform: uppercase;">    
    ỨNG LƯƠNG
  </h1>
  <a href="{{ route('ung-luong.create',['date_use' => $date_use]) }}" class="btn btn-info btn-sm" style="margin-bottom:5px; position: absolute; top: 10px; right: 5px">Add new</a>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      <!-- <div id="content_alert"></div> -->
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
    
      
  
      <div class="panel panel-default">        
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('ung-luong.index') }}" id="searchForm">
             <div class="row"> 
              <div class="form-group col-xs-12">
                   
                    <select class="form-control select2" name="status" id="status">               
                    <option value="" >--Status--</option>     
                      <option value="1"  {{ old('status', $status) == 1 ? "selected" : "" }}>Chưa cấn trừ</option>
                      <option value="2"  {{  old('status', $status) == 2 ? "selected" : "" }}>Đã cấn trừ</option>                    
                    </select>
                  </div> 
              <div class="form-group @if($time_type == 3) col-xs-6 @else col-xs-4 @endif" style="padding-right: 0px">              
                <select class="form-control select2" name="time_type" id="time_type">                  
                  <option value="">--Thời gian--</option>
                  <option value="1" {{ $time_type == 1 ? "selected" : "" }}>Theo tháng</option>
                  <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Khoảng ngày</option>
                  <option value="3" {{ $time_type == 3 ? "selected" : "" }}>Ngày cụ thể </option>
                </select>
              </div>
              @if($time_type == 1)
            <div class="form-group col-xs-4 chon-thang" style="padding-right: 5px">                
                <select class="form-control select2 " id="month_change" name="month">
                  <option value="">--THÁNG--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group col-xs-4 chon-thang" style="padding-left: 5px">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--NĂM--</option>                                            
                  <option value="2024" {{ $year == 2024 ? "selected" : "" }}>2024</option>
                  
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                  <option value="2027" {{ $year == 2027 ? "selected" : "" }}>2027</option>
                </select>
              </div>
            @endif

            @if($time_type == 2 || $time_type == 3)            
              <div class="form-group @if($time_type == 3) col-xs-6 @else col-xs-4 @endif"  style="@if($time_type!=3)padding-right: 5px; @endif padding-left: 5px">
                <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) Từ ngày @else Ngày @endif " value="{{ $arrSearch['use_date_from'] }}">
              </div>
              
              @if($time_type == 2)
              <div class="form-group col-xs-4" style="padding-left: 0px">
                <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" >
              </div>
              @endif
            @endif
            </div>
                    
              <div class="form-group ">
                  <select class="form-control select2" id="partner_id" name="partner_id">     
                    <option value="">--Nhân viên--</option>      
                    @foreach($partnerList as $cate)
                    <option value="{{ $cate->id }}" {{ $partner_id == $cate->id ? "selected" : "" }}>
                      {{ $cate->name }}
                    </option>
                    @endforeach
                  </select>
              </div>            
          
            @if($notNH)
            <div class="row">
              <div class="form-group col-xs-12">
              <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                <option value="">--Người chi--</option>
                 @foreach($collecterList as $payer)
                <option value="{{ $payer->id }}" {{ $nguoi_chi == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                @endforeach 
              </select>
            </div> 
            </div>
            @endif
           

            <button type="submit" class="btn btn-success btn-sm">Search</button>
            <button type="reset" class="btn btn-danger btn-sm">Reset</button>
          </form>         
        </div>
      </div>
      <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="padding: 5px;">
          <div class="table-responsive">
          <table class="table table-bordered" id="table_report" style="margin-bottom:0px;font-size: 14px;">
              <tr>
              <th>Tổng mục</th>
              <td class="text-right">{{ number_format($items->total()) }}</td>
            </tr>
            <tr>
              <th>Tổng tiền</th>
              <td class="text-right" style="color: red">{{ number_format($total_actual_amount) }}</td>
            </tr>            
          </table>

        </div>
        </div>
      </div>
      <div class="form-inline" style="padding: 5px">
        </div>
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  
   
             
            <div style="font-size: 18px;padding: 10px; border-bottom: 1px solid #ddd">
              Tổng: <span class="value">{{ $items->total() }} mục </span> - Tổng tiền: <span style="color:red">{{ number_format($total_actual_amount) }}</span>           
            </div>
            <ul style="padding: 0px">
             @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
                 <li id="row-{{ $item->id }}" class="booking" style="padding: 5px;background-color: #fff; font-size:17px;margin-bottom: 10px; border-radius: 5px; color: #2c323f;  @if($i%2 == 0)  background-color:#dde4eb @endif; list-style: none;" > 
                
                    {{ date('H:i d/m', strtotime($item->created_at)) }}  
                    
                    <br>                   
                  
                  @if($item->partner)
                 <span style="color:blue"> {{ $item->partner->name }}</span>
                  @endif
                   @if($item->status == 1)
                        <label class="label label-danger label-sm">Chưa cấn trừ
                            </label>                        
                    @elseif($item->status == 2)
                        <label class="label label-success label-sm">Đã cấn trừ</label>                    
                    @endif
                  <br>
                     
                    <i class="  glyphicon glyphicon-user"></i> Người chi: 
                    @if($item->nguoi_chi)
                    {{ $collecterNameArr[$item->nguoi_chi] }}
                    @endif                   
                                      
                    <br>               
                    <i class="  glyphicon glyphicon-usd"></i> {{ number_format($item->total_money) }}
                           <br>                      
                    @if($item->notes)
                    <span style="color:red; font-size: 13px !important; font-style: italic;">{!! nl2br($item->notes) !!}</span>
                    @endif    
                   
                           

                  @if(!$item->time_chi_tien && $item->status != 2)
                    
                    @if($item->costType)
                    <a style="float: right" onclick="return callDelete('{{ $item->costType->name . " - ".number_format($item->total_money) }}','{{ route( 'ung-luong.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                    @endif
                    <a style="float: right; margin-right: 5px" href="{{ route( 'ung-luong.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>
                    <div class="clearfix"></div>
                  @endif <!--Đã nộp tiền thì ko đc sửa / xóa-->
                  @if($item->user)
                  <br/> <i class="glyphicon glyphicon-user"></i> {{ $item->user->name }}
                  @endif
                </li>               
              @endforeach
            @else
            <li style="list-style: none;padding: 5px">
              <p>Không có dữ liệu.</p>
            </li>
            @endif
            </ul>
          

          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  
    
      <!-- /.box -->     
    </div>
    <!-- /.col -->  
  </div> 
</section>
<!-- /.content -->
</div>
<input type="hidden" id="table_name" value="articles">
<div class="modal fade" id="uncModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="text-align: center;">
       <div class="modal-header">        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img src="" id="unc_img" style="width: 100%">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>
<style type="text/css">
  .form-group{
    margin-bottom: 10px !important;
  }
    .content-wrapper, .right-side {
    background-color: #c7c4c4 !important;
}
</style>
@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    
    $('img.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).attr('src'));
      $('#uncModal').modal('show');
    }); 
    $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
   $('#searchForm select').change(function(){
        $('#searchForm').submit();
      });

  });
</script>
@stop