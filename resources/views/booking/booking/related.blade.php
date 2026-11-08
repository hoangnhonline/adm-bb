<option value="">Trực tiếp KS</option>
@if(!empty($relatedArr))
@foreach($relatedArr as $r)
<option value="{{ $r->id }}">{{ $r->name }}</option>
@endforeach
@endif