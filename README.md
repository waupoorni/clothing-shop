Clothify — PHP Clothing Shop

A simple web shop built with PHP + MySQL (no framework) for a coursework assignment.
Includes a public storefront (home, shop with filters, cart) and a secure admin area (messages, products CRUD, image uploads).

Runs great on any Apache/PHP/MySQL stack.

✨ Features

Public

Home page with featured/latest products

Shop grid with search, category/size filters, price range, sorting, and pagination

Session cart: add / update / remove / clear

About Us & Contact Us (stores messages in DB)

Reusable top navbar component

Admin

Admin register + login (passwords hashed)

Sidebar: View Messages, Add Clothes, View / Edit / Delete Clothes

Image uploads with type checking (jpg/png/gif/webp)

CSRF protection on forms, prepared statements for DB

🧰 Stack

PHP 8+ (works on 7.4+)

MySQL / MariaDB

Apache (XAMPP)

HTML/CSS (no frontend framework)

Sessions for auth & cart

📁 Project Structure
clothing-shop/
├─ admin/
│  ├─ add_cloth.php
│  ├─ clothes.php          # list + delete
│  ├─ edit_cloth.php       # update
│  ├─ view_cloth.php       # detail
│  ├─ login.php / register.php / logout.php
│  ├─ messages.php
│  ├─ sidebar.php
│  └─ auth.php             # session + CSRF helpers
├─ components/
│  └─ navbar.php
├─ config/
│  └─ db.php               # DB connection
├─ uploads/
│  └─ clothes/             # product images (writable)
├─ assets/                 # optional static images/css
├─ index.php               # home (featured)
├─ shop.php                # storefront with filters
├─ cart.php                # session cart
├─ about.php
└─ contact.php

⚙️ Setup
1) Clone into your web root

XAMPP (macOS):

cd /Applications/XAMPP/xamppfiles/htdocs
git clone <your-repo-url> clothing-shop

2) Create database & tables

Open phpMyAdmin → create database: clothing_shop, then run:

USE clothing_shop;

-- Admin users
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  subject VARCHAR(160),
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products
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


Optional seed product:

INSERT INTO clothes (name, sku, price, category, size, stock)
VALUES ('Sample Tee', 'TEE-001', 19.99, 'Men', 'M', 10);

3) Configure DB connection

Create config/db.php:

<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";           // default for XAMPP
$db   = "clothing_shop";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("DB connection failed: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8mb4");

4) Make upload folder writable
sudo mkdir -p /Applications/XAMPP/xamppfiles/htdocs/clothing-shop/uploads/clothes
# Check which user Apache runs as: daemon or _www
ps aux | egrep '(httpd|apache)' | grep -v grep

# If 'daemon':
sudo chown -R daemon:daemon /Applications/XAMPP/xamppfiles/htdocs/clothing-shop/uploads
# If '_www', use _www:_www instead

sudo find /Applications/XAMPP/xamppfiles/htdocs/clothing-shop/uploads -type d -exec chmod 775 {} \;
sudo find /Applications/XAMPP/xamppfiles/htdocs/clothing-shop/uploads -type f -exec chmod 664 {} \;
sudo chmod g+s /Applications/XAMPP/xamppfiles/htdocs/clothing-shop/uploads

5) Start the server

Start Apache + MySQL in XAMPP

Visit:

http://localhost/clothing-shop/ (Home)

http://localhost/clothing-shop/shop.php (Shop)

http://localhost/clothing-shop/cart.php (Cart)

http://localhost/clothing-shop/admin/register.php (Admin register)

http://localhost/clothing-shop/admin/login.php (Admin login)

http://localhost/clothing-shop/admin/add_cloth.php (Add product)

🔐 Security Notes

Passwords hashed with password_hash() / verified by password_verify().

CSRF tokens on sensitive forms (admin & cart).

Prepared statements for all DB writes/reads.

Uploads: extension + (where available) MIME checks; files stored under /uploads/clothes/.

🧪 Common Issues & Fixes

“Upload directory not writable”
Fix ownership/permissions (see step 4). Confirm by visiting
http://localhost/clothing-shop/uploads/clothes/ after placing a test file.

“Invalid CSRF token”
The page/session expired or was opened in multiple tabs. Refresh the page and resubmit.

Images don’t show
Ensure image_url saved like /clothing-shop/uploads/clothes/filename.jpg and your project folder is indeed clothing-shop.

File upload fails silently
Make sure the form has enctype="multipart/form-data" and increase PHP limits in php.ini if needed:

file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M

🔗 Useful Routes

/ — Home (Featured)

/shop.php — Products grid (search/filters/sort/pagination)

/cart.php — Session cart

/about.php, /contact.php

/admin/login.php, /admin/register.php

/admin/add_cloth.php, /admin/clothes.php, /admin/edit_cloth.php, /admin/view_cloth.php

/admin/messages.php

🛣️ Roadmap / Ideas

Product detail page (product.php)

Cart → Checkout → Orders (tables: orders, order_items)

Image size validation + thumbnails

Admin: bulk upload via CSV

Basic email notifications for new messages/orders

📸 Screenshots

Add images to /screenshots and reference here:

![Home](screenshots/home.png)
![Shop](screenshots/shop.png)
![Admin](screenshots/admin.png)

📝 License

MIT — do whatever you want, just keep the license and attribution.

🙌 Credits

Built for coursework by Clothify team. Thanks to the PHP & XAMPP communities.
