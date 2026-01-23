-- SQL Script to create MediStock database and user
-- Run this in MySQL Workbench after connecting as root

-- Create the database
CREATE DATABASE IF NOT EXISTS medistock 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Create the user (if it doesn't exist)
CREATE USER IF NOT EXISTS 'medistock'@'localhost' IDENTIFIED BY '!ChangeMe!';
CREATE USER IF NOT EXISTS 'medistock'@'%' IDENTIFIED BY '!ChangeMe!';

-- Grant privileges
GRANT ALL PRIVILEGES ON medistock.* TO 'medistock'@'localhost';
GRANT ALL PRIVILEGES ON medistock.* TO 'medistock'@'%';

-- Refresh privileges
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES LIKE 'medistock';
SELECT User, Host FROM mysql.user WHERE User = 'medistock';

