-- Đa ngôn ngữ: lưu ngôn ngữ giao diện (vi / en) theo từng tài khoản. NULL = tiếng Việt (mặc định).
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NULL DEFAULT NULL;
