<?php
// index.php (Home)
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/config/db.php";

// Public cart CSRF (shared with cart.php)
if (empty($_SESSION['cart_csrf'])) {
  $_SESSION['cart_csrf'] = bin2hex(random_bytes(32));
}
$cartCsrf = $_SESSION['cart_csrf'];

// Fetch latest 6 clothes as "Featured"
$featured = $conn->query("
  SELECT id, name, price, image_url, stock, category, size
  FROM clothes
  ORDER BY created_at DESC
  LIMIT 6
");
?>
<?php
// Include Navbar (keep this after session so it can read session vars)
include __DIR__ . "/components/navbar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clothify - Home</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    :root { --brand:#ff4d4d; }
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#fafafa}
    .wrap{max-width:1200px;margin:40px auto;padding:0 16px}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    .card{background:#fff;border:1px solid #eaeaea;border-radius:12px;overflow:hidden}
    .img{width:100%;height:220px;object-fit:cover;background:#f3f3f3;display:block}
    .px{padding:12px}
    .title{font-weight:600;margin:6px 0}
    .meta{color:#666;font-size:.9em}
    .price{font-size:1.05rem;font-weight:700;margin:8px 0}
    .btn{padding:10px 14px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;text-decoration:none;display:inline-block}
    .btn.secondary{background:#e7e7e7;color:#111}
    .qty{width:80px;padding:8px;border:1px solid #ccc;border-radius:8px}
    @media (max-width:1000px){.grid{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:640px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>

  <!-- Hero Section -->
  <header style="background:url('assets/images/hero.jpg') center/cover no-repeat; min-height:420px; display:flex; justify-content:center; align-items:center; color:white; text-align:center;">
    <div style="background:rgba(0,0,0,0.5); padding:22px 26px; border-radius:10px;">
      <h1 style="margin:0 0 6px;">Welcome to Clothify</h1>
      <p style="margin:0 0 14px;">Trendy Clothes for Every Style</p>
      <a href="shop.php" class="btn" style="background:var(--brand);">Shop Now</a>
    </div>
  </header>

  <!-- Featured Products (Latest) -->
  <section class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
      <h2 style="margin:0;">Featured Products</h2>
      <a class="btn secondary" href="shop.php">View All</a>
    </div>

    <?php if(!$featured || $featured->num_rows === 0): ?>
      <div class="card" style="padding:16px;">No products yet. Check back soon!</div>
    <?php else: ?>
      <div class="grid">
        <?php while($p = $featured->fetch_assoc()): ?>
          <div class="card">
            <?php if(!empty($p['image_url'])): ?>
              <img class="img" src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
              <div class="img" style="display:flex;align-items:center;justify-content:center;color:#888;">No Image</div>
            <?php endif; ?>

            <div class="px">
              <div class="title"><?= htmlspecialchars($p['name']) ?></div>
              <div class="meta">
                <?= htmlspecialchars($p['category'] ?: 'Uncategorized') ?> ·
                <?= htmlspecialchars($p['size'] ?: '-') ?>
              </div>
              <div class="price">$<?= number_format((float)$p['price'], 2) ?></div>
              <div class="meta">Stock: <?= (int)$p['stock'] ?></div>

              <div style="display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap;">
                <?php if((int)$p['stock'] <= 0): ?>
                  <span class="btn secondary" style="pointer-events:none;opacity:.6;">Out of Stock</span>
                <?php else: ?>
                  <!-- Add to Cart -->
                  <form method="POST" action="cart.php" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($cartCsrf) ?>">
                    <input class="qty" type="number" name="qty" min="1" max="<?= (int)$p['stock'] ?>" value="1">
                    <button class="btn" type="submit">Add to Cart</button>
                  </form>
                <?php endif; ?>

                <?php /* If you add a product details page later:
                <a class="btn secondary" href="product.php?id=<?= (int)$p['id'] ?>">View</a>
                */ ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Footer -->
  <footer style="background:#222; color:#fff; text-align:center; padding:20px; margin-top:40px;">
    <p>&copy; <?= date("Y"); ?> Clothify. All Rights Reserved.</p>
  </footer>

</body>
</html>
