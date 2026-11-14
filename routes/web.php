<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/guest', ['uses' => 'BookingNoAuthController@index', 'as' => 'guest']);
Route::get('/cost-guest', ['uses' => 'CostGuestController@index', 'as' => 'cost-guest']);
Route::get('/diem-danh/{code}', ['as' => 'diem-danh-public', 'uses' => 'MediaController@diemDanhPublic']);
Route::get('/ajax-store-public', ['as' => 'ajax-store-public', 'uses' => 'MediaController@ajaxStorePublic']);
Route::get('/debug', ['as' => 'debugg', 'uses' => 'GeneralController@debug']);
Route::get('/pdf-tour', ['uses' => 'PdfController@tour', 'as' => 'pdf-tour']);
Route::get('/view-pdf', ['uses' => 'PdfController@viewPdf', 'as' => 'view-pdf']);
Route::get('/', ['uses' => 'Frontend\HomeController@getChild', 'as' => 'get-child']);

Route::get('/revertExport', ['uses' => 'BookingController@revertExport', 'as' => 'revertExport']);
Route::get('/cal-tour', ['uses' => 'BookingController@calTour', 'as' => 'calTour']);
Route::get('/daily', ['uses' => 'BookingController@daily', 'as' => 'daily']);
Route::get('/total', ['uses' => 'BookingController@totalByUser', 'as' => 'total-by-user']);

Route::get('/test', ['uses' => 'TestController@index', 'as' => 'xxxx']);

Route::post('/get-child', ['uses' => 'Frontend\HomeController@getChild', 'as' => 'get-child']);
Route::get('parse', ['uses' => 'CrawlerController@parse', 'as' => 'parse']);
Route::get('chatbot-zalo', ['uses' => 'ZaloController@index', 'as' => 'chatbot-zalo']);
Route::get('zalo', ['uses' => 'ZaloController@zalo', 'as' => 'zalo']);
Route::get('zalo2', ['uses' => 'ZaloController@zalo2', 'as' => 'zalo2']);
Route::get('reset-pass', ['uses' => 'AccountController@resetPass', 'as' => 'resetPass']);
Route::get('/change-value-chung', ['as' => 'change-value-by-column-chung', 'uses' => 'GeneralController@changeValueByColumnChung']);

Route::post('api-sms-payment', ['uses' => 'BookingController@smsPayment', 'as' => 'api-sms-payment']);
Route::group(['prefix' => 'report-guest'], function () {  
        Route::get('/chi-phi', ['as' => 'report-guest.chi-phi', 'uses' => 'ReportGuestController@chiphi']);
      
        
    });
