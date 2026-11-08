<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">              
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Danh mục trắc nghiệm
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <ul>
        <?php 
          $parentList = DB::table('quiz_cate')->orderBy('display_order')->get();

          ?>
          @foreach($parentList as $cate)          
          <li>
            <label>
            <input type="radio" name="menu_select" data-title="{{ $cate->name }}" data-link="{!! route('quiz-list', $cate->slug) !!}" data-value="{{ $cate->id }}" data-type="1" class="menu_select"> {{ $cate->name }}
            </label>       
              
          </li>
          @endforeach
        </ul>                             
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingABC">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseABC" aria-expanded="true" aria-controls="collapseABC">
          Danh mục khóa học
        </a>
      </h4>
    </div>
    <div id="collapseABC" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingABC">
      <div class="panel-body">
        <ul>
        <?php 
          $parentList = DB::table('courses_cate')->orderBy('display_order')->get();

          ?>
          @foreach($parentList as $cate)          
          <li>
            <label>
            <input type="radio" name="menu_select" data-title="{{ $cate->name }}" data-link="{!! route('courses-cate', $cate->slug) !!}" data-value="{{ $cate->id }}" data-type="1" class="menu_select"> {{ $cate->name }}
            </label>       
              
          </li>
          @endforeach
        </ul>                             
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingTwo">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Danh mục bài viết
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
      <div class="panel-body">
        <ul>
          @foreach($articlesCateList as $value)
          <li>
            <label>
            <input type="radio" name="menu_select" data-link="{{ route('news-list', $value->slug) }}" data-value="{{ $value->id }}" data-type="5" data-title="{{ $value->name }}" class="menu_select"> {{ $value->name }}
            </label>            
          </li>
          @endforeach
        </ul>                             
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingThree">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          Trang
        </a>
      </h4>
    </div>
    <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
      <div class="panel-body">
       <ul>
          @foreach($pageList as $value)
          <li>
          <label>
            <input type="radio" name="menu_select" data-link="{{ route('pages', $value->slug) }}" data-value="{{ $value->id }}" data-type="4" data-title="{{ $value->title }}" class="menu_select"> {{ $value->title }}            
          </label>
          </li>
          @endforeach
        </ul>                              
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" id="">
      <h4 class="panel-title">
        <a>
          Tùy chỉnh
        </a>
      </h4>
    </div>
    <div>
      <div class="panel-body" id="collapseFour">
        <div class="form-group">
          <label>Text hiển thị <span class="red-star">*</span></label>
          <input type="text" class="form-control" name="title_custom" id="title_custom"  value="{{ old('title_custom') }}">
        </div>
        <input type="hidden" name="type" value="3">
        <input type="hidden" name="custom" value="1">
         <div class="form-group">
          <label>URL <span class="red-star">*</span></label>
          <input type="text" class="form-control" name="url_custom" id="url_custom" value="{{ old('url_custom') }}">
        </div>
        <button type="button" class="btnAddToMenuCustom btn btn-sm btn-info" style="float:right" data-parent="collapseFour">Thêm vào menu</button>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</div>  
<form method="POST" action="{{ route('menu.store')}}" id="formMenu">
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Hiển thị</h3>
  </div>
  <!-- /.box-header -->               
    {!! csrf_field() !!}

    <div class="box-body">
         <!-- text input -->
        <div class="form-group">
          <label>Text hiển thị <span class="red-star">*</span></label>
          <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}">
        </div>
         <div class="form-group">
          <label>URL <span class="red-star">*</span></label>
          <input type="text" class="form-control" name="url" readonly="readonly" id="url" value="{{ old('url') }}">
        </div>                
        <input type="hidden" name="type" id="type" value="">
        <input type="hidden" name="parent_id" id="parent_id" value="{{ $parent_id }}">
        <input type="hidden" name="object_id" id="object_id" value="">
    </div>
    <!-- /.box-body -->                
    <div class="box-footer">
      <button type="submit" class="btn btn-primary btn-sm">Save</button>
      <a class="btn btn-default btn-sm" class="btn btn-primary btn-sm" data-dismiss="modal">Cancel</a>
    </div>
    
</div>
</form>
