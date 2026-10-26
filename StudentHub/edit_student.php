
<?php
require_once "auth.php";
requireRole("admin");
require_once "mysqli_db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
}

if (!$id || $id < 1) {
    header("Location: students.php?error=invalid");
    exit;
}

$stmt = $conn->prepare("SELECT id, name, email, course FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: students.php?error=invalid");
    exit;
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $message = "Invalid request. Please refresh the page.";
        $messageType = "error";
    } elseif ($name === "" || $email === "" || $course === "") {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        try {
            $check = $conn->prepare(
                "SELECT id FROM users WHERE email = ? AND id <> ?"
            );
            $check->bind_param("si", $email, $id);
            $check->execute();

            if ($check->get_result()->num_rows > 0) {
                $message = "Another user already uses this email.";
                $messageType = "error";
            } else {
                $update = $conn->prepare(
                    "UPDATE users SET name = ?, email = ?, course = ? WHERE id = ?"
                );
                $update->bind_param("sssi", $name, $email, $course, $id);
                $update->execute();

                header("Location: students.php?status=updated");
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $message = "Failed to update student.";
            $messageType = "error";
        }
    }

    $user["name"] = $name;
    $user["email"] = $email;
    $user["course"] = $course;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Student - StudentHub</title>
    <link rel="stylesheet" href="student_management.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Edit Student</h1>
        <a class="btn btn-light" href="students.php">Back to Students</a>
    </div>

    <div class="card">
        <?php if ($message !== ""): ?>
            <p class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="edit_student.php">
            <input type="hidden" name="id" value="<?php echo (int) $user["id"]; ?>">
            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="field">
                <label for="name">Full Name</label>
                <input id="name" name="name" required
                       value="<?php echo htmlspecialchars($user["name"]); ?>">
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required
                       value="<?php echo htmlspecialchars($user["email"]); ?>">
            </div>

            <div class="field">
                <label for="course">Course</label>
                <input id="course" name="course" required
                       value="<?php echo htmlspecialchars($user["course"]); ?>">
            </div>

            <button type="submit">Update Student</button>
            <a class="btn btn-secondary" href="students.php">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
