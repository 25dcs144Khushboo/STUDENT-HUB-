
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set("session.use_strict_mode", "1");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

$sessionTimeout = 1200;

function requireLogin(): void
{
    if (empty($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }

    global $sessionTimeout;

    if (
        isset($_SESSION["last_activity"]) &&
        time() - $_SESSION["last_activity"] > $sessionTimeout
    ) {
        $_SESSION = [];
        session_destroy();

        header("Location: login.php?timeout=1");
        exit;
    }

    $_SESSION["last_activity"] = time();
}

function requireRole(string $requiredRole): void
{
    requireLogin();

    if (
        !isset($_SESSION["role"]) ||
        !is_string($_SESSION["role"]) ||
        $_SESSION["role"] !== $requiredRole
    ) {
        http_response_code(403);
        exit("Access denied. You do not have permission to view this page.");
    }
}
