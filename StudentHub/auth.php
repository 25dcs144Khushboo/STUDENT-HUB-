
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (empty($_SESSION["user_id"])) {
        header("Location: /StudentHub/login.php");
        exit;
    }
}

function requireRole($requiredRole) {
    requireLogin();

    if (($_SESSION["role"] ?? "") !== $requiredRole) {
        http_response_code(403);
        exit("Access denied.");
    }
}
