# Matcha Shop - ร้านมัทฉะพรีเมียม

เว็บร้านมัทฉะ Full-Stack ด้วย PHP, Vue 3, และ MongoDB

![Black-Green Theme](https://img.shields.io/badge/Theme-Black%20%26%20Green-2d5a27)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4)
![Vue](https://img.shields.io/badge/Vue-3.4+-4FC08D)
![MongoDB](https://img.shields.io/badge/MongoDB-Latest-47A248)

## ✨ Features

- 🛍️ **หน้าร้าน** - แสดงสินค้าพร้อมรูปภาพ ราคา คำอธิบาย
- 🔐 **Authentication** - Login / Register ด้วย JWT
- 🛒 **ตะกร้าสินค้า** - เพิ่ม/ลบ/แก้ไขจำนวนสินค้า
- 📁 **หมวดหมู่** - จัดกลุ่มสินค้าตามประเภท
- 👨‍💼 **Admin Panel** - จัดการสินค้าและหมวดหมู่ (CRUD)
- 🎨 **Premium Design** - โทนสีดำ-เขียว, Glassmorphism, Animations

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- Node.js 18+
- MongoDB (ติดตั้ง local หรือใช้ MongoDB Atlas)
- Composer

### 1. Backend Setup

```bash
cd backend

# ติดตั้ง dependencies
composer install

# สร้างข้อมูลตัวอย่าง
php seed.php

# รัน development server
php -S localhost:8000 -t public
```

### 2. Frontend Setup

```bash
cd frontend

# ติดตั้ง dependencies
npm install

# รัน development server
npm run dev
```

### 3. เปิดเว็บ

- **หน้าร้าน**: http://localhost:5173
- **Admin Panel**: http://localhost:5173/admin

### Admin Login

```
Email: admin@matchazuki.com
Password: admin123
```

## 📁 Project Structure

```
├── backend/                    # PHP Backend API
│   ├── config/                 # Configuration
│   ├── public/                 # Entry point
│   └── src/
│       ├── Core/               # Database, Router, JWT
│       ├── Entity/             # Data models
│       ├── Repository/         # Database operations
│       └── Controller/         # API handlers
│
└── frontend/                   # Vue 3 Frontend
    ├── src/
    │   ├── assets/             # CSS Design System
    │   ├── components/         # Reusable components
    │   ├── views/              # Page components
    │   ├── stores/             # Pinia stores
    │   └── router/             # Vue Router
    └── public/                 # Static assets
```

## 🔗 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | สมัครสมาชิก |
| POST | `/api/auth/login` | เข้าสู่ระบบ |
| GET | `/api/products` | ดูสินค้าทั้งหมด |
| POST | `/api/products` | เพิ่มสินค้า (Admin) |
| PUT | `/api/products/:id` | แก้ไขสินค้า (Admin) |
| DELETE | `/api/products/:id` | ลบสินค้า (Admin) |
| GET | `/api/categories` | ดูหมวดหมู่ |
| GET | `/api/cart` | ดูตะกร้า |
| POST | `/api/cart/items` | เพิ่มสินค้าลงตะกร้า |

## 🎨 Design System

- **Primary**: `#2d5a27` (Matcha Green)
- **Secondary**: `#1a1a1a` (Dark)
- **Accent**: `#8fbc8b` (Light Green)
- **Font**: Outfit (Google Fonts)
- **Effects**: Glassmorphism, Gradients, Smooth animations

## 📝 License

MIT License
