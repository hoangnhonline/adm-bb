<?php
/*
|--------------------------------------------------------------------------
| Phân quyền theo chức năng × hành động (14/11/2026)
|--------------------------------------------------------------------------
| - modules: danh sách chức năng và hành động hiển thị trên ma trận phân quyền.
|   Mã quyền = "<module>.<action>", ví dụ "bill.edit".
| - routes: route name -> mã quyền cần có. Hỗ trợ dấu * ở cuối (vd 'report.doanh-thu*').
|   Tài khoản đã gán nhóm quyền chỉ vào được route có trong bảng này hoặc trong 'always'.
| - always: route ai đăng nhập cũng dùng được (đổi mật khẩu, đổi ngôn ngữ...).
| - Tài khoản CHƯA gán nhóm (users.role_id NULL) và tài khoản id 1 không bị ảnh hưởng.
| Thêm chức năng mới: thêm hành động vào 'modules' và route vào 'routes'.
*/
return [
    'super_admin_ids' => [1],

    'actions' => [
        'view' => 'Xem', 'create' => 'Thêm', 'edit' => 'Sửa', 'delete' => 'Xóa', 'export' => 'Xuất Excel',
        'edit_old' => 'Sửa/xóa bill ngày cũ', 'transaction' => 'Ghi thanh toán / hoa hồng',
    ],

    'modules' => [
        'bill'        => ['label' => 'Bill', 'actions' => ['view', 'create', 'edit', 'delete', 'export', 'edit_old']],
        'kitchen'     => ['label' => 'Màn hình bếp', 'actions' => ['view', 'edit']],
        'cost'        => ['label' => 'Chi phí', 'actions' => ['view', 'create', 'edit', 'delete', 'export']],
        'bill_upload' => ['label' => 'Upload bill chi phí', 'actions' => ['view', 'create', 'delete']],
        'purchase'    => ['label' => 'Nhập hàng & giá', 'actions' => ['view']],
        'report'      => ['label' => 'Báo cáo', 'actions' => ['view']],
        'partner'     => ['label' => 'Đối tác & công nợ', 'actions' => ['view', 'create', 'edit', 'transaction']],
        'catalog'     => ['label' => 'Danh mục (món ăn, NCC)', 'actions' => ['view', 'create', 'edit', 'delete']],
        'account'     => ['label' => 'Tài khoản', 'actions' => ['view', 'edit']],
        'bill_alert'  => ['label' => 'Bill bất thường (Sửa = đánh dấu đã xem)', 'actions' => ['view', 'edit']],
    ],

    'always' => [
        'logout', 'login', 'home', 'account.change-pass', 'account.store-pass', 'locale.change', 'filter-prefs.save',
    ],

    'routes' => [
        // Bill
        'dashboard' => 'bill.view', 'booking.index' => 'bill.view', 'booking.fast-search' => 'bill.view', 'booking.detail' => 'bill.view',
        'booking-qrcode' => 'bill.view',
        'booking.create' => 'bill.create', 'booking.store' => 'bill.create', 'booking.get-dish' => 'bill.create',
        'booking.create-short' => 'bill.create', 'booking.store-short' => 'bill.create',
        'booking.edit' => 'bill.edit', 'booking.update' => 'bill.edit', 'booking.change-value-by-column' => 'bill.edit',
        'booking.info' => 'bill.edit', 'booking.save-info' => 'bill.edit',
        'booking-payment.*' => 'bill.edit',
        'booking.destroy' => 'bill.delete',
        'booking.excel' => 'bill.export',
        // Bếp
        'booking.orders' => 'kitchen.view', 'booking.curry' => 'kitchen.view', 'booking.tandoor' => 'kitchen.view',
        'booking.dish-done' => 'kitchen.edit',
        // Chi phí
        'cost.index' => 'cost.view', 'cost.image' => 'cost.view', 'cost.ajax-doi-tac' => 'cost.view', 'cost.ajax-cost-type' => 'cost.view',
        'cost.create' => 'cost.create', 'cost.store' => 'cost.create', 'cost.copy' => 'cost.create',
        'cost.api.master-data' => 'cost.create', 'cost.api.check-duplicate' => 'cost.create', 'cost.api.store-batch' => 'cost.create',
        'cost.edit' => 'cost.edit', 'cost.update' => 'cost.edit', 'cost.change-value-by-column' => 'cost.edit',
        'cost.destroy' => 'cost.delete',
        'cost.export' => 'cost.export',
        // Upload bill
        'cost.bill-upload' => 'bill_upload.view', 'cost.api.inbox' => 'bill_upload.view',
        'cost.api.upload-bill' => 'bill_upload.create',
        'cost.api.inbox-delete' => 'bill_upload.delete', 'cost.api.inbox-delete-all' => 'bill_upload.delete',
        // Nhập hàng
        'cost.nhap-hang' => 'purchase.view', 'cost.bieu-do-gia' => 'purchase.view', 'cost.canh-bao-nhap' => 'purchase.view',
        // Báo cáo
        'report.dish' => 'report.view', 'report.chi-phi-doanh-thu' => 'report.view', 'report.doanh-thu*' => 'report.view',
        'report.chi-phi' => 'report.view', 'report.loi-nhuan' => 'report.view', 'report.thu-tien' => 'report.view',
        // Đối tác & công nợ
        'doi-tac.index' => 'partner.view', 'doi-tac.show' => 'partner.view', 'doi-tac.report' => 'partner.view', 'doi-tac.export' => 'partner.view',
        'doi-tac.create' => 'partner.create', 'doi-tac.store' => 'partner.create',
        'doi-tac.edit' => 'partner.edit', 'doi-tac.update' => 'partner.edit', 'doi-tac.destroy' => 'partner.edit', 'doi-tac.recalc' => 'partner.edit',
        'doi-tac.transaction.store' => 'partner.transaction', 'doi-tac.transaction.cancel' => 'partner.transaction',
        // Danh mục
        'dish.index' => 'catalog.view', 'partner.index' => 'catalog.view',
        'dish.create' => 'catalog.create', 'dish.store' => 'catalog.create', 'partner.create' => 'catalog.create', 'partner.store' => 'catalog.create',
        'dish.edit' => 'catalog.edit', 'dish.update' => 'catalog.edit', 'dish.change-value-by-column' => 'catalog.edit',
        'partner.edit' => 'catalog.edit', 'partner.update' => 'catalog.edit', 'partner.change-value-by-column' => 'catalog.edit',
        'dish.destroy' => 'catalog.delete', 'partner.destroy' => 'catalog.delete',
        // Tổng quan & bill bất thường
        'tong-quan.index' => 'report.view',
        'bill-anomaly.index' => 'bill_alert.view', 'bill-anomaly.review' => 'bill_alert.edit',
        // Tài khoản
        'account.index' => 'account.view',
        'account.create' => 'account.edit', 'account.store' => 'account.edit', 'account.edit' => 'account.edit', 'account.update' => 'account.edit',
        'account.update-status' => 'account.edit', 'account.destroy' => 'account.edit',
    ],

    // Kiểm tra theo bản ghi (chi nhánh + bill ngày cũ). key = route name
    'record_checks' => [
        'booking.edit' => ['table' => 'booking', 'param' => 'id', 'old_perm' => 'bill.edit_old'],
        'booking.update' => ['table' => 'booking', 'param' => 'id', 'old_perm' => 'bill.edit_old'],
        'booking.destroy' => ['table' => 'booking', 'param' => 'id', 'old_perm' => 'bill.edit_old'],
        'booking.change-value-by-column' => ['table' => 'booking', 'param' => 'id', 'old_perm' => 'bill.edit_old'],
        'booking.detail' => ['table' => 'booking', 'param' => 'id'],
        'cost.edit' => ['table' => 'cost', 'param' => 'id'],
        'cost.copy' => ['table' => 'cost', 'param' => 'id'],
        'cost.update' => ['table' => 'cost', 'param' => 'id'],
        'cost.destroy' => ['table' => 'cost', 'param' => 'id'],
        'cost.change-value-by-column' => ['table' => 'cost', 'param' => 'id'],
    ],

    // Menu cho tài khoản có nhóm quyền: [route, mã quyền, icon, nhãn]
    'menu' => [
        ['tong-quan.index', 'report.view', 'fa-dashboard', 'TỔNG QUAN'],
        ['booking.index', 'bill.view', 'fa-list', 'QUẢN LÝ BILL'],
        ['booking.create', 'bill.create', 'fa-plus', 'TẠO BILL'],
        ['booking.orders', 'kitchen.view', 'fa-cutlery', 'BẾP'],
        ['cost.index', 'cost.view', 'fa-money', 'CHI PHÍ'],
        ['cost.bill-upload', 'bill_upload.view', 'fa-upload', 'UPLOAD BILL'],
        ['cost.nhap-hang', 'purchase.view', 'fa-shopping-cart', 'NHẬP HÀNG'],
        ['cost.canh-bao-nhap', 'purchase.view', 'fa-exclamation-triangle', 'CẢNH BÁO NHẬP HÀNG'],
        ['cost.bieu-do-gia', 'purchase.view', 'fa-line-chart', 'BIỂU ĐỒ GIÁ'],
        ['report.dish', 'report.view', 'fa-trophy', 'MÓN BÁN CHẠY'],
        ['report.chi-phi-doanh-thu', 'report.view', 'fa-pie-chart', 'CHI PHÍ / DOANH THU'],
        ['bill-anomaly.index', 'bill_alert.view', 'fa-exclamation-circle', 'BILL BẤT THƯỜNG'],
        ['doi-tac.index', 'partner.view', 'fa-handshake-o', 'ĐỐI TÁC & CÔNG NỢ'],
        ['doi-tac.report', 'partner.view', 'fa-line-chart', 'BÁO CÁO CÔNG NỢ ĐỐI TÁC'],
        ['dish.index', 'catalog.view', 'fa-book', 'MÓN ĂN'],
        ['account.index', 'account.view', 'fa-users', 'TÀI KHOẢN'],
    ],
];
