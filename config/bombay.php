<?php
/*
| Cấu hình nghiệp vụ nhà hàng Bombay (14/11/2026)
*/
return [
    // Thuế VAT (%) cộng thêm trên (tổng bill − giảm giá), trừ bill tick "KHÔNG VAT"
    'vat_rate' => 8,
    // Chi nhánh KHÔNG tính VAT (bill đồng bộ từ Sapo): 12 = Vui Fest
    'no_vat_branches' => [12],
    // Chi nhánh dùng màn hình bếp (BẾP / CURRY / BÁNH): 1 = Night Market. Chi nhánh khác: bill lưu xong là hoàn tất
    'kitchen_branch_id' => 1,
    // Chi nhánh có ô tick "KIOT" trên danh sách bill (booking.is_kiot): 1 = Night Market, 2 = 124 THD
    'kiot_branches' => [1, 2],
    // Hoa hồng đối tác mặc định (dùng khi chưa có cài đặt trong bảng settings)
    'com_alacarte_pct_default' => 15,
    'com_buffet_per_pax_default' => 20000,
    // Hoa hồng buffet trẻ em (món tên bắt đầu "BUFFET TE"), đ/khách — áp dụng chung mọi đối tác
    'com_buffet_child_per_pax_default' => 10000,
];
