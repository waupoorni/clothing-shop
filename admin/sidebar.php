<?php
require_once __DIR__ . "/auth.php";
require_admin();
if (session_status() === PHP_SESSION_NONE) session_start();
$who = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');

function active($slug){
  $p = basename($_SERVER['PHP_SELF']);
  return $p === $slug ? 'class="active"' : '';
}
?>
<style>
  .admin-shell{display:grid;grid-template-columns:240px 1fr;min-height:100vh;background:#f6f7f9}
  .admin-side{background:#111;color:#fff;padding:16px 12px;position:sticky;top:0;height:100vh}
  .brand{font-weight:700;margin-bottom:18px}
  .me{font-size:.92em;color:#c9c9c9;margin-bottom:10px}
  .nav a{display:block;padding:10px 12px;margin:6px 0;border-radius:8px;color:#eee;text-decoration:none;border:1px solid transparent}
  .nav a:hover{background:#1a1a1a}
  .nav a.active, .nav a[aria-current="page"]{background:#fff;color:#111;border-color:#e5e5e5}
  .admin-main{padding:18px}
  .admin-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
  .btn-logout{background:#333;color:#fff;border:0;border-radius:8px;padding:8px 12px;text-decoration:none}
  @media (max-width:900px){
    .admin-shell{grid-template-columns:1fr}
    .admin-side{position:relative;height:auto;border-bottom:1px solid #222}
  }
</style>

<div class="admin-shell">
  <aside class="admin-side">
    <div class="brand">Clothify · Admin</div>
    <div class="me">Hello, <?=$who?></div>
    <nav class="nav">
      <a <?=active("messages.php")?> href="messages.php">📨 View Messages</a>
      <a <?=active("add_cloth.php")?> href="add_cloth.php">➕ Add Clothes</a>
      <a <?=active("clothes.php")?> href="clothes.php">👗 View / Delete Clothes</a>
    </nav>
    <div style="margin-top:18px;">
      <a class="btn-logout" href="logout.php">Logout</a>
    </div>
  </aside>
  <!-- NOTE: the page content should open a <main class="admin-main"> ... -->
