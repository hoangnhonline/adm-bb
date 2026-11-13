@extends('layout')
@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <h1>Upload bill</h1>
    <ol class="breadcrumb">
      <li><a href="{{ route('cost.index') }}">Cost</a></li>
      <li class="active">Upload bill</li>
    </ol>
  </section>
  <section class="content">
    <div class="box box-primary">
      <div class="box-body">
        <div id="drop-zone" style="border:2px dashed #3c8dbc;border-radius:6px;padding:40px;text-align:center;cursor:pointer;background:#f7fbff">
          <p style="font-size:16px;margin:0">Kéo thả ảnh bill vào đây hoặc bấm để chọn (nhiều ảnh cùng lúc)</p>
          <p style="color:#888;margin:5px 0 0">jpg / png / webp, tối đa 8MB mỗi ảnh</p>
          <input type="file" id="bill-files" accept="image/jpeg,image/png,image/webp" multiple style="display:none">
        </div>
        <div id="upload-status" style="margin-top:10px"></div>
        <h4 style="margin-top:20px">Ảnh đang chờ nhập (<span id="inbox-count">0</span>)
          <button type="button" id="btn-del-all" class="btn btn-danger btn-sm pull-right" style="display:none"><i class="fa fa-trash"></i> Xóa tất cả</button>
        </h4>
        <div id="inbox-list" class="row"></div>
      </div>
    </div>
  </section>
</div>
@stop
@section('js')
<script>
(function(){
  var zone = document.getElementById('drop-zone'), input = document.getElementById('bill-files');
  var token = '{{ csrf_token() }}';
  function loadInbox(){
    fetch('{{ route('cost.api.inbox') }}', {headers:{'Accept':'application/json'}, credentials:'same-origin'})
      .then(function(r){ return r.json(); }).then(function(d){
        document.getElementById('inbox-count').textContent = d.items.length;
        document.getElementById('btn-del-all').style.display = d.items.length ? '' : 'none';
        document.getElementById('inbox-list').innerHTML = d.items.map(function(i){
          return '<div class="col-md-2 col-xs-6" style="margin-bottom:10px"><a href="'+i.url+'" target="_blank"><img src="'+i.url+'" class="img-thumbnail" style="height:160px;width:100%;object-fit:cover"></a><div style="font-size:11px;color:#666">'+i.name+'</div><button type="button" class="btn btn-danger btn-xs btn-del-bill" data-name="'+i.name+'" style="margin-top:3px">Xóa</button></div>';
        }).join('');
      });
  }
  function upload(files){
    if(!files.length) return;
    var fd = new FormData();
    for(var i=0;i<files.length;i++){ fd.append('files[]', files[i]); }
    var st = document.getElementById('upload-status');
    st.innerHTML = '<span class="text-info">Đang upload '+files.length+' ảnh...</span>';
    fetch('{{ route('cost.api.upload-bill') }}', {method:'POST', body:fd, credentials:'same-origin', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}})
      .then(function(r){ return r.json(); }).then(function(d){
        st.innerHTML = '<span class="text-success">Đã upload '+d.saved.length+' ảnh.</span>' + (d.errors.length ? '<br><span class="text-danger">'+d.errors.join('<br>')+'</span>' : '');
        loadInbox();
      }).catch(function(){ st.innerHTML = '<span class="text-danger">Upload lỗi, thử lại.</span>'; });
  }
  document.getElementById('inbox-list').addEventListener('click', function(e){
    var btn = e.target.closest('.btn-del-bill');
    if(!btn) return;
    if(!confirm('Xóa ảnh này khỏi danh sách chờ?')) return;
    btn.disabled = true;
    fetch('{{ route('cost.api.inbox-delete') }}', {method:'POST', credentials:'same-origin',
      headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','Content-Type':'application/json'},
      body: JSON.stringify({name: btn.getAttribute('data-name')})})
      .then(function(r){ return r.json(); }).then(function(d){
        if(!d.ok){ alert(d.errors.join('\n')); btn.disabled = false; }
        loadInbox();
      }).catch(function(){ alert('Xóa lỗi, thử lại.'); btn.disabled = false; });
  });
  document.getElementById('btn-del-all').addEventListener('click', function(){
    var btn = this, n = document.getElementById('inbox-count').textContent;
    if(!confirm('Xóa toàn bộ '+n+' ảnh đang chờ nhập?')) return;
    btn.disabled = true;
    fetch('{{ route('cost.api.inbox-delete-all') }}', {method:'POST', credentials:'same-origin',
      headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}})
      .then(function(r){ return r.json(); }).then(function(d){
        document.getElementById('upload-status').innerHTML = '<span class="text-success">Đã xóa '+d.deleted+' ảnh.</span>' + (d.errors.length ? '<br><span class="text-danger">'+d.errors.join('<br>')+'</span>' : '');
        btn.disabled = false; loadInbox();
      }).catch(function(){ alert('Xóa lỗi, thử lại.'); btn.disabled = false; });
  });
  zone.addEventListener('click', function(){ input.click(); });
  input.addEventListener('change', function(){ upload(input.files); input.value=''; });
  zone.addEventListener('dragover', function(e){ e.preventDefault(); zone.style.background='#e3f1ff'; });
  zone.addEventListener('dragleave', function(){ zone.style.background='#f7fbff'; });
  zone.addEventListener('drop', function(e){ e.preventDefault(); zone.style.background='#f7fbff'; upload(e.dataTransfer.files); });
  loadInbox();
})();
</script>
@stop
