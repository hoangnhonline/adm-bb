@php
  $bk = $detail ?? null;
  $dtId = old('doi_tac_id', $bk->doi_tac_id ?? '');
  $dtCk = old('co_chiet_khau', $bk->co_chiet_khau ?? 0);
  $dtNo = old('ghi_no', $bk->ghi_no ?? 0);
  $dtTypes = \App\Models\Partner::$doiTacTypes;
@endphp
<input type="hidden" name="doi_tac_form" value="1">
<div class="row" id="doi-tac-box" style="background:#f4f8fb;border:1px solid #d6e4f0;margin:5px 0 12px;padding-top:10px">
  <div class="form-group col-md-5 col-xs-12">
    <label>Đối tác</label>
    <select class="form-control select2" name="doi_tac_id" id="doi_tac_id" style="width:100%">
      <option value="">— Khách lẻ —</option>
      @foreach(($doiTacList ?? []) as $dt)
      <option value="{{ $dt->id }}" {{ $dtId == $dt->id ? 'selected' : '' }}
        data-buffet="{{ (int) $dt->com_buffet_per_pax }}" data-pct="{{ (float) $dt->com_alacarte_pct }}">
        {{ $dt->name }} ({{ $dtTypes[$dt->type] ?? $dt->type }})
      </option>
      @endforeach
    </select>
  </div>
  <div class="form-group col-md-3 col-xs-6 dt-opt" style="padding-top:25px">
    <label style="font-weight:normal;cursor:pointer"><input type="checkbox" name="co_chiet_khau" id="co_chiet_khau" value="1" {{ $dtCk ? 'checked' : '' }}> Có chiết khấu (tính hoa hồng)</label>
  </div>
  <div class="form-group col-md-4 col-xs-6 dt-opt" style="padding-top:25px">
    <label style="font-weight:normal;cursor:pointer"><input type="checkbox" name="ghi_no" id="ghi_no" value="1" {{ $dtNo ? 'checked' : '' }}> Ghi nợ (đối tác trả sau)</label>
  </div>
  <div class="col-xs-12 dt-opt" style="padding-bottom:8px">
    <span id="dt-preview" style="color:#31708f"></span>
  </div>
</div>
<script>
(function(){
  function fmt(n){ return (Math.round(n)||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
  function num(v){ return parseInt(String(v||'0').replace(/,/g,''),10)||0; }
  window.dtPreview = function(){
    var $ = window.jQuery; if(!$) return;
    var opt = $('#doi_tac_id option:selected');
    var has = !!$('#doi_tac_id').val();
    $('.dt-opt').toggle(has);
    if(!has){ $('#dt-preview').text(''); return; }
    var pax = 0;
    $('.services').each(function(){
      var r = $(this), t = (r.find('.cate option:selected').text()||'').toUpperCase();
      var nm = $.trim(t).replace(/^\d+\s*(-\s*)?/, ''); // "12 - BUFFET..." hoặc "12 BUFFET ... - giá"
      if(nm.indexOf('BUFFET') === 0 && r.find('.cate').val()){ pax += num(r.find('.amount').val()); }
    });
    var net = Math.max(0, num($('#total_price').val()) - num($('#discount').val()));
    var txt = '';
    if(!$('#co_chiet_khau').is(':checked')){
      txt = 'Không tính hoa hồng (chưa tick "Có chiết khấu").';
    }else if(pax > 0){
      txt = 'Bill BUFFET – hoa hồng dự kiến: ' + pax + ' khách × ' + fmt(opt.data('buffet')) + ' = ' + fmt(pax*opt.data('buffet')) + ' đ';
    }else{
      txt = 'Bill ALACARTE – hoa hồng dự kiến: ' + opt.data('pct') + '% × ' + fmt(net) + ' = ' + fmt(net*opt.data('pct')/100) + ' đ';
    }
    if($('#ghi_no').is(':checked')) txt += ' | Ghi nợ: ' + fmt(net) + ' đ';
    $('#dt-preview').text(txt);
  };
  function bind(){
    var $ = window.jQuery; if(!$){ return setTimeout(bind, 200); }
    $(document).on('change', '#doi_tac_id, #co_chiet_khau, #ghi_no, .cate, .amount', function(){ setTimeout(dtPreview, 50); });
    $(document).on('keyup change', '#discount, #total_price', function(){ setTimeout(dtPreview, 50); });
    $(dtPreview);
  }
  bind();
})();
</script>
