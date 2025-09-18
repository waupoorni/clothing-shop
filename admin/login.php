<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../config/db.php";

// Fallback hardcoded admin (keep for safety during development)
const ADMIN_USER = "admin";
const ADMIN_PASS = "admin123";

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $u = trim($_POST["username"] ?? "");
  $p = trim($_POST["password"] ?? "");

  // 1) Try DB user
  $stmt = $conn->prepare("SELECT id, username, name, password_hash FROM admins WHERE username = ? OR email = ? LIMIT 1");
  $stmt->bind_param("ss", $u, $u);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    if (password_verify($p, $row['password_hash'])) {
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_username']  = $row['username'];
      $_SESSION['admin_name']      = $row['name'];
      header("Location: messages.php");
      exit;
    } else {
      $error = "Invalid credentials.";
    }
  } else {
    // 2) Fallback to hardcoded
    if ($u === ADMIN_USER && $p === ADMIN_PASS) {
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_username']  = ADMIN_USER;
      $_SESSION['admin_name']      = "Administrator";
      header("Location: messages.php");
      exit;
    } else {
      $error = "Invalid credentials.";
    }
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Clothify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f6f7f9;margin:0}
    .wrap{max-width:420px;margin:8vh auto;background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:24px}
    .h{margin:0 0 16px}
    .label{display:block;margin:10px 0 6px;font-weight:600}
    .input{width:100%;padding:12px;border:1px solid #cfcfcf;border-radius:8px}
    .btn{width:100%;padding:12px;border:0;border-radius:8px;background:#111;color:#fff;margin-top:14px;cursor:pointer}
    .err{background:#ffecec;border:1px solid #ff5a5a;color:#b00000;padding:10px;border-radius:8px;margin:10px 0}
  </style>
</head>
<body>
  <div class="wrap">
    <h2 class="h">Clothify Admin</h2>
    <?php if($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" action="login.php" autocomplete="off">
      <label class="label" for="username">Username</label>
      <input class="input" id="username" name="username" required>
      <label class="label" for="password">Password</label>
      <input class="input" id="password" name="password" type="password" required>
      <button class="btn" type="submit">Login</button>
    </form>
  </div>
</body>
</html>
