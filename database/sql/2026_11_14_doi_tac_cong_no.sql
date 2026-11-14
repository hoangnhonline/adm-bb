-- =====================================================================
-- Đối tác & Công nợ đối tác & gán bill cho đối tác
-- Ngày: 2026-11-14   |  MariaDB 10.x (ADD COLUMN IF NOT EXISTS)
-- Chạy 1 lần. An toàn khi chạy lại (IF NOT EXISTS / WHERE NOT EXISTS).
-- KHÔNG xóa / sửa dữ liệu cũ.
-- =====================================================================
SET NAMES utf8mb4;

-- 1) Mở rộng bảng partners (bảng cũ dùng cho NCC chi phí theo cost_type_id).
--    Đối tác nhà hàng được đánh dấu doi_tac = 1 và cost_type_id = NULL
--    nên KHÔNG xuất hiện trong các dropdown chi phí cũ.
ALTER TABLE `partners`
  ADD COLUMN IF NOT EXISTS `doi_tac` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = đối tác bán hàng của nhà hàng',
  ADD COLUMN IF NOT EXISTS `type` VARCHAR(20) NULL COMMENT 'tour|hotel|hdv|ptt|cano',
  ADD COLUMN IF NOT EXISTS `contact_name` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `tax_code` VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS `address` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `bank_info` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `pay_term_days` INT NOT NULL DEFAULT 0 COMMENT 'số ngày được nợ, 0 = trả ngay',
  ADD COLUMN IF NOT EXISTS `com_buffet_per_pax` INT NOT NULL DEFAULT 20000 COMMENT 'hoa hồng buffet / khách',
  ADD COLUMN IF NOT EXISTS `com_alacarte_pct` DECIMAL(5,2) NOT NULL DEFAULT 10.00 COMMENT '% hoa hồng alacarte trên (tổng bill - giảm giá)',
  ADD COLUMN IF NOT EXISTS `branch_id` INT NULL COMMENT 'NULL = mọi chi nhánh';
ALTER TABLE `partners` MODIFY `phone` VARCHAR(30) NULL;
ALTER TABLE `partners` ADD INDEX IF NOT EXISTS `idx_partners_doi_tac` (`doi_tac`, `status`);

-- 2) Bill (booking): gán đối tác + hoa hồng
ALTER TABLE `booking`
  ADD COLUMN IF NOT EXISTS `doi_tac_id` INT NULL COMMENT 'partners.id; NULL = khách lẻ',
  ADD COLUMN IF NOT EXISTS `co_chiet_khau` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = tính hoa hồng cho đối tác',
  ADD COLUMN IF NOT EXISTS `ghi_no` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = đối tác nợ lại tiền bill',
  ADD COLUMN IF NOT EXISTS `bill_type` TINYINT(1) NULL COMMENT '1 buffet, 2 alacarte (tự nhận diện)',
  ADD COLUMN IF NOT EXISTS `pax` INT NOT NULL DEFAULT 0 COMMENT 'số khách buffet (tổng SL món BUFFET*)',
  ADD COLUMN IF NOT EXISTS `com_amount` INT NOT NULL DEFAULT 0 COMMENT 'tiền hoa hồng phải trả đối tác';
ALTER TABLE `booking` ADD INDEX IF NOT EXISTS `idx_booking_doi_tac` (`doi_tac_id`, `use_date`);

-- 3) Sổ giao dịch công nợ đối tác (chỉ các khoản tiền; phần bill tính trực tiếp từ booking)
CREATE TABLE IF NOT EXISTS `partner_transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `partner_id` INT NOT NULL,
  `branch_id` INT NULL,
  `date` DATE NOT NULL,
  `type` TINYINT(1) NOT NULL COMMENT '1 đối tác thanh toán (giảm phải thu), 2 chi hoa hồng (giảm phải trả), 3 bù trừ (giảm cả hai)',
  `amount` BIGINT NOT NULL DEFAULT 0,
  `pay_method` INT NULL COMMENT 'collecter.id: quỹ thu / quỹ chi',
  `cost_id` INT NULL COMMENT 'cost.id tự tạo khi chi hoa hồng / bù trừ',
  `notes` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = đã hủy',
  `created_user` INT NULL,
  `updated_user` INT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pt_partner` (`partner_id`, `status`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Loại chi phí "Hoa hồng đối tác" (dùng khi chi hoa hồng / bù trừ)
INSERT INTO `cost_type` (`name`, `display_order`, `type`, `status`, `group_name`)
SELECT 'Hoa hồng đối tác', 60, 1, 1, 'Vận hành' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `cost_type` WHERE `name` = 'Hoa hồng đối tác');
