## ✨ Tính năng

### 🛒 Customer Features
- ✅ **Đăng ký/Đăng nhập** tài khoản
- ✅ **Duyệt sản phẩm** theo danh mục
- ✅ **Tìm kiếm và lọc** sản phẩm
- ✅ **Giỏ hàng** thêm/xóa/cập nhật
- ✅ **Đặt hàng** với nhiều phương thức thanh toán
- ✅ **Theo dõi đơn hàng** và lịch sử
- ✅ **Quản lý profile** cá nhân
- ✅ **Responsive design** mobile/desktop

### 👨‍💼 Admin Features
- ✅ **Dashboard** tổng quan
- ✅ **Quản lý sản phẩm** CRUD với upload ảnh
- ✅ **Quản lý đơn hàng** và cập nhật trạng thái
- ✅ **Quản lý danh mục** sản phẩm
- ✅ **Xem chi tiết đơn hàng** và lịch sử
- ✅ **Responsive admin panel**

## 🛠️ Công nghệ sử dụng

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Vue Router** - Client-side routing
- **Axios** - HTTP client
- **Vite** - Build tool và dev server
- **CSS3** - Custom styling với CSS variables

### Backend
- **PHP 8+** - Server-side language
- **MySQL** - Relational database
- **REST API** - API architecture
- **JWT** - Authentication tokens
- **File Upload** - Image handling

### Development Tools
- **Git** - Version control
- **npm** - Package manager
- **XAMPP/WAMP** - Local development environment

## 🚀 Cài đặt

### 1. Clone dự án
```bash
git clone <repository-url>
cd ecommerce-project
```

### 2. Cài đặt Database
```bash
# Tạo database MySQL
mysql -u root -p
CREATE DATABASE ecommerce_db;

# Import schema và dữ liệu mẫu
mysql -u root -p ecommerce_db < database/ecommerce_db.sql
```

### 3. Cấu hình Backend
```bash
# Cập nhật thông tin database trong BE/api/config.php
# Thay đổi username, password, host nếu cần
```

### 4. Cài đặt Frontend Customer
```bash
cd FE
npm install
```

### 5. Cài đặt Admin Panel
```bash
cd ../admin
npm install
```

## ▶️ Cách chạy

### 1. Khởi động Web Server
```bash
# Với XAMPP: Start Apache và MySQL
# Hoặc với PHP built-in server:
cd BE
php -S localhost:8000
```

### 2. Chạy Customer Frontend
```bash
cd FE
npm run dev
# Mở http://localhost:5173
```

### 3. Chạy Admin Panel
```bash
cd admin
npm run dev
# Mở http://localhost:5174
```

### 4. Truy cập ứng dụng
- **Customer**: http://localhost:5173
- **Admin**: http://localhost:5174
- **API**: http://localhost/ecommerce-project/BE/api/

## 👤 Tài khoản demo

### Customer Account
- **Username**: `customer1`
- **Email**: `customer1@example.com`
- **Password**: `password`

### Admin Account
- **Username**: `admin`
- **Email**: `admin@example.com`
- **Password**: `password`
