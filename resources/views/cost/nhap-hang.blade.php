@extends('layout')
@section('content')
<div class="content-wrapper">
<section class="content-header">
  <h1>Thống kê nhập hàng theo nguyên liệu</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route('cost.index') }}">Cost</a></li>
    <li class="active">Nhập hàng</li>
  </ol>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Bộ lọc</h3></div>
        <div class="panel-body">
          <form class="form-inline" role="form" method="GET" action="{{ route('cost.nhap-hang') }}" id="searchForm">
            <div class="form-group">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_from" placeholder="Từ ngày" value="{{ $arrSearch['use_date_from'] }}" style="width:110px">
            </div>
            <div class="form-group">
              <input type="text" class="form-control datepicker" autocomplete="off" name="use_date_to" placeholder="Đến ngày" value="{{ $arrSearch['use_date_to'] }}" style="width:110px">
            </div>
            <div class="form-group">
              <select class="form-control select2" name="cate_id" style="min-width:200px">
                <option value="">--Nhóm nguyên liệu--</option>
                @include('cost._cate-options', ['selectedCate' => $arrSearch['cate_id']])
              </select>
            </div>
            <div class="form-group">
              <select class="form-control select2" name="ncc_id" style="min-width:160px">
                <option value="">--Nhà cung cấp--</option>
                @foreach($nccList as $ncc)
                  <option value="{{ $ncc->id }}" {{ $arrSearch['ncc_id'] == $ncc->id ? "selected" : "" }}>{{ $ncc->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <select class="form-control select2" name="ingredient_id" style="min-width:180px">
                <option value="">--Nguyên liệu (xem chi tiết)--</option>
                <option value="none" {{ $arrSearch['ingredient_id'] == 'none' ? "selected" : "" }}>(Chưa gắn nguyên liệu)</option>
                @foreach($ingredientList as $ing)
                  <option value="{{ $ing->id }}" {{ (string) $arrSearch['ingredient_id'] === (string) $ing->id ? "selected" : "" }}>{{ $ing->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              &nbsp;<input type="checkbox" name="include_review" id="include_review" value="1" {{ $arrSearch['include_review'] ? "checked" : "" }}>
              <label for="include_review">Tính cả dòng cần kiểm tra</label>
            </div>
            <button type="submit" class="btn btn-info btn-sm" style="margin-top:-5px">Xem</button>
            <div>
              @foreach($branchList as $beach)
                <div class="form-group">
                  &nbsp;&nbsp;&nbsp;<input type="checkbox" name="branch_ids[]" id="branch_{{ $beach->id }}" value="{{ $beach->id }}" {{ in_array($beach->id, $arrSearch['branch_ids']) || empty($arrSearch['branch_ids']) ? "checked" : "" }}>
                  <label for="branch_{{ $beach->id }}">{{ $beach->name }}</label>
                </div>
              @endforeach
            </div>
          </form>
        </div>
      </div>

      {{-- Độ phủ dữ liệu --}}
      @php
        $covBills = (int) ($coverage->bills ?? 0);
        $covMoney = (float) ($coverage->money ?? 0);
        $covPct = $covMoney > 0 ? round(($coverage->money_detail ?? 0) / $covMoney * 100) : 0;
      @endphp
      <div class="row">
        <div class="col-md-3 col-xs-6">
          <div class="small-box bg-aqua"><div class="inner">
            <h3 style="font-size:24px">{{ number_format($totalAmount) }}</h3>
            <p>Tiền hàng có chi tiết (theo bộ lọc)</p>
          </div></div>
        </div>
        <div class="col-md-3 col-xs-6">
          <div class="small-box {{ $covPct >= 80 ? 'bg-green' : 'bg-yellow' }}"><div class="inner">
            <h3 style="font-size:24px">{{ $covPct }}%</h3>
            <p>Độ phủ: {{ number_format($coverage->bills_detail ?? 0) }}/{{ number_format($covBills) }} bill giá vốn có chi tiết</p>
          </div></div>
        </div>
        <div class="col-md-3 col-xs-6">
          <div class="small-box bg-red"><div class="inner">
            <h3 style="font-size:24px">{{ number_format($coverage->bills_check ?? 0) }}</h3>
            <p><a href="{{ route('cost.index', ['detail_check' => 1, 'time_type' => 2, 'use_date_from' => $arrSearch['use_date_from'], 'use_date_to' => $arrSearch['use_date_to']]) }}" style="color:#fff;text-decoration:underline">Bill lệch tiền chi tiết</a></p>
          </div></div>
        </div>
        <div class="col-md-3 col-xs-6">
          <div class="small-box bg-gray"><div class="inner">
            <h3 style="font-size:24px">{{ number_format($reviewRows) }}</h3>
            <p>Dòng cần kiểm tra {{ $arrSearch['include_review'] ? '(đang tính vào)' : '(chưa tính vào)' }}</p>
          </div></div>
        </div>
      </div>

      {{-- Tổng hợp theo nguyên liệu --}}
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Tổng hợp theo nguyên liệu ({{ $arrSearch['use_date_from'] }} – {{ $arrSearch['use_date_to'] }})</h3>
        </div>
        <div class="box-body table-responsive">
          <table class="table table-bordered table-hover table-condensed">
            <thead>
              <tr style="background:#f4f4f4">
                <th style="width:1%">#</th>
                <th>Nguyên liệu</th>
                <th>Nhóm</th>
                <th class="text-right">Số lượng</th>
                <th>ĐVT</th>
                <th class="text-right">Đơn giá TB</th>
                <th class="text-right">Tiền</th>
                <th class="text-right">% tiền</th>
                <th class="text-center">Số bill</th>
                <th class="text-center">Dòng cần KT</th>
              </tr>
            </thead>
            <tbody>
              @forelse($items as $k => $it)
                @php
                  $avg = ($it->qty_has_amount ?? 0) > 0 ? $it->amount_has_qty / $it->qty_has_amount : null;
                  $ingKey = $it->ingredient_id ?: 'none';
                @endphp
                <tr>
                  <td>{{ $k + 1 }}</td>
                  <td>
                    <a href="{{ route('cost.nhap-hang', array_merge($arrSearch, ['ingredient_id' => $ingKey])) }}">
                      {{ $it->name ?: '(Chưa gắn nguyên liệu)' }}
                    </a>
                  </td>
                  <td>{{ $it->cost_type_id && isset($cateArr[$it->cost_type_id]) ? $cateArr[$it->cost_type_id] : '' }}</td>
                  <td class="text-right">{{ $it->qty !== null ? rtrim(rtrim(number_format($it->qty, 2), '0'), '.') : '' }}</td>
                  <td>{{ $it->unit }}</td>
                  <td class="text-right">{{ $avg !== null ? number_format($avg) : '' }}</td>
                  <td class="text-right"><b>{{ number_format($it->amount) }}</b></td>
                  <td class="text-right">{{ $totalAmount > 0 ? number_format($it->amount / $totalAmount * 100, 1) : 0 }}%</td>
                  <td class="text-center">{{ $it->bills }}</td>
                  <td class="text-center">{!! $it->review_lines > 0 ? '<span class="label label-warning">'.$it->review_lines.'</span>' : '' !!}</td>
                </tr>
              @empty
                <tr><td colspan="10" class="text-center">Chưa có dữ liệu chi tiết hàng trong kỳ này.</td></tr>
              @endforelse
            </tbody>
            @if($items->count())
            <tfoot>
              <tr style="background:#f4f4f4">
                <th colspan="6" class="text-right">Tổng</th>
                <th class="text-right">{{ number_format($totalAmount) }}</th>
                <th colspan="3"></th>
              </tr>
            </tfoot>
            @endif
          </table>
          <p class="text-muted" style="margin:0"><i>Số lượng và đơn giá TB chỉ cộng các dòng cùng ĐVT chuẩn của nguyên liệu. Bấm vào tên nguyên liệu để xem từng lần nhập.</i></p>
        </div>
      </div>

      {{-- Chi tiết từng lần nhập của 1 nguyên liệu --}}
      @if($arrSearch['ingredient_id'])
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Chi tiết từng lần nhập:
            {{ $arrSearch['ingredient_id'] == 'none' ? '(Chưa gắn nguyên liệu)' : optional($ingredientList->firstWhere('id', (int) $arrSearch['ingredient_id']))->name }}
          </h3>
        </div>
        <div class="box-body table-responsive">
          <table class="table table-bordered table-condensed">
            <thead>
              <tr style="background:#f4f4f4">
                <th>Ngày</th>
                <th>Bill</th>
                <th>Chi nhánh</th>
                <th>NCC</th>
                <th class="text-right">SL</th>
                <th>ĐVT</th>
                <th class="text-right">Đơn giá</th>
                <th class="text-right">Thành tiền</th>
                <th>Ghi chú</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @php $lineSum = 0; @endphp
              @forelse($lines as $ln)
                @php $lineSum += (int) $ln->amount; @endphp
                <tr>
                  <td style="white-space:nowrap">{{ date('d/m/Y', strtotime($ln->date_use)) }}</td>
                  <td><a href="{{ route('cost.edit', $ln->cost_id) }}" target="_blank">CP{{ $ln->cost_id }}</a>
                    @if($ln->detail_check) <span class="label label-danger" title="Bill lệch tiền chi tiết">Lệch</span> @endif
                  </td>
                  <td>{{ $beachArr[$ln->branch_id] ?? '' }}</td>
                  <td>{{ $nccArr[$ln->ncc_id] ?? '' }}</td>
                  <td class="text-right">{{ $ln->quantity !== null ? rtrim(rtrim(number_format($ln->quantity, 3), '0'), '.') : '' }}</td>
                  <td>{{ $ln->unit ?: $ln->ingredient_unit }}</td>
                  <td class="text-right">{{ $ln->unit_price !== null ? number_format($ln->unit_price) : '' }}</td>
                  <td class="text-right">{{ $ln->amount !== null ? number_format($ln->amount) : '' }}</td>
                  <td>{{ $ln->notes }}</td>
                  <td>
                    @if($ln->need_review) <span class="label label-warning">Cần KT</span> @endif
                    @if($ln->source == 2) <span class="label label-default" title="Bóc tách từ ghi chú cũ">Ghi chú cũ</span> @endif
                    @if($ln->source == 3) <span class="label label-info" title="Đọc từ ảnh hóa đơn">Đọc từ ảnh</span> @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="10" class="text-center">Không có dòng nào.</td></tr>
              @endforelse
            </tbody>
            @if($lines->count())
            <tfoot>
              <tr style="background:#f4f4f4">
                <th colspan="7" class="text-right">Tổng</th>
                <th class="text-right">{{ number_format($lineSum) }}</th>
                <th colspan="2"></th>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
      </div>
      @endif

    </div>
  </div>
</section>
</div>
@stop
@section('js')
<script type="text/javascript">
  $(document).ready(function () {
    $('#searchForm input[name="branch_ids[]"], #include_review').change(function () {
      $('#searchForm').submit();
    });
  });
</script>
@stop
