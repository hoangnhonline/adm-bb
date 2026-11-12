{{--
  Bảng "Chi tiết hàng" của 1 bill (không bắt buộc).
  Biến: $ingredientList, $details (tùy chọn: collection CostDetail hoặc mảng)
  Dòng có need_review = 1 (dữ liệu bóc tách từ ghi chú cũ) hiện nhãn "Cần KT";
  sửa bất kỳ ô nào của dòng đó -> coi như đã kiểm tra.
--}}
@php
  $cdFmtQty = function ($v) {
      if ($v === null || $v === '') return '';
      if (!is_numeric($v)) return $v;
      return rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');
  };
  $cdMoney = function ($v) {
      if ($v === null || $v === '') return '';
      $v = preg_replace('/[^0-9\-]/', '', (string) $v);
      return $v === '' ? '' : (int) $v;
  };
  $cdRows = old('details');
  if ($cdRows === null) {
      $cdRows = [];
      foreach (($details ?? []) as $d) {
          $d = is_array($d) ? $d : $d->toArray();
          $cdRows[] = $d;
      }
  }
  $cdRows = array_values($cdRows);
@endphp
<div class="form-group col-xs-12" id="cost-details-box">
  <input type="hidden" name="details_submitted" value="1">
  <label>Chi tiết hàng <small style="font-weight:normal;color:#888">(không bắt buộc – nhập để thống kê theo từng nguyên liệu)</small></label>
  <div class="table-responsive">
    <table class="table table-bordered table-condensed" id="tblCostDetail" style="margin-bottom:5px;min-width:760px">
      <thead>
        <tr style="background:#f4f4f4">
          <th style="width:28%">Nguyên liệu</th>
          <th style="width:9%">SL</th>
          <th style="width:9%">ĐVT</th>
          <th style="width:13%" class="text-right">Đơn giá</th>
          <th style="width:14%" class="text-right">Thành tiền</th>
          <th>Ghi chú</th>
          <th style="width:1%"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($cdRows as $i => $row)
          @include('cost._details-row', ['i' => $i, 'row' => $row])
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="7">
            <button type="button" class="btn btn-default btn-sm" id="btnAddCostDetail"><i class="fa fa-plus"></i> Thêm dòng</button>
            <span style="margin-left:15px">Tổng chi tiết: <b id="cdSum">0</b></span>
            <span style="margin-left:15px">Total bill: <b id="cdBill">0</b></span>
            <span style="margin-left:15px" id="cdDiff"></span>
            <button type="button" class="btn btn-warning btn-xs" id="btnUseDetailTotal" style="margin-left:10px;display:none">Lấy tổng chi tiết làm Total</button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script type="text/template" id="tplCostDetailRow">
  @include('cost._details-row', ['i' => '__i__', 'row' => []])
</script>
