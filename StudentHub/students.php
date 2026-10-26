
<?php
require_once "auth.php";
requireRole("admin");
require_once "mysqli_db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

$messages = [
    "added" => ["Student added successfully.", "success"],
    "updated" => ["Student updated successfully.", "success"],
    "deleted" => ["Student deleted successfully.", "success"],
    "delete" => ["Unable to delete student.", "error"],
    "invalid" => ["Invalid student record.", "error"]
];

$status = $_GET["status"] ?? "";
$error = $_GET["error"] ?? "";

if (isset($messages[$status])) {
    [$message, $messageType] = $messages[$status];
} elseif (isset($messages[$error])) {
    [$message, $messageType] = $messages[$error];
}

$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");
$roleFilter = trim($_GET["role"] ?? "");

$sql = "SELECT id, name, email, course, role
        FROM users
        WHERE (name LIKE ? OR email LIKE ?)";

$searchTerm = "%" . $search . "%";
$params = [$searchTerm, $searchTerm];
$types = "ss";

if ($courseFilter !== "") {
    $sql .= " AND course = ?";
    $params[] = $courseFilter;
    $types .= "s";
}

if (in_array($roleFilter, ["student", "admin"], true)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $courseResult = $conn->query(
        "SELECT DISTINCT course FROM users
         WHERE course IS NOT NULL AND course <> ''
         ORDER BY course"
    );
    $courses = $courseResult->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Unable to load student records. Check the database table.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management - StudentHub</title>
    <link rel="stylesheet" href="student_management.css">
</head>
<body>
<div class="container">

    <header class="header">
        <div>
            <h1>Student Management</h1>
            <p>Add, view, edit, delete, search and filter records.</p>
        </div>
        <a class="btn btn-light" href="admin.php">Admin Dashboard</a>
    </header>

    <main class="card">
        <?php if ($message !== ""): ?>
            <p class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <div class="actions">
            <a class="btn" href="add_student.php">+ Add Student</a>
        </div>

        <form method="GET" action="students.php" class="filters">
            <input
                type="search"
                name="search"
                placeholder="Search by name or email"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <select name="course">
                <option value="">All Courses</option>
                <?php foreach ($courses as $item): ?>
                    <option
                        value="<?php echo htmlspecialchars($item["course"]); ?>"
                        <?php echo $courseFilter === $item["course"] ? "selected" : ""; ?>
                    >
                        <?php echo htmlspecialchars($item["course"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="role">
                <option value="">All Roles</option>
                <option value="student" <?php echo $roleFilter === "student" ? "selected" : ""; ?>>Student</option>
                <option value="admin" <?php echo $roleFilter === "admin" ? "selected" : ""; ?>>Admin</option>
            </select>

            <button type="submit">Search / Filter</button>
            <a class="btn btn-secondary" href="students.php">Clear</a>
        </form>

        <p>Matching records: <?php echo count($users); ?></p>

        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo (int) $user["id"]; ?></td>
                            <td><?php echo htmlspecialchars($user["name"]); ?></td>
                            <td><?php echo htmlspecialchars($user["email"]); ?></td>
                            <td><?php echo htmlspecialchars($user["course"]); ?></td>
                            <td>
                                <span class="role">
                                    <?php echo htmlspecialchars($user["role"]); ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn" href="edit_student.php?id=<?php echo (int) $user["id"]; ?>">Edit</a>

                                <?php if ($user["role"] === "student"): ?>
                                    <form class="inline-form" method="POST"
                                          action="delete_student.php"
                                          onsubmit="return confirm('Delete this student record?');">
                                        <input type="hidden" name="id"
                                               value="<?php echo (int) $user["id"]; ?>">
                                        <input type="hidden" name="csrf_token"
                                               value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">
                                        <button class="btn-danger" type="submit">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No matching student records found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
