<?php
require_once __DIR__ . "/auth.php";
require_admin();
require_once __DIR__ . "/../config/db.php";

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Count total
$total = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM contact_messages");
if ($res) { $row = $res->fetch_assoc(); $total = (int)$row['c']; }

// Fetch page
$stmt = $conn->prepare("SELECT id, name, email, subject, message, created_at 
                        FROM contact_messages 
                        ORDER BY created_at DESC 
                        LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Deletion (POST)
$deleteMsg = $deleteErr = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
  if (!verify_csrf($_POST['csrf'] ?? "")) {
    $deleteErr = "Invalid CSRF token. Refresh and try again.";
  } else {
    $delId = (int)$_POST['delete_id'];
    $del = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $del->bind_param("i", $delId);
    if ($del->execute()) {
      header("Location: messages.php?page=" . $page . "&deleted=1");
      exit;
    } else {
      $deleteErr = "Failed to delete.";
    }
    $del->close();
  }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin · Messages - Clothify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f6f7f9}
    .card{background:#fff;border:1px solid #e8e8e8;border-radius:10px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px 10px;border-bottom:1px solid #f0f0f0;vertical-align:top}
    th{text-align:left;background:#fafafa}
    .badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#eef;border:1px solid #cfe}
    .btn{padding:8px 10px;border:0;border-radius:8px;cursor:pointer}
    .btn.del{background:#e11;color:#fff}
    .msg{margin:12px 0;padding:10px;border-radius:8px}
    .msg.ok{background:#eaffea;border:1px solid #9ce29c}
    .msg.err{background:#ffecec;border:1px solid #ff5a5a}
    .pagination{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0}
    .page{padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;text-decoration:none;color:#222}
    .page.active{background:#111;color:#fff;border-color:#111}
    .empty{padding:16px;color:#666}
    .meta{color:#666;font-size:.9em}
    textarea.readonly{width:100%;min-height:80px;resize:vertical;border:1px solid #ddd;border-radius:8px;padding:8px;background:#fcfcfc}
  </style>
</head>
<body>
<?php include __DIR__ . "/sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-top">
    <h2 style="margin:0">Contact Messages</h2>
    <span class="badge"><?= (int)$total ?> total</span>
  </div>

  <div class="card" style="padding:16px;">
    <?php if(isset($_GET['deleted'])): ?>
      <div class="msg ok">Message deleted.</div>
    <?php endif; ?>
    <?php if(!empty($deleteMsg)): ?>
      <div class="msg ok"><?= htmlspecialchars($deleteMsg) ?></div>
    <?php endif; ?>
    <?php if(!empty($deleteErr)): ?>
      <div class="msg err"><?= htmlspecialchars($deleteErr) ?></div>
    <?php endif; ?>

    <?php if($result->num_rows === 0): ?>
      <div class="empty">No messages yet.</div>
    <?php else: ?>
      <div style="overflow:auto;">
        <table>
          <thead>
            <tr>
              <th style="width:60px;">ID</th>
              <th style="width:220px;">From</th>
              <th>Message</th>
              <th style="width:160px;">Date</th>
              <th style="width:100px;">Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td>#<?= (int)$row['id'] ?></td>
              <td>
                <div><strong><?= htmlspecialchars($row['name']) ?></strong></div>
                <div class="meta"><?= htmlspecialchars($row['email']) ?></div>
                <?php if($row['subject']): ?>
                  <div class="meta"><em>Subject:</em> <?= htmlspecialchars($row['subject']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <textarea class="readonly" readonly><?= htmlspecialchars($row['message']) ?></textarea>
              </td>
              <td class="meta"><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Delete this message?')">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                  <button class="btn del" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
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
    <?php endif; ?>
  </div>
</main></div>
</body>
</html>
