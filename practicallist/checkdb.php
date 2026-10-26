<?php
echo "CHECKDB FILE UPDATED";
echo "<br>File path: " . __FILE__;

require_once __DIR__ . "/db.php";

echo "<br>Connected database: ";
var_dump($pdo->query("SELECT DATABASE()")->fetchColumn());
?>