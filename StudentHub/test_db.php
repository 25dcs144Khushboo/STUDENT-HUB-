
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "Step 1: PHP is working.<br>";

require_once "auth.php";

echo "Step 2: Auth file loaded successfully.<br>";

require_once "db.php";

echo "Step 3: Database connected successfully.";
?>
