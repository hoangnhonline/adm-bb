@extends('layout')
@section('content')
<div class="content-wrapper">


<!-- Content Header (Page header) -->
<section class="content-header">
  <h1 style="text-transform: uppercase;">
    Quản lí Bill
  </h1>  
</section>

<!-- Main content -->
<section class="content">
  <div class="row">

    <div class="col-md-12">
      <div id="content_alert"></div>
      @if(Session::has('message'))
      <p class="alert alert-info" >{{ Session::get('message') }}</p>
      @endif    

      <div class="panel panel-default">

        <div class="panel-body" style="padding: 5px !important;">
          <form class="form-inline" role="form" method="GET" action="{{ route('booking.index') }}" id="searchForm" style="margin-bottom: 0px;">
            
            <div class="form-group">
              <select class="form-control select2" name="time_type" id="time_type" style="width: 150px;">
                <option value="">--Thời gian--</option>
                <option value="1" {{ $time_type == 1 ? "selected" : "" }}>Theo tháng</option>
                <option value="2" {{ $time_type == 2 ? "selected" : "" }}>Khoảng ngày</option>
                <option value="3" {{ $time_type == 3 ? "selected" : "" }}>Theo ngày</option>
              </select>
            </div>
            @if($time_type == 1)
            <div class="form-group  chon-thang">
                <select class="form-control select2" id="month_change" name="month">
                  <option value="">--THÁNG--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group  chon-thang">
                <select class="form-control select2" id="year_change" name="year">
                  <option value="">--NĂM--</option>                 
                  <option value="2025" {{ $year == 2025 ? "selected" : "" }}>2025</option>
                  <option value="2026" {{ $year == 2026 ? "selected" : "" }}>2026</option>
                  <option value="2027" {{ $year == 2027 ? "selected" : "" }}>2027</option>
                </select>
              </div>
            @endif
            @if($time_type == 2 || $time_type == 3)
            <div class="form-group chon-ngay">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="@if($time_type == 2) Từ ngày @else Ngày @endif " value="{{ $arrSearch['use_date_from'] }}" style="width: 110px">
            </div>
            @if($time_type == 2)
            <div class="form-group chon-ngay den-ngay">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" style="width: 110px">
            </div>
             @endif
            @endif  
            
            <div class="form-group">
              <select class="form-control select2" name="nguoi_thu_tien" id="nguoi_thu_tien" style="width: 120px;">
                <option value="">--Thu tiền--</option>
                @foreach($collecterList as $payer)
                  @if($payer->is_view == 1)
                  <option value="{{ $payer->id }}" {{ $arrSearch['nguoi_thu_tien'] == $payer->id ? "selected" : "" }}>{{ $payer->name_view }}</option>
                  @endif
                @endforeach
              </select>
            </div> 
         
             
            <button type="submit" class="btn btn-info btn-sm" style="margin-top: -5px">Lọc</button>
            <div class="form-group">
              <button type="button" id="btnReset" class="btn btn-default btn-sm">Reset</button>
            </div>

            <div>             
            </div>            
          </form>
        </div>
      </div>     
      <div class="box">       
         <!-- /.box-header -->
          <div class="box-header with-border">
              <h3 class="box-title">Danh sách ( <span class="value">{{ $items->total() }} bill )</span>
              
                - Tổng doanh thu: <span style="color: blue; font-weight: bold;">{{ number_format($totalRevenueKoGiam) }}</span>
               
              </h3>
          </div>
        <!-- /.box-header -->
        <div class="clearfix"></div>
        <div class="box-body">

          <div style="text-align:center">
            {{ $items->appends( $arrSearch )->links() }}
          </div>
          
          <div class="table-responsive">
          <table class="table table-bordered table-hover" id="table-list-data">
            <tr style="background-color: #f4f4f4">             
              <th width="1%">STT.</th>

              <th style="width: 100px">KH</th>
              <th width="40%">Chi tiết</th>
              <th class="text-center" width="120">Ngày</th>
              <th class="text-right" width="100">Tổng tiền</th>
              <th class="text-center"  width="100">Thu tiền</th>             
            </tr>
            <tbody>
            @if( $items->count() > 0 )
              <?php $l = 0; 
              $total_revenue = 0;
              ?>
              @foreach( $items as $item )
                <?php $l ++; 
                $total_revenue+= $item->con_lai;
                ?>
              <tr class="booking" id="row-{{ $item->booking_id }}" data-id="{{ $item->booking_id }}" data-date="{{ $item->use_date }}" style="border-bottom: 1px solid #000 !important; @if($l%2 == 0)  background-color:#c9ced6 @endif">
                
                <td class="text-center"> {{ $l }}</td>
                <td>
                    @if($item->table_no)
                    TABLE <span style="color: blue; font-weight: bold">{{ $item->table_no }} - <strong>{{ date('H:i', strtotime($item->created_at)) }}</strong></span>
                    @endif           
                    @if($item->take_away == 1)
                    <br><span style="color: blue">TAKE AWAY</span>
                    @endif
                    @if($item->indian == 2)
                    <br><span style="color: red">NOT INDIAN</span>
                    @endif
                </td>
                <td>
                  <table class="table" style="margin-top:5px;margin-bottom: 10px;">

                   @foreach($item->details as $dishs)
                   <tr>
                     <td width="50%">                     
                      @if($dishs->is_drink == 1)
                      <i class="fa fa-coffee" style="color: blue" aria-hidden="true"></i>
                      @endif
                      {{ $dishs->dish ? $dishs->dish->name  : "" }}
                      <br>
                      <span style="color: red; font-weight: bold; text-transform: uppercase;">{{ $dishs->notes }}</span>
                    </td>
                     <td width="20%">{{ $dishs->amount }} x {{ number_format($dishs->price) }}</td>
                     <td width="30%" class="text-right">{{ number_format($dishs->total_price) }}</td>
                   </tr>
                   @endforeach                  
                    @if($item->notes)
                    <div class="clearfix"><span style="color:red; font-style: italic;">{!! nl2br($item->notes) !!}</span></div>
                    @endif
                 </table>
                </td>
                <td class="text-center">
                  {{ date('d/m/Y', strtotime($item->use_date)) }}                 
                </td>
                <td class="text-right">
                  {{ number_format($item->total_price) }}
                  @if($item->discount)
                  <br>
                  Giảm giá: {{ $item->discount ?number_format($item->discount) : '-' }}
                  @endif
                  @if($item->con_lai)
                  <br> Doanh thu: {{ number_format($item->con_lai) }}
                  @endif
                </td>               
          
                <td class="text-center">                   
                 @if($item->nguoi_thu_tien)
                  {{ $item->collecter->name_view }}
                  @endif
                     
                </td>
              </tr>
              @endforeach
            @else
            <tr>
              <td colspan="9">Không có dữ liệu!</td>
            </tr>
            @endif

          </tbody>
          </table>          
          </div>
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

