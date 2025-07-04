-- Permissions table
CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Permissions table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default permissions
INSERT INTO permissions (name, description) VALUES
('view_places', 'عرض الأماكن'),
('add_places', 'إضافة الأماكن'),
('edit_places', 'تعديل الأماكن'),
('delete_places', 'حذف الأماكن'),
('export_places', 'تصدير بيانات الأماكن'),
('view_countries', 'عرض الدول'),
('add_countries', 'إضافة الدول'),
('edit_countries', 'تعديل الدول'),
('delete_countries', 'حذف الدول'),
('view_users', 'عرض المستخدمين'),
('add_users', 'إضافة المستخدمين'),
('edit_users', 'تعديل المستخدمين'),
('delete_users', 'حذف المستخدمين'),
('manage_permissions', 'إدارة الصلاحيات');

-- Grant all permissions to admin user (assuming admin user has ID 1)
INSERT INTO user_permissions (user_id, permission_id)
SELECT 1, id FROM permissions;

-- Add indexes
CREATE INDEX idx_permissions_name ON permissions(name);
CREATE INDEX idx_user_permissions_user ON user_permissions(user_id);
CREATE INDEX idx_user_permissions_permission ON user_permissions(permission_id);