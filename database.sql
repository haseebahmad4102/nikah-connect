-- NikahConnect Database Schema
-- Islamic Matrimonial Platform
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS nikahconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nikahconnect;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    role ENUM('user', 'wali', 'parent', 'admin') DEFAULT 'user',
    -- For a parent (guardian) account: the child (boy/girl) user this account guards
    child_id INT NULL,
    guardian_relation VARCHAR(50) NULL,
    is_verified TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    profile_complete TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Profiles table
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    age INT,
    date_of_birth DATE,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Pakistan',
    marital_status ENUM('never_married', 'divorced', 'widowed') DEFAULT 'never_married',
    education VARCHAR(150),
    occupation VARCHAR(150),
    monthly_income VARCHAR(100),
    height VARCHAR(20),
    complexion VARCHAR(50),
    sect VARCHAR(100),
    religiosity ENUM('practicing', 'moderate', 'learning') DEFAULT 'moderate',
    prayers_per_day ENUM('5_times', '3_times', '1_time', 'occasionally') DEFAULT '5_times',
    hijab_niqab VARCHAR(50),
    beard VARCHAR(50),
    languages VARCHAR(200),
    family_type ENUM('joint', 'nuclear') DEFAULT 'nuclear',
    about_me TEXT,
    about_family TEXT,
    photo VARCHAR(255),
    wali_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Preferences table
CREATE TABLE IF NOT EXISTS preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    pref_age_min INT DEFAULT 18,
    pref_age_max INT DEFAULT 45,
    pref_city VARCHAR(100),
    pref_country VARCHAR(100),
    pref_marital_status VARCHAR(100),
    pref_education VARCHAR(100),
    pref_occupation VARCHAR(100),
    pref_religiosity VARCHAR(100),
    pref_sect VARCHAR(100),
    pref_family_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wali (Guardian) table
CREATE TABLE IF NOT EXISTS walis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    wali_name VARCHAR(100) NOT NULL,
    wali_relation VARCHAR(50) NOT NULL,
    wali_phone VARCHAR(20),
    wali_email VARCHAR(150),
    wali_consent TINYINT(1) DEFAULT 0,
    consent_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Photo gallery table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    caption VARCHAR(200),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Interest/Connection requests
CREATE TABLE IF NOT EXISTS interests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    -- 'pending' = awaiting both parents' approval, 'success' = both parents approved the rishta
    status ENUM('pending', 'accepted', 'success', 'rejected', 'withdrawn') DEFAULT 'pending',
    message TEXT,
    wali_notified TINYINT(1) DEFAULT 0,
    -- Parent (guardian) approvals. The boy/girl themselves cannot approve.
    sender_parent_approved TINYINT(1) DEFAULT 0,
    receiver_parent_approved TINYINT(1) DEFAULT 0,
    sender_parent_approved_at TIMESTAMP NULL,
    receiver_parent_approved_at TIMESTAMP NULL,
    -- Visit scheduling after a successful rishta
    visit_status ENUM('none', 'proposed', 'confirmed') DEFAULT 'none',
    visit_date DATE NULL,
    visit_proposed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_interest (sender_id, receiver_id),
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Halal Messages (restricted communication)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    is_flagged TINYINT(1) DEFAULT 0,
    wali_can_view TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Admin reports/logs
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(200),
    target_user_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
-- Password for admin: 'admin123'  | Password for users: 'user123'
-- These hashes are generated by PHP password_hash() with PASSWORD_DEFAULT (bcrypt)
-- If login fails, use the reset script or phpMyAdmin to update manually

INSERT INTO users (full_name, email, password, gender, role, is_verified, is_active, profile_complete)
VALUES ('Admin', 'admin@nikahconnect.com', '$2y$10$5qH.soCeYWmq1arlG79xleAM5VHVlPHDjmVygFD9VP78cooGSceym', 'male', 'admin', 1, 1, 1);

