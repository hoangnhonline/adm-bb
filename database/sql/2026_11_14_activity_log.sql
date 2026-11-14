-- Lịch sử thao tác của user (14/11/2026)
--  activity_log: thay đổi dữ liệu (thêm / sửa / xóa) — LƯU VĨNH VIỄN
--  request_log : các thao tác gửi form / xóa (không ghi lượt xem trang) — tự xóa sau 12 tháng
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `model` VARCHAR(60) NOT NULL COMMENT 'tên bảng: booking, cost, ...',
  `model_id` BIGINT NULL,
  `action` VARCHAR(10) NOT NULL COMMENT 'created | updated | deleted',
  `old_values` MEDIUMTEXT NULL COMMENT 'JSON',
  `new_values` MEDIUMTEXT NULL COMMENT 'JSON',
  `request_uid` VARCHAR(32) NULL COMMENT 'liên kết với request_log',
  `url` VARCHAR(500) NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_al_model` (`model`, `model_id`),
  KEY `idx_al_user` (`user_id`, `created_at`),
  KEY `idx_al_created` (`created_at`),
  KEY `idx_al_req` (`request_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `request_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_uid` VARCHAR(32) NOT NULL,
  `user_id` INT NULL,
  `method` VARCHAR(10) NOT NULL,
  `route` VARCHAR(150) NULL,
  `url` VARCHAR(500) NULL,
  `params` MEDIUMTEXT NULL COMMENT 'JSON, đã bỏ mật khẩu/token',
  `status_code` SMALLINT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `duration_ms` INT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rl_user` (`user_id`, `created_at`),
  KEY `idx_rl_created` (`created_at`),
  KEY `idx_rl_uid` (`request_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
