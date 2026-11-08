@extends('layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Khách hàng
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route( 'dashboard' ) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('customer.index') }}">Hoàn tiền</a></li>
            <li class="active">List</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @if(Session::has('message'))
                <p class="alert alert-info">{{ Session::get('message') }}</p>
                @endif
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Bộ lọc</h3>
                    </div>
                    <div class="panel-body">
                        <form class="form-inline" role="form" method="GET" action="{{ route('customer.index') }}" id="frmContact">
                            <div class="form-group">              
                                <select class="form-control" name="status" id="status">
                                    <option value="">-- Status-- </option>
                                    <option value="1" {{ $status == 1 ? "selected" : "" }}>Mở</option>
                                    <option value="2" {{ $status == 2 ? "selected" : "" }}>Khóa</option>
                                </select>
                            </div> 
                            <div class="form-group">
                                <label for="phone">&nbsp;&nbsp;Số điện thoại</label>
                                <input type="text" class="form-control" name="phone" value="{{ $phone }}">
                            </div>
                            <div class="form-group">
                                <label for="code">&nbsp;&nbsp;Mã Code</label>
                                <input type="text" class="form-control" name="code" value="{{ $code }}">
                            </div>
                            <button type="submit" class="btn btn-default btn-sm">Search</button>
                        </form>
                    </div>
                </div>
                <div class="box">

                    <div class="box-header with-border">
                        <h3 class="box-title">List ( <span class="value">{{ $items->total() }} Khách hàng
                                )</span></h3>
                    </div>

                    <!-- /.box-header -->
                    <div class="box-body">
                        <a href="{{ route('customer.export') }}" class="btn btn-info btn-sm"
                            style="margin-bottom:5px;float:right" target="_blank">Export</a>
                        <div style="text-align:center">
                            {{ $items->appends( ['phone' => $phone] )->links() }}
                        </div>
                        <table class="table table-bordered" id="table-list-data">
                            <tr>
                                <th style="width: 1%">#</th>
                                <th>Họ tên</th>
                                <th>Số điện thoại</th>
                                <th>Email</th>
                                <th>Code</th>
                                <th>Ngày đi</th>
                                <th>Status</th>
                                <th width="1%;white-space:nowrap">Thao tác</th>
                            </tr>
                            <tbody>
                                @if( $items->count() > 0 )
                                <?php $i = 0; ?>
                                @foreach( $items as $item )
                                <?php $i ++; ?>
                                <tr id="row-{{ $item->id }}">
                                    <td><span class="order">{{ $i }}</span></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->phone }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td>
                                        @if ( (isset($item->code)) && ($item->code != ''))
                                        {{ $item->code }}
                                        @else
                                        Chờ cấp
                                        @endif

                                    </td>
                                    <td style="white-space:nowrap">{{ date('d-m-Y', strtotime($item->use_date)) }}
                                    </td>
                                    <td>{{ $item->status == 1 ? "Mở"  : "Khóa" }}</td>
                                    <td style="white-space:nowrap;#e8a23e">
                                        <a href="{{ route( 'customer.edit', [ 'id' => $item->id ]) }}"
                                            class="btn btn-warning btn-sm"><span
                                                class="glyphicon glyphicon-pencil"></span></a>

                                        <a onclick="return callDelete('{{ $item->title }}','{{ route( 'customer.destroy', [ 'id' => $item->id ]) }}');"
                                            class="btn btn-danger btn-sm"><span
                                                class="glyphicon glyphicon-trash"></span></a>

                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7">Không có dữ liệu.</td>
                                </tr>
                                @endif

                            </tbody>
                        </table>
                        <div style="text-align:center">
                            {{ $items->appends( ['phone' => $phone] )->links() }}
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
@section('js')

<script type="text/javascript">
    function callDelete(name, url) {
        swal({
            title: 'Bạn muốn xóa "' + name + '"?',
            text: "Dữ liệu sẽ không thể phục hồi.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then(function () {
            location.href = url;
        })
        return flag;
    }
    $(document).ready(function(){
        $('#status').change(function(){
            $('#frmContact').submit();
        });
    });
</script>
@stop
