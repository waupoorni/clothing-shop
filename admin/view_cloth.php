<?php
require_once __DIR__ . "/auth.php";
require_admin();
require_once __DIR__ . "/../config/db.php";

$id = (int)($_GET['id'] ?? 0);
$itm = null;

if ($id > 0) {
  $stmt = $conn->prepare("SELECT id, name, sku, price, category, size, stock, image_url, description, created_at FROM clothes WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $itm = $res->fetch_assoc();
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin · View Cloth</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .card{background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:16px}
    .meta{color:#666}
    .img-big{max-width:360px;max-height:360px;object-fit:cover;border-radius:10px;border:1px solid #eee}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media (max-width:900px){.row{grid-template-columns:1fr}}
    .btn{display:inline-block;padding:8px 12px;border:0;border-radius:8px;background:#111;color:#fff;text-decoration:none}
  </style>
</head>
<body>
<?php include __DIR__ . "/sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-top">
    <h2 style="margin:0">View Item</h2>
    <a class="btn" href="clothes.php">Back</a>
  </div>

  <?php if(!$itm): ?>
    <div class="card">Item not found.</div>
  <?php else: ?>
    <div class="card">
      <div class="row">
        <div>
          <?php if(!empty($itm['image_url'])): ?>
            <img class="img-big" src="<?= htmlspecialchars($itm['image_url']) ?>" alt="">
          <?php else: ?>
            <div class="img-big" style="display:flex;align-items:center;justify-content:center;background:#f3f3f3;color:#888;">No Image</div>
          <?php endif; ?>
        </div>
        <div>
          <h3 style="margin-top:0;"><?= htmlspecialchars($itm['name']) ?></h3>
          <p class="meta">#<?= (int)$itm['id'] ?> · <?= htmlspecialchars($itm['created_at']) ?></p>
          <p><strong>SKU:</strong> <?= htmlspecialchars($itm['sku'] ?? '-') ?></p>
          <p><strong>Category:</strong> <?= htmlspecialchars($itm['category'] ?? '-') ?></p>
          <p><strong>Size:</strong> <?= htmlspecialchars($itm['size'] ?? '-') ?></p>
          <p><strong>Price:</strong> $<?= number_format((float)$itm['price'], 2) ?></p>
          <p><strong>Stock:</strong> <?= (int)$itm['stock'] ?></p>
          <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($itm['description'] ?? '')) ?></p>
          <p style="margin-top:16px;">
            <a class="btn" href="edit_cloth.php?id=<?= (int)$itm['id'] ?>">Edit</a>
          </p>
        </div>
      </div>
    </div>
  <?php endif; ?>
</main></div>
</body>
</html>
