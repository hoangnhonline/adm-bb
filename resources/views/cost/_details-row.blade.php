{{-- 1 dòng của bảng chi tiết hàng. Biến: $i, $row, $ingredientList, $cdFmtQty, $cdMoney --}}
@php
  $needReview = !empty($row['need_review']) ? 1 : 0;
  $rowIngredient = $row['ingredient_id'] ?? '';
@endphp
<tr class="cd-row" data-need-review="{{ $needReview }}">
  <td>
    <input type="hidden" name="details[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
    <input type="hidden" name="details[{{ $i }}][need_review]" class="cd-need-review" value="{{ $needReview }}">
    <select name="details[{{ $i }}][ingredient_id]" class="form-control cd-ingredient" style="width:100%">
      <option value="">--Chọn--</option>
      @foreach($ingredientList as $ing)
        <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" data-aliases="{{ $ing->aliases }}" {{ (string) $rowIngredient === (string) $ing->id ? 'selected' : '' }}>{{ $ing->name }}</option>
      @endforeach
    </select>
    @if($needReview)
      <span class="label label-warning cd-review-label" title="Dòng bóc tách từ ghi chú cũ – sửa bất kỳ ô nào để xác nhận đã kiểm tra">Cần KT</span>
    @endif
  </td>
  <td><input type="text" name="details[{{ $i }}][quantity]" class="form-control cd-qty" autocomplete="off" value="{{ $cdFmtQty($row['quantity'] ?? '') }}"></td>
  <td><input type="text" name="details[{{ $i }}][unit]" class="form-control cd-unit" autocomplete="off" value="{{ $row['unit'] ?? '' }}"></td>
  <td><input type="text" name="details[{{ $i }}][unit_price]" class="form-control text-right cd-price cd-money" autocomplete="off" value="{{ $cdMoney($row['unit_price'] ?? '') }}"></td>
  <td><input type="text" name="details[{{ $i }}][amount]" class="form-control text-right cd-amount cd-money" autocomplete="off" value="{{ $cdMoney($row['amount'] ?? '') }}"></td>
  <td><input type="text" name="details[{{ $i }}][notes]" class="form-control cd-notes" autocomplete="off" value="{{ $row['notes'] ?? '' }}"></td>
  <td><button type="button" class="btn btn-danger btn-sm cd-remove" title="Xóa dòng"><span class="glyphicon glyphicon-trash"></span></button></td>
</tr>
