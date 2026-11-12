{{-- JS cho bảng "Chi tiết hàng" (cost/_details.blade.php) --}}
<script type="text/javascript">
(function ($) {
  var $box = $('#cost-details-box');
  if (!$box.length) return;
  var $tbody = $('#tblCostDetail tbody');
  var $total = $('input[name="total_money"]');
  var rowSeq = 1000;

  function norm(s) {
    s = (s || '').toString().toLowerCase();
    if (s.normalize) s = s.normalize('NFD').replace(/[̀-ͯ]/g, '');
    return s.replace(/đ/g, 'd');
  }
  function money(v) {
    v = (v === undefined || v === null) ? '' : String(v).replace(/[^0-9\-]/g, '');
    return v === '' || v === '-' ? null : parseInt(v, 10);
  }
  function qty(v) {
    v = $.trim(String(v || ''));
    if (v === '') return null;
    v = (v.indexOf(',') > -1 && v.indexOf('.') === -1) ? v.replace(',', '.') : v.replace(/,/g, '');
    var n = parseFloat(v);
    return isNaN(n) ? null : n;
  }
  function fmt(n) {
    return (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }
  function matcher(params, data) {
    if ($.trim(params.term) === '') return data;
    var hay = norm(data.text + ' ' + ($(data.element).data('aliases') || ''));
    return hay.indexOf(norm(params.term)) > -1 ? data : null;
  }
  function markReviewed($tr) {
    if ($tr.data('need-review') == 1) {
      $tr.data('need-review', 0);
      $tr.find('.cd-need-review').val(0);
      $tr.find('.cd-review-label').remove();
    }
  }
  function initRow($tr) {
    $tr.find('.cd-ingredient').select2({ matcher: matcher, width: '100%' });
    $tr.find('.cd-money').number(true, 0);
  }
  function addRow() {
    rowSeq++;
    var html = $('#tplCostDetailRow').html().replace(/__i__/g, 'n' + rowSeq);
    var $tr = $(html);
    $tbody.append($tr);
    initRow($tr);
    return $tr;
  }
  function recalc() {
    var sum = 0, hasAmount = false;
    $tbody.find('.cd-amount').each(function () {
      var v = money($(this).val());
      if (v !== null) { sum += v; hasAmount = true; }
    });
    var bill = money($total.val()) || 0;
    $('#cdSum').text(fmt(sum));
    $('#cdBill').text(fmt(bill));
    var $diff = $('#cdDiff');
    if (!hasAmount) {
      $diff.html('');
      $('#btnUseDetailTotal').hide();
    } else if (sum === bill) {
      $diff.html('<span class="text-green"><i class="fa fa-check"></i> Khớp</span>');
      $('#btnUseDetailTotal').hide();
    } else {
      $diff.html('<span class="text-red"><i class="fa fa-warning"></i> Lệch ' + fmt(sum - bill) +
        ' – vẫn lưu được, bill sẽ được đánh dấu "cần kiểm tra"</span>');
      $('#btnUseDetailTotal').show();
    }
  }

  $(document).ready(function () {
    $tbody.find('.cd-row').each(function () { initRow($(this)); });
    if ($tbody.find('.cd-row').length === 0) addRow();
    recalc();

    $('#btnAddCostDetail').on('click', function () {
      addRow().find('.cd-ingredient').select2('open');
    });
    $tbody.on('click', '.cd-remove', function () {
      $(this).closest('tr').remove();
      if ($tbody.find('.cd-row').length === 0) addRow();
      recalc();
    });
    $tbody.on('change', '.cd-ingredient', function () {
      var $tr = $(this).closest('tr');
      var unit = $(this).find('option:selected').data('unit') || '';
      if ($.trim($tr.find('.cd-unit').val()) === '') $tr.find('.cd-unit').val(unit);
      markReviewed($tr);
    });
    $tbody.on('keyup change', '.cd-qty, .cd-price', function () {
      var $tr = $(this).closest('tr');
      var q = qty($tr.find('.cd-qty').val()), p = money($tr.find('.cd-price').val());
      if (q !== null && p !== null) $tr.find('.cd-amount').val(Math.round(q * p));
      markReviewed($tr);
      recalc();
    });
    $tbody.on('keyup change', '.cd-amount, .cd-unit, .cd-notes', function () {
      markReviewed($(this).closest('tr'));
      recalc();
    });
    $total.on('keyup change blur', recalc);
    $('#btnUseDetailTotal').on('click', function () {
      var sum = money($('#cdSum').text()) || 0;
      $total.val(sum).trigger('change');
      recalc();
    });
  });
})(jQuery);
</script>
