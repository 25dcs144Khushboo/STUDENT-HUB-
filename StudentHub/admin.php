
<?php
require_once "auth.php";
requireRole("admin");
require_once "mysqli_db.php";

try {
    $result = $conn->query(
        "SELECT
            COUNT(*) AS total_users,
            COALESCE(SUM(role = 'student'), 0) AS total_students,
            COALESCE(SUM(role = 'admin'), 0) AS total_admins
         FROM users"
    );

    $stats = $result->fetch_assoc();
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Unable to load dashboard statistics.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - StudentHub</title>
    <link rel="stylesheet" href="student_management.css">

    <style>
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
        }

        .stat-card {
            background: #e6f0ff;
            padding: 22px;
            border-radius: 10px;
        }

        .stat-card h2 {
            margin-bottom: 8px;
            font-size: 17px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: bold;
            color: #234d7a;
        }
    </style>
</head>
<body>
<div class="container">

    <header class="header">
        <div>
            <h1>StudentHub Admin Dashboard</h1>
            <p>Welcome to the administration panel.</p>
        </div>
        <a class="btn btn-light" href="logout.php">Logout</a>
    </header>

    <section class="card">
        <h2>Dashboard Overview</h2>

        <div class="stats">
            <div class="stat-card">
                <h2>Total Users</h2>
                <div class="stat-number">
                    <?php echo (int) $stats["total_users"]; ?>
                </div>
            </div>

            <div class="stat-card">
                <h2>Students</h2>
                <div class="stat-number">
                    <?php echo (int) $stats["total_students"]; ?>
                </div>
            </div>

            <div class="stat-card">
                <h2>Administrators</h2>
                <div class="stat-number">
                    <?php echo (int) $stats["total_admins"]; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <h2>Student Management</h2>
        <p>Manage registered students and their information.</p>

        <div class="actions">
            <a class="btn" href="students.php">View, Search & Filter Students</a>
            <a class="btn" href="add_student.php">+ Add Student</a>
        </div>
    </section>

</div>
</body>
</html>
