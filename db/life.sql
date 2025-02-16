CREATE DATABASE life;
USE life;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'healthcare_provider', 'donor', 'recipient') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE donors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    blood_type VARCHAR(10),
    histo_compatibility TEXT,
    organ_type ENUM('Kidney', 'Liver'),  -- ✅ Only Kidney & Liver
    availability_status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE recipients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    blood_type VARCHAR(10),
    histo_compatibility TEXT,
    organ_needed ENUM('Kidney', 'Liver'), -- ✅ Only Kidney & Liver
    urgency_level ENUM('low', 'medium', 'high'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

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

CREATE TABLE hospital_centers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    contact_info TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transplants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    match_id INT,
    hospital_id INT,
    status ENUM('scheduled', 'completed', 'failed'),
    performed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospital_centers(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT,
    status ENUM('unread', 'read'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


ALTER TABLE users ADD COLUMN passkey VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN last_login DATETIME DEFAULT NULL;

ALTER TABLE users ADD COLUMN otp VARCHAR(6) DEFAULT NULL;
ALTER TABLE users ADD COLUMN is_verified BOOLEAN DEFAULT FALSE;
