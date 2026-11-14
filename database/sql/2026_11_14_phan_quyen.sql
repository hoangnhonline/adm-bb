-- Phân quyền theo chức năng × hành động + giới hạn chi nhánh (14/11/2026)
-- Chạy 1 lần, an toàn khi chạy lại. Không sửa dữ liệu cũ.
-- Tài khoản chưa gán nhóm (users.role_id NULL) vẫn dùng quyền cũ như hiện tại.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL,
  `all_branches` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = thấy mọi chi nhánh',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT NOT NULL,
  `permission` VARCHAR(60) NOT NULL,
  PRIMARY KEY (`role_id`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_branches` (
  `user_id` INT NOT NULL,
  `branch_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `role_id` INT NULL DEFAULT NULL COMMENT 'nhóm quyền mới; NULL = quyền cũ';

-- 4 nhóm mẫu (chỉ tạo nếu chưa có nhóm nào)
INSERT INTO `roles` (`id`, `name`, `description`, `all_branches`, `status`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 1 AS id, 'Quản lý chi nhánh' AS name, 'Bill, chi phí, nhập hàng, báo cáo của chi nhánh mình; sửa/xóa bill trong ngày' AS description, 0 AS all_branches, 1 AS status, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 2, 'Thu ngân / phục vụ', 'Xem, tạo, sửa bill trong ngày', 0, 1, NOW(), NOW() UNION ALL
  SELECT 3, 'Kế toán', 'Chi phí, upload bill, nhập hàng, công nợ đối tác, báo cáo; bill chỉ xem', 1, 1, NOW(), NOW() UNION ALL
  SELECT 4, 'Bếp', 'Màn hình bếp', 0, 1, NOW(), NOW()
) t WHERE NOT EXISTS (SELECT 1 FROM `roles`);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission`) VALUES
 (1,'bill.view'),(1,'bill.create'),(1,'bill.edit'),(1,'bill.delete'),(1,'bill.export'),(1,'kitchen.view'),(1,'kitchen.edit'),
 (1,'cost.view'),(1,'cost.create'),(1,'cost.edit'),(1,'cost.delete'),(1,'cost.export'),
 (1,'bill_upload.view'),(1,'bill_upload.create'),(1,'bill_upload.delete'),(1,'purchase.view'),(1,'report.view'),
 (2,'bill.view'),(2,'bill.create'),(2,'bill.edit'),(2,'kitchen.view'),
 (3,'bill.view'),(3,'bill.export'),(3,'cost.view'),(3,'cost.create'),(3,'cost.edit'),(3,'cost.delete'),(3,'cost.export'),
 (3,'bill_upload.view'),(3,'bill_upload.create'),(3,'bill_upload.delete'),(3,'purchase.view'),(3,'report.view'),
 (3,'partner.view'),(3,'partner.create'),(3,'partner.edit'),(3,'partner.transaction'),
 (4,'kitchen.view'),(4,'kitchen.edit');
