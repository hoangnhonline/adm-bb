<option value="">--Chọn HDV--</option>
@if($hdvList)
@foreach($hdvList as $hdv)
<option value="{{ $hdv->id }}">{{ $hdv->name }}</option>
@endforeach
@endif