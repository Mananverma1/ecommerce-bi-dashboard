<?php
// public/logout.php
session_start();

// Purge all dynamic in-memory variables
$_SESSION = [];

// Clean up cookie tracking configurations if they exist
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the tracking token instance completely
session_destroy();

header("Location: login.php");
exit;