<?php
/*
| Cấu hình nghiệp vụ nhà hàng Bombay (14/11/2026)
*/
return [
    // Thuế VAT (%) cộng thêm trên (tổng bill − giảm giá), trừ bill tick "KHÔNG VAT"
    'vat_rate' => 8,
    // Chi nhánh KHÔNG tính VAT (bill đồng bộ từ Sapo): 12 = Vui Fest
    'no_vat_branches' => [12],
    // Hoa hồng đối tác mặc định (dùng khi chưa có cài đặt trong bảng settings)
    'com_alacarte_pct_default' => 15,
    'com_buffet_per_pax_default' => 20000,
];
