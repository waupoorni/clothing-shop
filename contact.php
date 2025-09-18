<?php
// Include Navbar first
include("components/navbar.php");

// Handle form submit
$successMsg = $errorMsg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once __DIR__ . "/config/db.php";

    // Basic sanitization
    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $message === "") {
        $errorMsg = "Please fill in Name, Email, and Message.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    } else {
        // Insert using prepared statement
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            $successMsg = "Thanks! Your message has been sent.";
        } else {
            $errorMsg = "Something went wrong. Please try again.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us - Clothify</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .container{max-width:1100px;margin:40px auto;padding:0 16px}
    .grid{display:grid;grid-template-columns:1.2fr .8fr;gap:28px}
    .card{border:1px solid #ddd;border-radius:8px;padding:18px;background:#fff}
    .input{width:100%;padding:12px;border:1px solid #ccc;border-radius:6px}
    .label{font-weight:600;margin-bottom:6px;display:block}
    .btn{background:#222;color:#fff;border:0;padding:12px 18px;border-radius:6px;cursor:pointer}
    .alert{padding:12px 14px;border-radius:6px;margin-bottom:14px}
    .alert.success{background:#e6ffed;border:1px solid #00a341}
    .alert.error{background:#ffecec;border:1px solid #ff5a5a}
    @media (max-width:900px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>

<header style="background:#f4f4f4; padding:40px 16px; text-align:center;">
  <h1>Contact Us</h1>
  <p>We’d love to hear from you. Send us a message and we’ll reply soon.</p>
</header>

<section class="container">
  <div class="grid">
    <!-- Contact Form -->
    <div class="card">
      <h2 style="margin-top:0">Send a Message</h2>

      <?php if($successMsg): ?>
        <div class="alert success"><?= htmlspecialchars($successMsg) ?></div>
      <?php endif; ?>
      <?php if($errorMsg): ?>
        <div class="alert error"><?= htmlspecialchars($errorMsg) ?></div>
      <?php endif; ?>

      <form method="POST" action="contact.php" novalidate>
        <div style="margin-bottom:12px;">
          <label class="label" for="name">Name *</label>
          <input class="input" type="text" id="name" name="name" placeholder="Your name" required>
        </div>

        <div style="margin-bottom:12px;">
          <label class="label" for="email">Email *</label>
          <input class="input" type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>

        <div style="margin-bottom:12px;">
          <label class="label" for="subject">Subject</label>
          <input class="input" type="text" id="subject" name="subject" placeholder="How can we help?">
        </div>

        <div style="margin-bottom:16px;">
          <label class="label" for="message">Message *</label>
          <textarea class="input" id="message" name="message" rows="5" placeholder="Write your message..." required></textarea>
        </div>

        <button class="btn" type="submit">Send Message</button>
      </form>
    </div>

    <!-- Contact Info -->
    <aside class="card">
      <h3 style="margin-top:0;">Our Store</h3>
      <p>
        Clothify<br>
        123 Fashion Street,<br>
        Colombo, Sri Lanka
      </p>
      <p><strong>Email:</strong> support@clothify.local</p>
      <p><strong>Phone:</strong> +94 77 123 4567</p>

      <hr style="margin:18px 0;">
      <h3 style="margin-top:0;">Business Hours</h3>
      <p>Mon–Fri: 9:00 AM – 6:00 PM<br>Sat: 10:00 AM – 4:00 PM</p>
    </aside>
  </div>
</section>

<footer style="background:#222; color:#fff; text-align:center; padding:20px; margin-top:50px;">
  <p>&copy; <?php echo date("Y"); ?> Clothify. All Rights Reserved.</p>
</footer>

</body>
</html>
