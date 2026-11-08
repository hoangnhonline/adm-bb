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
      <li><a href="{{ route('ung-luong.index') }}">Ứng lương</a></li>
      <li class="active">Update</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('ung-luong.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('ung-luong.update') }}" id="dataForm">
      <input type="hidden" name="id" value="{{ $detail->id }}">
    <div class="row">
      <!-- left column -->

      <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Update</h3>
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
            
             
              <div class="row">
                <div class="form-group col-xs-12">
                    <label for="status">Status</label>
                    <select class="form-control select2" name="status" id="status">                    
                      <option value="1"  {{ old('status', $detail->status) == 1 ? "selected" : "" }}>Chưa cấn trừ</option>
                      <option value="2"  {{  old('status', $detail->status) == 2 ? "selected" : "" }}>Đã cấn trừ</option>                    
                    </select>
                  </div>
                  <div class="form-group col-xs-6">
                      <label>Nhân viên<span class="red-star">*</span></label>
                      <select class="form-control select2" id="partner_id" name="partner_id">     
                        <option value="">--Chọn--</option>      
                        @foreach($partnerList as $cate)
                        <option value="{{ $cate->id }}" {{ old('partner_id', $detail->partner_id) == $cate->id ? "selected" : "" }}>
                          {{ $cate->name }}
                        </option>
                        @endforeach
                      </select>
                  </div>
                    <div class="form-group col-xs-6 col-md-6">
                @php
                    if($detail->date_use){
                        $date_use = old('date_use', date('d/m/Y', strtotime($detail->date_use)));
                    }else{
                        $date_use = old('date_use');
                    }
                  @endphp
                <label for="email">Ngày</label>
                 <input type="text" name="date_use" class="form-control datepicker" value="{{ old('date_use', $date_use) }}" autocomplete="off">
                </div> 
              </div>      
                   
               <div class="row">                          
                              
                                    
                  <div class="form-group col-xs-6">
                    <label for="total_money">Số tiền</label>
                    <input type="text" name="total_money" class="form-control number total" autocomplete="off" placeholder="Số tiền" value="{{ old('total_money', $detail->total_money) }}">
                  </div>
                   <div class="form-group col-xs-6">
                    <label for="notes">Notes</label>
                    <input type="text" name="notes" class="form-control" autocomplete="off" placeholder="Notes" value="{{ old('notes', $detail->notes) }}">
                  </div>
            
               </div>
               <div class="row">
               
                <div class="form-group col-md-12" >
                      <label>Payer <span class="red-star">*</span></label>
                      <select class="form-control select2" name="nguoi_chi" id="nguoi_chi">
                        @foreach($collecterList as $payer)
                        <option value="{{ $payer->id }}" {{ old('nguoi_chi', $detail->nguoi_chi) == $payer->id ? "selected" : "" }}>{{ $payer->name }}</option>
                        @endforeach                       
                      </select>
                  </div>            
                             
                 
                </div>           
                
                
            
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('ung-luong.index')}}">Cancel</a>
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

@stop