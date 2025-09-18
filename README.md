# Clothify — PHP Clothing Shop (Windows)

A simple web shop built with **PHP + MySQL** (no framework).
Public storefront (home, shop with filters, cart) + secure admin area (messages, products CRUD, image uploads).

> Tested on **Windows 10/11** with **XAMPP** (Apache, PHP 8+, MySQL/MariaDB).

---

## ✨ Features

**Public**

* Home with featured/latest products
* Shop grid with **search**, **category/size** filters, **price range**, **sorting**, **pagination**
* Session **cart**: add / update / remove / clear
* About Us & Contact Us (saves messages to DB)
* Reusable navbar

**Admin**

* Admin **register** & **login** (hashed passwords)
* Sidebar: **View Messages**, **Add Clothes**, **View / Edit / Delete**
* Image uploads (`jpg/png/gif/webp`) with basic validation
* CSRF tokens + prepared statements

---

## 🧰 Stack

* PHP 8+ (works on PHP 7.4+)
* MySQL / MariaDB
* Apache (via XAMPP)
* HTML/CSS, PHP sessions

---

## 📁 Project Structure

```
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
```

---

## ⚙️ Setup on Windows (XAMPP)

### 1) Install & start XAMPP

* Download XAMPP from apachefriends.org and install to `C:\xampp`.
* Open **XAMPP Control Panel** → Start **Apache** and **MySQL**.

### 2) Clone or copy the project into `htdocs`

```powershell
cd C:\xampp\htdocs
git clone <your-repo-url> clothing-shop
# Or copy the folder here manually
```

### 3) Create database & tables

Open `http://localhost/phpmyadmin` → create database: **`clothing_shop`** → run:

```sql
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
```

*(Optional seed)*:

```sql
INSERT INTO clothes (name, sku, price, category, size, stock)
VALUES ('Sample Tee', 'TEE-001', 19.99, 'Men', 'M', 10);
```

### 4) Configure DB connection

Create `config/db.php`:

```php
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
```

### 5) Create the uploads folder

Make sure these folders exist (Windows usually doesn’t need special permissions):

```
C:\xampp\htdocs\clothing-shop\uploads\
C:\xampp\htdocs\clothing-shop\uploads\clothes\
```

> In the code, images are saved with a URL like
> `/clothing-shop/uploads/clothes/filename.jpg`

### 6) (If needed) increase upload limits

Edit `C:\xampp\php\php.ini` and set:

```
file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M
```

Restart Apache from XAMPP Control Panel.

---

## ▶️ Run

* Home: **[http://localhost/clothing-shop/](http://localhost/clothing-shop/)**
* Shop: **[http://localhost/clothing-shop/shop.php](http://localhost/clothing-shop/shop.php)**
* Cart: **[http://localhost/clothing-shop/cart.php](http://localhost/clothing-shop/cart.php)**
* Admin Register: **[http://localhost/clothing-shop/admin/register.php](http://localhost/clothing-shop/admin/register.php)**
* Admin Login: **[http://localhost/clothing-shop/admin/login.php](http://localhost/clothing-shop/admin/login.php)**
* Add Product: **[http://localhost/clothing-shop/admin/add\_cloth.php](http://localhost/clothing-shop/admin/add_cloth.php)**
* Manage Products: **/admin/clothes.php**
* Messages: **/admin/messages.php**

---

## 🔐 Security

* Passwords hashed (`password_hash` / `password_verify`)
* CSRF tokens on admin & cart forms
* Prepared statements everywhere
* Basic file-type checks on uploads

---

## 🧪 Troubleshooting (Windows)

* **Apache won’t start / Port 80 in use**

  * Stop “World Wide Web Publishing Service” (IIS) or change Apache’s port in `httpd.conf`.
* **Images don’t show**

  * Ensure the project folder is exactly `clothing-shop` (used in image URLs).
  * Confirm the file exists under `uploads\clothes\`.
* **Upload fails**

  * Verify the `uploads\clothes` folder exists.
  * Check PHP limits in `php.ini` (Step 6).
* **“Invalid CSRF token”**

  * Refresh the page (session expired) and submit again.

---

## 🔗 Routes Recap

* `/` – Home
* `/shop.php` – Product grid (filters, sort, pagination)
* `/cart.php` – Session cart
* `/about.php`, `/contact.php`
* `/admin/*` – Admin pages (register, login, add, view/edit/delete, messages)

---

## 🛣️ Roadmap

* `product.php` detail page
* Orders & checkout (orders + order\_items tables)
* Image size validation/thumbnails
* Admin CSV import

---

## 📸 Screenshots (optional)

```
![Home](screenshots/home.png)
![Shop](screenshots/shop.png)
![Admin](screenshots/admin.png)
```

---

## 📝 License

MIT

---

