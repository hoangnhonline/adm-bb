@extends('layout')
@section('content')
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Cost
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route( 'cost.index' ) }}">Cost</a></li>
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
      <a href="{{ route('cost.create',['month' => $month, 'cate_id' => $cate_id]) }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new</a>
      <a href="{{ route('cost.nhap-hang') }}" class="btn btn-success btn-sm" style="margin-bottom:5px"><i class="fa fa-bar-chart"></i> Thống kê nhập hàng</a>
      @endif
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Filter</h3>
        </div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('cost.index') }}" id="searchForm">
            @if($notNH)
            <div class="form-group">
              <input type="text" class="form-control" autocomplete="off" name="id_search" placeholder="ID" value="{{ $arrSearch['id_search'] }}" style="width: 100px">
            </div>     
               
            <div class="form-group">
               <select class="form-control select2" name="status" id="status">
                    <option value="">--Status--</option>
                    <option value="1" {{ $arrSearch['status'] == 1 ? "selected" : "" }}>Not yet paid</option>
                    <option value="2" {{ $arrSearch['status'] == 2 ? "selected" : "" }}>Đã thanh toán</option>
                    <option value="3" {{ $arrSearch['status'] == 3 ? "selected" : "" }}>Thanh toán sau</option>
                </select>
            </div>
            @endif    
            
            <div class="form-group">              
              <select class="form-control select2" name="cate_id" id="cate_id">
                <option value="">--Cost category--</option>
                @foreach($cateList as $cate)
                <option value="{{ $cate->id }}" {{ $arrSearch['cate_id'] == $cate->id ? "selected" : "" }}>{{ $cate->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">             
              <select class="form-control select2" name="ncc_id" id="ncc_id">
                <option value="">--Nhà cung cấp--</option>
                @foreach($nccList as $ncc)
                  <option value="{{ $ncc->id }}" {{ $arrSearch['ncc_id'] == $ncc->id ? "selected" : "" }}>
                    {{ $ncc->name }}
                  </option>
                @endforeach
              </select>
            </div>  
            <div class="form-group">
              <select class="form-control select2" name="detail_check" id="detail_check">
                <option value="">--Chi tiết hàng--</option>
                <option value="1" {{ ($arrSearch['detail_check'] ?? null) == 1 ? "selected" : "" }}>Lệch tiền – cần kiểm tra</option>
              </select>
            </div>
               
            <div class="form-group">              
              <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                <option value="">--Payer--</option>
                @foreach($collecterList as $payer)
                <option value="{{ $payer->id }}" {{ $nguoi_chi == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                @endforeach                
              </select>
            </div> 
           
            <div class="form-group">              
              <select class="form-control select2" name="time_type" id="time_type">                
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>By month</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Date range</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>By date</option>
              </select>
            </div> 
            @if($time_type == 1)
            <div class="form-group  chon-thang">                
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">--Month--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group  chon-thang">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--Year--</option>                  
                 
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                  <option value="2027" {{ $year == 2027 ? "selected" : "" }}>2027</option>
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)
            
            <div class="form-group chon-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) From date @else Ngày @endif" value="{{ $arrSearch['use_date_from'] }}" style="width: 100px">
            </div>
           
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">              
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="To date" value="{{ $arrSearch['use_date_to'] }}" style="width: 100px">
            </div>
             @endif
            @endif
           
           
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Search</button> 
            <button type="reset" class="btn btn-default btn-sm" style="margin-top: -5px">Reset</button>
             <div>
              @foreach($branchList as $beach)
                <div class="form-group">
                  &nbsp;&nbsp;&nbsp;<input type="checkbox" name="branch_ids[]" id="branch_ids" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }} value="{{$beach->id}}">
                  <label for="branch_ids">{{$beach->name}}</label>
                </div>
              @endforeach
            </div>            
          </form>         
        </div>
      </div>
      <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="padding: 5px;">
          <div class="table-responsive">
          <table class="table table-bordered" id="table_report" style="margin-bottom:0px;font-size: 14px;">
              <tr style="background-color: #f4f4f4">
                <th class="text-left">Total items</th>
                <th class="text-right">Total</th>
                @foreach($arrReport as $cate_id => $amountByCate)
                <th class="text-right">{!! isset($cateArr[$cate_id]) ? $cateArr[$cate_id] : "<span style=color:red>Không xác định</span>" !!}</th>
                @endforeach
              </tr>
              <tr>
                <th class="text-left">{{ number_format($total_quantity) }}</th>
                <th class="text-right">{{ number_format($total_actual_amount) }}</th>
                @foreach($arrReport as $cate_id => $amountByCate)
                <th class="text-right">{{ number_format($amountByCate) }}</th>
                @endforeach
              </tr>
          </table>

        </div>
        </div>
      </div>
      @if($notNH)
      <p style="text-align: right;"><a href="javascript:;" class="btn btn-primary btn-sm" id="btnExport">Export Excel</a>
      @endif
      <div class="box">

        <div class="box-header with-border">
          <h3 class="box-title">List ( <span class="value">{{ $items->total() }} mục )</span> - Tổng tiền: <span style="color:red">{{ number_format($total_actual_amount) }} </span>- Số lượng: <span style="color:red">{{ $total_quantity }} </span></h3>
        </div>
        @if(Auth::user()->role == 1 && $notNH)
        <div class="form-inline" style="padding: 5px">
          <div class="form-group">              
              <select class="form-control select2 multi-change-column-value" data-column="branch_id">                  
                <option value="">--SET BRANCH--</option>
                @foreach($branchList as $beach)
                <option value="{{ $beach->id }}">{{ $beach->name }}</option>
                @endforeach 
              </select>
            </div> 
  
          <div class="form-group">            
            <select class="form-control select2 multi-change-column-value" data-column="nguoi_chi">
                <option value="">--SET PAYER--</option>
                 @foreach($collecterList as $payer)
                <option value="{{ $payer->id }}">{{ $payer->name }}</option>
                @endforeach
              </select>                         
          </div>           
          <div class="form-group">        
              <select class="form-control select2 multi-change-column-value" data-column="cate_id">
                <option value="">--SET CATEGORY--</option>
                @foreach($cateList as $cate)
                <option value="{{ $cate->id }}">{{ $cate->name }}</option>
                @endforeach
              </select>
            </div>
          <div class="form-group">            
            <select class="form-control select2 multi-change-column-value" data-column="status">
                <option value="">--SET STATUS--</option>                 
                <option value="1">Not yet paid</option>
                <option value="2">Paid</option>
                <option value="3">Pay later</option>
                <option value="0">Deleted</option>
              </select>
          </div>        
        </div>
        @endif
        <!-- /.box-header -->
        <div class="box-body">
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>  
          <table class="table table-bordered table-hover" id="table-list-data">
            <tr>                   
              @if($notNH)
              <th style="width: 1%"><input type="checkbox" id="check_all" value="1"></th>
              @endif
              <th style="width: 1%">#</th>
              <th class="text-left">Created at</th>
              <th class="text-left">Date</th>
              <th class="text-center" style="width: 120px">Branch</th>
              <th class="text-left">Content</th>
              <th class="text-center">UNC</th>
              <th class="text-center">Amount</th>
              <th class="text-right">Price</th>
              <th class="text-right">Total</th>
              <th width="1%" style="white-space: nowrap;" class="text-center">Payer</th> 
              <th>NCC</th>
              @if($notNH)             
              <th width="1%;white-space:nowrap">Action</th>
              @endif
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
              <tr class="cost" id="row-{{ $item->id }}">    
                @if($notNH)            
                <td>                  
                  <input type="checkbox" id="checked{{ $item->id }}" class="check_one" value="{{ $item->id }}">
                </td> 
                @endif               
                <td><span class="order">{{ $i }}</span></td>   
                <td class="text-left">  
                  <strong style="color: red">{{ $item->id }}</strong><br>
                    {{ date('H:i d/m', strtotime($item->created_at)) }}                          
                </td>
                <td class="text-left">  
                    {{ date('d/m/y', strtotime($item->date_use)) }} 
                    @if($notNH)
                    @if($item->status == 1)
                        <label class="label label-danger label-sm">Not yet paid</label>                        
                    @elseif($item->status == 2)
                        <label class="label label-success label-sm">Paid</label>
                    @else
                      <label class="label label-warning label-sm">Pay later</label>
                    @endif
                    @endif
                    @if($item->user)
                     <br/> <i class="glyphicon glyphicon-user"></i> {{ $item->user->name }}
                     @endif
                </td>
                <td class="text-center">
                 
                  @if($item->branch_id)                                  
                  <br><i class="fa fa-map-marker" aria-hidden="true"></i> {{ $beachArr[$item->branch_id] }}
                  @endif
                </td>
                <td>
                  @if($item->costType)
                  <?php 
                  $str = $item->partner_id; 
                  ?>
                  <a href="https://plantotravel.vn/cost/{{ Helper::mahoa('mahoa', $str ) }}">{{ $item->costType->name }}</a>
                  @endif
                  
                  <p style="color:red; font-style: italic">{{ $item->notes }}</p>
                  @if($item->ncc)
                  {{ $item->ncc->name }}
                  @endif
                   
                </td>
                <td class="text-center">
                  @if($item->image_url && $item->unc_type == 1)
                  <span style="color: blue; cursor: pointer;" class="img-unc" data-src="{{ config('plantotravel.upload_url').$item->image_url }}">View Images</span>               
                  @endif
                  @if($notNH)
                    <select class="form-control select2 change-column-value" data-id="{{ $item->id }}" data-column="cate_id" style="width: 100%" data-table="cost">
                      <option value="">--Cost category--</option>
                      @foreach($cateList as $cate)
                      <option value="{{ $cate->id }}" {{ $cate->id == $item->cate_id ? "selected" : "" }}>{{ $cate->name }}</option>
                      @endforeach
                    </select>
                   
                  @endif
                </td>
                <td class="text-center">{{ $item->amount }}</td>
                <td class="text-right">{{ number_format($item->price) }}</td>
                <td class="text-right">
                  {{ number_format($item->total_money) }}                   
                  @if($item->detail_check == 1)
                    <br><span class="label label-danger" title="Tổng chi tiết hàng: {{ number_format($item->detail_total) }}">Lệch {{ number_format($item->detail_total - $item->total_money) }}</span>
                  @endif
                </td>
                <td class="text-center" style="white-space: nowrap;" data-id="{{ $item->nguoi_chi }}">
                 @if($item->nguoi_chi)
                  {{ isset($collecterNameArr[$item->nguoi_chi]) ?? $item->nguoi_chi }}
                  @endif
                </td>   
                <td>
                  <select class="form-control select2 change-column-value" data-id="{{ $item->id }}" data-column="ncc_id" style="width: 100%" data-table="cost">
                  @foreach($nccList as $ncc)
                  <option value="{{ $ncc->id }}" {{ $ncc->id == $item->ncc_id ? "selected" : "" }}>
                    {{ $ncc->name }}
                  </option>
                  @endforeach
                </select>
            
                </td>
                @if($notNH)                          
                <td style="white-space:nowrap">                 
                    <a href="{{ route( 'cost.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>
                    @if($item->costType)
                    <a onclick="return callDelete('{{ $item->costType->name . " - ".number_format($item->total_money) }}','{{ route( 'cost.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                    @else
                    <a onclick="return callDelete('{{ number_format($item->total_money) }}','{{ route( 'cost.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                    @endif
                 
                </td>
                @endif
              </tr> 
              @endforeach
            @else
            <tr>
              <td colspan="4">No data!</td>
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
    $('.btn-qrcode').click(function (e) {
        e.preventDefault();
        $('#qrCodeModal').find('img').attr('src', $(this).attr('href'));
        $('#qrCodeModal').modal('show');
    });
    $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
    $('.multi-change-column-value').change(function(){
          var obj = $(this);      
          $('.check_one:checked').each(function(){
              $.ajax({
                url : "{{ route('cost.change-value-by-column') }}",
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
    $('tr.cost').click(function(){
      $(this).find('.check_one').attr('checked', 'checked');
    });
    $("#check_all").click(function(){
        $('input.check_one').not(this).prop('checked', this.checked);
    });
    $('#btnExport').click(function(){
        var oldAction = $('#searchForm').attr('action');
        $('#searchForm').attr('action', "{{ route('cost.export') }}").submit().attr('action', oldAction);
      });
    // $('#partner_id').on('change', function(){
    //   $(this).parents('form').submit();
    // });
    $('#cate_id').change(function(){
        $.ajax({
          url : "{{ route('cost.ajax-doi-tac') }}",
          data: {
            cate_id : $(this).val()
          },
          type : "GET", 
          success : function(data){  
            if(data != 'null'){
              $('#load_doi_tac').html(data);
              if($('#partner_id').length==1){
                $('#partner_id').select2();  
              }              
            }
          }
        });
    });
  });
  $(document).ready(function(){
    $('.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).data('src'));
      $('#uncModal').modal('show');
    }); 
    $('.change-column-value').change(function(){
          var obj = $(this);         
          $.ajax({
            url : "{{ route('cost.change-value-by-column') }}",
            type : 'GET',
            data : {
              id : obj.data('id'),
              col : obj.data('column'),
              value: obj.val(),
            },
            success: function(data){
                console.log(data);
            }
          });
       });
  });
</script>
@stop