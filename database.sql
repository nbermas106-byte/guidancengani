CREATE DATABASE IF NOT EXISTS qnhs_guidance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qnhs_guidance;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 student_no VARCHAR(60) NULL UNIQUE,
 grade_level VARCHAR(30) NULL,
 section VARCHAR(80) NULL,
 password VARCHAR(255) NOT NULL,
 profile_picture VARCHAR(255) NULL,
 role ENUM('student','counselor','admin') NOT NULL DEFAULT 'student',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
 id INT AUTO_INCREMENT PRIMARY KEY,
 student_id INT NOT NULL,
 consultation_type VARCHAR(80) NOT NULL,
 preferred_date DATE NOT NULL,
 preferred_time TIME NOT NULL,
 mode ENUM('In-person','Online') NOT NULL DEFAULT 'In-person',
 notes TEXT NULL,
 status ENUM('Pending','Approved','Completed','Declined') NOT NULL DEFAULT 'Pending',
 counselor_note TEXT NULL,
 reviewed_by INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_appointment_student FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_appointment_reviewer FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS career_profiles (
 id INT AUTO_INCREMENT PRIMARY KEY,
 student_id INT NOT NULL UNIQUE,
 interest_area VARCHAR(100) NOT NULL,
 answers JSON NOT NULL,
 goal TEXT NULL,
 strengths TEXT NULL,
 plan_details LONGTEXT NULL,
 post_shs_plan LONGTEXT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_career_student FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