Route::group([
    'middleware' => 'auth',  
], function () {    
	Route::get('/', 'BookingController@index')->name('dashboard');
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/sms', ['as' => 'media.sms', 'uses' => 'MediaController@smsList']);
    Route::get('/book-phong', 'HomeController@bookPhong')->name('book-phong');
    Route::get('/book-tour-cau-muc', 'HomeController@bookTourCauMuc')->name('book-tour-cau-muc');
    Route::get('/confirm-phong', 'HomeController@confirmPhong')->name('confirm-phong');
    Route::get('/mail-preview', 'HomeController@mailPreview')->name('mail-preview');
    Route::get('/mail-confirm', 'HomeController@mailConfirm')->name('mail-confirm');
    Route::get('/saveBookingCode', 'HomeController@saveBookingCode')->name('saveBookingCode');
    Route::get('/save-hoa-hong', 'HomeController@saveHoaHong')->name('save-hoa-hong');
    Route::get('/hh', ['uses' => 'BookingController@tinhHoaHong', 'as' => 'tinhHoaHong']);    
    Route::get('/get-boat-prices', 'GeneralController@getBoatPrices')->name('get-boat-prices');
    Route::get('/booking-qrcode/{id}', 'BookingController@qrCode')->name('booking-qrcode');
    Route::group(['prefix' => 'hoa-hong'], function () {        
        Route::get('/hotel', ['as' => 'hoa-hong-hotel', 'uses' => 'BookingHotelController@commission']);
        Route::get('/tour', ['as' => 'hoa-hong-tour', 'uses' => 'BookingTourController@commission']);          
    });
    Route::get('/hoa-hong-khach-san', 'BookingController@calCommissionHotel')->name('hoa-hong-khach-san');
     
    Route::group(['prefix' => 'media'], function () {        
        Route::get('/', ['as' => 'media.index', 'uses' => 'MediaController@index']);
        Route::get('/diem-danh', ['as' => 'media.diem-danh', 'uses' => 'MediaController@diemDanh']);
        Route::get('/create', ['as' => 'media.create', 'uses' => 'MediaController@create']);
        Route::post('/store', ['as' => 'media.store', 'uses' => 'MediaController@store']);
        Route::get('/ajax-store', ['as' => 'media.ajax-store', 'uses' => 'MediaController@ajaxStore']);
        
        Route::get('{id}/edit',   ['as' => 'media.edit', 'uses' => 'MediaController@edit']);
        Route::post('/update', ['as' => 'media.update', 'uses' => 'MediaController@update']);
        Route::get('{id}/destroy', ['as' => 'media.destroy', 'uses' => 'MediaController@destroy']);  
    });
    Route::group(['prefix' => 'partner'], function () {        
        Route::get('/', ['as' => 'partner.index', 'uses' => 'PartnerController@index']);      
        Route::get('/create', ['as' => 'partner.create', 'uses' => 'PartnerController@create']);
        Route::post('/store', ['as' => 'partner.store', 'uses' => 'PartnerController@store']);
        Route::get('{id}/edit',   ['as' => 'partner.edit', 'uses' => 'PartnerController@edit']);
        Route::post('/update', ['as' => 'partner.update', 'uses' => 'PartnerController@update']);
        Route::get('{id}/destroy', ['as' => 'partner.destroy', 'uses' => 'PartnerController@destroy']);
        Route::get('/change-value', ['as' => 'partner.change-value-by-column', 'uses' => 'PartnerController@changeValueByColumn']);  
    });
    Route::post('/filter-prefs/save', ['as' => 'filter-prefs.save', 'uses' => 'FilterPrefController@save']); // bộ lọc tùy chọn theo user
    Route::get('/locale/{lang}', ['as' => 'locale.change', 'uses' => 'LocaleController@change']); // đa ngôn ngữ VI/EN
    // Tổng quan & bill bất thường (14/11/2026)
    Route::get('/tong-quan', ['as' => 'tong-quan.index', 'uses' => 'OverviewController@index']);
    Route::get('/bill-bat-thuong', ['as' => 'bill-anomaly.index', 'uses' => 'BillAnomalyController@index']);
    Route::post('/bill-bat-thuong/review', ['as' => 'bill-anomaly.review', 'uses' => 'BillAnomalyController@review']);
    // Phân quyền (chỉ tài khoản quản trị chính)
    Route::group(['prefix' => 'phan-quyen'], function () {
        Route::get('/', ['as' => 'phan-quyen.index', 'uses' => 'RoleController@index']);
        Route::get('/create', ['as' => 'phan-quyen.create', 'uses' => 'RoleController@create']);
        Route::get('/{id}/edit', ['as' => 'phan-quyen.edit', 'uses' => 'RoleController@edit'])->where('id', '[0-9]+');
        Route::post('/save', ['as' => 'phan-quyen.save', 'uses' => 'RoleController@save']);
        Route::get('/{id}/destroy', ['as' => 'phan-quyen.destroy', 'uses' => 'RoleController@destroy'])->where('id', '[0-9]+');
        Route::get('/users', ['as' => 'phan-quyen.users', 'uses' => 'RoleController@users']);
        Route::post('/users/save', ['as' => 'phan-quyen.user-save', 'uses' => 'RoleController@saveUser']);
        Route::get('/users/{id}/check', ['as' => 'phan-quyen.check', 'uses' => 'RoleController@check'])->where('id', '[0-9]+');
    });
    Route::get('/activity-log', ['as' => 'activity-log.index', 'uses' => 'ActivityLogController@index']); // lịch sử thao tác (chỉ id 1)
    // Đối tác bán hàng & công nợ (14/11/2026)
    Route::group(['prefix' => 'doi-tac'], function () {
        Route::get('/', ['as' => 'doi-tac.index', 'uses' => 'DoiTacController@index']);
        Route::get('/report', ['as' => 'doi-tac.report', 'uses' => 'DoiTacController@report']);
        Route::get('/create', ['as' => 'doi-tac.create', 'uses' => 'DoiTacController@create']);
        Route::post('/store', ['as' => 'doi-tac.store', 'uses' => 'DoiTacController@store']);
        Route::post('/update', ['as' => 'doi-tac.update', 'uses' => 'DoiTacController@update']);
        Route::post('/settings', ['as' => 'doi-tac.settings', 'uses' => 'DoiTacController@saveSettings']);
        Route::get('/transaction/{tid}/cancel', ['as' => 'doi-tac.transaction.cancel', 'uses' => 'DoiTacController@cancelTransaction'])->where('tid', '[0-9]+');
        Route::get('/{id}', ['as' => 'doi-tac.show', 'uses' => 'DoiTacController@show'])->where('id', '[0-9]+');
        Route::get('/{id}/edit', ['as' => 'doi-tac.edit', 'uses' => 'DoiTacController@edit'])->where('id', '[0-9]+');
        Route::get('/{id}/destroy', ['as' => 'doi-tac.destroy', 'uses' => 'DoiTacController@destroy'])->where('id', '[0-9]+');
        Route::get('/{id}/recalc', ['as' => 'doi-tac.recalc', 'uses' => 'DoiTacController@recalc'])->where('id', '[0-9]+');
        Route::get('/{id}/export', ['as' => 'doi-tac.export', 'uses' => 'DoiTacController@export'])->where('id', '[0-9]+');
        Route::post('/{id}/transaction', ['as' => 'doi-tac.transaction.store', 'uses' => 'DoiTacController@storeTransaction'])->where('id', '[0-9]+');
    });
    Route::group(['prefix' => 'revenue'], function () {        
        Route::get('/', ['as' => 'revenue.index', 'uses' => 'RevenueController@index']);      
        Route::get('/create', ['as' => 'revenue.create', 'uses' => 'RevenueController@create']);
        Route::post('/store', ['as' => 'revenue.store', 'uses' => 'RevenueController@store']);
        Route::get('{id}/edit',   ['as' => 'revenue.edit', 'uses' => 'RevenueController@edit']);
        Route::post('/update', ['as' => 'revenue.update', 'uses' => 'RevenueController@update']);
        Route::get('{id}/destroy', ['as' => 'revenue.destroy', 'uses' => 'RevenueController@destroy']);
        Route::get('/change-value', ['as' => 'revenue.change-value-by-column', 'uses' => 'RevenueController@changeValueByColumn']);  
    });
    Route::group(['prefix' => 'debt'], function () {        
        Route::get('/', ['as' => 'debt.index', 'uses' => 'DebtController@index']);      
        Route::get('/report', ['as' => 'debt.report', 'uses' => 'DebtController@report']); 
        Route::get('/export', ['as' => 'debt.export', 'uses' => 'DebtController@export']);         
        Route::get('/create', ['as' => 'debt.create', 'uses' => 'DebtController@create']);
        Route::post('/store', ['as' => 'debt.store', 'uses' => 'DebtController@store']);
        Route::get('{id}/edit',   ['as' => 'debt.edit', 'uses' => 'DebtController@edit']);
        Route::post('/update', ['as' => 'debt.update', 'uses' => 'DebtController@update']);
        Route::get('{id}/destroy', ['as' => 'debt.destroy', 'uses' => 'DebtController@destroy']);
        Route::get('/change-value', ['as' => 'debt.change-value-by-column', 'uses' => 'DebtController@changeValueByColumn']);  
    });
    Route::group(['prefix' => 'drivers'], function () {        
        Route::get('/', ['as' => 'drivers.index', 'uses' => 'DriversController@index']);      
        Route::get('/create', ['as' => 'drivers.create', 'uses' => 'DriversController@create']);
        Route::post('/store', ['as' => 'drivers.store', 'uses' => 'DriversController@store']);
        Route::get('{id}/edit',   ['as' => 'drivers.edit', 'uses' => 'DriversController@edit']);
        Route::post('/update', ['as' => 'drivers.update', 'uses' => 'DriversController@update']);
        Route::get('{id}/destroy', ['as' => 'drivers.destroy', 'uses' => 'DriversController@destroy']);
        Route::get('/change-value', ['as' => 'drivers.change-value-by-column', 'uses' => 'DriversController@changeValueByColumn']);  
    });
    Route::group(['prefix' => 'coupon'], function () {        
        Route::get('/', ['as' => 'coupon.index', 'uses' => 'CouponController@index']);        
        Route::get('/create', ['as' => 'coupon.create', 'uses' => 'CouponController@create']);
        Route::post('/store', ['as' => 'coupon.store', 'uses' => 'CouponController@store']);
        Route::get('/ajax-store', ['as' => 'coupon.ajax-store', 'uses' => 'CouponController@ajaxStore']);        
        Route::get('{id}/edit',   ['as' => 'coupon.edit', 'uses' => 'CouponController@edit']);
        Route::post('/update', ['as' => 'coupon.update', 'uses' => 'CouponController@update']);
        Route::get('{id}/destroy', ['as' => 'coupon.destroy', 'uses' => 'CouponController@destroy']);  
    });  
    Route::group(['prefix' => 'location'], function () {
        Route::get('/', ['as' => 'location.index', 'uses' => 'LocationController@index']);
        Route::get('/save-toa-do', ['as' => 'location.save-toa-do', 'uses' => 'LocationController@saveToaDo']);
        Route::get('/ajax-delete', ['as' => 'location.ajax-delete', 'uses' => 'LocationController@ajaxDelete']);
        Route::get('/save-name', ['as' => 'location.save-name', 'uses' => 'LocationController@saveName']);
        Route::get('/delete-multi', ['as' => 'location.delete-multi', 'uses' => 'LocationController@deleteMulti']);
        Route::get('/create', ['as' => 'location.create', 'uses' => 'LocationController@create']);
        Route::post('/store', ['as' => 'location.store', 'uses' => 'LocationController@store']);
        Route::post('/ajaxSave', ['as' => 'location.ajax-save', 'uses' => 'LocationController@ajaxSave']);  
        Route::get('/ajax-list', ['as' => 'location.ajax-list', 'uses' => 'LocationController@ajaxList']);      
        Route::get('{id}/edit',   ['as' => 'location.edit', 'uses' => 'LocationController@edit']);
        Route::post('/update', ['as' => 'location.update', 'uses' => 'LocationController@update']);
        Route::get('{id}/destroy', ['as' => 'location.destroy', 'uses' => 'LocationController@destroy']);
        Route::get('/save-value-column', ['as' => 'location.save-value-column', 'uses' => 'LocationController@saveValueColumn']);
        Route::get('/change-value-by-column', ['as' => 'location.change-value-by-column', 'uses' => 'LocationController@changeValueByColumn']);
        
        Route::get('/set-distance', ['as' => 'set-distance', 'uses' => 'LocationController@setDistance']);
        
    });
   
    Route::group(['prefix' => 'bank-info'], function () {   
        Route::post('/ajaxSave', ['as' => 'bank-info.ajax-save', 'uses' => 'BankInfoController@ajaxSave']);  
        Route::get('/ajax-list', ['as' => 'bank-info.ajax-list', 'uses' => 'BankInfoController@ajaxList']);  
    });
     Route::group(['prefix' => 'ticket'], function () {   
        Route::get('/manage', ['as' => 'ticket.manage', 'uses' => 'TicketController@manage']);
        Route::get('{id}/edit',   ['as' => 'ticket.edit', 'uses' => 'TicketController@edit']);
         Route::get('{id}/view-pdf',   ['as' => 'ticket.view-pdf', 'uses' => 'TicketController@viewPdf']);
        Route::post('/update', ['as' => 'ticket.update', 'uses' => 'BookingController@updateTicket']);
    });
    Route::group(['prefix' => 'notification'], function () {   
        Route::get('/', ['as' => 'noti.index', 'uses' => 'NotiController@index']);
        Route::get('/update-noti', ['as' => 'noti.update-multi', 'uses' => 'NotiController@updateMulti']);
        Route::get('{id}/edit',   ['as' => 'noti.edit', 'uses' => 'NotiController@edit']);
        Route::get('{id}/read',   ['as' => 'noti.read', 'uses' => 'NotiController@read']);
         Route::get('{id}/detail',   ['as' => 'noti.detail', 'uses' => 'NotiController@detail']);
    });
    Route::group(['prefix' => 'ticket-type'], function () {   
        Route::get('/', ['as' => 'ticket-type.index', 'uses' => 'TicketTypeController@index']);
        Route::get('{id}/edit',   ['as' => 'ticket-type.edit', 'uses' => 'TicketTypeController@edit']);
        Route::post('/update', ['as' => 'ticket-type.update', 'uses' => 'TicketTypeController@update']);
        Route::get('/create',   ['as' => 'ticket-type.create', 'uses' => 'TicketTypeController@create']);
        Route::post('/store',   ['as' => 'ticket-type.store', 'uses' => 'TicketTypeController@store']);
    });
    Route::group(['prefix' => 'dish'], function () {
        Route::get('/', ['as' => 'dish.index', 'uses' => 'DishController@index']);
        Route::get('/create', ['as' => 'dish.create', 'uses' => 'DishController@create']);
        Route::post('/store', ['as' => 'dish.store', 'uses' => 'DishController@store']);
        Route::get('{id}/edit',   ['as' => 'dish.edit', 'uses' => 'DishController@edit']);
        Route::post('/update', ['as' => 'dish.update', 'uses' => 'DishController@update']);
        Route::get('{id}/destroy', ['as' => 'dish.destroy', 'uses' => 'DishController@destroy']);
        Route::get('/change-value', ['as' => 'dish.change-value-by-column', 'uses' => 'DishController@changeValueByColumn']);
    });    
    Route::group(['prefix' => 'room'], function () {
        Route::get('/', ['as' => 'room.index', 'uses' => 'RoomController@index']);
        Route::get('/create', ['as' => 'room.create', 'uses' => 'RoomController@create']);
        Route::post('/store', ['as' => 'room.store', 'uses' => 'RoomController@store']);
        Route::get('{id}/edit',   ['as' => 'room.edit', 'uses' => 'RoomController@edit']);
        Route::get('{id}/price',   ['as' => 'room.price', 'uses' => 'RoomController@price']);
        Route::post('/store-price', ['as' => 'room.store-price', 'uses' => 'RoomController@storePrice']);
        Route::post('/update', ['as' => 'room.update', 'uses' => 'RoomController@update']);
        Route::get('{id}/destroy', ['as' => 'room.destroy', 'uses' => 'RoomController@destroy']);
    });
    Route::group(['prefix' => 'export'], function () {  
        Route::get('/cong-no-tour', ['as' => 'export.cong-no-tour', 'uses' => 'ExportController@congNoTour']);
        Route::get('/gui-tour', ['as' => 'export.gui-tour', 'uses' => 'ExportController@exportGui']);
        Route::get('/cong-no-hotel', ['as' => 'export.cong-no-hotel', 'uses' => 'ExportController@congNoHotel']);
    });
    Route::group(['prefix' => 'report'], function () {  
        Route::get('/chi-phi', ['as' => 'report.chi-phi', 'uses' => 'ReportController@chiphi']);
        Route::get('/dish', ['as' => 'report.dish', 'uses' => 'ReportController@dishReport']);
        Route::get('/vat', ['as' => 'report.vat', 'uses' => 'VatReportController@index']);
        Route::get('/thu-tien-nguoi', ['as' => 'report.thu-tien-nguoi', 'uses' => 'ThuTienReportController@index']);
        Route::post('/vat/no-vat', ['as' => 'report.vat.no-vat', 'uses' => 'VatReportController@setNoVat']);
        Route::get('/chi-phi-doanh-thu', ['as' => 'report.chi-phi-doanh-thu', 'uses' => 'ReportController@chiPhiDoanhThu']);
        Route::get('/customer-by-level', ['as' => 'report.customer-by-level', 'uses' => 'ReportController@customerByLevel']);
        Route::get('/doanh-thu-thang', ['as' => 'report.doanh-thu-thang', 'uses' => 'ReportController@doanhthuthang']);
        Route::get('/doanh-thu', ['as' => 'report.doanh-thu', 'uses' => 'ReportController@doanhthuview']);
        Route::get('/doanh-thu-thang-2', ['as' => 'report.doanh-thu-thang-2', 'uses' => 'ReportController@doanhthuthang2']);
        Route::get('/loi-nhuan', ['as' => 'report.loi-nhuan', 'uses' => 'ReportController@loiNhuan']);
        Route::get('/doanh-thu-thang-new', ['as' => 'report.doanh-thu-thang-new', 'uses' => 'ReportController@doanhthuthangNew']);
        Route::get('/doanh-so-doi-tac', ['as' => 'report.ds-doi-tac', 'uses' => 'ReportController@dsDoitac']);
        Route::get('/cano', ['as' => 'report.cano', 'uses' => 'ReportController@cano']);
        Route::get('/car', ['as' => 'report.car', 'uses' => 'ReportController@car']);
        Route::get('/cano-detail', ['as' => 'report.cano-detail', 'uses' => 'ReportController@canoDetail']);
        Route::get('/ajax-detail-cost', ['as' => 'report.ajax-detail-cost', 'uses' => 'ReportController@detailCostByPartner']);
        Route::get('/ben', ['as' => 'report.ben', 'uses' => 'ReportController@ben']);
        Route::get('/ajax-search-ben', ['as' => 'report.ajax-search-ben', 'uses' => 'ReportController@ajaxSearchBen']);
        Route::get('/thu-tien', ['as' => 'report.thu-tien', 'uses' => 'ReportController@thuTien']);
        Route::get('/thong-ke', ['as' => 'report.thong-ke', 'uses' => 'ReportController@thongkedoitac']);
        Route::get('/thong-ke-bai-bien', ['as' => 'report.thong-ke-bai-bien', 'uses' => 'ReportController@thongkebai']);
        
        
    });

    Route::group(['prefix' => 'booking'], function () {        
        Route::get('/', ['as' => 'booking.index', 'uses' => 'BookingController@index']);
        Route::get('/orders', ['as' => 'booking.orders', 'uses' => 'BookingController@orders']);
        Route::get('/curry', ['as' => 'booking.curry', 'uses' => 'BookingController@curry']);
        Route::get('/tandoor', ['as' => 'booking.tandoor', 'uses' => 'BookingController@tandoor']);
        Route::post('/orders/{detail}/done', ['as'=> 'booking.dish-done', 'uses' => 'BookingController@markDone']);

        Route::get('/im', ['as' => 'booking.im', 'uses' => 'BookingController@im']);
        Route::post('/storeIm', ['as' => 'booking.storeIm', 'uses' => 'BookingController@storeIm']);    
        Route::get('/get-dish', ['as' => 'booking.get-dish', 'uses' => 'BookingController@getDish']);
        Route::get('/fast-search', ['as' => 'booking.fast-search', 'uses' => 'BookingController@fastSearch']);
        Route::get('/excel', ['as' => 'booking.excel', 'uses' => 'BookingController@exportExcel']);
        Route::get('/not-export', ['as' => 'booking.not-export', 'uses' => 'BookingController@notExport']);
        Route::get('/change-export-status', ['as' => 'change-export-status', 'uses' => 'BookingController@changeExport']);
        Route::get('/change-status', ['as' => 'change-status', 'uses' => 'BookingController@changeStatus']);
        Route::get('/change-value', ['as' => 'change-value-by-column', 'uses' => 'BookingController@changeValueByColumn']);
        Route::get('/create', ['as' => 'booking.create', 'uses' => 'BookingController@create']);
        Route::get('/create-short', ['as' => 'booking.create-short', 'uses' => 'BookingController@createShort']);
        
        Route::get('/get-info', ['as' => 'booking.info', 'uses' => 'BookingController@info']);
        Route::get('/save-info', ['as' => 'booking.save-info', 'uses' => 'BookingController@saveInfo']);
        Route::post('/store', ['as' => 'booking.store', 'uses' => 'BookingController@store']);
        Route::post('/storeShort', ['as' => 'booking.store-short', 'uses' => 'BookingController@storeShort']);
        Route::post('/store-hotels', ['as' => 'booking.store-hotels', 'uses' => 'BookingController@storeHotel']);
        Route::post('/store-car', ['as' => 'booking.store-car', 'uses' => 'BookingController@storeCar']);
        Route::post('/update-hotels', ['as' => 'booking.update-hotels', 'uses' => 'BookingController@updateHotel']);
        Route::post('/update-car', ['as' => 'booking.update-car', 'uses' => 'BookingController@updateCar']);
        Route::get('{id}/edit',   ['as' => 'booking.edit', 'uses' => 'BookingController@edit']);      
       
       Route::get('{id}/detail',   ['as' => 'booking.detail', 'uses' => 'BookingController@detail']);
        Route::get('{id}/history',   ['as' => 'history.booking', 'uses' => 'HistoryController@booking']);
        Route::post('/update', ['as' => 'booking.update', 'uses' => 'BookingController@update']);
        Route::get('{id}/destroy', ['as' => 'booking.destroy', 'uses' => 'BookingController@destroy']);  
        Route::post('/store-cam', ['as' => 'booking.store-cam', 'uses' => 'BookingController@storeCam']);
        Route::post('/update-cam', ['as' => 'booking.update-cam', 'uses' => 'BookingController@updateCam']);
        Route::get('/check-error', ['as' => 'booking.checkError', 'uses' => 'BookingController@checkError']);
        Route::get('/check-unc', ['as' => 'booking.check-unc', 'uses' => 'BookingController@checkUnc']);
        Route::get('/export-customer', ['as' => 'booking.export-customer', 'uses' => 'ExportController@customerTour']);
        Route::get('/maps', ['as' => 'booking.maps', 'uses' => 'BookingController@maps']);

        Route::get('/sms', ['as' => 'booking.sms', 'uses' => 'BookingController@sms']);
        Route::post('/parse-sms', ['as' => 'booking.parse-sms', 'uses' => 'BookingController@parseSms']);
        Route::get('/change-value', ['as' => 'booking.change-value-by-column', 'uses' => 'BookingController@changeValueByColumn']);
    });
    Route::group(['prefix' => 'booking-hotel'], function () {        
        Route::get('/', ['as' => 'booking-hotel.index', 'uses' => 'BookingHotelController@index']);
        Route::get('/create', ['as' => 'booking-hotel.create', 'uses' => 'BookingHotelController@create']);
        Route::post('/store', ['as' => 'booking-hotel.store', 'uses' => 'BookingHotelController@store']);
        Route::post('/update', ['as' => 'booking-hotel.update', 'uses' => 'BookingHotelController@update']);
        Route::get('{id}/edit',   ['as' => 'booking-hotel.edit', 'uses' => 'BookingHotelController@edit']);      
        Route::get('{id}/destroy', ['as' => 'booking-hotel.destroy', 'uses' => 'BookingHotelController@destroy']);  
        Route::get('/related', ['as' => 'booking.related', 'uses' => 'BookingHotelController@related']);  
    });
    Route::group(['prefix' => 'booking-ticket'], function () {        
        Route::get('/', ['as' => 'booking-ticket.index', 'uses' => 'BookingTicketController@index']);
        Route::get('/create', ['as' => 'booking-ticket.create', 'uses' => 'BookingTicketController@create']);
        Route::post('/store', ['as' => 'booking-ticket.store', 'uses' => 'BookingTicketController@store']);
        Route::post('/update', ['as' => 'booking-ticket.update', 'uses' => 'BookingTicketController@update']);
        Route::get('{id}/edit',   ['as' => 'booking-ticket.edit', 'uses' => 'BookingTicketController@edit']);      
        Route::get('{id}/destroy', ['as' => 'booking-ticket.destroy', 'uses' => 'BookingTicketController@destroy']);    
    });
    Route::group(['prefix' => 'booking-camera'], function () {        
        Route::get('/', ['as' => 'booking-camera.index', 'uses' => 'BookingCameraController@index']);
        Route::get('/create', ['as' => 'booking-camera.create', 'uses' => 'BookingCameraController@create']);
        Route::post('/store', ['as' => 'booking-camera.store', 'uses' => 'BookingCameraController@store']);
        Route::post('/update', ['as' => 'booking-camera.update', 'uses' => 'BookingCameraController@update']);
        Route::get('{id}/edit',   ['as' => 'booking-camera.edit', 'uses' => 'BookingCameraController@edit']);      
        Route::get('{id}/destroy', ['as' => 'booking-camera.destroy', 'uses' => 'BookingCameraController@destroy']);    
    });
    Route::group(['prefix' => 'booking-car'], function () {        
        Route::get('/', ['as' => 'booking-car.index', 'uses' => 'BookingCarController@index']);
        Route::get('/create', ['as' => 'booking-car.create', 'uses' => 'BookingCarController@create']);
        Route::post('/store', ['as' => 'booking-car.store', 'uses' => 'BookingCarController@store']);
        Route::post('/update', ['as' => 'booking-car.update', 'uses' => 'BookingCarController@update']);
        Route::get('{id}/edit',   ['as' => 'booking-car.edit', 'uses' => 'BookingCarController@edit']);      
        Route::get('{id}/destroy', ['as' => 'booking-car.destroy', 'uses' => 'BookingCarController@destroy']);    
    });
    Route::group(['prefix' => 'booking-tour-dn'], function () {        
        Route::get('/', ['as' => 'booking-tour-dn.index', 'uses' => 'BookingTourDnController@index']);
        
        Route::get('/export', ['as' => 'booking-tour-dn.export', 'uses' => 'BookingTourDnController@export']);
        Route::get('/not-export', ['as' => 'booking-tour-dn.not-export', 'uses' => 'BookingTourDnController@notExport']);
        Route::get('/change-export-status', ['as' => 'change-export-status', 'uses' => 'BookingTourDnController@changeExport']);
        Route::get('/change-status', ['as' => 'change-status', 'uses' => 'BookingTourDnController@changeStatus']);
        Route::get('/change-value', ['as' => 'change-value-by-column', 'uses' => 'BookingTourDnController@changeValueByColumn']);
        Route::get('/create', ['as' => 'booking-tour-dn.create', 'uses' => 'BookingTourDnController@create']);
        Route::get('/related', ['as' => 'booking-tour-dn.related', 'uses' => 'BookingTourDnController@related']);
        Route::get('/related-partner', ['as' => 'booking-tour-dn.related-partner', 'uses' => 'BookingTourDnController@relatedPartner']);
        Route::get('/get-info', ['as' => 'booking-tour-dn.info', 'uses' => 'BookingTourDnController@info']);
        Route::get('/save-info', ['as' => 'booking-tour-dn.save-info', 'uses' => 'BookingTourDnController@saveInfo']);
        Route::post('/store', ['as' => 'booking-tour-dn.store', 'uses' => 'BookingTourDnController@store']);
       
        Route::get('{id}/edit',   ['as' => 'booking-tour-dn.edit', 'uses' => 'BookingTourDnController@edit']);
        Route::get('{id}/history',   ['as' => 'booking-tour-dn.booking', 'uses' => 'BookingTourDnController@booking']);
        Route::post('/update', ['as' => 'booking-tour-dn.update', 'uses' => 'BookingTourDnController@update']);
        Route::get('{id}/destroy', ['as' => 'booking-tour-dn.destroy', 'uses' => 'BookingTourDnController@destroy']);  
        Route::get('/check-error', ['as' => 'booking-tour-dn.checkError', 'uses' => 'BookingTourDnController@checkError']);
    });

    Route::group(['prefix' => 'booking-ticket-dn'], function () {        
        Route::get('/', ['as' => 'booking-ticket-dn.index', 'uses' => 'BookingTicketDnController@index']);
        
        Route::get('/export', ['as' => 'booking-ticket-dn.export', 'uses' => 'BookingTicketDnController@export']);
        Route::get('/not-export', ['as' => 'booking-ticket-dn.not-export', 'uses' => 'BookingTicketDnController@notExport']);
        Route::get('/change-export-status', ['as' => 'change-export-status', 'uses' => 'BookingTicketDnController@changeExport']);
        Route::get('/change-status', ['as' => 'change-status', 'uses' => 'BookingTicketDnController@changeStatus']);
        Route::get('/change-value', ['as' => 'change-value-by-column', 'uses' => 'BookingTicketDnController@changeValueByColumn']);
        Route::get('/create', ['as' => 'booking-ticket-dn.create', 'uses' => 'BookingTicketDnController@create']);
        Route::get('/related', ['as' => 'booking-ticket-dn.related', 'uses' => 'BookingTicketDnController@related']);
        Route::get('/get-info', ['as' => 'booking-ticket-dn.info', 'uses' => 'BookingTicketDnController@info']);
        Route::get('/save-info', ['as' => 'booking-ticket-dn.save-info', 'uses' => 'BookingTicketDnController@saveInfo']);
        Route::post('/store-ticket', ['as' => 'booking-ticket-dn.store-ticket', 'uses' => 'BookingTicketDnController@storeTicket']);
        Route::post('/update-ticket', ['as' => 'booking-ticket-dn.update-ticket', 'uses' => 'BookingTicketDnController@updateTicket']);
       
        Route::get('{id}/edit',   ['as' => 'booking-ticket-dn.edit', 'uses' => 'BookingTicketDnController@edit']);
        Route::get('{id}/history',   ['as' => 'booking-ticket-dn.booking', 'uses' => 'BookingTicketDnController@booking']);
        Route::get('{id}/destroy', ['as' => 'booking-ticket-dn.destroy', 'uses' => 'BookingTicketDnController@destroy']);  
        Route::get('/check-error', ['as' => 'booking-ticket-dn.checkError', 'uses' => 'BookingTicketDnController@checkError']);
    });
    Route::group(['prefix' => 'booking-hotel-dn'], function () {        
        Route::get('/', ['as' => 'booking-hotel-dn.index', 'uses' => 'BookingHotelDnController@index']);
        
        Route::get('/export', ['as' => 'booking-hotel-dn.export', 'uses' => 'BookingHotelDnController@export']);
        Route::get('/not-export', ['as' => 'booking-hotel-dn.not-export', 'uses' => 'BookingHotelDnController@notExport']);
        Route::get('/change-export-status', ['as' => 'change-export-status', 'uses' => 'BookingHotelDnController@changeExport']);
        Route::get('/change-status', ['as' => 'change-status', 'uses' => 'BookingHotelDnController@changeStatus']);
        Route::get('/change-value', ['as' => 'change-value-by-column', 'uses' => 'BookingHotelDnController@changeValueByColumn']);
        Route::get('/create', ['as' => 'booking-hotel-dn.create', 'uses' => 'BookingHotelDnController@create']);
        Route::get('/related', ['as' => 'booking-hotel-dn.related', 'uses' => 'BookingHotelDnController@related']);
        Route::get('/related-partner', ['as' => 'booking-hotel-dn.related-partner', 'uses' => 'BookingHotelDnController@relatedPartner']);
        Route::get('/get-info', ['as' => 'booking-hotel-dn.info', 'uses' => 'BookingHotelDnController@info']);
        Route::get('/save-info', ['as' => 'booking-hotel-dn.save-info', 'uses' => 'BookingHotelDnController@saveInfo']);
        Route::post('/store-hotels', ['as' => 'booking-hotel-dn.store-hotels', 'uses' => 'BookingHotelDnController@storeHotel']);
        Route::post('/update-hotels', ['as' => 'booking-hotel-dn.update-hotels', 'uses' => 'BookingHotelDnController@updateHotel']);
       
        Route::get('{id}/edit',   ['as' => 'booking-hotel-dn.edit', 'uses' => 'BookingHotelDnController@edit']);
        Route::get('{id}/history',   ['as' => 'booking-hotel-dn.booking', 'uses' => 'BookingHotelDnController@booking']);
        Route::post('/update', ['as' => 'booking-hotel-dn.update', 'uses' => 'BookingHotelDnController@update']);
        Route::get('{id}/destroy', ['as' => 'booking-hotel-dn.destroy', 'uses' => 'BookingHotelDnController@destroy']);  
        Route::get('/check-error', ['as' => 'booking-hotel-dn.checkError', 'uses' => 'BookingHotelDnController@checkError']);
    });
    Route::group(['prefix' => 'booking-car-dn'], function () {        
        Route::get('/', ['as' => 'booking-car-dn.index', 'uses' => 'BookingCarDnController@index']);
        
        Route::get('/export', ['as' => 'booking-car-dn.export', 'uses' => 'BookingCarDnController@export']);
        Route::get('/not-export', ['as' => 'booking-car-dn.not-export', 'uses' => 'BookingCarDnController@notExport']);
        Route::get('/change-export-status', ['as' => 'change-export-status', 'uses' => 'BookingCarDnController@changeExport']);
        Route::get('/change-status', ['as' => 'change-status', 'uses' => 'BookingCarDnController@changeStatus']);
        Route::get('/change-value', ['as' => 'change-value-by-column', 'uses' => 'BookingCarDnController@changeValueByColumn']);
        Route::get('/create', ['as' => 'booking-car-dn.create', 'uses' => 'BookingCarDnController@create']);
        Route::get('/related', ['as' => 'booking-car-dn.related', 'uses' => 'BookingCarDnController@related']);
        Route::get('/related-partner', ['as' => 'booking-car-dn.related-partner', 'uses' => 'BookingCarDnController@relatedPartner']);
        Route::get('/get-info', ['as' => 'booking-car-dn.info', 'uses' => 'BookingCarDnController@info']);
        Route::get('/save-info', ['as' => 'booking-car-dn.save-info', 'uses' => 'BookingCarDnController@saveInfo']);
        Route::post('/store-car', ['as' => 'booking-car-dn.store-car', 'uses' => 'BookingCarDnController@storeCar']);
        Route::post('/update-car', ['as' => 'booking-car-dn.update-car', 'uses' => 'BookingCarDnController@updateCar']);
       
        Route::get('{id}/edit',   ['as' => 'booking-car-dn.edit', 'uses' => 'BookingCarDnController@edit']);
        Route::get('{id}/history',   ['as' => 'booking-car-dn.booking', 'uses' => 'BookingCarDnController@booking']);
        Route::post('/update', ['as' => 'booking-car-dn.update', 'uses' => 'BookingCarDnController@update']);
        Route::get('{id}/destroy', ['as' => 'booking-car-dn.destroy', 'uses' => 'BookingCarDnController@destroy']);  
        Route::get('/check-error', ['as' => 'booking-car-dn.checkError', 'uses' => 'BookingCarDnController@checkError']);
    });
    Route::group(['prefix' => 'cost-payment'], function () {        
        Route::get('/', ['as' => 'cost-payment.index', 'uses' => 'CostPaymentController@index']);
        Route::get('/create', ['as' => 'cost-payment.create', 'uses' => 'CostPaymentController@create']);
        Route::post('/store', ['as' => 'cost-payment.store', 'uses' => 'CostPaymentController@store']);
        
        Route::get('{id}/edit',   ['as' => 'cost-payment.edit', 'uses' => 'CostPaymentController@edit']);
        Route::post('/update', ['as' => 'cost-payment.update', 'uses' => 'CostPaymentController@update']);
        Route::get('{id}/destroy', ['as' => 'cost-payment.destroy', 'uses' => 'CostPaymentController@destroy']);  
    });
    Route::group(['prefix' => 'booking-payment'], function () {        
        Route::get('/', ['as' => 'booking-payment.index', 'uses' => 'BookingPaymentController@index']);
        Route::get('/create', ['as' => 'booking-payment.create', 'uses' => 'BookingPaymentController@create']);
        Route::get('/store', ['as' => 'booking-payment.store', 'uses' => 'BookingPaymentController@storeAjax']);
        
        Route::get('{id}/edit',   ['as' => 'booking-payment.edit', 'uses' => 'BookingPaymentController@edit']);
        Route::post('/update', ['as' => 'booking-payment.update', 'uses' => 'BookingPaymentController@update']);
        Route::get('{id}/destroy', ['as' => 'booking-payment.destroy', 'uses' => 'BookingPaymentController@destroy']);  
    });
    Route::group(['prefix' => 'booking-bill'], function () {        
        Route::get('/', ['as' => 'booking-bill.index', 'uses' => 'BookingBillController@index']);
        Route::get('/create', ['as' => 'booking-bill.create', 'uses' => 'BookingBillController@create']);
        Route::post('/store', ['as' => 'booking-bill.store', 'uses' => 'BookingBillController@store']);
        
        Route::get('{id}/edit',   ['as' => 'booking-bill.edit', 'uses' => 'BookingBillController@edit']);
        Route::post('/update', ['as' => 'booking-bill.update', 'uses' => 'BookingBillController@update']);
        Route::get('{id}/destroy', ['as' => 'booking-bill.destroy', 'uses' => 'BookingBillController@destroy']);  
    });
    Route::group(['prefix' => 'food'], function () {        
        Route::get('/', ['as' => 'food.index', 'uses' => 'FoodController@index']);
        Route::get('/create', ['as' => 'food.create', 'uses' => 'FoodController@create']);
        Route::post('/store', ['as' => 'food.store', 'uses' => 'FoodController@store']);
        
        Route::get('{id}/edit',   ['as' => 'food.edit', 'uses' => 'FoodController@edit']);
        Route::post('/update', ['as' => 'food.update', 'uses' => 'FoodController@update']);
        Route::get('{id}/destroy', ['as' => 'food.destroy', 'uses' => 'FoodController@destroy']);  
    });  
    Route::group(['prefix' => 'orders'], function () {        
        Route::get('/', ['as' => 'orders.index', 'uses' => 'OrdersController@index']);
        Route::get('/image', ['as' => 'orders.image', 'uses' => 'OrdersController@image']);
        Route::get('/create', ['as' => 'orders.create', 'uses' => 'OrdersController@create']);
        Route::post('/store', ['as' => 'orders.store', 'uses' => 'OrdersController@store']);
        
        Route::get('{id}/edit',   ['as' => 'orders.edit', 'uses' => 'OrdersController@edit']);
        Route::post('/update', ['as' => 'orders.update', 'uses' => 'OrdersController@update']);
        Route::get('{id}/destroy', ['as' => 'orders.destroy', 'uses' => 'OrdersController@destroy']);  
    });  
    Route::group(['prefix' => 'settings'], function () {        
        Route::get('/', ['as' => 'settings.index', 'uses' => 'SettingsController@index']);
        
        Route::post('/store', ['as' => 'settings.store', 'uses' => 'SettingsController@store']);
        Route::post('/update', ['as' => 'settings.update', 'uses' => 'SettingsController@update']);
        
    });  
    Route::group(['prefix' => 'cost'], function () {        
        Route::get('/', ['as' => 'cost.index', 'uses' => 'CostController@index']);
        Route::get('/nhap-hang', ['as' => 'cost.nhap-hang', 'uses' => 'CostController@nhapHang']);
        Route::get('/bieu-do-gia', ['as' => 'cost.bieu-do-gia', 'uses' => 'CostController@bieuDoGia']);
        Route::get('/canh-bao-nhap', ['as' => 'cost.canh-bao-nhap', 'uses' => 'PurchaseAlertController@index']); // cảnh báo nhập tăng bất thường
        Route::get('/export', ['as' => 'cost.export', 'uses' => 'CostController@export']);
        Route::get('/ajax-doi-tac', ['as' => 'cost.ajax-doi-tac', 'uses' => 'CostController@ajaxDoiTac']);
        Route::get('/image', ['as' => 'cost.image', 'uses' => 'CostController@image']);
        Route::get('/create', ['as' => 'cost.create', 'uses' => 'CostController@create']);
        Route::post('/store', ['as' => 'cost.store', 'uses' => 'CostController@store']);
        Route::get('/sms', ['as' => 'cost.sms', 'uses' => 'CostController@sms']);
        Route::post('/parse-sms', ['as' => 'cost.parse-sms', 'uses' => 'CostController@parseSms']);
        Route::get('{id}/edit',   ['as' => 'cost.edit', 'uses' => 'CostController@edit']);
        Route::get('{id}/copy',   ['as' => 'cost.copy', 'uses' => 'CostController@copy']);
        Route::post('/update', ['as' => 'cost.update', 'uses' => 'CostController@update']);
        Route::get('{id}/destroy', ['as' => 'cost.destroy', 'uses' => 'CostController@destroy']);  
        Route::get('/change-value', ['as' => 'cost.change-value-by-column', 'uses' => 'CostController@changeValueByColumn']);
        Route::get('/ajax-cost-type', ['as' => 'cost.ajax-cost-type', 'uses' => 'CostController@ajaxGetCostType']);
        // API nhập chi phí nhanh (Claude gọi từ trình duyệt đã đăng nhập)
        Route::get('/api/master-data', ['as' => 'cost.api.master-data', 'uses' => 'CostApiController@masterData']);
        Route::post('/api/check-duplicate', ['as' => 'cost.api.check-duplicate', 'uses' => 'CostApiController@checkDuplicate']);
        Route::post('/api/store-batch', ['as' => 'cost.api.store-batch', 'uses' => 'CostApiController@storeBatch']);
        Route::get('/bill-upload', ['as' => 'cost.bill-upload', 'uses' => 'CostApiController@billUpload']);
        Route::post('/api/upload-bill', ['as' => 'cost.api.upload-bill', 'uses' => 'CostApiController@uploadBill']);
        Route::get('/api/inbox', ['as' => 'cost.api.inbox', 'uses' => 'CostApiController@inbox']);
        Route::post('/api/inbox-delete', ['as' => 'cost.api.inbox-delete', 'uses' => 'CostApiController@inboxDelete']);
        Route::post('/api/inbox-delete-all', ['as' => 'cost.api.inbox-delete-all', 'uses' => 'CostApiController@inboxDeleteAll']);
    }); 
    Route::group(['prefix' => 'grandworld-schedule'], function () {        
        Route::get('/', ['as' => 'grandworld-schedule.index', 'uses' => 'GrandworldScheduleController@index']);
    }); 
    Route::post('/change-value', ['as' => 'change-value', 'uses' => 'GeneralController@changeValue']);
    Route::get('/set-price', ['as' => 'set-price', 'uses' => 'HotelController@price']);
    Route::get('w-text', ['as' => 'w-text.index', 'uses' => "WSettingsController@text"]);
    Route::post('w-save-text', ['as' => 'w-text.save', 'uses' => "WSettingsController@saveText"]);
    Route::get('dashboard', ['as' => 'dashboard.index', 'uses' => "WSettingsController@dashboard"]);
    Route::post('save-content', ['as' => 'save-content', 'uses' => "WSettingsController@saveContent"]);   
    
    Route::post('/tmp-upload', ['as' => 'image.tmp-upload', 'uses' => 'UploadController@tmpUpload']);
    Route::post('/tmp-upload-multiple', ['as' => 'image.tmp-upload-multiple', 'uses' => 'UploadController@tmpUploadMultiple']);
        
    Route::post('/update-order', ['as' => 'update-order', 'uses' => 'GeneralController@updateOrder']);
    Route::post('/ck-upload', ['as' => 'ck-upload', 'uses' => 'UploadController@ckUpload']);
    Route::post('/get-slug', ['as' => 'get-slug', 'uses' => 'GeneralController@getSlug']);    

    Route::group(['prefix' => 'package'], function () {
        Route::get('/', ['as' => 'package.index', 'uses' => 'PackageController@index']);
        Route::get('/create', ['as' => 'package.create', 'uses' => 'PackageController@create']);
        Route::post('/store', ['as' => 'package.store', 'uses' => 'PackageController@store']);
        Route::get('{id}/edit',   ['as' => 'package.edit', 'uses' => 'PackageController@edit']);
        Route::post('/update', ['as' => 'package.update', 'uses' => 'PackageController@update']);
        Route::get('{id}/destroy', ['as' => 'package.destroy', 'uses' => 'PackageController@destroy']);
    });
    Route::group(['prefix' => 'account'], function () {
        Route::get('/', ['as' => 'account.index', 'uses' => 'AccountController@index']);
        Route::get('/change-password', ['as' => 'account.change-pass', 'uses' => 'AccountController@changePass']);
        Route::post('/store-password', ['as' => 'account.store-pass', 'uses' => 'AccountController@storeNewPass']);
        Route::get('/update-status/{status}/{id}', ['as' => 'account.update-status', 'uses' => 'AccountController@updateStatus']);
        Route::get('/create', ['as' => 'account.create', 'uses' => 'AccountController@create']);
        Route::get('/create-tx', ['as' => 'account.create-tx', 'uses' => 'AccountController@createTx']);
        Route::post('/store', ['as' => 'account.store', 'uses' => 'AccountController@store']);
        Route::post('/store-tx', ['as' => 'account.store-tx', 'uses' => 'AccountController@storeTx']);
        Route::get('{id}/edit',   ['as' => 'account.edit', 'uses' => 'AccountController@edit']);
        Route::post('/update', ['as' => 'account.update', 'uses' => 'AccountController@update']);
        Route::get('{id}/destroy', ['as' => 'account.destroy', 'uses' => 'AccountController@destroy']);
    });    
    Route::group(['prefix' => 'articles'], function () {
        Route::get('/', ['as' => 'articles.index', 'uses' => 'ArticlesController@index']);
        Route::get('/create', ['as' => 'articles.create', 'uses' => 'ArticlesController@create']);
        Route::post('/store', ['as' => 'articles.store', 'uses' => 'ArticlesController@store']);
        Route::get('{id}/edit',   ['as' => 'articles.edit', 'uses' => 'ArticlesController@edit']);      
        Route::post('/update', ['as' => 'articles.update', 'uses' => 'ArticlesController@update']);
        Route::get('{id}/destroy', ['as' => 'articles.destroy', 'uses' => 'ArticlesController@destroy']);
    });
    Route::group(['prefix' => 'customer'], function () {
        Route::get('/', ['as' => 'customer.index', 'uses' => 'CustomerController@index']);
        Route::get('{id}/edit',   ['as' => 'customer.edit', 'uses' => 'CustomerController@edit']);
        Route::post('/update', ['as' => 'customer.update', 'uses' => 'CustomerController@update']);
        Route::get('{id}/destroy', ['as' => 'customer.destroy', 'uses' => 'CustomerController@destroy']);
        Route::get('/update-status/{status}/{id}', ['as' => 'customer.update-status', 'uses' => 'CustomerController@updateStatus']);
        Route::get('/export',   ['as' => 'customer.export', 'uses' => 'CustomerController@export']);
    });

    Route::group(['prefix' => 'staff'], function () {
        Route::get('/', ['as' => 'staff.index', 'uses' => 'StaffController@index']);
        Route::get('{id}/edit',   ['as' => 'staff.edit', 'uses' => 'StaffController@edit']);
        Route::get('/create', ['as' => 'staff.create', 'uses' => 'StaffController@create']);
        Route::post('/store', ['as' => 'staff.store', 'uses' => 'StaffController@store']);
        Route::post('/update', ['as' => 'staff.update', 'uses' => 'StaffController@update']);
        Route::get('{id}/destroy', ['as' => 'staff.destroy', 'uses' => 'StaffController@destroy']);
        Route::get('/update-status/{status}/{id}', ['as' => 'custaffstomer.update-status', 'uses' => 'StaffController@updateStatus']);
        Route::get('/export',   ['as' => 'staff.export', 'uses' => 'StaffController@export']);
        Route::get('{id}/reset-pass', ['uses' => 'StaffController@editPass', 'as' => 'staff.editPass']);
        Route::get('modal-staff', ['uses' => 'StaffController@getModalStaff', 'as' => 'staff.getModal']);
    });
    Route::group(['prefix' => 'ctv'], function () {
        Route::get('/', ['as' => 'ctv.index', 'uses' => 'CtvController@index']);
        Route::get('{id}/destroy', ['as' => 'ctv.destroy', 'uses' => 'CtvController@destroy']);
        Route::get('{id}/edit',   ['as' => 'ctv.edit', 'uses' => 'CtvController@edit']);
        Route::get('/create', ['as' => 'ctv.create', 'uses' => 'CtvController@create']);
        Route::post('/store', ['as' => 'ctv.store', 'uses' => 'CtvController@store']);
        Route::post('/update', ['as' => 'ctv.update', 'uses' => 'CtvController@update']);
        
        Route::get('/update-status/{status}/{id}', ['as' => 'ctv.update-status', 'uses' => 'CtvController@updateStatus']);
        Route::get('/export',   ['as' => 'staff.export', 'uses' => 'CtvController@export']);
        Route::get('{id}/reset-pass', ['uses' => 'CtvController@editPass', 'as' => 'ctv.editPass']);
        Route::get('modal-staff', ['uses' => 'CtvController@getModalStaff', 'as' => 'ctv.getModal']);
    });
    Route::group(['prefix' => 'hdv'], function () {
        Route::get('/', ['as' => 'hdv.index', 'uses' => 'HdvController@index']);
        Route::get('{id}/destroy', ['as' => 'hdv.destroy', 'uses' => 'HdvController@destroy']);
        Route::get('{id}/edit',   ['as' => 'hdv.edit', 'uses' => 'HdvController@edit']);
        Route::get('/create', ['as' => 'hdv.create', 'uses' => 'HdvController@create']);
        Route::post('/store', ['as' => 'hdv.store', 'uses' => 'HdvController@store']);
        Route::post('/update', ['as' => 'hdv.update', 'uses' => 'HdvController@update']);   
    });
    Route::group(['prefix' => 'payment-request'], function () {        
        Route::get('/', ['as' => 'payment-request.index', 'uses' => 'PaymentRequestController@index']);
        Route::get('/urgent', ['as' => 'payment-request.urgent', 'uses' => 'PaymentRequestController@urgent']);
        Route::get('/diem-danh', ['as' => 'payment-request.diem-danh', 'uses' => 'PaymentRequestController@diemDanh']);
        Route::get('/create', ['as' => 'payment-request.create', 'uses' => 'PaymentRequestController@create']);
        Route::post('/store', ['as' => 'payment-request.store', 'uses' => 'PaymentRequestController@store']);
        Route::get('/ajax-store', ['as' => 'payment-request.ajax-store', 'uses' => 'PaymentRequestController@ajaxStore']);
        
        Route::get('{id}/edit',   ['as' => 'payment-request.edit', 'uses' => 'PaymentRequestController@edit']);
        Route::post('/update', ['as' => 'payment-request.update', 'uses' => 'PaymentRequestController@update']);
        Route::get('{id}/destroy', ['as' => 'payment-request.destroy', 'uses' => 'PaymentRequestController@destroy']); 
        Route::get('/change-value', ['as' => 'payment-request.change-value-by-column', 'uses' => 'PaymentRequestController@changeValueByColumn']);   
        Route::get('/export',   ['as' => 'payment-request.export', 'uses' => 'PaymentRequestController@export']);
    });

    Route::group(['prefix' => 'task'], function () {
        Route::get('/', ['as' => 'task.index', 'uses' => 'TaskController@index']);
        Route::get('/create', ['as' => 'task.create', 'uses' => 'TaskController@create']);
        Route::post('/store', ['as' => 'task.store', 'uses' => 'TaskController@store']);
        Route::get('{id}/edit',   ['as' => 'task.edit', 'uses' => 'TaskController@edit']);
        Route::post('/update', ['as' => 'task.update', 'uses' => 'TaskController@update']);
        Route::get('{id}/destroy', ['as' => 'task.destroy', 'uses' => 'TaskController@destroy']);
        Route::get('{id}/delete', ['as' => 'task.delete', 'uses' => 'TaskController@delete']);
        Route::post('/ajaxSave', ['as' => 'task.ajax-save', 'uses' => 'TaskController@ajaxSave']);  
        Route::get('/ajax-list', ['as' => 'task.ajax-list', 'uses' => 'TaskController@ajaxList']);      
    });

    Route::group(['prefix' => 'task-detail'], function () {
        Route::get('/', ['as' => 'task-detail.index', 'uses' => 'TaskDetailController@index']);
        Route::get('/create', ['as' => 'task-detail.create', 'uses' => 'TaskDetailController@create']);
        Route::post('/store', ['as' => 'task-detail.store', 'uses' => 'TaskDetailController@store']);
        Route::get('{id}/edit',   ['as' => 'task-detail.edit', 'uses' => 'TaskDetailController@edit']);
        Route::post('/update', ['as' => 'task-detail.update', 'uses' => 'TaskDetailController@update']);
        Route::get('{id}/destroy', ['as' => 'task-detail.destroy', 'uses' => 'TaskDetailController@destroy']);
    });
    Route::group(['prefix' => 'combo'], function () {
        Route::get('/', ['as' => 'combo.index', 'uses' => 'ComboController@index']);
        Route::get('/create', ['as' => 'combo.create', 'uses' => 'ComboController@create']);
        Route::post('/store', ['as' => 'combo.store', 'uses' => 'ComboController@store']);
        Route::get('{id}/edit',   ['as' => 'combo.edit', 'uses' => 'ComboController@edit']);
        Route::post('/update', ['as' => 'combo.update', 'uses' => 'ComboController@update']);
        Route::get('{id}/destroy', ['as' => 'combo.destroy', 'uses' => 'ComboController@destroy']);
    }); 
    Route::group(['prefix' => 'booking-car'], function () {        
        Route::get('/', ['as' => 'booking-car.index', 'uses' => 'BookingCarController@index']);
        
       
    });
     Route::group(['prefix' => 'ntv'], function () {
        Route::get('/', ['as' => 'ntv.index', 'uses' => 'NguoiTuVanController@index']);
        Route::get('/create', ['as' => 'ntv.create', 'uses' => 'NguoiTuVanController@create']);
        Route::post('/store', ['as' => 'ntv.store', 'uses' => 'NguoiTuVanController@store']);
        Route::get('{id}/edit',   ['as' => 'ntv.edit', 'uses' => 'NguoiTuVanController@edit']);
        Route::post('/update', ['as' => 'ntv.update', 'uses' => 'NguoiTuVanController@update']);
        Route::get('{id}/destroy', ['as' => 'ntv.destroy', 'uses' => 'NguoiTuVanController@destroy']);
        Route::get('/change-value', ['as' => 'ntv.change-value-by-column', 'uses' => 'NguoiTuVanController@changeValueByColumn']);
    });    

    Route::group(['prefix' => 'booking-bbc'], function () {        
        Route::get('/', ['as' => 'booking-bbc.index', 'uses' => 'BookingBbcController@index']);
        Route::get('/change-value', ['as' => 'change-value-by-column', 'uses' => 'BookingBbcController@changeValueByColumn']);
        Route::get('/create', ['as' => 'booking-bbc.create', 'uses' => 'BookingBbcController@create']);       
        Route::post('/store', ['as' => 'booking-bbc.store', 'uses' => 'BookingBbcController@store']);
        Route::get('{id}/edit',   ['as' => 'booking-bbc.edit', 'uses' => 'BookingBbcController@edit']);
        Route::get('{id}/history',   ['as' => 'history.booking', 'uses' => 'HistoryController@booking']);
        Route::post('/update', ['as' => 'booking-bbc.update', 'uses' => 'BookingBbcController@update']);
        Route::get('{id}/destroy', ['as' => 'booking-bbc.destroy', 'uses' => 'BookingBbcController@destroy']);        
        Route::get('/change-value', ['as' => 'booking-bbc.change-value-by-column', 'uses' => 'BookingBbcController@changeValueByColumn']);
    });
    Route::group(['prefix' => 'ung-luong'], function () {        
        Route::get('/', ['as' => 'ung-luong.index', 'uses' => 'UngLuongController@index']);  
       
        Route::get('/create', ['as' => 'ung-luong.create', 'uses' => 'UngLuongController@create']);
        Route::post('/store', ['as' => 'ung-luong.store', 'uses' => 'UngLuongController@store']);
       
        Route::get('{id}/edit',   ['as' => 'ung-luong.edit', 'uses' => 'UngLuongController@edit']);

        Route::post('/update', ['as' => 'ung-luong.update', 'uses' => 'UngLuongController@update']);
        Route::get('{id}/destroy', ['as' => 'ung-luong.destroy', 'uses' => 'UngLuongController@destroy']);  
        Route::get('/change-value', ['as' => 'ung-luong.change-value-by-column', 'uses' => 'UngLuongController@changeValueByColumn']);
        
    }); 
    Route::group(['prefix' => 'jetski'], function () {        
        Route::get('/', ['as' => 'jetski.index', 'uses' => 'JetskiController@index']);      
        Route::get('/report', ['as' => 'jetski.report', 'uses' => 'JetskiController@report']); 
        Route::get('/export', ['as' => 'jetski.export', 'uses' => 'JetskiController@export']);         
        Route::get('/create', ['as' => 'jetski.create', 'uses' => 'JetskiController@create']);
        Route::post('/store', ['as' => 'jetski.store', 'uses' => 'JetskiController@store']);
        Route::get('{id}/edit',   ['as' => 'jetski.edit', 'uses' => 'JetskiController@edit']);
        Route::post('/update', ['as' => 'jetski.update', 'uses' => 'JetskiController@update']);
        Route::get('{id}/destroy', ['as' => 'jetski.destroy', 'uses' => 'JetskiController@destroy']);
        Route::get('/change-value', ['as' => 'jetski.change-value-by-column', 'uses' => 'JetskiController@changeValueByColumn']);  
    });
});