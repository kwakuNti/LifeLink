DROP DATABASE IF EXISTS life;
-- Create Database
CREATE DATABASE life;
USE life;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'healthcare_provider', 'donor', 'recipient') NOT NULL,
    region VARCHAR(100) DEFAULT NULL, -- User region
    city VARCHAR(100) DEFAULT NULL,   -- User city
    latitude DECIMAL(9,6) DEFAULT NULL, -- User coordinates (for distance calculation)
    longitude DECIMAL(9,6) DEFAULT NULL,
    passkey VARCHAR(255) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    otp VARCHAR(6) DEFAULT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Donors Table
CREATE TABLE donors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,                  -- link to users table
    init_age INT,                 -- e.g., 50
    bmi_tcr FLOAT,                -- e.g., 25.0
    dayswait_alloc INT,           -- e.g., 180
    kidney_cluster INT,           -- e.g., 0 or 1
    dgn_tcr FLOAT DEFAULT 0.0,    -- optional diagnosis code
    wgt_kg_tcr FLOAT DEFAULT 0.0, 
    hgt_cm_tcr FLOAT DEFAULT 0.0,
    gfr FLOAT DEFAULT 0.0,        -- for cluster determination
    on_dialysis BOOLEAN DEFAULT FALSE,  -- 'Y' => true, 'N' => false
    blood_type VARCHAR(5) DEFAULT NULL,   -- ABO type (A, B, AB, O)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Recipients Table
CREATE TABLE recipients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    blood_type VARCHAR(10),
    histo_compatibility TEXT,
    organ_needed ENUM('Kidney', 'Liver'),
    urgency_level ENUM('low', 'medium', 'high'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Matches Table
CREATE TABLE matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT,
    recipient_id INT,
    match_score FLOAT,
    status ENUM('pending', 'matched', 'transplanted', 'canceled'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES recipients(id) ON DELETE CASCADE
);

-- Hospitals Table (Updated with Coordinates and credentials)
CREATE TABLE hospitals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    region VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    organ_specialty ENUM('Kidney', 'Liver', 'Both') NOT NULL,
    contact_info TEXT,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transplants Table
CREATE TABLE transplants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    match_id INT,
    hospital_id INT,
    status ENUM('scheduled', 'completed', 'failed'),
    performed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
);

-- Notifications Table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT,
    status ENUM('unread', 'read'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User Search History (Stores previous searches & selections)
CREATE TABLE user_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    selected_region VARCHAR(100),
    selected_city VARCHAR(100),
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    selected_hospital INT NULL,
    search_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_hospital) REFERENCES hospitals(id) ON DELETE SET NULL
);

-- (In production, generate the hash dynamically using password_hash('Default@1', PASSWORD_DEFAULT).)
INSERT INTO hospitals (name, region, city, latitude, longitude, organ_specialty, contact_info, username, password) VALUES
    ('Korle Bu Teaching Hospital', 'Greater Accra', 'Accra', 5.536584, -0.226373, 'Both', '0302739510', 'korlebu', '$2y$10$9H4X4O/PPV.yzwKJXf6EPOxO7E/6iK0KB1XxOfY5mW/1eVJrsV7gG'),
    ('The Bank Hospital', 'Greater Accra', 'Accra', 5.584862, -0.162038, 'Kidney', '+233 302 739 373', 'bankhospital', '$2y$10$9H4X4O/PPV.yzwKJXf6EPOxO7E/6iK0KB1XxOfY5mW/1eVJrsV7gG'),
    ('Komfo Anokye Teaching Hospital', 'Ashanti', 'Kumasi', 6.697208, -1.629675, 'Kidney', '+233 593 830 400', 'komfoanokye', '$2y$10$9H4X4O/PPV.yzwKJXf6EPOxO7E/6iK0KB1XxOfY5mW/1eVJrsV7gG'),
    ('37 Military Hospital', 'Greater Accra', 'Accra', 5.587329, -0.184266, 'Kidney', NULL, '37military', '$2y$10$9H4X4O/PPV.yzwKJXf6EPOxO7E/6iK0KB1XxOfY5mW/1eVJrsV7gG'),
    ('University of Ghana Medical Centre (UGMC)', 'Greater Accra', 'Accra', 5.632420, -0.185963, 'Kidney', '+233 302 550843', 'ugmc', '$2y$10$9H4X4O/PPV.yzwKJXf6EPOxO7E/6iK0KB1XxOfY5mW/1eVJrsV7gG');


ALTER TABLE matches 
  MODIFY status ENUM(
    'pending',
    'matched',
    'accepted',
    'declined',
    'transplanted',
    'canceled'
  ) NOT NULL DEFAULT 'pending';