-- Sample male user
INSERT INTO users (full_name, email, password, gender, role, is_verified, is_active, profile_complete)
VALUES ('Ahmed Ali', 'ahmed@example.com', '$2y$10$/D0xGts8o4YVFWKqipxF0ukz.SpIv/J.uHZQjtz./3covWghx0EjK', 'male', 'user', 1, 1, 1);

-- Sample female user
INSERT INTO users (full_name, email, password, gender, role, is_verified, is_active, profile_complete)
VALUES ('Fatima Zahra', 'fatima@example.com', '$2y$10$/D0xGts8o4YVFWKqipxF0ukz.SpIv/J.uHZQjtz./3covWghx0EjK', 'female', 'user', 1, 1, 1);

-- Sample profiles
INSERT INTO profiles (user_id, age, city, country, marital_status, education, occupation, height, complexion, sect, religiosity, prayers_per_day, beard, languages, family_type, about_me, about_family, wali_approved)
VALUES (2, 28, 'Lahore', 'Pakistan', 'never_married', 'BS Computer Science', 'Software Engineer', '5\'10"', 'Fair', 'Sunni', 'practicing', '5_times', 'Yes (short)', 'Urdu, English', 'nuclear', 'Alhamdulillah, I am a practicing Muslim looking for a pious life partner who shares my Islamic values. I believe in building a family on the foundations of Islam.', 'We are a small, educated and religious family based in Lahore. My parents are very supportive and involved in this process.', 1);

INSERT INTO profiles (user_id, age, city, country, marital_status, education, occupation, height, complexion, sect, religiosity, prayers_per_day, hijab_niqab, languages, family_type, about_me, about_family, wali_approved)
VALUES (3, 24, 'Karachi', 'Pakistan', 'never_married', 'BS Education', 'Teacher', '5\'4"', 'Fair', 'Sunni', 'practicing', '5_times', 'Hijab', 'Urdu, English, Arabic', 'joint', 'Bismillah. I am a modest and practicing Muslimah seeking a righteous life partner. I value deen above all else and wish to build a peaceful Islamic home.', 'We are a joint family system with strong Islamic values. My wali (father) is actively involved in this process.', 1);

-- Sample wali entries
INSERT INTO walis (user_id, wali_name, wali_relation, wali_phone, wali_email, wali_consent)
VALUES (2, 'Muhammad Ali', 'Father', '03001234567', 'mali@example.com', 1);

INSERT INTO walis (user_id, wali_name, wali_relation, wali_phone, wali_email, wali_consent)
VALUES (3, 'Usman Zahra', 'Father', '03007654321', 'uzahra@example.com', 1);

-- Sample preferences
INSERT INTO preferences (user_id, pref_age_min, pref_age_max, pref_country, pref_religiosity, pref_sect)
VALUES (2, 20, 28, 'Pakistan', 'practicing', 'Sunni');

INSERT INTO preferences (user_id, pref_age_min, pref_age_max, pref_country, pref_religiosity, pref_sect)
VALUES (3, 25, 35, 'Pakistan', 'practicing', 'Sunni');

-- Sample parent (guardian) accounts — password: 'user123'
-- Parent of Ahmed Ali (user 2)
INSERT INTO users (full_name, email, password, gender, role, child_id, guardian_relation, is_verified, is_active, profile_complete)
VALUES ('Muhammad Ali (Wali)', 'parent.ahmed@example.com', '$2y$10$/D0xGts8o4YVFWKqipxF0ukz.SpIv/J.uHZQjtz./3covWghx0EjK', 'male', 'parent', 2, 'Father', 1, 1, 1);

-- Parent of Fatima Zahra (user 3)
INSERT INTO users (full_name, email, password, gender, role, child_id, guardian_relation, is_verified, is_active, profile_complete)
VALUES ('Usman Zahra (Wali)', 'parent.fatima@example.com', '$2y$10$/D0xGts8o4YVFWKqipxF0ukz.SpIv/J.uHZQjtz./3covWghx0EjK', 'female', 'parent', 3, 'Father', 1, 1, 1);
