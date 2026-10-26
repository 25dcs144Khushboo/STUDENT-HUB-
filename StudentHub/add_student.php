
<?php
require_once "auth.php";
requireRole("admin");
require_once "mysqli_db.php";

$message = "";
$messageType = "";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $password = $_POST["password"] ?? "";
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $message = "Invalid request. Please refresh the page.";
        $messageType = "error";
    } elseif ($name === "" || $email === "" || $course === "" || $password === "") {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $messageType = "error";
    } else {
        try {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();

            if ($check->get_result()->num_rows > 0) {
                $message = "This email is already registered.";
                $messageType = "error";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = "student";

                $stmt = $conn->prepare(
                    "INSERT INTO users (name, email, course, password, role)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("sssss", $name, $email, $course, $hash, $role);
                $stmt->execute();

                header("Location: students.php?status=added");
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $message = "Failed to add student. Please check the database.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Student - StudentHub</title>
    <link rel="stylesheet" href="student_management.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Add Student</h1>
        <a class="btn btn-light" href="students.php">Back to Students</a>
    </div>

    <div class="card">
        <?php if ($message !== ""): ?>
            <p class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="add_student.php">
            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="field">
                <label for="name">Full Name</label>
                <input id="name" name="name" required
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="field">
                <label for="course">Course</label>
                <input id="course" name="course" required
                       value="<?php echo htmlspecialchars($_POST['course'] ?? ''); ?>">
            </div>

            <div class="field">
                <label for="password">Initial Password</label>
                <input id="password" type="password" name="password"
                       minlength="8" required>
            </div>

            <button type="submit">Save Student</button>
            <a class="btn btn-secondary" href="students.php">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
