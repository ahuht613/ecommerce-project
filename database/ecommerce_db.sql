-- =====================================================
-- COMPLETE E-COMMERCE DATABASE SCHEMA - FIXED VERSION
-- Xóa và tạo lại database hoàn chỉnh với tất cả cột cần thiết
-- =====================================================

-- Xóa database cũ nếu tồn tại và tạo mới
DROP DATABASE IF EXISTS ecommerce_db;
CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- =====================================================
-- 1. BẢNG CATEGORIES (Tạo trước vì products sẽ reference)
-- =====================================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. BẢNG USERS (Admin và Customer)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    avatar_url VARCHAR(500),
    birth_date DATE,
    gender ENUM('male', 'female', 'other'),
    role ENUM('admin', 'customer') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. BẢNG PRODUCTS (Với đầy đủ cột cần thiết)
-- =====================================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT NULL,
    image_url VARCHAR(500),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. BẢNG CART
-- =====================================================
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. BẢNG ORDERS
-- =====================================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method ENUM('cod', 'bank', 'card') DEFAULT 'cod',
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT,
    cancel_reason VARCHAR(255),
    cancel_notes TEXT,
    cancelled_by INT NULL,
    cancelled_at TIMESTAMP NULL,
    confirmed_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. BẢNG ORDER_ITEMS
-- =====================================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. BẢNG ORDER_STATUS_HISTORY
-- =====================================================
CREATE TABLE order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled', 'completed') NOT NULL,
    notes TEXT,
    changed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. BẢNG CANCEL_REQUESTS
-- =====================================================
CREATE TABLE cancel_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    reason ENUM('changed_mind', 'found_better_price', 'wrong_product', 'delivery_too_long', 'payment_issue', 'other') NOT NULL,
    other_reason TEXT,
    additional_notes TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    processed_by INT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. DỮ LIỆU MẪU
-- =====================================================

-- Thêm categories mẫu
INSERT INTO categories (name, description) VALUES
('Điện tử', 'Thiết bị điện tử và công nghệ'),
('Thời trang', 'Quần áo và phụ kiện'),
('Sách', 'Sách và văn học'),
('Gia dụng', 'Đồ gia dụng và nội thất'),
('Thể thao', 'Dụng cụ thể thao và thể dục');

-- Thêm users mẫu (password = "password" đã hash)
INSERT INTO users (username, email, password, full_name, phone, address, role, email_verified) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0123456789', 'Admin Office', 'admin', TRUE),
('customer1', 'customer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn An', '0987654321', '123 Đường ABC, Quận 1, TP.HCM', 'customer', TRUE),
('customer2', 'customer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Bình', '0912345678', '456 Đường XYZ, Quận 2, TP.HCM', 'customer', TRUE);

-- Thêm products mẫu
INSERT INTO products (name, description, price, stock_quantity, category_id, image_url, status) VALUES
('iPhone 15 Pro', 'Điện thoại thông minh cao cấp với chip A17 Pro', 25000000, 50, 1, 'https://via.placeholder.com/300x300?text=iPhone+15+Pro', 'active'),
('MacBook Air M2', 'Laptop siêu mỏng với chip M2 mạnh mẽ', 28000000, 30, 1, 'https://via.placeholder.com/300x300?text=MacBook+Air', 'active'),
('Áo thun nam', 'Áo thun cotton cao cấp, thoáng mát', 250000, 100, 2, 'https://via.placeholder.com/300x300?text=Áo+thun', 'active'),
('Quần jeans nữ', 'Quần jeans skinny fit thời trang', 450000, 75, 2, 'https://via.placeholder.com/300x300?text=Quần+jeans', 'active'),
('Sách lập trình', 'Học lập trình từ cơ bản đến nâng cao', 180000, 200, 3, 'https://via.placeholder.com/300x300?text=Sách+lập+trình', 'active'),
('Nồi cơm điện', 'Nồi cơm điện thông minh 1.8L', 1200000, 40, 4, 'https://via.placeholder.com/300x300?text=Nồi+cơm', 'active');

-- Thêm orders mẫu
INSERT INTO orders (user_id, full_name, phone, shipping_address, payment_method, total_amount, status, notes, created_at) VALUES
(2, 'Nguyễn Văn An', '0987654321', '123 Đường ABC, Quận 1, TP.HCM', 'cod', 25250000, 'pending', 'Giao hàng giờ hành chính', '2024-01-15 10:30:00'),
(2, 'Nguyễn Văn An', '0987654321', '123 Đường ABC, Quận 1, TP.HCM', 'bank', 700000, 'confirmed', '', '2024-01-10 14:20:00'),
(3, 'Trần Thị Bình', '0912345678', '456 Đường XYZ, Quận 2, TP.HCM', 'cod', 1380000, 'delivered', '', '2024-01-05 09:15:00');

-- Thêm order items
INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES
(1, 1, 'iPhone 15 Pro', 1, 25000000),
(1, 3, 'Áo thun nam', 1, 250000),
(2, 4, 'Quần jeans nữ', 1, 450000),
(2, 3, 'Áo thun nam', 1, 250000),
(3, 6, 'Nồi cơm điện', 1, 1200000),
(3, 5, 'Sách lập trình', 1, 180000);

-- Thêm lịch sử trạng thái đơn hàng
INSERT INTO order_status_history (order_id, status, notes, changed_by) VALUES
(1, 'pending', 'Đơn hàng được tạo', 2),
(2, 'pending', 'Đơn hàng được tạo', 2),
(2, 'confirmed', 'Đơn hàng đã được xác nhận', 1),
(3, 'pending', 'Đơn hàng được tạo', 3),
(3, 'confirmed', 'Đơn hàng đã được xác nhận', 1),
(3, 'processing', 'Đang chuẩn bị hàng', 1),
(3, 'shipping', 'Đang giao hàng', 1),
(3, 'delivered', 'Đã giao hàng thành công', 1);

-- =====================================================
-- 10. KIỂM TRA KẾT QUẢ
-- =====================================================
SELECT 'Database created successfully!' as message;
SELECT 'Categories count:' as info, COUNT(*) as count FROM categories;
SELECT 'Users count:' as info, COUNT(*) as count FROM users;
SELECT 'Products count:' as info, COUNT(*) as count FROM products;
SELECT 'Orders count:' as info, COUNT(*) as count FROM orders;

-- Hiển thị cấu trúc bảng products để kiểm tra
DESCRIBE products;
