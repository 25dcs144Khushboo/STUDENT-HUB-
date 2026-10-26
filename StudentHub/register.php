
<?php
require_once "db.php";
require_once "auth.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm"] ?? "";

    if ($name === "" || $course === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter valid details.";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = "This email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, course, password, role)
                 VALUES (?, ?, ?, ?, 'student')"
            );

            $stmt->execute([$name, $email, $course, $hash]);

            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <main>
        <section class="register-box">
            <header>
                <h1>StudentHub</h1>
                <p>Create Your Account</p>
            </header>

            <?php if ($message !== ""): ?>
                <p class="message" role="alert">
                    <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="input-box">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                        placeholder="Enter your full name"
                        maxlength="100" autocomplete="name" required>
                </div>

                <div class="input-box">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        placeholder="Enter your email"
                        maxlength="255" autocomplete="email" required>
                </div>

                <div class="input-box">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course"
                        placeholder="Enter your course"
                        maxlength="100" required>
                </div>

                <div class="input-box">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        placeholder="Create password"
                        minlength="8" autocomplete="new-password" required>
                </div>

                <div class="input-box">
                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm"
                        placeholder="Confirm password"
                        minlength="8" autocomplete="new-password" required>
                </div>

                <button type="submit">Register</button>
            </form>

            <footer>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </footer>
        </section>
    </main>
</body>
</html>
