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
    region VARCHAR(100) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    latitude DECIMAL(9,6) DEFAULT NULL,
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
    user_id INT,                  
    init_age INT,                 
    bmi_tcr FLOAT,                
    dayswait_alloc INT,           
    kidney_cluster INT,           
    dgn_tcr FLOAT DEFAULT 0.0,    
    wgt_kg_tcr FLOAT DEFAULT 0.0, 
    hgt_cm_tcr FLOAT DEFAULT 0.0,
    gfr FLOAT DEFAULT 0.0,        
    on_dialysis BOOLEAN DEFAULT FALSE,
    blood_type VARCHAR(5) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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


CREATE TABLE recipients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,                  -- Link to users table (if applicable)
    patient_code VARCHAR(50) UNIQUE,  -- Unique patient code (e.g. "Patient #3234")
    phone VARCHAR(20) DEFAULT NULL,   -- Recipient phone number
    description TEXT DEFAULT NULL,    -- A story or additional description
    location VARCHAR(255) DEFAULT NULL,  -- Location information
    init_age INT,                 
    bmi_tcr FLOAT,                
    dayswait_alloc INT,           
    kidney_cluster INT,           
    dgn_tcr FLOAT DEFAULT 0.0,    
    wgt_kg_tcr FLOAT DEFAULT 0.0, 
    hgt_cm_tcr FLOAT DEFAULT 0.0,
    gfr FLOAT DEFAULT 0.0,        
    on_dialysis BOOLEAN DEFAULT FALSE,
    blood_type VARCHAR(5) DEFAULT NULL,
    hospital_id INT DEFAULT NULL,       -- New column to track which hospital added the patient
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL
);


-- Matches Table
CREATE TABLE matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT,
    recipient_id INT,
    match_score FLOAT,
    status ENUM('pending', 'matched', 'accepted', 'declined', 'transplanted', 'canceled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES recipients(id) ON DELETE CASCADE
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

-- User Search History
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

-- Insert Hardcoded Hospitals into the Database with fixed usernames and a default password.
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

ALTER TABLE donors 
ADD COLUMN organ_type ENUM('Kidney', 'Liver', 'Both') DEFAULT NULL AFTER blood_type;

ALTER TABLE recipients 
ADD COLUMN organ_type ENUM('Kidney', 'Liver', 'Both') DEFAULT NULL;
