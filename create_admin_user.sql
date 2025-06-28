-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS Db_Catalog;

-- Create 'Admin' user if it doesn't exist
CREATE USER IF NOT EXISTS 'Admin'@'localhost' IDENTIFIED BY 'Admin';

-- Grant privileges to Admin user on Db_Catalog
GRANT ALL PRIVILEGES ON Db_Catalog.* TO 'Admin'@'localhost';

-- Refresh privileges
FLUSH PRIVILEGES;

-- Select the database
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

-- Insert initial admin user (only if the table is empty)
INSERT INTO users (username, password, email, full_name, is_admin)
SELECT 'admin', MD5('admin123'), 'admin@example.com', 'Administrator', 1
FROM dual
WHERE NOT EXISTS (SELECT * FROM users WHERE username = 'admin');
