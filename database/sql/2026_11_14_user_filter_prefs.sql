-- Bộ lọc tùy chọn theo user (port từ Plan To Travel). Lưu các ô filter mỗi user chọn hiển thị trên từng trang.
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS `user_filter_prefs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `page` VARCHAR(100) NOT NULL COMMENT 'route name của trang',
  `visible_keys` TEXT NULL COMMENT 'JSON: danh sách filter user chọn hiển thị',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_page` (`user_id`, `page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
