
<?php
require_once "auth.php";
require_once "db.php";

requireRole("admin");

try {
    $stmt = $pdo->query(
        "SELECT id, name, email, course, role, created_at
         FROM users
         ORDER BY id DESC"
    );

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin users query failed: " . $e->getMessage());
    $error = "Unable to load users. Please check your database table.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f7fc;
            color: #263445;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .header {
            background: #93b6da;
            padding: 22px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        h1 {
            margin: 0;
            font-size: 26px;
        }

        .subtitle {
            margin-bottom: 25px;
            color: #526174;
        }

        .logout {
            background: #ffffff;
            color: #263445;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .card {
            background: white;
            margin-top: 25px;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background: #e6f0ff;
        }

        .role {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            background: #e6f0ff;
            color: #234d7a;
            text-transform: capitalize;
        }

        .error {
            color: #b42318;
            background: #fee4e2;
            padding: 12px;
            border-radius: 6px;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .header {
                align-items: flex-start;
            }

            h1 {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <h1>StudentHub Admin Dashboard</h1>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <p class="subtitle">View registered users and their assigned roles.</p>

    <div class="card">
        <h2>Registered Users</h2>

        <?php if (isset($error)): ?>
            <p class="error">
                <?php echo htmlspecialchars($error); ?>
            </p>

        <?php else: ?>

            <p>Total registered users: <?php echo count($users); ?></p>

            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Role</th>
                        <th>Registration Date</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (count($users) > 0): ?>

                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)$user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['course']); ?></td>
                                <td>
                                    <span class="role">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['created_at'] ?? 'N/A'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty">
                                No registered users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>

</div>
</body>
</html>
