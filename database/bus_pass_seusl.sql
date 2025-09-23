-- Create database
CREATE DATABASE IF NOT EXISTS bus_pass_seusl;
USE bus_pass_seusl;

-- Users table (for authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('student', 'depot', 'admin') NOT NULL,
    status ENUM('pending', 'active', 'suspended', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, user_type, status) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Depots table
CREATE TABLE IF NOT EXISTS depots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    depot_name VARCHAR(100) NOT NULL,
    manager_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample depots
INSERT INTO depots (user_id, depot_name, manager_name, email, phone, address, location) VALUES
(1, 'Akkaraipattu Depot', 'Mr. R. Kumar', 'akdepot@example.com', '0652245678', 'Main Road, Akkaraipattu', 'Akkaraipattu'),
(1, 'Sammanthurai Depot', 'Mr. M. Rafeek', 'samdepot@example.com', '0672267890', 'Kandy Road, Sammanthurai', 'Sammanthurai'),
(1, 'Kalmunai Depot', 'Mr. A. Rahim', 'kaldepot@example.com', '0672278912', 'Batticaloa Road, Kalmunai', 'Kalmunai');

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    registration_no VARCHAR(20) NOT NULL UNIQUE,
    faculty VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    depot_id INT NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (depot_id) REFERENCES depots(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Routes table
CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_point VARCHAR(100) NOT NULL,
    end_point VARCHAR(100) NOT NULL,
    distance_km DECIMAL(5,2) NOT NULL,
    estimated_time VARCHAR(20) NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert routes
INSERT INTO routes (start_point, end_point, distance_km, estimated_time, fare) VALUES
('Akkaraipattu', 'Oluvil', 10.00, '12 min', 50.00),
('Addalaichenai', 'Oluvil', 7.00, '10 min', 40.00),
('Palamunai', 'Oluvil', 7.00, '10 min', 40.00),
('Nintavur', 'Oluvil', 9.00, '11 min', 45.00),
('Sammanthurai', 'Oluvil', 16.50, '21 min', 60.00),
('Karaitivu', 'Oluvil', 12.70, '15 min', 55.00),
('Sainthamaruthu', 'Oluvil', 12.00, '14 min', 50.00),
('Kalmunai', 'Oluvil', 17.00, '21 min', 65.00),
('Maruthamunai', 'Oluvil', 18.60, '24 min', 70.00);

-- Bus passes table
CREATE TABLE IF NOT EXISTS bus_passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    route_id INT NOT NULL,
    depot_id INT NOT NULL,
    pass_number VARCHAR(20) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'rejected') NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_reference VARCHAR(100) DEFAULT NULL,
    qr_code VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    FOREIGN KEY (depot_id) REFERENCES depots(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    bus_pass_id INT DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('online', 'cash', 'card') NOT NULL,
    payment_reference VARCHAR(100) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP NULL DEFAULT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (bus_pass_id) REFERENCES bus_passes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create a view for active bus passes
CREATE OR REPLACE VIEW active_bus_passes AS
SELECT 
    bp.id,
    bp.pass_number,
    s.full_name,
    s.registration_no,
    s.department,
    d.depot_name,
    r.start_point,
    r.end_point,
    bp.issue_date,
    bp.expiry_date,
    bp.status
FROM 
    bus_passes bp
JOIN 
    students s ON bp.student_id = s.id
JOIN 
    depots d ON bp.depot_id = d.id
JOIN 
    routes r ON bp.route_id = r.id
WHERE 
    bp.status = 'active' 
    AND bp.expiry_date >= CURDATE();

-- Create a view for expired bus passes
CREATE OR REPLACE VIEW expired_bus_passes AS
SELECT 
    bp.id,
    bp.pass_number,
    s.full_name,
    s.registration_no,
    d.depot_name,
    r.start_point,
    r.end_point,
    bp.issue_date,
    bp.expiry_date,
    DATEDIFF(CURDATE(), bp.expiry_date) AS days_expired
FROM 
    bus_passes bp
JOIN 
    students s ON bp.student_id = s.id
JOIN 
    depots d ON bp.depot_id = d.id
JOIN 
    routes r ON bp.route_id = r.id
WHERE 
    bp.status = 'active' 
    AND bp.expiry_date < CURDATE();

-- Create a view for payment summary
CREATE OR REPLACE VIEW payment_summary AS
SELECT 
    p.id,
    s.registration_no,
    s.full_name,
    p.amount,
    p.payment_method,
    p.status,
    p.payment_date,
    p.created_at
FROM 
    payments p
JOIN 
    students s ON p.student_id = s.id
ORDER BY 
    p.created_at DESC;

-- Create a view for student dashboard
CREATE OR REPLACE VIEW student_dashboard AS
SELECT 
    s.id AS student_id,
    s.full_name,
    s.registration_no,
    s.faculty,
    s.department,
    s.email,
    s.phone,
    s.status AS student_status,
    d.depot_name,
    d.location AS depot_location,
    d.phone AS depot_phone,
    (SELECT COUNT(*) FROM bus_passes WHERE student_id = s.id) AS total_passes,
    (SELECT COUNT(*) FROM bus_passes WHERE student_id = s.id AND status = 'active' AND expiry_date >= CURDATE()) AS active_passes,
    (SELECT SUM(amount) FROM payments WHERE student_id = s.id AND status = 'completed') AS total_payments
FROM 
    students s
LEFT JOIN 
    depots d ON s.depot_id = d.id;

-- Create a view for depot dashboard
CREATE OR REPLACE VIEW depot_dashboard AS
SELECT 
    d.id AS depot_id,
    d.depot_name,
    d.manager_name,
    d.email,
    d.phone,
    d.location,
    d.status AS depot_status,
    (SELECT COUNT(*) FROM students WHERE depot_id = d.id) AS total_students,
    (SELECT COUNT(*) FROM bus_passes WHERE depot_id = d.id) AS total_passes_issued,
    (SELECT COUNT(*) FROM bus_passes WHERE depot_id = d.id AND status = 'active' AND expiry_date >= CURDATE()) AS active_passes,
    (SELECT COUNT(*) FROM students WHERE depot_id = d.id AND status = 'active') AS active_students
FROM 
    depots d;

-- Create a view for admin dashboard
CREATE OR REPLACE VIEW admin_dashboard AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE user_type = 'student' AND status = 'active') AS total_students,
    (SELECT COUNT(*) FROM users WHERE user_type = 'depot' AND status = 'active') AS total_depots,
    (SELECT COUNT(*) FROM bus_passes WHERE status = 'active' AND expiry_date >= CURDATE()) AS active_passes,
    (SELECT COUNT(*) FROM bus_passes WHERE status = 'expired' OR expiry_date < CURDATE()) AS expired_passes,
    (SELECT COUNT(*) FROM payments WHERE status = 'completed' AND DATE(payment_date) = CURDATE()) AS today_payments,
    (SELECT IFNULL(SUM(amount), 0) FROM payments WHERE status = 'completed' AND MONTH(payment_date) = MONTH(CURDATE())) AS monthly_revenue,
    (SELECT COUNT(*) FROM notifications WHERE is_read = FALSE) AS unread_notifications;
