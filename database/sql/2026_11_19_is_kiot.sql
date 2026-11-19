-- Đánh dấu bill đã nhập KiotViet (chi nhánh Night Market & 124 THD) (19/11/2026). An toàn khi chạy lại.
SET NAMES utf8mb4;
ALTER TABLE `booking` ADD COLUMN IF NOT EXISTS `is_kiot` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = đã nhập Kiot';
