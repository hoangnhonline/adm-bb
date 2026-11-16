-- Tiền chiết khấu (hoa hồng đối tác) sửa tay trên từng bill (16/11/2026)
-- NULL = hệ thống tự tính; có số = dùng đúng số người dùng nhập. An toàn khi chạy lại.
SET NAMES utf8mb4;
ALTER TABLE `booking` ADD COLUMN IF NOT EXISTS `com_manual` INT NULL COMMENT 'Tiền chiết khấu nhập tay; NULL = tự tính' AFTER `com_pct`;
