<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title" id="title_edit"> <span style="color:#06b7a4; text-transform: uppercase;">{{ $detail->name }}</span> <i class="  glyphicon glyphicon-map-marker"></i> {{ $detail->address }}</h4>
  </div>
  <div class="modal-body">
      <div class="form-group">
        <label>Status gọi</label>
        <select class="form-control" id="call_status">                  
          <option value="1" {{ $detail->call_status == 1 ? "selected" : "" }}>Chưa gọi</option>
          <option value="2" {{ $detail->call_status == 2 ? "selected" : "" }}>Gọi OK</option>
          <option value="3" {{ $detail->call_status == 3 ? "selected" : "" }}>Chưa nghe</option>
          <option value="4" {{ $detail->call_status == 4 ? "selected" : "" }}>Thuê bao</option>
          <option value="5" {{ $detail->call_status == 5 ? "selected" : "" }}>Sai số</option>
          <option value="6" {{ $detail->call_status == 6 ? "selected" : "" }}>Dời ngày</option>
          <option value="7" {{ $detail->call_status == 7 ? "selected" : "" }}>Khách hủy</option>
        </select>
      </div>
      <div class="form-group">
        <label>Hướng dẫn viên</label>
        <select class="form-control" id="hdv_id">                  
          <option value="0">--Chưa chọn--</option>
          @foreach($listUser as $user)
          @if($user->hdv==1)
          <option value="{{ $user->id }}" @if($detail->hdv_id == $user->id) selected @endif>{{ $user->name }}</option>
          @endif
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Notes</label>
        <textarea class="form-control" id="hdv_notes" rows="7">{!! nl2br($detail->hdv_notes) !!}</textarea>
      </div> 
      <input type="hidden" id="booking_id" value="{{ $detail->id }}">
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-info" id="btnSaveInfo">Save lại</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>

  </div>