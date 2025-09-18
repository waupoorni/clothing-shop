<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/auth.php";
require_admin();
require_once __DIR__ . "/../config/db.php";

function upload_err_msg($code){
  return [
    UPLOAD_ERR_OK => 'OK',
    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize (php.ini)',
    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE (form)',
    UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
    UPLOAD_ERR_NO_FILE => 'No file uploaded',
    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload'
  ][$code] ?? ('Unknown upload error '.$code);
}
function url_to_abs_path($urlPath) {
  $root = dirname(__DIR__);
  $urlPath = preg_replace('#^/clothing-shop#', '', (string)$urlPath);
  return $root . $urlPath;
}

$publicPrefix = "/clothing-shop/uploads/clothes/";
$uploadDir    = dirname(__DIR__) . "/uploads/clothes/";
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: clothes.php"); exit; }

$ok = $err = "";
// Load current
$cur = null;
$st = $conn->prepare("SELECT id, name, sku, price, category, size, stock, image_url, description FROM clothes WHERE id = ?");
$st->bind_param("i", $id);
$st->execute();
$res = $st->get_result();
$cur = $res->fetch_assoc();
$st->close();
if (!$cur) { header("Location: clothes.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (!verify_csrf($_POST['csrf'] ?? "")) {
    $err = "Invalid CSRF token.";
  } else {
    $name     = trim($_POST['name'] ?? "");
    $sku      = trim($_POST['sku'] ?? "");
    $price    = (float)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? "");
    $size     = trim($_POST['size'] ?? "");
    $stock    = (int)($_POST['stock'] ?? 0);
    $desc     = trim($_POST['description'] ?? "");
    $old_img  = trim($_POST['old_image_url'] ?? "");

    if ($name === "" || $price < 0) {
      $err = "Name and valid price are required.";
    } else {
      $new_image_url = $old_img;

      // Optional new image
      if (!empty($_FILES['image']['name'])) {
        if (!is_writable($uploadDir)) {
          $err = "Upload directory not writable.";
        } else {
          $f = $_FILES['image'];
          if ($f['error'] !== UPLOAD_ERR_OK) {
            $err = "Upload failed: " . upload_err_msg($f['error']);
          } else {
            $allowedExt  = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
              $err = "Invalid file extension .$ext";
            } else {
              $fname = time().'_'.bin2hex(random_bytes(3)).'.'.$ext;
              $dest  = $uploadDir.$fname;
              if (!move_uploaded_file($f['tmp_name'], $dest)) {
                $err = "move_uploaded_file failed.";
              } else {
                $new_image_url = $publicPrefix.$fname;
                // remove old file if existed and local
                if (!empty($old_img)) {
                  $abs = url_to_abs_path($old_img);
                  if (is_file($abs)) @unlink($abs);
                }
              }
            }
          }
        }
      }

      if (!$err) {
        $u = $conn->prepare("UPDATE clothes SET name=?, sku=?, price=?, category=?, size=?, stock=?, image_url=?, description=? WHERE id=?");
        $u->bind_param("ssdssissi", $name, $sku, $price, $category, $size, $stock, $new_image_url, $desc, $id);
        if ($u->execute()) {
          header("Location: clothes.php?updated=1");
          exit;
        } else {
          $err = "Update failed. Possibly duplicate SKU.";
        }
        $u->close();
      }
    }
  }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin · Edit Cloth</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .card{background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:16px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .input, textarea{width:100%;padding:10px;border:1px solid #cfcfcf;border-radius:8px}
    .label{display:block;margin:8px 0 6px;font-weight:600}
    .btn{padding:10px 14px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer}
    .msg{margin:10px 0;padding:10px;border-radius:8px}
    .ok{background:#eaffea;border:1px solid #9ce29c}
    .err{background:#ffecec;border:1px solid #ff5a5a}
    .preview{display:flex;gap:12px;align-items:center}
    .img{width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #eee}
    @media (max-width:900px){.grid{grid-template-columns:1fr}}
    .link{display:inline-block;padding:8px 12px;border-radius:8px;background:#333;color:#fff;text-decoration:none}
  </style>
</head>
<body>
<?php include __DIR__ . "/sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-top">
    <h2 style="margin:0">Edit Item</h2>
    <a class="link" href="clothes.php">Back</a>
  </div>

  <?php if($ok): ?><div class="msg ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
  <?php if($err): ?><div class="msg err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="card">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="old_image_url" value="<?= htmlspecialchars($cur['image_url'] ?? '') ?>">

    <div class="grid">
      <div>
        <label class="label">Name *</label>
        <input class="input" name="name" value="<?= htmlspecialchars($cur['name']) ?>" required>
      </div>
      <div>
        <label class="label">SKU (unique)</label>
        <input class="input" name="sku" value="<?= htmlspecialchars($cur['sku']) ?>">
      </div>

      <div>
        <label class="label">Price *</label>
        <input class="input" type="number" step="0.01" name="price" value="<?= htmlspecialchars($cur['price']) ?>" required>
      </div>
      <div>
        <label class="label">Category</label>
        <input class="input" name="category" value="<?= htmlspecialchars($cur['category']) ?>" placeholder="Men / Women / Kids / Accessories">
      </div>

      <div>
        <label class="label">Size</label>
        <input class="input" name="size" value="<?= htmlspecialchars($cur['size']) ?>" placeholder="S, M, L, XL">
      </div>
      <div>
        <label class="label">Stock</label>
        <input class="input" type="number" name="stock" value="<?= (int)$cur['stock'] ?>">
      </div>

      <div>
        <label class="label">Image (leave empty to keep current)</label>
        <div class="preview">
          <?php if(!empty($cur['image_url'])): ?>
            <img class="img" src="<?= htmlspecialchars($cur['image_url']) ?>" alt="">
          <?php else: ?>
            <div class="img" style="display:flex;align-items:center;justify-content:center;background:#f3f3f3;color:#888;">N/A</div>
          <?php endif; ?>
          <input class="input" type="file" name="image" accept="image/*" style="border:0">
        </div>
      </div>

      <div>
        <label class="label">Description</label>
        <textarea name="description" rows="3"><?= htmlspecialchars($cur['description'] ?? '') ?></textarea>
      </div>
    </div>

    <div style="margin-top:12px;">
      <button class="btn" type="submit">Update</button>
    </div>
  </form>
</main></div>
</body>
</html>
