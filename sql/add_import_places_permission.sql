-- Add import_places permission to the permissions table
INSERT INTO permissions (name, description) 
VALUES ('import_places', 'استيراد الأماكن من ملف Excel') 
ON DUPLICATE KEY UPDATE description = 'استيراد الأماكن من ملف Excel';

-- Grant this permission to admin users by default
INSERT INTO user_permissions (user_id, permission_id)
SELECT u.id, p.id 
FROM users u, permissions p 
WHERE p.name = 'import_places' AND u.role = 'admin'
ON DUPLICATE KEY UPDATE user_id = user_id;