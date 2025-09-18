<?php
// shop.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/config/db.php";

// Public CSRF token shared with cart.php
if (empty($_SESSION['cart_csrf'])) {
  $_SESSION['cart_csrf'] = bin2hex(random_bytes(32));
}
$cartCsrf = $_SESSION['cart_csrf'];

// Get cart count for header button
$cartItemsCount = 0;
if (!empty($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $it) { $cartItemsCount += (int)($it['qty'] ?? 0); }
}

// (Optional) include the public navbar
include __DIR__ . "/components/navbar.php";

// -------- Query params (filters) --------
$q        = trim($_GET['q'] ?? "");
$category = trim($_GET['category'] ?? "");
$size     = trim($_GET['size'] ?? "");
$minPrice = $_GET['min'] !== null && $_GET['min'] !== "" ? (float)$_GET['min'] : null;
$maxPrice = $_GET['max'] !== null && $_GET['max'] !== "" ? (float)$_GET['max'] : null;
$sort     = $_GET['sort'] ?? "newest";
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 9;
$offset   = ($page - 1) * $perPage;

// -------- Build WHERE safely --------
$where = [];
$types = "";
$vals  = [];

if ($q !== "") {
  $where[] = "(name LIKE CONCAT('%', ?, '%') OR sku LIKE CONCAT('%', ?, '%') OR category LIKE CONCAT('%', ?, '%') OR size LIKE CONCAT('%', ?, '%'))";
  $types .= "ssss";
  $vals[] = $q; $vals[] = $q; $vals[] = $q; $vals[] = $q;
}
if ($category !== "") {
  $where[] = "category = ?";
  $types .= "s";
  $vals[] = $category;
}
if ($size !== "") {
  $where[] = "size = ?";
  $types .= "s";
  $vals[] = $size;
}
if ($minPrice !== null) {
  $where[] = "price >= ?";
  $types .= "d";
  $vals[] = $minPrice;
}
if ($maxPrice !== null) {
  $where[] = "price <= ?";
  $types .= "d";
  $vals[] = $maxPrice;
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// -------- Sorting (whitelist) --------
switch ($sort) {
  case "price_asc":  $orderBy = "price ASC, id DESC"; break;
  case "price_desc": $orderBy = "price DESC, id DESC"; break;
  case "name_asc":   $orderBy = "name ASC, id DESC"; break;
  default:           $orderBy = "created_at DESC"; // newest
}

// -------- Count total --------
$sqlCount = "SELECT COUNT(*) AS c FROM clothes $whereSql";
$stmt = $conn->prepare($sqlCount);
if ($types !== "") { $stmt->bind_param($types, ...$vals); }
$stmt->execute();
$resCount = $stmt->get_result();
$total = (int)($resCount->fetch_assoc()['c'] ?? 0);
$stmt->close();

// -------- Fetch page --------
$sqlList = "SELECT id, name, sku, price, category, size, stock, image_url 
            FROM clothes
            $whereSql
            ORDER BY $orderBy
            LIMIT ? OFFSET ?";
$typesList = $types . "ii";
$valsList  = $vals;
$valsList[] = $perPage;
$valsList[] = $offset;

$stmt = $conn->prepare($sqlList);
$stmt->bind_param($typesList, ...$valsList);
$stmt->execute();
$list = $stmt->get_result();

// -------- Distinct filters (categories/sizes) --------
$cats = [];
$szs  = [];

$rc = $conn->query("SELECT DISTINCT category FROM clothes WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
if ($rc) while ($r = $rc->fetch_assoc()) { $cats[] = $r['category']; }

$rs = $conn->query("SELECT DISTINCT size FROM clothes WHERE size IS NOT NULL AND size <> '' ORDER BY size ASC");
if ($rs) while ($r = $rs->fetch_assoc()) { $szs[] = $r['size']; }

// Helper for query string builder (preserve filters)
function qs($overrides = []) {
  $params = array_merge($_GET, $overrides);
  return http_build_query(array_filter($params, function($v){ return $v !== null && $v !== ""; }));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shop - Clothify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#fafafa}
    .wrap{max-width:1200px;margin:24px auto;padding:0 16px}
    .toolbar{display:grid;grid-template-columns:1.2fr .8fr;gap:16px;margin-bottom:16px}
    .card{background:#fff;border:1px solid #eaeaea;border-radius:10px;padding:12px}
    .filters .row{display:grid;grid-template-columns:repeat(6,1fr);gap:8px}
    .input,.select{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;background:#fff}
    .btn{padding:10px 14px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;text-decoration:none;display:inline-block}
    .btn.secondary{background:#e7e7e7;color:#111}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .product{background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden;display:flex;flex-direction:column}
    .img{width:100%;height:240px;object-fit:cover;background:#f3f3f3}
    .px{padding:12px}
    .title{font-weight:600;margin:6px 0}
    .meta{color:#666;font-size:.9em}
    .price{font-size:1.1em;font-weight:700;margin:8px 0}
    .empty{padding:18px;color:#666}
    .toprow{display:flex;justify-content:space-between;align-items:center}
    .badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#f6f6f6;border:1px solid #eee}
    .pagination{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0}
    .page{padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;text-decoration:none;color:#222}
    .page.active{background:#111;color:#fff;border-color:#111}
    @media(max-width:1000px){.grid{grid-template-columns:repeat(2,1fr)} .filters .row{grid-template-columns:repeat(3,1fr)} .toolbar{grid-template-columns:1fr}}
    @media(max-width:640px){.grid{grid-template-columns:1fr} .filters .row{grid-template-columns:1fr 1fr}}
    .qty{width:80px;padding:8px;border:1px solid #ccc;border-radius:8px}
  </style>
</head>
<body>

<div class="wrap">
  <div class="toprow" style="margin-bottom:10px;">
    <h1 style="margin:0;">Shop</h1>
    <div style="display:flex;gap:8px;align-items:center;">
      <span class="badge"><?= $total ?> result<?= $total==1?"":"s" ?></span>
      <a class="btn secondary" href="cart.php">🛒 Cart (<?= (int)$cartItemsCount ?>)</a>
    </div>
  </div>

  <!-- Filters + Sort -->
  <div class="toolbar">
    <div class="card filters">
      <form method="GET">
        <div class="row">
          <input class="input" type="text" name="q" placeholder="Search name, SKU, category..." value="<?= htmlspecialchars($q) ?>">
          <select class="select" name="category">
            <option value="">All Categories</option>
            <?php foreach($cats as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>" <?= $category===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="select" name="size">
            <option value="">All Sizes</option>
            <?php foreach($szs as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= $size===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
          <input class="input" type="number" step="0.01" name="min" placeholder="Min price" value="<?= $minPrice!==null?htmlspecialchars($minPrice):'' ?>">
          <input class="input" type="number" step="0.01" name="max" placeholder="Max price" value="<?= $maxPrice!==null?htmlspecialchars($maxPrice):'' ?>">
          <button class="btn" type="submit">Filter</button>
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
          <a class="btn secondary" href="shop.php">Reset</a>
        </div>
      </form>
    </div>

    <div class="card">
      <form method="GET" style="display:flex;gap:8px;align-items:center;justify-content:flex-end;">
        <!-- keep existing filters when changing sort -->
        <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
        <input type="hidden" name="size" value="<?= htmlspecialchars($size) ?>">
        <input type="hidden" name="min" value="<?= $minPrice!==null?htmlspecialchars($minPrice):'' ?>">
        <input type="hidden" name="max" value="<?= $maxPrice!==null?htmlspecialchars($maxPrice):'' ?>">
        <label for="sort" class="meta">Sort:</label>
        <select class="select" id="sort" name="sort" onchange="this.form.submit()">
          <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option>
          <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Price: Low → High</option>
          <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High → Low</option>
          <option value="name_asc" <?= $sort==='name_asc'?'selected':'' ?>>Name: A → Z</option>
        </select>
        <noscript><button class="btn" type="submit">Apply</button></noscript>
      </form>
    </div>
  </div>

  <!-- Product Grid -->
  <div class="grid">
    <?php if($list->num_rows === 0): ?>
      <div class="empty card" style="grid-column:1/-1;">No products found. Try adjusting filters.</div>
    <?php else: ?>
      <?php while($row = $list->fetch_assoc()): ?>
        <div class="product">
          <?php if(!empty($row['image_url'])): ?>
            <img class="img" src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
          <?php else: ?>
            <div class="img" style="display:flex;align-items:center;justify-content:center;color:#888;">No Image</div>
          <?php endif; ?>
          <div class="px">
            <div class="title"><?= htmlspecialchars($row['name']) ?></div>
            <div class="meta"><?= htmlspecialchars($row['category'] ?: 'Uncategorized') ?> · <?= htmlspecialchars($row['size'] ?: '-') ?></div>
            <div class="price">$<?= number_format((float)$row['price'], 2) ?></div>
            <div class="meta">Stock: <?= (int)$row['stock'] ?></div>

            <div style="display:flex;gap:8px;margin-top:10px;align-items:center;flex-wrap:wrap;">
              <?php if((int)$row['stock'] <= 0): ?>
                <span class="btn secondary" style="pointer-events:none;opacity:.6;">Out of Stock</span>
              <?php else: ?>
                <!-- Add to Cart form -->
                <form method="POST" action="cart.php" style="display:flex;gap:8px;align-items:center;">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($cartCsrf) ?>">
                  <input class="qty" type="number" name="qty" min="1" max="<?= (int)$row['stock'] ?>" value="1" />
                  <button class="btn" type="submit">Add to Cart</button>
                </form>
              <?php endif; ?>

              <?php /* If you add a product details page later:
              <a class="btn secondary" href="product.php?id=<?= (int)$row['id'] ?>">View</a>
              */ ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php
    $pages = max(1, (int)ceil($total / $perPage));
    if ($pages > 1):
  ?>
  <div class="pagination">
    <?php for($i=1; $i<=$pages; $i++): ?>
      <a class="page <?= $i===$page ? 'active' : '' ?>" href="?<?= qs(['page'=>$i]) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

</body>
</html>
