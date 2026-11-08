/*
 * Author: Abdullah A Almsaeed
 * Date: 4 Jan 2014
 * Description:
 *      This is a demo file used only for the main dashboard (index.html)
 **/
 /* Vietnamese localization for the jQuery UI date picker plugin. */
/* Written by Tien Do (tiendq@gmail.com) */

jQuery(function ($)
{
  $.datepicker.regional["vi-VN"] =
  {
    closeText: "Đóng",
    prevText: "Trước",
    nextText: "Sau",
    currentText: "Hôm nay",
    monthNames: ["Tháng một", "Tháng hai", "Tháng ba", "Tháng tư", "Tháng năm", "Tháng sáu", "Tháng bảy", "Tháng tám", "Tháng chín", "Tháng mười", "Tháng mười một", "Tháng mười hai"],
    monthNamesShort: ["Một", "Hai", "Ba", "Bốn", "Năm", "Sáu", "Bảy", "Tám", "Chín", "Mười", "Mười một", "Mười hai"],
    dayNames: ["Chủ nhật", "Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy"],
    dayNamesShort: ["CN", "Hai", "Ba", "Tư", "Năm", "Sáu", "Bảy"],
    dayNamesMin: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
    weekHeader: "Tuần",
    dateFormat: "dd/mm/yy",
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ""
  };

  $.datepicker.setDefaults($.datepicker.regional["vi-VN"]);
});
var h = screen.height;
var w = screen.width;
var left = (screen.width/2)-((w-300)/2);
var top = (screen.height/2)-((h-100)/2);

function singleUpload(obj) {
    window.KCFinder = {};
    window.KCFinder.callBack = function(url) {
       $('#' + obj.data('set')).val(url);
       $('#' + obj.data('image')).attr('src', $('#app_url').val() + url);
        window.KCFinder = null;
    };
    window.open($('#url_open_kc_finder').val(), 'kcfinder_single','scrollbars=1,menubar=no,width='+ (w-300) +',height=' + (h-300) +',top=' + top+',left=' + left);
}

function callDelete(name, url){  
  swal({
    title: 'Bạn muốn xóa "' + name +'"?',
    text: "Dữ liệu sẽ không thể phục hồi.",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes'
  }).then(function() {
    location.href= url;
  })
  return flag;
}
$(document).on('change', '.get-child', function(){
    var table = $(this).data('mod');
    var child = $(this).data('child');
    var column = $(this).data('col');
    var value = $(this).val();
    $.ajax({
      url : $('#get-child-route').val(),
      data : {
        table : table,
        column : column,
        id : value
      },
      type : 'POST',
      dataType : 'html',
      success : function(data){
        $('#' + child).html(data);
      }
    });
});
$(document).on('change', '.get-child-2', function(){
    var table = $(this).data('mod2');
    var child = $(this).data('child2');
    var column = $(this).data('col2');
    var value = $(this).val();
    $.ajax({
      url : $('#get-child-route').val(),
      data : {
        table : table,
        column : column,
        id : value
      },
      type : 'POST',
      dataType : 'html',
      success : function(data){
        $('#' + child).html(data);
      }
    });
});
$(document).ready(function(){

  "use strict";

  $('select.change-value').change(function(){
    var obj = $(this);    
    $.ajax({
      url : $('#route-change-value').val(),
      type :'POST',
      data : {
        id : obj.data('id'),
        value : obj.val(),
        column : obj.data('col'),
        table : obj.data('table')
      },
      success : function(data){
        location.reload();
      }
    });
  });

  $('.btnSingleUpload').click(function(){        
    singleUpload($(this));
  });
  $('.btnMultiUpload').click(function(){        
    multiUpload();
  });
  $('.btnUploadEditor').click(function(){  
  $('#editor').val($(this).data('editor'));       
    uploadToEditor();
  });
  
  $('#searchForm select').change(function(){
    if($(this).attr('id') == 'hdv0'){
      $('#hdv_id').val('');
    }
    $('#searchForm').submit();
  });
  $('#dataForm #name').change(function(){
       var name = $.trim( $(this).val() );         
        $.ajax({
          url: $('#route_get_slug').val(),
          type: "POST",
          async: false,      
          data: {
            str : name
          },              
          success: function (response) {
            if( response.str ){                  
              $('#dataForm #slug').val( response.str );
            }                
          }
        });         
    });

  $(".select2").select2();

  $('#title').change(function(){
       var name = $.trim( $(this).val() );
       
          $.ajax({
            url: $('#route_get_slug').val(),
            type: "POST",
            async: false,      
            data: {
              str : name
            },              
            success: function (response) {
              if( response.str ){                  
                $('#slug').val( response.str );
              }                
            }
          });
       
    });
  if($('#content').length == 1){
    CKEDITOR.replace( 'content');
  }
  if($('#description').length == 1){
    CKEDITOR.replace( 'description', {
      height : 300
    });
  }
  if($('#incentives').length == 1){
    CKEDITOR.replace( 'incentives', {
      height : 300
    });
  }
  if($('#object').length == 1){
    CKEDITOR.replace( 'object', {
      height : 200
    });
  }
  $(document).on('click', '#btnSaveTagAjax', function(){
      $.ajax({
        url : $('#formAjaxTag').attr('action'),
        data: $('#formAjaxTag').serialize(),
        type : "post", 
        success : function(str_id){          
          $('#btnCloseModalTag').click();
          $.ajax({
            url : $('#route-ajax-tag-list').val(),
            data: { 
              type : 1 ,
              tagSelected : $('#tags').val(),
              str_id : str_id
            },
            type : "get", 
            success : function(data){
                $('#tags').html(data);
                $('#tags').select2('refresh');
                
            }
          });
        }
      });
   }); 
  $('#btnAddTag').click(function(){
          $('#tagModal').modal('show');
      });
   $('#contentTag #name').change(function(){
         var name = $.trim( $(this).val() );
         if( name != '' && $('#contentTag #slug').val() == ''){
            $.ajax({
              url: $('#route_get_slug').val(),
              type: "POST",
              async: false,      
              data: {
                str : name
              },              
              success: function (response) {
                if( response.str ){                  
                  $('#contentTag #slug').val( response.str );
                }                
              }
            });
         }
      });



     $(document).on('click', '#btnSaveGroupAjax', function(){
      $.ajax({
        url : $('#formAjaxGroup').attr('action'),
        data: $('#formAjaxGroup').serialize(),
        type : "post", 
        success : function(str_id){          
          $('#btnCloseModalGroup').click();
          $.ajax({
            url : $('#route-ajax-tag-list').val(),
            data: { 
              type : 1 ,           
              str_id : str_id
            },
            type : "get", 
            success : function(data){
                $('#group_id').html(data);
                $('#group_id').select2('refresh');                
            }
          });
        }
      });
   }); 
  $('#btnAddGroup').click(function(){
          $('#groupModal').modal('show');
      });
   $('#contentGroup #name').change(function(){
       var name = $.trim( $(this).val() );
       if( name != '' && $('#contentGroup #slug').val() == ''){
          $.ajax({
            url: $('#route_get_slug').val(),
            type: "POST",
            async: false,      
            data: {
              str : name
            },              
            success: function (response) {
              if( response.str ){                  
                $('#contentGroup #slug').val( response.str );
              }                
            }
          });
       }
    });


});
var toolbar = [
    { name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source' ] },
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: ['Image', 'Bold', 'Italic', 'Underline', 'Subscript', 'Superscript', 'NumberedList', 'BulletedList', 'Link', 'Unlink' ] },                             
    { name: 'styles', items: [ 'Format' ] },
    { name: 'tools', items: [ 'Maximize' ] },                      
];

