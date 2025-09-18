<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
if (!is_dir(__DIR__ . '/../logs')) { @mkdir(__DIR__ . '/../logs', 0777, true); }
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

require_once __DIR__ . "/auth.php";
require_admin();
require_once __DIR__ . "/../config/db.php";

/* Helpers */
function csrf() { return csrf_token(); }
function url_to_abs_path($urlPath) {
  // convert "/clothing-shop/uploads/xyz.jpg" -> ".../htdocs/clothing-shop/uploads/xyz.jpg"
  $root = dirname(__DIR__); // .../clothing-shop
  $urlPath = preg_replace('#^/clothing-shop#', '', (string)$urlPath); // strip folder prefix
  return $root . $urlPath; // -> .../clothing-shop/uploads/...
}

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$total = 0;
$cnt = $conn->query("SELECT COUNT(*) AS c FROM clothes");
if ($cnt) { $row = $cnt->fetch_assoc(); $total = (int)$row['c']; }

// Delete handler
$flash_ok = $flash_err = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
  if (!verify_csrf($_POST['csrf'] ?? "")) {
    $flash_err = "Invalid CSRF token.";
  } else {
    $delId = (int)$_POST['delete_id'];

    // fetch image path
    $q = $conn->prepare("SELECT image_url FROM clothes WHERE id = ?");
    $q->bind_param("i", $delId);
    $q->execute();
    $r = $q->get_result();
    $img = $r && $r->num_rows ? $r->fetch_assoc()['image_url'] : null;
    $q->close();

    // delete record
    $del = $conn->prepare("DELETE FROM clothes WHERE id = ?");
    $del->bind_param("i", $delId);
    if ($del->execute()) {
      // try to unlink local file
      if (!empty($img)) {
        $abs = url_to_abs_path($img);
        if (is_file($abs)) @unlink($abs);
      }
      header("Location: clothes.php?deleted=1&page=" . $page);
      exit;
    } else {
      $flash_err = "Failed to delete.";
    }
    $del->close();
  }
}

$csrf = csrf();

// Fetch list
$stmt = $conn->prepare("SELECT id, name, sku, price, category, size, stock, image_url, created_at FROM clothes ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$list = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin · Clothes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .card{background:#fff;border:1px solid #e8e8e8;border-radius:10px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #f0f0f0;vertical-align:top}
    th{text-align:left;background:#fafafa}
    .img{width:70px;height:70px;object-fit:cover;border-radius:8px;border:1px solid #eee}
    .meta{color:#666;font-size:.9em}
    .btn{padding:8px 10px;border:0;border-radius:8px;cursor:pointer;text-decoration:none;display:inline-block}
    .btn.del{background:#e11;color:#fff}
    .btn.view{background:#0a7;color:#fff}
    .btn.edit{background:#1565c0;color:#fff}
    .pagination{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0}
    .page{padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;text-decoration:none;color:#222}
    .page.active{background:#111;color:#fff;border-color:#111}
    .msg{margin:12px 0;padding:10px;border-radius:8px}
    .ok{background:#eaffea;border:1px solid #9ce29c}
    .err{background:#ffecec;border:1px solid #ff5a5a}
    @media (max-width:900px){.hide-sm{display:none}}
    .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  </style>
</head>
<body>
<?php include __DIR__ . "/sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-top">
    <h2 style="margin:0">Clothes (<?= (int)$total ?>)</h2>
    <a href="add_cloth.php" class="btn" style="background:#111;color:#fff;">Add New</a>
  </div>

  <?php if(isset($_GET['deleted'])): ?><div class="msg ok">Item deleted.</div><?php endif; ?>
  <?php if($flash_ok): ?><div class="msg ok"><?= htmlspecialchars($flash_ok) ?></div><?php endif; ?>
  <?php if($flash_err): ?><div class="msg err"><?= htmlspecialchars($flash_err) ?></div><?php endif; ?>

  <div class="card" style="padding:12px;overflow:auto;">
    <?php if($list->num_rows === 0): ?>
      <div style="padding:12px;color:#666">No items yet.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th class="hide-sm">SKU / Category / Size</th>
            <th>Price / Stock</th>
            <th class="hide-sm">Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $list->fetch_assoc()): ?>
          <tr>
            <td>
              <div style="display:flex;gap:10px;align-items:center;">
                <?php if(!empty($row['image_url'])): ?>
                  <img class="img" src="<?= htmlspecialchars($row['image_url']) ?>" alt="">
                <?php else: ?>
                  <div class="img" style="display:flex;align-items:center;justify-content:center;background:#f3f3f3;color:#888;">N/A</div>
                <?php endif; ?>
                <div>
                  <div><strong><?= htmlspecialchars($row['name']) ?></strong></div>
                  <div class="meta">#<?= (int)$row['id'] ?></div>
                </div>
              </div>
            </td>
            <td class="hide-sm">
              <div class="meta">SKU: <?= htmlspecialchars($row['sku'] ?? '-') ?></div>
              <div class="meta">Category: <?= htmlspecialchars($row['category'] ?? '-') ?></div>
              <div class="meta">Size: <?= htmlspecialchars($row['size'] ?? '-') ?></div>
            </td>
            <td>
              <div>$<?= number_format((float)$row['price'], 2) ?></div>
              <div class="meta">Stock: <?= (int)$row['stock'] ?></div>
            </td>
            <td class="hide-sm">
              <div class="meta"><?= htmlspecialchars($row['created_at']) ?></div>
            </td>
            <td>
              <div class="actions">
                <a class="btn view" href="view_cloth.php?id=<?= (int)$row['id'] ?>">View</a>
                <a class="btn edit" href="edit_cloth.php?id=<?= (int)$row['id'] ?>">Edit</a>
                <form method="POST" onsubmit="return confirm('Delete this item?')">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                  <button class="btn del" type="submit">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <?php
    $pages = max(1, (int)ceil($total / $perPage));
    if ($pages > 1):
  ?>
  <div class="pagination">
    <?php for($i=1; $i<=$pages; $i++): ?>
      <a class="page <?= $i===$page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</main></div>
</body>
</html>
