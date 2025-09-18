<?php
// Update credentials if you changed MySQL username/password/database
$host = "localhost";
$user = "root";
$pass = "";                // XAMPP default on Mac is empty password
$db   = "clothing_shop";   // make sure this DB exists (see SQL below)

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
?>