@stop
<style type="text/css">
  .hdv{
    cursor: pointer;
  }
  .hdv:hover, .hdv.selected{
    background-color: #06b7a4;
    color: #FFF
  }
  label{
    cursor: pointer;
  }
  #table_report th td {padding: 2px !important;}
  #searchForm, #searchForm input{
    font-size: 13px;
  }
  .form-control{
    font-size: 13px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered{

    font-size: 12px !important;
  }
  tr.error{
    background-color:#ffe6e6
  }
</style>
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    $('.img-unc').click(function(){
      $('#unc_img').attr('src', $(this).data('src'));
      $('#uncModalImg').modal('show');
    });
    $('.btn-qrcode').click(function (e) {
                e.preventDefault();
                $('#qrCodeModal').find('img').attr('src', $(this).attr('href'));
                $('#qrCodeModal').modal('show');
            })
    $('#btnSavePayment').click(function(){
          $.ajax({
            url: "{{ route('booking-payment.store') }}",
            type: "GET",
            data: {
              image_url : $('#image_url').val(),
              image_name : $('#image_name').val(),
              notes : $('#notes_payment').val(),
              booking_id : $('#booking_id_unc').val()
            },
            beforeSend : function(){
              //$('#btnSavePayment').hide();
             // $('#btnLoading').show();
            },
            success: function (response) {

              window.location.reload();
            },
            error: function(response){
                alert('Có lỗi xảy ra');
            }
          });
    });  
      
  });
</script>
<script type="text/javascript">
    $(document).ready(function(){
       $(document).on('click', '.btnUnc',function(){
         $('#uncModal').modal('show');
        $('#booking_id_unc').val($(this).data('id'));
        $('#id_load_unc').html($(this).data('id'));

      });
      $('#searchForm input[type=checkbox]').change(function(){
        $('#searchForm').submit();
      });
      $('tr.booking').click(function(){
        $(this).find('.check_one').attr('checked', 'checked');
      });
      $("#check_all").click(function(){
          $('input.check_one').not(this).prop('checked', this.checked);
      });
      $('#btnExcel').click(function(){
        var oldAction = $('#searchForm').attr('action');
        $('#searchForm').attr('action', "{{ route('export.cong-no-tour') }}").submit().attr('action', oldAction);
      });      

       $('.change-column-value').change(function(){
          var obj = $(this);
          if(obj.data('column') == 'cano_id'){
           // alert('Tất cả các booking cùng HDV sẽ được gán chung vào cano này');
          }
          $.ajax({
            url : "{{ route('booking.change-value-by-column') }}",
            type : 'GET',
            data : {
              id : obj.data('id'),
              col : obj.data('column'),
              value: obj.val()
            },
            success: function(data){
                console.log(data);
            }
          });
       });
       $('.multi-change-column-value').change(function(){
          var obj = $(this);
          $('.check_one:checked').each(function(){
              $.ajax({
                url : "{{ route('booking.change-value-by-column') }}",
                type : 'GET',
                data : {
                  id : $(this).val(),
                  col : obj.data('column'),
                  value: obj.val()
                },
                success: function(data){
                  window.location.reload();
                }
              });
          });

       });

      $('#btnReset').click(function(){
        $('#searchForm select').val('');
        $('#searchForm').submit();
      });
      $('.change-column-value-booking').change(function(){
          var obj = $(this);
          ajaxChange(obj.data('id'), obj);
       });
    
  });
  function ajaxChange(id, obj){
        $.ajax({
            url : "{{ route('booking.change-value-by-column') }}",
            type : 'GET',
            data : {
              id : id,
              col : obj.data('column'),
              value: obj.val()
            },
            success: function(data){
                console.log(data);
            }
          });
      }
  </script>
@stop
