-- Permissions Schema
-- This file contains the SQL statements to create the permissions tables
-- and insert default permissions for the application.

-- Create permissions table
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_permissions table
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permission` (`user_id`, `permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions
INSERT INTO `permissions` (`name`, `description`) VALUES
-- Places permissions
('view_places', 'عرض الأماكن'),
('add_places', 'إضافة الأماكن'),
('edit_places', 'تعديل الأماكن'),
('delete_places', 'حذف الأماكن'),
('export_places', 'تصدير الأماكن'),

-- Countries permissions
('view_countries', 'عرض الدول'),
('add_countries', 'إضافة الدول'),
('edit_countries', 'تعديل الدول'),
('delete_countries', 'حذف الدول'),

-- Users permissions
('view_users', 'عرض المستخدمين'),
('add_users', 'إضافة المستخدمين'),
('edit_users', 'تعديل المستخدمين'),
('delete_users', 'حذف المستخدمين'),
('manage_users', 'إدارة المستخدمين'),

-- Permissions management
('manage_permissions', 'إدارة الصلاحيات')

ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- Grant all permissions to admin user (ID 1)
INSERT INTO `user_permissions` (`user_id`, `permission_id`)
SELECT 1, id FROM `permissions`
ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`), `permission_id` = VALUES(`permission_id`);