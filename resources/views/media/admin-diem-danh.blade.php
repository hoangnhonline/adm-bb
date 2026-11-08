@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      CHẤM CÔNG MEDIA
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>     
      <li class="active">Chấm công</li>
    </ol>
  </section>
<style type="text/css">
  .co-di {
    background-color: #3c8dbc;
    color: #FFF;
  }
  .co-di-chua-lam{
    background-color: red;
    color: #FFF
  }
</style>
  <!-- Main content -->
  <section class="content">    
    <form role="form" method="POST" action="{{ route('media.store') }}" id="dataForm">
    <div class="row">
      <!-- left column -->

      <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Chấm công</h3>
          </div>
          <!-- /.box-header -->               
            {!! csrf_field() !!}

            <div class="box-body">
              @if (count($errors) > 0)
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif 
              
              @if(Auth::user()->role == 1)
              <div class="form-group">
                <label>THỢ</label>
                <select class="form-control" name="user_id" id="user_id">
                  <option value="">--Chọn--</option>
                  @foreach($userList as $u)
                  <option value="{{ $u->id }}" {{ isset($user_id) && $user_id == $u->id ? "selected" : "" }}>{{ $u->name }}</option>
                  @endforeach
                </select>
              </div>
              @endif
              <div class="form-group">
                <label>THÁNG</label>
                <select class="form-control" id="month_change">
                  <option value="">--Chọn--</option>
                  @for($i = 1; $i <=12; $i++)
                  <option value="{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}" {{ $month == $i ? "selected" : "" }}>{{ str_pad($i, 2, "0", STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group">
                <label>NĂM</label>
                <select class="form-control" id="year_change">
                  <option value="">--Chọn--</option>
                  <option value="2021" {{ $year== '2021' ? "selected" : "" }}>2021</option>
                  <option value="2022" {{ $year== '2022' ? "selected" : "" }}>2022</option>
                </select>
              </div>
              @php
              $list=array();              
              $year = date('Y');
              $listDay = [];
              for($d=1; $d<=31; $d++)
              {
                  $time=mktime(12, 0, 0, $month, $d, $year);          
                  if (date('m', $time)==$month)       
                      $listDay[]=date('d', $time);
              }
              @endphp
              <div style="text-align: left;margin-top: 10px;">
                <label style="font-size: 20px">THÁNG {{ $month }}/{{ $year }} @if($user_id) - <span style="color: red">Lương : {{ number_format($totalLuong) }}</span> @endif</label>
             <p class="clearfix" style="margin-top: 10px"></p>
                <?php $i = 0; ?>
              @foreach($listDay as $day)
              <?php $i++; ?>
              @php
             // dd($day);
             $link_anh = $link_flycam = '';
              if(in_array($day, array_keys($mediaDay))){
                if($mediaDay[$day] == null){
                  $class = 'co-di-chua-lam';
                }else{
                  $class = 'co-di';
                  $link_anh = isset($detailArr[$day][1]) ? $detailArr[$day][1]->link : '';
                  $link_flycam = isset($detailArr[$day][2]) ? $detailArr[$day][2]->link : '';
                }
                $di = 1;
              }else{
                $class = "ko_di";
                $di = 0;
              }      
              $tour_id = isset($cateArr[$day]) ? $cateArr[$day] : 1;
              $area_id = isset($areaArr[$day]) ? $areaArr[$day] : 1;
              @endphp
              <span class="cham-cong {{ $class }}" 

               data-month="{{ $month }}" data-di="{{ $di }}" data-year="{{ $year }}" data-value="{{ $day }}" style="padding: 10px; border: 1px solid #CCC; cursor: pointer;"

               data-anh="{{ $link_anh }}" data-flycam="{{ $link_flycam }}" data-cate="{{ $tour_id }}" data-area="{{ $area_id }}"
               >{{ $day }}</span>              
          
              @if($i%7 == 0)
              <hr>
              @endif
              @endforeach
              </div>
            </div>           
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('media.index')}}">Cancel</a>
            </div>
            
        </div>
        <!-- /.box -->     

      </div>
      <div class="col-md-7">
             
    </div>
    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<!-- Button trigger modal -->
<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 style="font-weight: bold;color: #06b7a4; text-transform: uppercase;" class="modal-title" id="exampleModalLabel">NGÀY <span id="ngay_show"></span> - <span id="name_show" style="color: black; font-style: italic">{{ Auth::user()->name }}</span></h5>        
      </div>
      <form id="formAjax">
      <div class="modal-body" >        
        <input type="hidden" name="user_id" id="user_id_show" value="">
        <input type="hidden" name="day" id="day" value="">
        <input type="hidden" name="month" id="month" value="">
        <input type="hidden" name="year" id="year" value="">
        <div class="row">          
              <div id="div_da_di">
                <div class="row" style="margin: 20px 0px">
                <div class="form-group">
                    <div class="form-check col-md-3 col-xs-6">
                      <input type="radio" class="form-check-input" id="area_id1" name="area_id" value="1" checked>
                      <label class="form-check-label" for="area_id1">Tour đảo</label>
                    </div>
                    <div class="form-check col-md-3 col-xs-6">
                      <input type="radio" class="form-check-input"  id="area_id2" name="area_id" value="2">
                      <label class="form-check-label" for="area_id2">Grand World</label>
                    </div>
                    <div class="form-check col-md-3 col-xs-6">
                      <input type="radio" value="3"  id="area_id3"  name="area_id" class="form-check-input">
                      <label class="form-check-label" for="area_id3">Rạch Vẹm</label>
                    </div>
                    <div class="form-check col-md-3 col-xs-6">
                      <input type="radio"  id="area_id4" value="4"  name="area_id" class="form-check-input">
                      <label class="form-check-label" for="area_id4">Hòn Thơm</label>
                    </div>
                    <div class="form-check col-md-3 col-xs-6">
                      <input type="radio" class="form-check-input"  id="area_id5" name="area_id" value="5">
                      <label class="form-check-label" for="area_id5">Bãi Sao - 2 Đảo</label>
                    </div>
                </div>
                </div>
                <div class="form-group col-md-12" >                  
                  <label>LINK ẢNH<span class="red-star">*</span></label>
                  <input type="text" class="form-control" autocomplete="off" name="link_anh" id="link_anh" value="{{ old('link_anh') }}">
                </div> 
                <div class="form-group col-md-12" >                  
                  <label>LINK FLYCAM (nếu có)</label>
                  <input type="text" class="form-control" autocomplete="off" name="link_flycam" id="link_flycam" value="{{ old('link_flycam') }}">
                </div> 
            </div>
        </div>
      </div>
      </form>
      <div class="modal-footer">       
        <button type="button" id="btnLuu" class="btn btn-primary">Save</button>
         <button type="button"  class="btn btn-secondary" data-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>
@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function(){
    $('#user_id').change(function(){
      location.href='{{ route('media.diem-danh') }}' + '?user_id=' + $(this).val() + '&month=' + $('#month_change').val();
    });    
    $('#year_change, #month_change').change(function(){
      var str ='{{ route('media.diem-danh') }}' + '?month=' + $('#month_change').val() + '&year=' + $('#year_change').val();
      @if(Auth::user()->role == 1)
      str+= '&user_id=' + $('#user_id').val();
      @endif
      location.href=str;
    });
    $('#btnLuu').click(function(){

      var link_flycam = $('#link_flycam').val();
      var link_anh = $('#link_anh').val();      
      $(this).attr('disabled', 'disabled');
      $.ajax({
        url : "{{ route('media.ajax-store') }}",
        type : 'GET',
        data : $('#formAjax').serialize(),
        success: function(data){
          console.log(data);
          alert('Save thành công!');
          window.location.reload();
        }
      });
    });
    $('.cham-cong').click(function(){

      if($(this).data('di') == 0){
        $('#div_di').show();
      }else{
        $('#div_di').hide();
      }
      @if(Auth::user()->role == 1)
      if($('#user_id').val() > 0){
        $('#user_id_show').val($('#user_id').val());
        $('#name_show').html($("#user_id :selected").text());
      }else{
        alert('Vui lòng chọn THỢ!'); return false;
      }
      @endif
      var ngay = $(this).data('value');
      var month = $(this).data('month');
      var year = $(this).data('year');
      $('#ngay_show').html(ngay);
      $('#day').val(ngay);
      $('#month').val(month);
      $('#year').val(year);
      $('#tour_id').val($(this).data('cate'));
      $('#link_anh').val($(this).data('anh'));
      var area_id = $(this).data('area');
      $('#area_id'+area_id).attr('checked', 'checked');
      $('#link_flycam').val($(this).data('flycam'));
      $('#detailModal').modal('show');
    });
  });
</script>
@stop