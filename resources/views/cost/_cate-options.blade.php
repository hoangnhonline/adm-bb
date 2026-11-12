{{-- Danh mục chi phí chia nhóm theo cost_type.group_name. Biến: $cateList, $selectedCate --}}
@php
  $cateGroups = $cateList->groupBy(function ($c) { return $c->group_name ?: ''; });
@endphp
@if($cateGroups->count() == 1 && $cateGroups->keys()->first() === '')
  @foreach($cateList as $cate)
    <option value="{{ $cate->id }}" {{ $selectedCate == $cate->id ? "selected" : "" }}>{{ $cate->name }}</option>
  @endforeach
@else
  @foreach($cateGroups as $groupName => $cates)
    <optgroup label="{{ $groupName ?: 'Khác' }}">
      @foreach($cates as $cate)
        <option value="{{ $cate->id }}" {{ $selectedCate == $cate->id ? "selected" : "" }}>{{ $cate->name }}</option>
      @endforeach
    </optgroup>
  @endforeach
@endif
