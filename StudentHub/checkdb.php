
<?php
require_once __DIR__ . "/db.php";

echo "<h2>StudentHub Database Test</h2>";

echo "Connected database: ";
echo htmlspecialchars($pdo->query("SELECT DATABASE()")->fetchColumn());

echo "<br>Total users: ";
echo (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

echo "<h3>Users table columns</h3>";

$stmt = $pdo->query("SHOW COLUMNS FROM users");

foreach ($stmt->fetchAll() as $column) {
    echo htmlspecialchars($column["Field"]) . "<br>";
}
?>
