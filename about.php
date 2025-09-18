<?php
// Include Navbar
include("components/navbar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Clothify</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Hero Banner -->
    <header style="background:#f4f4f4; padding:50px 20px; text-align:center;">
        <h1>About Us</h1>
        <p>Discover the story behind Clothify</p>
    </header>

    <!-- About Section -->
    <section style="max-width:1200px; margin:50px auto; padding:20px; line-height:1.8;">
        <h2>Who We Are</h2>
        <p>
            At <strong>Clothify</strong>, we believe fashion should be stylish, affordable, and sustainable. 
            We started our journey with a simple idea: to bring trendy clothing to everyone without compromising on quality.
        </p>

        <h2>Our Mission</h2>
        <p>
            Our mission is to provide high-quality clothing that fits every lifestyle. 
            Whether you're looking for casual wear, office outfits, or party looks, 
            Clothify has something for you.
        </p>

        <h2>Why Choose Us?</h2>
        <ul style="margin-top:15px;">
            <li>✔️ Affordable yet stylish collections</li>
            <li>✔️ Fast and reliable shipping</li>
            <li>✔️ Easy returns and customer support</li>
        </ul>
    </section>

    <!-- Team Section -->
    <section style="max-width:1200px; margin:50px auto; text-align:center;">
        <h2>Meet Our Team</h2>
        <div style="display:flex; justify-content:center; gap:30px; margin-top:20px;">
            <div style="width:250px; border:1px solid #ccc; padding:15px;">
                <img src="assets/images/team1.jpg" alt="Founder" style="width:100%; height:250px; object-fit:cover;">
                <h3>Sarah Johnson</h3>
                <p>Founder & CEO</p>
            </div>
            <div style="width:250px; border:1px solid #ccc; padding:15px;">
                <img src="assets/images/team2.jpg" alt="Designer" style="width:100%; height:250px; object-fit:cover;">
                <h3>Mark Lee</h3>
                <p>Head of Design</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background:#222; color:#fff; text-align:center; padding:20px; margin-top:50px;">
        <p>&copy; <?php echo date("Y"); ?> Clothify. All Rights Reserved.</p>
    </footer>

</body>
</html>
