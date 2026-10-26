
<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

requireRole("admin");

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

function findColumn($columns, $choices) {
    foreach ($choices as $choice) {
        if (in_array($choice, $columns, true)) {
            return $choice;
        }
    }
    return null;
}

$totalUsers = 0;
$totalStudents = 0;
$totalEvents = 0;
$latestUsers = [];
$error = "";

try {
    $totalUsers = (int)$pdo->query(
        "SELECT COUNT(*) FROM users"
    )->fetchColumn();

    $totalEvents = (int)$pdo->query(
        "SELECT COUNT(*) FROM events"
    )->fetchColumn();

    $columnInfo = $pdo->query("SHOW COLUMNS FROM users")
                      ->fetchAll(PDO::FETCH_ASSOC);

    $columns = array_column($columnInfo, "Field");

    $roleColumn = findColumn($columns, ["role", "user_role"]);

    if ($roleColumn !== null) {
        $roleSQL = "`" . $roleColumn . "`";

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE LOWER($roleSQL) = ?"
        );
        $stmt->execute(["student"]);
        $totalStudents = (int)$stmt->fetchColumn();
    }

    $idColumn = findColumn($columns, ["user_id", "id"]);
    $nameColumn = findColumn($columns, ["name", "full_name", "username"]);
    $emailColumn = findColumn($columns, ["email"]);
    $dateColumn = findColumn(
        $columns,
        ["created_at", "registration_date", "registered_at"]
    );

    if ($emailColumn !== null) {
        $select = [];

        $select[] = $nameColumn
            ? "`$nameColumn` AS display_name"
            : "'' AS display_name";

        $select[] = "`$emailColumn` AS display_email";

        $select[] = $roleColumn
            ? "`$roleColumn` AS display_role"
            : "'' AS display_role";

        $select[] = $dateColumn
            ? "`$dateColumn` AS registered_on"
            : "NULL AS registered_on";

        $orderBy = $dateColumn
            ? "`$dateColumn` DESC"
            : ($idColumn ? "`$idColumn` DESC" : "`$emailColumn` ASC");

        $sql = "SELECT " . implode(", ", $select)
             . " FROM users ORDER BY $orderBy LIMIT 5";

        $latestUsers = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $ex) {
    error_log("StudentHub dashboard error: " . $ex->getMessage());
    $error = "Unable to load dashboard statistics. Check your database table structure.";
}

$role = $_SESSION["role"] ?? "student";
$name = $_SESSION["name"] ?? "User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentHub</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fc;
            color: #26364a;
        }
        header {
            background: #93b6da;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        header h2 { margin: 0; }
        nav { display: flex; gap: 16px; flex-wrap: wrap; }
        nav a {
            color: #183652;
            text-decoration: none;
            font-weight: bold;
        }
        main { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin: 24px 0;
        }
        .card, .panel {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0000000b;
        }
        .card p { margin: 0 0 10px; color: #62748a; }
        .number { font-size: 32px; font-weight: bold; }
        .panel { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td {
            padding: 13px 10px;
            text-align: left;
            border-bottom: 1px solid #e6edf5;
        }
        th { background: #f4f7fc; }
        .message { background: #fff0f0; color: #9d2020; padding: 12px; border-radius: 6px; }
        .empty { color: #62748a; padding: 15px 0; }
        @media (max-width: 650px) {
            .cards { grid-template-columns: 1fr; }
            header { align-items: flex-start; }
        }
    </style>
</head>
<body>
<header>
    <h2>StudentHub Admin</h2>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="event_management.php">Manage Events</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= e($name) ?>. Here's the latest overview of StudentHub.</p>

    <?php if ($error !== ""): ?>
        <p class="message"><?= e($error) ?></p>
    <?php endif; ?>

    <section class="cards">
        <div class="card">
            <p>Total Users</p>
            <div class="number"><?= $totalUsers ?></div>
        </div>
        <div class="card">
            <p>Total Students</p>
            <div class="number"><?= $totalStudents ?></div>
        </div>
        <div class="card">
            <p>Total Events</p>
            <div class="number"><?= $totalEvents ?></div>
        </div>
    </section>

    <section class="panel">
        <h2>Latest Registrations</h2>

        <?php if (count($latestUsers) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($latestUsers as $user): ?>
                    <tr>
                        <td><?= e($user["display_name"] ?: "—") ?></td>
                        <td><?= e($user["display_email"]) ?></td>
                        <td><?= e($user["display_role"] ?: "—") ?></td>
                        <td>
                            <?= e($user["registered_on"] ?: "Not available") ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty">No registrations found, or the users table has no email column.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
