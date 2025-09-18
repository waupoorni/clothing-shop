<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../config/db.php";

$err = "";
$ok  = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // CSRF
  if (!verify_csrf($_POST['csrf'] ?? "")) {
    $err = "Invalid CSRF token. Please refresh and try again.";
  } else {
    // Collect + sanitize
    $name     = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $pass     = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm_password"] ?? "";

    // Basic validation
    if ($name === "" || $username === "" || $email === "" || $pass === "" || $confirm === "") {
      $err = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $err = "Please enter a valid email address.";
    } elseif (strlen($username) < 4) {
      $err = "Username must be at least 4 characters.";
    } elseif (strlen($pass) < 6) {
      $err = "Password must be at least 6 characters.";
    } elseif ($pass !== $confirm) {
      $err = "Passwords do not match.";
    } else {
      // Check duplicates
      $chk = $conn->prepare("SELECT id FROM admins WHERE email = ? OR username = ? LIMIT 1");
      $chk->bind_param("ss", $email, $username);
      $chk->execute();
      $rs = $chk->get_result();
      if ($rs && $rs->num_rows > 0) {
        $err = "Email or Username already exists.";
      } else {
        // Insert
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO admins (name, username, email, password_hash) VALUES (?,?,?,?)");
        $ins->bind_param("ssss", $name, $username, $email, $hash);
        if ($ins->execute()) {
          // Optional: auto-login after register
          $_SESSION['admin_logged_in'] = true;
          $_SESSION['admin_username']  = $username;
          $_SESSION['admin_name']      = $name;
          header("Location: messages.php?registered=1");
          exit;
        } else {
          $err = "Failed to create admin. Please try again.";
        }
        $ins->close();
      }
      $chk->close();
    }
  }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Admin · Clothify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f6f7f9;margin:0}
    .wrap{max-width:520px;margin:6vh auto;background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:24px}
    .h{margin:0 0 16px}
    .label{display:block;margin:10px 0 6px;font-weight:600}
    .input{width:100%;padding:12px;border:1px solid #cfcfcf;border-radius:8px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .btn{width:100%;padding:12px;border:0;border-radius:8px;background:#111;color:#fff;margin-top:14px;cursor:pointer}
    .msg{margin:12px 0;padding:10px;border-radius:8px}
    .err{background:#ffecec;border:1px solid #ff5a5a;color:#b00000}
    .info{background:#eef7ff;border:1px solid #bcdcff}
    a.link{color:#0b5fff;text-decoration:none}
  </style>
</head>
<body>
  <div class="wrap">
    <h2 class="h">Create Admin Account</h2>

    <?php if($err): ?>
      <div class="msg err"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <label class="label" for="name">Full Name</label>
      <input class="input" id="name" name="name" required>

      <div class="row">
        <div>
          <label class="label" for="username">Username</label>
          <input class="input" id="username" name="username" required>
        </div>
        <div>
          <label class="label" for="email">Email</label>
          <input class="input" id="email" name="email" type="email" required>
        </div>
      </div>

      <div class="row">
        <div>
          <label class="label" for="password">Password</label>
          <input class="input" id="password" name="password" type="password" required>
        </div>
        <div>
          <label class="label" for="confirm_password">Confirm Password</label>
          <input class="input" id="confirm_password" name="confirm_password" type="password" required>
        </div>
      </div>

      <button class="btn" type="submit">Register</button>
    </form>

    <p style="margin-top:14px;">Already have an account? <a class="link" href="login.php">Log in</a></p>
  </div>
</body>
</html>
