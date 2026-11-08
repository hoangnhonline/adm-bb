@if( $bankInfoList->count() > 0)
	@foreach( $bankInfoList as $value )
	<option value="{{ $value->id }}" {{ $value->id == $id_selected ? "selected" : "" }}>{{ $value->name }} - {{ $value->bank_name }} - {{ $value->bank_no }}</option>
	@endforeach
@endif