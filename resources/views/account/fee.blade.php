@extends('layout')
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
    Phí giao dịch thành viên : {{ $detail->email }}
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li><a href="{{ route('account.index') }}">Phí giao dịch</a></li>
      <li class="active">Update</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <a class="btn btn-default btn-sm" href="{{ route('account.index') }}" style="margin-bottom:5px">Back</a>
    <form role="form" method="POST" action="{{ route('account.updateFee') }}" id="formData">
    <div class="row">
      <!-- left column -->
      <input type="hidden" name="id" value="{{ $detail->id }}"> 
      <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            Update phí giao dịch
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
                 <!-- text input -->
                
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <thead>
                    <tr>
                      <th>Loại thẻ</th>
                      @foreach($menhgiaList as $menhgia)
                      <th>{!! $menhgia->name !!}</th>
                      @endforeach          
                    </tr>
                    </thead>
                    @foreach($loaiThe as $loai)
                    <tr>
                      <td style="font-weight: bold">{!! $loai->name !!}</td>
                      @foreach($menhgiaList as $menhgia)
                      <td>
                        <input type="text" class="form-control" name="feeArr[{{ $loai->id }}][{{ $menhgia->id }}]" value="{{ $feeArr[$loai->id][$menhgia->id] }}">
                      </td>
                      @endforeach
                    </tr>
                    @endforeach
                  </table>
                  </div>
            </div>
            <div class="box-footer">             
              <button type="submit" class="btn btn-primary btn-sm" id="btnSave">Save</button>
              <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" href="{{ route('account.index')}}">Cancel</a>
            </div>
            
        </div>
        <!-- /.box -->     

      </div>
      
      <!--/.col (left) -->      
    </div>
    </form>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
@stop
@section('js')
<script type="text/javascript">
     $(document).ready(function(){
      $('#formData').submit(function(){
        $('#btnSave').html('<i class="fa fa-spinner fa-spin">').attr('disabled', 'disabled');
      });      
    });
</script>
@stop
