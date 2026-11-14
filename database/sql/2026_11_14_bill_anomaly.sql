-- Cảnh báo bill bất thường: lưu trạng thái "Đã xem" của từng cảnh báo (14/11/2026)
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS `bill_anomaly_reviews` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `anomaly_key` VARCHAR(100) NOT NULL,
  `booking_id` INT NULL,
  `reviewed_by` INT NULL,
  `reviewed_at` DATETIME NULL,
  `note` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_anomaly_key` (`anomaly_key`),
  KEY `idx_bar_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
