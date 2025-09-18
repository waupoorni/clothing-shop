Clothify — PHP Clothing Shop (Windows)

A simple web shop built with PHP + MySQL (no framework).
Public storefront (home, shop with filters, cart) + secure admin area (messages, products CRUD, image uploads).

Tested on Windows 10/11 with XAMPP (Apache, PHP 8+, MySQL/MariaDB).

✨ Features

Public

Home with featured/latest products

Shop grid with search, category/size filters, price range, sorting, pagination

Session cart: add / update / remove / clear

About Us & Contact Us (saves messages to DB)

Reusable navbar

Admin

Admin register & login (hashed passwords)

Sidebar: View Messages, Add Clothes, View / Edit / Delete

Image uploads (jpg/png/gif/webp) with basic validation

CSRF tokens + prepared statements

🧰 Stack

PHP 8+ (works on PHP 7.4+)

MySQL / MariaDB

Apache (via XAMPP)

HTML/CSS, PHP sessions

📁 Project Structure

clothing-shop/
├─ admin/
│  ├─ add_cloth.php
│  ├─ clothes.php
│  ├─ edit_cloth.php
│  ├─ view_cloth.php
│  ├─ login.php / register.php / logout.php
│  ├─ messages.php
│  ├─ sidebar.php
│  └─ auth.php
├─ components/
│  └─ navbar.php
├─ config/
│  └─ db.php
├─ uploads/
│  └─ clothes/           ← product images (must exist)
├─ assets/               ← optional static images/css
├─ index.php             ← home (featured)
├─ shop.php              ← storefront
├─ cart.php              ← session cart
├─ about.php
└─ contact.php

⚙️ Setup on Windows (XAMPP)
1) Install & start XAMPP

Download XAMPP from apachefriends.org and install to C:\xampp.

Open XAMPP Control Panel → Start Apache and MySQL.

2) Clone or copy the project into htdocs

cd C:\xampp\htdocs
git clone <your-repo-url> clothing-shop
# Or copy the folder here manually


3) Create database & tables

Open http://localhost/phpmyadmin → create database: clothing_shop → run:

USE clothing_shop;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  subject VARCHAR(160),
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clothes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  sku VARCHAR(60) UNIQUE,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  category VARCHAR(100),
  size VARCHAR(40),
  stock INT NOT NULL DEFAULT 0,
  image_url VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


4) Configure DB connection

Create config/db.php:

<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";            // XAMPP default on Windows
$db   = "clothing_shop";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("DB connection failed: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8mb4");


5) Create the uploads folder

Make sure these folders exist (Windows usually doesn’t need special permissions):

C:\xampp\htdocs\clothing-shop\uploads\
C:\xampp\htdocs\clothing-shop\uploads\clothes\


In the code, images are saved with a URL like
/clothing-shop/uploads/clothes/filename.jpg


▶️ Run

1) Home: http://localhost/clothing-shop/


2) Shop: http://localhost/clothing-shop/shop.php


3) Cart: http://localhost/clothing-shop/cart.php


4)Admin Register: http://localhost/clothing-shop/admin/register.php


5)Admin Login: http://localhost/clothing-shop/admin/login.php


6) Add Product: http://localhost/clothing-shop/admin/add_cloth.php


7) Manage Products: /admin/clothes.php


8) Messages: /admin/messages.php
