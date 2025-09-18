<?php
// cart.php — session cart (Create/Read/Update/Delete)
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/config/db.php";   // DB for product lookups
include __DIR__ . "/components/navbar.php"; // public navbar

/* ---------------- CSRF (public) ---------------- */
if (empty($_SESSION['cart_csrf'])) {
  $_SESSION['cart_csrf'] = bin2hex(random_bytes(32));
}
function cart_csrf() { return $_SESSION['cart_csrf']; }
function cart_verify_csrf($t) {
  return isset($_SESSION['cart_csrf']) && hash_equals($_SESSION['cart_csrf'], $t ?? "");
}

/* ---------------- Helpers ---------------- */
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = []; // id => item

function get_product($conn, $id) {
  $stmt = $conn->prepare("SELECT id, name, price, stock, image_url, category, size FROM clothes WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();
  return $row ?: null;
}

function cart_add(&$cart, $p, $qty) {
  $id = (int)$p['id'];
  if (!isset($cart[$id])) {
    $cart[$id] = [
      'id'    => $id,
      'name'  => $p['name'],
      'price' => (float)$p['price'],
      'image' => $p['image_url'] ?? null,
      'size'  => $p['size'] ?? '',
      'stock' => (int)$p['stock'],
      'qty'   => 0,
    ];
  }
  // enforce stock min/max
  $max = max(0, (int)$p['stock']);
  $cart[$id]['qty'] = min($max > 0 ? $max : 999, max(1, (int)$cart[$id]['qty'] + $qty));
}

function cart_update_qty(&$cart, $id, $qty, $stockHint = null) {
  if (!isset($cart[$id])) return;
  $qty = max(0, (int)$qty);
  $max = $stockHint !== null ? max(0, (int)$stockHint) : ($cart[$id]['stock'] ?? 999);
  if ($qty === 0) unset($cart[$id]);
  else $cart[$id]['qty'] = min($max > 0 ? $max : 999, $qty);
}

function cart_remove(&$cart, $id) { unset($cart[$id]); }

function cart_totals($cart) {
  $items = 0; $subtotal = 0.0;
  foreach ($cart as $it) {
    $items += $it['qty'];
    $subtotal += $it['qty'] * (float)$it['price'];
  }
  $taxRate = 0.00; // set if you need tax
  $tax = $subtotal * $taxRate;
  $total = $subtotal + $tax;
  return [$items, $subtotal, $tax, $total];
}

/* ---------------- Actions ---------------- */
$flash = "";
$err   = "";

$action = $_POST['action'] ?? $_GET['action'] ?? "";
if ($action === "add") {
  // Accept both GET (id only) and POST (id, qty)
  $id  = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
  $qty = (int)($_POST['qty'] ?? 1);
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !cart_verify_csrf($_POST['csrf'] ?? "")) {
    $err = "Invalid request (CSRF).";
  } elseif ($id > 0 && $qty > 0) {
    if ($p = get_product($conn, $id)) {
      cart_add($_SESSION['cart'], $p, $qty);
      $flash = "Added “" . htmlspecialchars($p['name']) . "” to cart.";
    } else {
      $err = "Product not found.";
    }
  }
}
elseif ($action === "update" && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!cart_verify_csrf($_POST['csrf'] ?? "")) {
    $err = "Invalid request (CSRF).";
  } else {
    // expects qty[id] => newQty
    foreach (($_POST['qty'] ?? []) as $id => $q) {
      $id = (int)$id;
      $q  = (int)$q;
      // check current stock from DB to be safe
      if ($p = get_product($conn, $id)) {
        cart_update_qty($_SESSION['cart'], $id, $q, $p['stock']);
      } else {
        cart_remove($_SESSION['cart'], $id);
      }
    }
    $flash = "Cart updated.";
  }
}
elseif ($action === "remove") {
  $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !cart_verify_csrf($_POST['csrf'] ?? "")) {
    $err = "Invalid request (CSRF).";
  } else {
    cart_remove($_SESSION['cart'], $id);
    $flash = "Item removed.";
  }
}
elseif ($action === "clear") {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !cart_verify_csrf($_POST['csrf'] ?? "")) {
    $err = "Invalid request (CSRF).";
  } else {
    $_SESSION['cart'] = [];
    $flash = "Cart cleared.";
  }
}

