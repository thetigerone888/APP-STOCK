-- ========================================================
-- Tiger88 Expense System — Database Schema
-- นำไฟล์นี้ไป Import ผ่าน phpMyAdmin (cPanel > phpMyAdmin > เลือก DB > Import)
-- ========================================================

CREATE TABLE IF NOT EXISTS expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entry_date DATE NOT NULL,
  description TEXT,
  qty DECIMAL(10,2) DEFAULT 1,
  price DECIMAL(12,2) DEFAULT 0,
  amount DECIMAL(12,2) NOT NULL,
  main_category VARCHAR(100),
  sub_category VARCHAR(100),
  vendor VARCHAR(255),
  month_name VARCHAR(20),
  payer VARCHAR(50),
  source VARCHAR(20) DEFAULT 'manual',
  sheet_hash VARCHAR(64) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_sheet_hash (sheet_hash),
  INDEX idx_date (entry_date),
  INDEX idx_month (month_name),
  INDEX idx_payer (payer),
  INDEX idx_category (main_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_settings (
  setting_key VARCHAR(50) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
