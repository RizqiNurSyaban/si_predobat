-- SI-PREDOBAT Database Schema

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Apoteker Penanggung Jawab',
    avatar_initials VARCHAR(5) NOT NULL DEFAULT 'AF',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    target_stock_pct INT NOT NULL DEFAULT 85,
    color_class VARCHAR(50) NOT NULL DEFAULT 'teal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    dosage_form VARCHAR(50) NOT NULL DEFAULT 'Tablet', -- Tablet, Kapsul, Unit, Sachet, Sirup
    unit_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    current_stock INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 0,
    safety_stock INT NOT NULL DEFAULT 0,
    lead_time_days INT NOT NULL DEFAULT 7,
    nearest_expiry DATE NULL,
    status ENUM('Aman', 'Waspada', 'Kritis') NOT NULL DEFAULT 'Aman',
    predicted_demand_next INT NOT NULL DEFAULT 0,
    recommended_order_qty INT NOT NULL DEFAULT 0,
    priority ENUM('Tinggi', 'Sedang', 'Rendah') NOT NULL DEFAULT 'Sedang',
    order_before DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS medicine_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    expiry_date DATE NOT NULL,
    received_date DATE NOT NULL,
    supplier VARCHAR(100) NOT NULL DEFAULT 'PBF Medika Jaya',
    status ENUM('Aktif', 'Hampir Kedaluwarsa', 'Kedaluwarsa') NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS monthly_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    period_label VARCHAR(30) NOT NULL, -- 'Mei 2026', 'Jun 2026', etc.
    actual_usage INT NOT NULL DEFAULT 0,
    visits_count INT NOT NULL DEFAULT 0,
    disease_season_flag TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_med_period (medicine_id, year, month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS procurement_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    order_date DATE NOT NULL,
    target_period VARCHAR(30) NOT NULL DEFAULT 'Des 2026',
    supplier_name VARCHAR(150) NOT NULL DEFAULT 'PBF Medika Jaya',
    total_items INT NOT NULL DEFAULT 0,
    total_units INT NOT NULL DEFAULT 0,
    total_cost DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    status ENUM('Draf', 'Diajukan', 'Disetujui', 'Diproses', 'Selesai') NOT NULL DEFAULT 'Diajukan',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS procurement_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    current_stock INT NOT NULL DEFAULT 0,
    predicted_demand INT NOT NULL DEFAULT 0,
    ordered_qty INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    priority ENUM('Tinggi', 'Sedang', 'Rendah') NOT NULL DEFAULT 'Sedang',
    order_before_date DATE NULL,
    FOREIGN KEY (order_id) REFERENCES procurement_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

