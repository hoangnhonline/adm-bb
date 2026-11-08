@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Ứng lương
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'ung-luong.index' ) }}">Ứng lương</a></li>
    <li class="active">List</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
      @if($notNH)
      <a href="{{ route('ung-luong.create',['month' => $month]) }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new</a>
      @endif
      <div class="panel panel-default">
        
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('ung-luong.index') }}" id="searchForm">
            <div class="form-group">
                   
                    <select class="form-control select2" name="status" id="status">   
                    <option value="" >Status</option>                 
                      <option value="1"  {{ old('status', $status) == 1 ? "selected" : "" }}>Chưa cấn trừ</option>
                      <option value="2"  {{  old('status', $status) == 2 ? "selected" : "" }}>Đã cấn trừ</option>                    
                    </select>
                  </div>
            <div class="form-group">
             
                      
                <select class="form-control select2" id="partner_id" name="partner_id">     
                  <option value="">--Nhân viên--</option>      
                  @foreach($partnerList as $cate)
                  <option value="{{ $cate->id }}" {{ $partner_id == $cate->id ? "selected" : "" }}>
                    {{ $cate->name }}
                  </option>
                  @endforeach
                </select>
          
       
            </div>  
                 
            <div class="form-group">              
              <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                <option value="">--Người chi--</option>
                @foreach($collecterList as $payer)
                <option value="{{ $payer->id }}" {{ $nguoi_chi == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                @endforeach                
              </select>
            </div> 
            
            <div class="form-group">              
              <select class="form-control select2" name="time_type" id="time_type">                
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>Theo tháng</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Khoảng ngày</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>Ngày cụ thể </option>
              </select>
            </div> 
            @if($time_type == 1)
            <div class="form-group  chon-thang">                
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">--Tháng--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group  chon-thang">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--Năm--</option>                  
                  <option value="2022" {{ $year == 2022 ? "selected" : "" }}>2022</option>
                  <option value="2023" {{ $year == 2023 ? "selected" : "" }}>2023</option>
                  <option value="2024" {{ $year == 2024 ? "selected" : "" }}>2024</option>
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                  <option value="2027" {{ $year == 2027 ? "selected" : "" }}>2027</option>
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)
            
            <div class="form-group chon-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) Từ ngày @else Ngày @endif" value="{{ $arrSearch['use_date_from'] }}" style="width: 100px">
            </div>
           
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" style="width: 100px">
            </div>
             @endif
            @endif
            
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button> 
            <button type="reset" class="btn btn-danger btn-sm" style="margin-top: -5px">Reset</button>
             
          </form>         
        </div>
      </div>
      
      <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="padding: 5px;">
          <div class="table-responsive">
          <table class="table table-bordered" id="table_report" style="margin-bottom:0px;font-size: 14px;">
              <tr style="background-color: #f4f4f4">
                <th class="text-left">Tổng mục</th>
                <th class="text-right">Tổng tiền</th>                
              </tr>
              <tr>
                <th class="text-left">{{ number_format($items->total()) }}</th>
                <th class="text-right">{{ number_format($total_actual_amount) }}</th>
                
              </tr>
          </table>

        </div>
        </div>
      </div>
     <div class="form-inline" style="padding: 5px">
         
          <div class="form-group">            
            <select class="form-control select2 multi-change-column-value" data-column="status">
                <option value="">--SET TRẠNG THÁI--</option>
                 <option value="1">Chưa cấn trừ</option>
                 <option value="2">Đã cấn trừ</option>                 
              </select>                         
          </div> 
                
        </div>
      <div class="box">

        <div class="box-header with-border">
          <h3 class="box-title">List ( <span class="value">{{ $items->total() }} mục )</span> - Tổng tiền: <span style="color:red">{{ number_format($total_actual_amount) }} </span> </span></h3>
        </div>
       
        <!-- /.box-header -->
        <div class="box-body">
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  
          <table class="table table-bordered table-hover" id="table-list-data">
            <tr>                   
             
              <th style="width: 1%"><input type="checkbox" id="check_all" value="1"></th>
            
              <th style="width: 1%">#</th>
              <th class="text-left">Tạo lúc</th>
              <th class="text-left">Ngày</th>         
              <th class="text-left">Notes</th>                   
              <th class="text-right">Tổng tiền</th>
              <th width="200" style="white-space: nowrap;" class="text-center">Người chi</th> 
              @if($notNH)             
              <th width="1%;white-space:nowrap">Thao tác</th>
              @endif
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
              <tr class="cost" id="row-{{ $item->id }}">    
                       
                <td>                  
                  <input type="checkbox" id="checked{{ $item->id }}" class="check_one" value="{{ $item->id }}">
                </td> 
                
                <td><span class="order">{{ $i }}</span></td>   
                <td class="text-left">                   
                    {{ date('H:i d/m', strtotime($item->created_at)) }}                          
                </td>
                <td class="text-left">  
                    {{ date('d/m/y', strtotime($item->date_use)) }} 
                    @if($item->status == 1)
                        <label class="label label-danger label-sm">Chưa cấn trừ
                            </label>                        
                    @elseif($item->status == 2)
                        <label class="label label-success label-sm">Đã cấn trừ</label>                    
                    @endif
                    @if($item->user)
                     <br/> <i class="glyphicon glyphicon-user"></i> {{ $item->user->name }}
                     @endif
                </td>
                
                <td>
                
                  @if($item->partner)
                  {{ $item->partner->name }}
                  @endif               
                      @if($item->notes)
                  <br><span style="color: red">{{ $item->notes }}</span>
                  @endif         
                   
                </td>                        
                <td class="text-right">
                  {{ number_format($item->total_money) }}                   
                </td>
                <td class="text-center" style="white-space: nowrap;" data-id="{{ $item->nguoi_chi }}">
                 @if($item->nguoi_chi)
                 @if($item->nguoi_chi == 1)
                 <label class="label label-warning">{{ $collecterNameArr[$item->nguoi_chi] }}</label>
                 @else
                 <label class="label label-info"> {{ $collecterNameArr[$item->nguoi_chi] }}</label>
                  @endif
                  @endif
                </td>   
                                   
                <td style="white-space:nowrap">                  
                 
                    <a href="{{ route( 'ung-luong.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>
                    @if($item->costType)
                    <a onclick="return callDelete('{{ $item->costType->name . " - ".number_format($item->total_money) }}','{{ route( 'ung-luong.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                    @else
                    <a onclick="return callDelete('{{ number_format($item->total_money) }}','{{ route( 'ung-luong.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                    @endif
                 
                
                 
                </td>
            
              </tr> 
              @endforeach
            @else
            <tr>
              <td colspan="4">Không có dữ liệu.</td>
            </tr>
            @endif

          </tbody>
          </table>
          <div style="text-align:center">
           {{ $items->appends( $arrSearch )->links() }}
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
<div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="text-align: center;">
                <div class="modal-header bg-green">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4>QR CODE</h4>
                </div>
                <div class="modal-body">
                    <img src=""/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ĐÓNG</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
  $("#check_all").click(function(){
        $('input.check_one').not(this).prop('checked', this.checked);
    });
    $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
      
   $('.multi-change-column-value').change(function(){
          var obj = $(this);      
          $('.check_one:checked').each(function(){
              $.ajax({
                url : "{{ route('ung-luong.change-value-by-column') }}",
                type : 'GET',
                data : {
                  id : $(this).val(),
                  col : obj.data('column'),
                  value: obj.val()
                },
                success: function(data){

                }
              });
          });
          
       });
  });

</script>
@stop