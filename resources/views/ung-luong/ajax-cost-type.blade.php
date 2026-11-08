<option value="">-- Chọn --</option>
@if($list->count() > 0)     
	@foreach($list as $costType)
	<option value="{{ $costType->id }}">
		{{ $costType->name }}
	</option>
	@endforeach
@endif