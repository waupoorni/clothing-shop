<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
if (!is_dir(__DIR__ . '/../logs')) { @mkdir(__DIR__ . '/../logs', 0777, true); }
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

require_once __DIR__ . "/auth.php";
require_admin();
require_once __DIR__ . "/../config/db.php";

/* ---------- Helpers ---------- */
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

/* ---------- Paths ---------- */
$baseDir      = dirname(__DIR__);                       // /.../htdocs/clothing-shop
$uploadDir    = $baseDir . "/uploads/clothes/";         // FS path
$publicPrefix = "/clothing-shop/uploads/clothes/";      // URL path (change "clothing-shop" if your folder name differs)

// ensure folder exists
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }

$ok = $err = "";
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

    if ($name === "" || $price < 0) {
      $err = "Name and valid price are required.";
    } else {
      /* ----- Image upload (optional) ----- */
      $image_url = null;

      if (!empty($_FILES['image']['name'])) {
        if (!is_writable($uploadDir)) {
          $err = "Upload directory not writable: " . $uploadDir;
        } else {
          $f = $_FILES['image'];

          if ($f['error'] !== UPLOAD_ERR_OK) {
            $err = "Upload failed: " . upload_err_msg($f['error']);
          } else {
            $allowedExt  = ['jpg','jpeg','png','gif','webp'];
            $allowedMime = ['image/jpeg','image/png','image/gif','image/webp'];

            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $mime = null;
            if (function_exists('finfo_open')) {
              $fi = finfo_open(FILEINFO_MIME_TYPE);
              $mime = $fi ? finfo_file($fi, $f['tmp_name']) : null;
              if ($fi) finfo_close($fi);
            }

            if (!in_array($ext, $allowedExt)) {
              $err = "Invalid file extension .$ext (allowed: " . implode(', ', $allowedExt) . ")";
            } elseif ($mime && !in_array($mime, $allowedMime)) {
              $err = "Invalid MIME type $mime";
            } else {
              $fname = time().'_'.bin2hex(random_bytes(3)).'.'.$ext;
              $dest  = $uploadDir.$fname;

              if (!is_uploaded_file($f['tmp_name'])) {
                $err = "Temporary upload file missing.";
              } elseif (!move_uploaded_file($f['tmp_name'], $dest)) {
                $err = "move_uploaded_file failed to: " . $dest;
              } else {
                $image_url = $publicPrefix.$fname;   // Store URL path in DB
              }
            }
          }
        }
      }

      /* ----- Insert row if no errors ----- */
      if (!$err) {
        $stmt = $conn->prepare("INSERT INTO clothes (name, sku, price, category, size, stock, image_url, description) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssdssiss", $name, $sku, $price, $category, $size, $stock, $image_url, $desc);
        if ($stmt->execute()) {
          $ok = "Cloth item added successfully.";
        } else {
          $err = "Insert failed. Possibly duplicate SKU.";
        }
        $stmt->close();
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
  <title>Admin · Add Clothes</title>
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
    @media (max-width:900px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
<?php include __DIR__ . "/sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-top"><h2 style="margin:0">Add Clothes</h2></div>

  <?php if($ok): ?><div class="msg ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
  <?php if($err): ?><div class="msg err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="card">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="grid">
      <div>
        <label class="label">Name *</label>
        <input class="input" name="name" required>
      </div>
      <div>
        <label class="label">SKU (unique)</label>
        <input class="input" name="sku">
      </div>

      <div>
        <label class="label">Price *</label>
        <input class="input" type="number" step="0.01" name="price" required>
      </div>
      <div>
        <label class="label">Category</label>
        <input class="input" name="category" placeholder="Men / Women / Kids / Accessories">
      </div>

      <div>
        <label class="label">Size</label>
        <input class="input" name="size" placeholder="S, M, L, XL">
      </div>
      <div>
        <label class="label">Stock</label>
        <input class="input" type="number" name="stock" value="0">
      </div>

      <div>
        <label class="label">Image</label>
        <input class="input" type="file" name="image" accept="image/*">
      </div>
      <div>
        <label class="label">Description</label>
        <textarea name="description" rows="3"></textarea>
      </div>
    </div>

    <div style="margin-top:12px;">
      <button class="btn" type="submit">Save</button>
    </div>
  </form>
</main></div>
</body>
</html>
