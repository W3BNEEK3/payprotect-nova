-- NovaTrust Database Schema

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    fullname VARCHAR(200),
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(30),
    country VARCHAR(100),
    currency VARCHAR(10),
    balance DECIMAL(15,2) DEFAULT 0,
    refunded_balance DECIMAL(15,2) DEFAULT 0,
    account_number VARCHAR(30) UNIQUE,
    account_status VARCHAR(20) DEFAULT 'active',
    account_type VARCHAR(30) DEFAULT 'regular',
    is_upgraded TINYINT(1) DEFAULT 0,
    is_upgrade_verified TINYINT(1) DEFAULT 0,
    is_kyc_verified TINYINT(1) DEFAULT 0,
    kyc_code VARCHAR(50),
    is_imf_verified TINYINT(1) DEFAULT 0,
    imf_code VARCHAR(50),
    is_vat_verified TINYINT(1) DEFAULT 0,
    vat_code VARCHAR(50),
    is_ars_verified TINYINT(1) DEFAULT 0,
    ars_code VARCHAR(50),
    is_withdrawal_verified TINYINT(1) DEFAULT 0,
    withdrawal_code VARCHAR(50),
    is_virtual_card_cleared TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(255),
    password VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(200),
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(15,2),
    currency VARCHAR(10),
    type VARCHAR(20),
    message VARCHAR(255),
    receiver_id INT,
    status VARCHAR(30),
    method VARCHAR(50),
    reference VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS refunds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    account_number VARCHAR(30),
    amount DECIMAL(15,2),
    reason VARCHAR(255),
    refunded_by VARCHAR(200),
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS virtual_cards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    card_number VARCHAR(30),
    expiry_date VARCHAR(10),
    cvv VARCHAR(10),
    cardholder_name VARCHAR(200),
    balance DECIMAL(15,2),
    status VARCHAR(30),
    is_virtual_card_approved TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(100),
    message VARCHAR(255),
    type VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS support_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150),
    subject VARCHAR(255),
    message TEXT,
    date_sent DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS support (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    subject VARCHAR(255),
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
); 