function multiUpload() {
    window.KCFinder = {};
    window.KCFinder.callBackMultiple = function(files) {
        var strHtml = '';
        for (var i = 0; i < files.length; i++) {
             strHtml += '<div class="col-md-3">';

        strHtml += '<img class="img-thumbnail" src="' +  $('#app_url').val() + files[i]  + '" style="width:100%">';
         strHtml += '<div class="checkbox">';
         strHtml += '<input type="hidden" name="image_tmp_url[]" value="' + files[i]  + '">';
        
        strHtml += '<label><input type="radio" name="thumbnail_img" class="thumb" value="' + files[i]  + '"> &nbsp;  Ảnh đại diện </label>';
        strHtml += '<button class="btn btn-danger btn-sm remove-image" type="button" data-value="' +  $('#app_url').val() + files[i]  + '" data-id="" ><span class="glyphicon glyphicon-trash"></span></button></div></div>';      
        }
        $('#div-image').append(strHtml);
            if( $('#div-image input.thumb:checked').length == 0){
              $('#div-image input.thumb').eq(0).prop('checked', true);
            }
        window.KCFinder = null;
    };
    window.open($('#url_open_kc_finder').val(), 'kcfinder_multiple','scrollbars=1,menubar=no,width='+ (w-300) +',height=' + (h-300) +',top=' + top+',left=' + left);
}

function uploadToEditor(){
  window.KCFinder = {};
  window.KCFinder.callBackMultiple = function(files) {

      var editor = $('#editor').val();                            
      var editorTemp = CKEDITOR.instances[editor];        
      var edi_parent = $(CKEDITOR.instances[editor].document.getBody().$);                      
      var get_html,
      count_img = edi_parent.find('img').length
     ,
      table = $('<div></div>')
      ;
      var strHtml = '';
      for (var i = 0; i < files.length; i++) {

        var elementImg = editorTemp.document.createElement('img');
        elementImg.$.setAttribute('src', files[i]);
        elementImg.$.style.maxWidth="100%";
        html = $('<table width="100%" border="0" cellpadding="3" width="1" cellspacing="0" align="center" ><tr><td style="text-align:center"></td></tr><tr><td><p style="text-align:center">[Caption]</p></td></tr></table>');
        html.find('td:eq(0)').append($(elementImg.$));
        table.append(html);            
      }
      editorTemp.insertHtml(table.html());          
      window.KCFinder = null;
    };
    window.open($('#url_open_kc_finder').val(), 'kcfinder_multiple','scrollbars=1,menubar=no,width='+ (w-300) +',height=' + (h-300) +',top=' + top+',left=' + left);
}