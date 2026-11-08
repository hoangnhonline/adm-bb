@extends('layout')
@section('content')
<div class="content-wrapper">
  
<!-- Content Header (Page header) -->
<section class="content-header" style="padding-top: 10px;">
  <h1 style="text-transform: uppercase;">    
    COST MANAGEMENT {{$arrSearch['use_date_from']}}
  </h1>
  
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      <!-- <div id="content_alert"></div> -->
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif
      @if($notNH)
      <a href="{{ route('cost.create',['date_use' => $date_use]) }}" class="btn btn-info btn-sm" style="margin-bottom:5px">Add new cost</a>
      @endif
      <div class="panel panel-default">        
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('cost.index') }}" id="searchForm">
             <div class="row">  
              <div class="form-group @if($time_type == 3) col-xs-6 @else col-xs-4 @endif" style="padding-right: 0px">              
                <select class="form-control select2" name="time_type" id="time_type">                  
                  <option value="">--Time--</option>
                  <option value="1" {{ $time_type == 1 ? "selected" : "" }}>By month</option>
                  <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Date range</option>
                  <option value="3" {{ $time_type == 3 ? "selected" : "" }}>By date</option>
                </select>
              </div>
              @if($time_type == 1)
            <div class="form-group col-xs-4 chon-thang" style="padding-right: 5px">                
                <select class="form-control select2 " id="month_change" name="month">
                  <option value="">--Month--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group col-xs-4 chon-thang" style="padding-left: 5px">                
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--Year--</option>                   
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
            <div class="row">
                @foreach($branchList as $beach)
                  <div class="form-group col-xs-4" style="padding:0px; padding-left: 5px;">
                    &nbsp;&nbsp;&nbsp;<input type="checkbox" name="branch_ids[]" id="branch_ids" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }} value="{{$beach->id}}">
                    <label for="branch_ids">{{$beach->name}}</label>
                  </div>
                @endforeach
              </div>  
            @if($notNH)
        <!--       <div class="form-group">
                <div class="checkbox">
                        <label style="font-weight: bold; color: blue">
                          <input type="checkbox" id="xe_4t" name="xe_4t" value="1" {{ $arrSearch['xe_4t'] == 1 ? "checked" : "" }}>
                          4T
                        </label>
                  </div>
              </div> -->
              @endif
            <div class="row">
              @if($notNH)
              <div class="form-group col-xs-6">
                <input type="text" class="form-control" autocomplete="off" name="id_search" placeholder="ID" value="{{ $arrSearch['id_search'] }}">
              </div>
              <div class="form-group col-xs-6">
               <select class="form-control select2" name="status" id="status">
                    <option value="">--Status--</option>
                    <option value="1" {{ $arrSearch['status'] == 1 ? "selected" : "" }}>Chưa thanh toán</option>
                    <option value="2" {{ $arrSearch['status'] == 2 ? "selected" : "" }}>Đã thanh toán</option>
                    <option value="3" {{ $arrSearch['status'] == 3 ? "selected" : "" }}>Thanh toán sau</option>
                </select>
            </div>
            @endif
              
            <div class="form-group col-xs-6">              
              <select class="form-control select2" name="cate_id" id="cate_id">
                <option value="">--Cost category--</option>
                @foreach($cateList as $cate)
                <option value="{{ $cate->id }}" {{ $arrSearch['cate_id'] == $cate->id ? "selected" : "" }}>{{ $cate->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-xs-6">
              <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                <option value="">--Payer--</option>
                 @foreach($collecterList as $payer)
                <option value="{{ $payer->id }}" {{ $nguoi_chi == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                @endforeach 
              </select>
            </div> 
            </div>
            <button type="submit" class="btn btn-success btn-sm">Search</button>
            <button type="reset" class="btn btn-default btn-sm">Reset</button>
          </form>         
        </div>
      </div>
      <div class="panel" style="margin-bottom: 15px;">
        <div class="panel-body" style="padding: 5px;">
          <div class="table-responsive">
          <table class="table table-bordered" id="table_report" style="margin-bottom:0px;font-size: 14px;">
              <tr>
              <th>Total Amount</th>
              <td class="text-right">{{ number_format($total_quantity) }}</td>
            </tr>
            <tr>
              <th>Total Money</th>
              <td class="text-right" style="color: red">{{ number_format($total_actual_amount) }}</td>
            </tr>
            @foreach($arrReport as $cate_id => $amountByCate)
            <tr style="background-color: #ccc">
              <th>{!! isset($cateArr[$cate_id]) ? $cateArr[$cate_id] : "<span style=color:red>Không xác định</span>" !!}</th>
              <td class="text-right">{{ number_format($amountByCate) }}</td>
            </tr>
            @endforeach 
          </table>

        </div>
        </div>
      </div>
      
          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>
            <div style="font-size: 18px;padding: 10px; border-bottom: 1px solid #ddd">
              Total: <span class="value">{{ $items->total() }} items </span> - Total money: <span style="color:red">{{ number_format($total_actual_amount) }}</span>           
            </div>
            <ul style="padding: 0px">
             @if( $items->count() > 0 )
              <?php $i = 0; ?>
              @foreach( $items as $item )
                <?php $i ++; ?>
                 <li id="row-{{ $item->id }}" class="booking" style="padding: 5px;background-color: #fff; font-size:17px;margin-bottom: 10px; border-radius: 5px; color: #2c323f;  @if($i%2 == 0)  background-color:#dde4eb @endif; list-style: none;" > 
                  @if($notNH)
                  <input type="checkbox" id="checked{{ $item->id }}" class="check_one" value="{{ $item->id }}">
                  @endif
                  <strong style="color: red">{{ $item->id }}</strong>-
                    {{ date('H:i d/m', strtotime($item->created_at)) }}  
                    @if($notNH)
                    @if($item->status == 1)
                        <label class="label label-danger label-sm">Not yet pay</label>                        
                    @elseif($item->status == 2)
                        <label class="label label-success label-sm">Paid</label>
                    @else
                      <label class="label label-warning label-sm">Pay later</label>
                    @endif
                    @endif
                    <br>                   
                  @if($item->costType)
                  <a href="{{ route( 'cost.edit', [ 'id' => $item->id ]) }}">{{ $item->costType->name }}</a> - {{ date('d/m', strtotime($item->date_use)) }} 
                  @else
                  {{ $item->cost_type_id }}
                  @endif                 
                  <br>
                     @if($item->branch_id)                                  
                  <i class="fa fa-map-marker" aria-hidden="true"></i> {{ $beachArr[$item->branch_id] }} <br>
                  @endif
                    <i class="  glyphicon glyphicon-user"></i> Payer: 
                    @if($item->nguoi_chi)
                    {{ $collecterNameArr[$item->nguoi_chi] }}
                    @endif         
                    <br>               
                    <i class="  glyphicon glyphicon-usd"></i>{{ number_format($item->amount) }} x {{ number_format($item->price) }} = {{ number_format($item->total_money) }}
                           <br>                      
                    @if($item->notes)
                    <span style="color:red; font-size: 13px !important; font-style: italic;">{!! nl2br($item->notes) !!}</span>
                    @endif    
                    @if($item->unc_type == 2 && $item->image_url)
                    <p style="color: blue; font-style: italic;">
                      {{ $item->image_url }}
                    </p>
                    @endif
                    <div class="clearfix" style="margin-top: 3px; margin-bottom: 3px"></div>
                   @if($item->image_url && $item->unc_type == 1)
                  <img src="{{ config('plantotravel.upload_url').$item->image_url }}" height="80"  width="80" style="border: 1px solid red" class="img-unc">
                  @endif

                  @if(!$item->time_chi_tien && $item->status != 2)
                   
                    @if($item->costType)
                    <a style="float: right" onclick="return callDelete('{{ $item->costType->name . " - ".number_format($item->total_money) }}','{{ route( 'cost.destroy', [ 'id' => $item->id ]) }}');" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a>
                    @endif
                    <a style="float: right; margin-right: 5px" href="{{ route( 'cost.edit', [ 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-pencil"></span></a>
                    <div class="clearfix"></div>
                  @endif <!--Đã nộp tiền thì ko đc sửa / xóa-->
                  @if($item->user)
                  <br/> <i class="glyphicon glyphicon-user"></i> {{ $item->user->name }}
                  @endif
                </li>               
              @endforeach
            @else
            <li style="list-style: none;padding: 5px">
              <p>No data!</p>
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
</script>
@stop