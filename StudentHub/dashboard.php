
<?php
require_once "auth.php";
requireLogin();

$name = htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Dashboard</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <main>
        <section class="register-box">
            <header>
                <h1>StudentHub</h1>
                <p>Student Dashboard</p>
            </header>

            <h2>Welcome, <?= $name ?>!</h2>
            <p style="margin-top: 15px;">You are successfully logged in.</p>

            <form method="POST" action="logout.php" style="margin-top: 20px;">
                <button type="submit">Logout</button>
            </form>
        </section>
    </main>
</body>
</html>
