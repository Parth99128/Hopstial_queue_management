-- ============================================================
-- hospital_queue — FULL setup. Run ONCE via install.php in the browser
-- (or manually: mysql -u root < database.sql).
-- Only EMPTY tables — no demo rows. Real rows come from using the app.
-- Token numbers restart daily PER DOCTOR (counted in code, not stored),
-- so the same number (e.g. D3-001) may exist on DIFFERENT days.
-- Uniqueness is therefore per day: UNIQUE(booked_date, token_no).
-- ============================================================
CREATE DATABASE IF NOT EXISTS hospital_queue CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE hospital_queue;

-- 1. users: STAFF logins only (patients never log in — they just book).
--    mobile = login id (UNIQUE). admin manages accounts + everything staff can.
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('staff','admin') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. doctors: name + room/counter shown on the display board.
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    room VARCHAR(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB;

-- 3. tokens: THE QUEUE, one row per visit.
--    status flow: waiting -> called -> completed  (or cancelled any time before done)
CREATE TABLE IF NOT EXISTS tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_no VARCHAR(30) NOT NULL,
    booked_date DATE NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    doctor_id INT NOT NULL,
    status ENUM('waiting','called','completed','cancelled') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    UNIQUE KEY uq_token_day (booked_date, token_no),
    INDEX idx_doc_status (doctor_id, status),
    INDEX idx_doc_created (doctor_id, created_at)
) ENGINE=InnoDB;
