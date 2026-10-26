
<?php
require_once "auth.php";
requireRole("admin");
require_once "mysqli_db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: students.php");
    exit;
}

$token = $_POST["csrf_token"] ?? "";
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    header("Location: students.php?error=delete");
    exit;
}

if (!$id || $id < 1) {
    header("Location: students.php?error=invalid");
    exit;
}

try {
    $stmt = $conn->prepare(
        "DELETE FROM users WHERE id = ? AND role = 'student'"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        header("Location: students.php?status=deleted");
    } else {
        header("Location: students.php?error=delete");
    }
    exit;
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    header("Location: students.php?error=delete");
    exit;
}
