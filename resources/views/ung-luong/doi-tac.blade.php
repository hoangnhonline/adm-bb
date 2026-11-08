@if($partnerList->count() > 0)
<div class="form-group col-md-12">
  	<label>Chi tiết<span class="red-star">*</span></label>
  	<select class="form-control select2" id="partner_id" name="partner_id">     
    	<option value="">--Chọn--</option>      
	    @foreach($partnerList as $cate)
	    <option value="{{ $cate->id }}" {{ old('partner_id') == $cate->id ? "selected" : "" }}>
	    	{{ $cate->name }}
	    </option>
	    @endforeach
  	</select>
</div>
@endif