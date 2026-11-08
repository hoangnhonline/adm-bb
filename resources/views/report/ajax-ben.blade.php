<!-- /.box-header -->
   <div class="box-body">
    <div style="text-align:center">
      {{ $items->appends( $arrSearch )->links() }}
    </div>  <!--phan trang-->
    <div class="table-responsive">
      <div style="font-size: 18px;padding: 10px; border-bottom: 1px solid #ddd">
        Tổng <span style="color: red">{{ $items->total() }}</span> booking -
      Số NL: <span style="color: red">{{ number_format($tong_so_nguoi )}} </span>- Phần ăn: <span style="color: red">{{ $tong_phan_an }}</span></span> 
      </div>
      <ul style="padding: 10px">
       @if( $items->count() > 0 )
        <?php $i = 0; ?>
        @foreach( $items as $item )
          <?php $i ++; ?>
         <li style="border-bottom: 1px solid #ddd; padding-bottom: 10px; padding-top: 10px; clear: both;font-size: 15px">                  
                    @php $arrEdit = array_merge(['id' => $item->id], $arrSearch) @endphp
                    @if($item->status == 1)
                    <span class="label label-info">MỚI</span>
                    @elseif($item->status == 2)
                    <span class="label label-default">HOÀN TẤT</span>
                    @elseif($item->status == 3)
                    <span class="label label-danger">HỦY</span>
                    @endif 
                    @if($item->nguoi_thu_tien == 4)
                    <label class="label label-danger label-sm">CÔNG NỢ</label>
                    @endif
                    <br>
                    <span style="color:#06b7a4; text-transform: uppercase;"><span style="color: #ff9d00;font-weight: bold">PTT{{ $item->id }}</span> - {{ $item->name }} </span> 
                     @if($item->tour_id == 3)
                  <br><label class="label label-warning">Rạch Vẹm</label>
                  @elseif($item->tour_id == 4)
                  <br><label class="label label-warning">Câu Mực</label>
                  @elseif($item->tour_id == 5)
                  <br><label class="label label-warning">Grand World</label>
                  @elseif($item->tour_id == 6)
                  <br><label class="label label-warning">Bãi Sao-2 Đảo</label>
                  @elseif($item->tour_id == 7)
                  <br><label class="label label-warning">Bãi Sao-Địa Trung Hải</label>                        
                  @elseif($item->tour_id == 8)
                  <br><label class="label label-warning">Bãi Sao-Hòn Thơm</label> 
                  @endif
                    @if($item->tour_type == 3)
                  <label class="label label-warning">Thuê cano</label>
                  @elseif($item->tour_type == 2)
                  <label class="label label-danger">Tour riêng</label>                  
                  @endif
                           
                  @php $arrEdit = array_merge(['id' => $item->id], $arrSearch) @endphp
                
                   
                    <i class="fa fa-phone"></i> <a href="tel:{{ $item->phone }}" target="_blank">{{ $item->phone }}</a>

                    
                    <br><i class="glyphicon glyphicon-user"></i> 
                    
               
                  @if($item->user)
                    {{ $item->user->name }}
                  @else
                    {{ $item->user_id }}
                  @endif
                  @if($item->ctv)
                    - {{ $item->ctv->name }}                  
                  @endif
                    <br>
                    <i class="fa fa-calendar"></i> {{ date('d/m/Y', strtotime($item->use_date)) }}
                    <br>
                    <i class="fa fa-map-marker"></i>
                    @if($item->location)
                    {{ $item->location->name }}
                    @else
                    {{ $item->address }}
                    @endif
                    
                    <br>
                    <i class="fa fa-user-circle"></i> NL: <b>{{ $item->adults }}</b> / TE: {{ $item->childs }} / EB: {{ $item->infants }} - 
                    
                    <i class="fa fa-briefcase"></i> {{ $item->meals }}
                    
                    <br>
                    <i class="fa fa-usd"></i> Tiền thu khách: <span style="color:red">{{ number_format($item->con_lai) }}</span>
                    @if($item->ko_cap_treo)
                    <br>
                    <i style="color:red">KHÔNG CÁP</i>
                    @endif
                    @if($item->notes)
                    <br>                    
                    <span style="color:#ff9d00">{!! nl2br($item->notes) !!}</span>
                    @endif   
                    
                      @php
                      $countUNC = $item->payment->count();
                     // dd($countUNC);
                      $strpayment = "";
                      $tong_payment = 0;
                      foreach($item->payment as $p){                        
                        $strpayment .= "+". number_format($p->amount)." - ".date('d/m', strtotime($p->pay_date));                    
                        if($p->type == 1){
                          $strpayment .= " - UNC"."<br>";
                        }else{
                          $strpayment .= " - auto"."<br>";
                        }
                        $tong_payment += $p->amount;                    
                      }
                      if($countUNC > 0)
                      $strpayment .= "Tổng: ".number_format($tong_payment);
                    @endphp 
                    
                    <p style="clear: both; text-align: right;margin-bottom: 0px; padding-top:0px">                        
                        @if($item->nguoi_thu_tien == 4)
                        <button class="btn btn-sm btn-success btnThuTien" data-id="{{ $item->id }}" data-name="{{ $item->name }}" href="{{ route('report.thu-tien') }}?id={{ $item->id }}">Đã thu tiền</button>
                        @endif
                        <button class="btn btn-sm btn-warning btnUnc" title="{!! $strpayment !!}" data-toggle="tooltip" data-html="true" data-id="{{ $item->id }}">{{ $countUNC }} UNC</button>                       
                      
                        <a class="btn btn-sm btn-info" target="_blank" href="{{ route('history.booking', ['id' => $item->id]) }}">Lịch sử</a>    
                    </p>
                    
                   </li>              
        @endforeach
      @else
      <li>
        <p>Không có dữ liệu.</p>
      </li>
      @endif
      </ul>
    
    </div>
    <div style="text-align:center">
      {{ $items->appends( $arrSearch )->links() }}
    </div><!--phan trang-->
  </div><!--table-responsive--> 
</div><!--body-->