// Totals
[$items, $subtotal, $tax, $total] = cart_totals($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart - Clothify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#fafafa;margin:0}
    .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
    .grid{display:grid;grid-template-columns:2fr 1fr;gap:18px}
    .card{background:#fff;border:1px solid #eaeaea;border-radius:10px;padding:12px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #f2f2f2;vertical-align:top}
    th{text-align:left;background:#fafafa}
    .img{width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #eee;background:#f6f6f6}
    .qty{width:70px;padding:8px;border:1px solid #ccc;border-radius:8px}
    .right{text-align:right}
    .btn{padding:10px 14px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer;text-decoration:none;display:inline-block}
    .btn.secondary{background:#e7e7e7;color:#111}
    .msg{margin:12px 0;padding:10px;border-radius:8px}
    .ok{background:#eaffea;border:1px solid #9ce29c}
    .err{background:#ffecec;border:1px solid #ff5a5a}
    @media(max-width:900px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>

<div class="wrap">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
    <h1 style="margin:0;">Your Cart</h1>
    <a class="btn secondary" href="shop.php">← Continue shopping</a>
  </div>

  <?php if($flash): ?><div class="msg ok"><?= $flash ?></div><?php endif; ?>
  <?php if($err): ?><div class="msg err"><?= $err ?></div><?php endif; ?>

  <div class="grid">
    <div class="card">
      <?php if(empty($_SESSION['cart'])): ?>
        <p>Your cart is empty.</p>
      <?php else: ?>
        <form method="POST" action="cart.php">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cart_csrf()) ?>">

          <table>
            <thead>
              <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th class="right">Line Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($_SESSION['cart'] as $it): ?>
              <tr>
                <td>
                  <div style="display:flex;gap:10px;align-items:center;">
                    <?php if(!empty($it['image'])): ?>
                      <img class="img" src="<?= htmlspecialchars($it['image']) ?>" alt="">
                    <?php else: ?>
                      <div class="img" style="display:flex;align-items:center;justify-content:center;color:#888;">N/A</div>
                    <?php endif; ?>
                    <div>
                      <div style="font-weight:600;"><?= htmlspecialchars($it['name']) ?></div>
                      <div style="color:#666;font-size:.9em;">
                        <?= htmlspecialchars($it['size'] ?: '-') ?>
                        <?php if(isset($it['stock'])): ?> · Stock: <?= (int)$it['stock'] ?><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td>$<?= number_format((float)$it['price'], 2) ?></td>
                <td>
                  <input class="qty" type="number" min="0" max="<?= (int)($it['stock'] ?: 999) ?>" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>">
                </td>
                <td class="right">$<?= number_format($it['qty'] * (float)$it['price'], 2) ?></td>
                <td>
                  <form method="POST" action="cart.php" onsubmit="return confirm('Remove this item?')">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(cart_csrf()) ?>">
                    <button class="btn secondary" type="submit">Remove</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>

          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
            <button class="btn" type="submit">Update Cart</button>
            <form method="POST" action="cart.php" onsubmit="return confirm('Clear the entire cart?')">
              <input type="hidden" name="action" value="clear">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(cart_csrf()) ?>">
              <button class="btn secondary" type="submit">Clear Cart</button>
            </form>
          </div>
        </form>
      <?php endif; ?>
    </div>

    <aside class="card">
      <h3 style="margin-top:0;">Summary</h3>
      <p>Items: <strong><?= (int)$items ?></strong></p>
      <p>Subtotal: <strong>$<?= number_format($subtotal, 2) ?></strong></p>
      <?php if($tax > 0): ?>
        <p>Tax: <strong>$<?= number_format($tax, 2) ?></strong></p>
      <?php endif; ?>
      <hr>
      <p style="font-size:1.2em;">Total: <strong>$<?= number_format($total, 2) ?></strong></p>

      <button class="btn" style="width:100%;margin-top:10px;" onclick="alert('Checkout coming soon!')">Proceed to Checkout</button>
    </aside>
  </div>
</div>

</body>
</html>
