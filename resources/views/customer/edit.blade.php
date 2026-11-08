@extends('layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Khách hàng: <span style="color:#e8a23e">{{ $detail->name }}</span>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route( 'dashboard' ) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('customer.index') }}">Khách hàng</a></li>
            <li class="active">Update</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <a class="btn btn-default btn-sm" href="{{ route('customer.index') }}"
            style="margin-bottom:5px">Back</a>
        <form role="form" method="POST" action="{{ route('customer.update') }}" id="dataForm">
            <div class="row">
                <!-- left column -->
                <input name="id" value="{{ $detail->id }}" type="hidden">
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            Chỉnh sửa
                        </div>
                        <!-- /.box-header -->
                        {!! csrf_field() !!}

                        <div class="box-body">
                            @if(Session::has('message'))
                            <p class="alert alert-info">{{ Session::get('message') }}</p>
                            @endif
                            @if (count($errors) > 0)
                            <div class="alerts alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="form-group">

                                <label>Họ tên</label>
                                <input type="text" class="form-control" name="name" id="name" readonly="readonly"
                                    value="{{ $detail->name }}">
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" class="form-control" name="phone" id="phone" readonly="readonly"
                                    value="{{ $detail->phone }}">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" id="email"
                                    value="{{ $detail->email }}">
                            </div>
                            <div class="form-group">
                                <label>Địa chỉ</label>
                                <input type="text" class="form-control" name="address" id="address"
                                    value="{{ $detail->address }}">
                            </div>
                            <div class="form-group">
                                <label>Ngày sinh (d/m/y)</label>
                                <input type="text" class="form-control datepicker" name="birthday" id="birthday" value="{{ old('birthday') }}" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label>Ngày đi</label>
                                <input type="text" class="form-control" 
                                    value="{{ date('d-m-Y', strtotime($detail->use_date)) }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Ngày book tour</label>
                                <input type="text" class="form-control" 
                                    value="{{ date('d-m-Y H:i', strtotime($detail->created_at)) }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Code</label>
                                <input type="text" class="form-control" name="code" id="code" 
                                    value="{{ $detail->code }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="2" {{ $detail->status == 2 ? "selected" : "" }}>Khóa</option>
                                    <option value="1" {{ $detail->status == 1 ? "selected" : "" }}>Mở</option>
                                </select>
                            </div>

                        </div>
                        
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm"
                                href="{{ route('customer.index')}}">Cancel</a>
                        </div>

                    </div>
                    <!-- /.box -->

                </div>
        </form>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
@stop
