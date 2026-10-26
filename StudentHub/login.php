
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM users WHERE email = ? LIMIT 1"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $storedPassword = $user["password_hash"]
                ?? $user["password"]
                ?? "";

            $userId = $user["user_id"] ?? $user["id"] ?? null;

            if (
                $user &&
                $userId !== null &&
                $storedPassword !== "" &&
                password_verify($password, $storedPassword)
            ) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $userId;
                $_SESSION["role"] = $user["role"] ?? "student";

                if ($_SESSION["role"] === "admin") {
                    header("Location: /StudentHub/events_api.html");
                } else {
                    header("Location: /StudentHub/index.php");
                }
                exit;
            }

            $error = "Invalid email or password.";
        } catch (Throwable $e) {
            error_log("StudentHub login error: " . $e->getMessage());
            $error = "Login failed. Please check your database configuration.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fc;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            width: 320px;
            max-width: calc(100% - 48px);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 18px #00000012;
        }
        h2 {
            text-align: center;
            color: #24466b;
        }
        label {
            display: block;
            margin-top: 14px;
        }
        input {
            box-sizing: border-box;
            width: 100%;
            padding: 11px;
            margin-top: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: 0;
            background: #93b6da;
            cursor: pointer;
            border-radius: 6px;
        }
        .error {
            color: #b00020;
            text-align: center;
        }
    </style>
</head>
<body>
    <form class="login-box" method="post" action="login.php">
        <h2>StudentHub Login</h2>

        <?php if ($error !== ""): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            required
            value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <button type="submit">Log In</button>
    </form>
</body>
</html>
