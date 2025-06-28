USE Db_Catalog;

-- Users table (extends the existing admin table with invitation functionality)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    invitation_code VARCHAR(50) DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0
);

-- Invitations table
CREATE TABLE IF NOT EXISTS invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL DEFAULT NULL,
    used_by INT DEFAULT NULL,
    status ENUM('active', 'used', 'expired') DEFAULT 'active',
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- APK files table
CREATE TABLE IF NOT EXISTS apk_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    version VARCHAR(20) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'deprecated') DEFAULT 'active',
    uploaded_by INT NOT NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Download logs table
CREATE TABLE IF NOT EXISTS download_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    apk_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apk_id) REFERENCES apk_files(id) ON DELETE CASCADE
);

-- Verifică și adaugă coloana status dacă nu există
SET @exist_status = (SELECT COUNT(*) 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA='Db_Catalog' AND TABLE_NAME='users' AND COLUMN_NAME='status');

SET @query = IF(@exist_status = 0, 
                'ALTER TABLE users ADD COLUMN status ENUM("active", "inactive", "banned") DEFAULT "active" AFTER created_at', 
                'SELECT "Column status already exists"');

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert initial admin user
INSERT INTO users (username, password, email, full_name, is_admin)
VALUES ('admin', MD5('admin123'), 'pavelmarius28@yahoo.com.com', 'Marius', 1);