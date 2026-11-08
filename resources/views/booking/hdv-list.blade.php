<option value="">-Dish name-</option>
@if($hdvList)
@foreach($hdvList as $hdv)
<option value="{{ $hdv->id }}">{{ $hdv->name }} - {{ number_format($hdv->price) }}</option>
@endforeach
@endif