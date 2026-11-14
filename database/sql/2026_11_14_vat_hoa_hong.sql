-- VAT + hoa hồng: % hoa hồng alacarte sửa được trên từng bill, mặc định toàn hệ thống 15% (14/11/2026)
-- Chạy SAU file 2026_11_14_doi_tac_cong_no.sql. An toàn khi chạy lại.
SET NAMES utf8mb4;
ALTER TABLE `booking` ADD COLUMN IF NOT EXISTS `com_pct` DECIMAL(5,2) NULL COMMENT '% hoa hồng alacarte riêng cho bill; NULL = theo đối tác';
ALTER TABLE `partners` ALTER COLUMN `com_alacarte_pct` SET DEFAULT 15.00;
INSERT INTO `settings` (`name`, `value`, `created_at`, `updated_at`)
SELECT 'com_alacarte_pct_default', '15', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `name` = 'com_alacarte_pct_default');
INSERT INTO `settings` (`name`, `value`, `created_at`, `updated_at`)
SELECT 'com_buffet_per_pax_default', '20000', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `name` = 'com_buffet_per_pax_default');
