<?php
session_start();

// 1. Clear out all active session variables in memory arrays
$_SESSION = array();

// 2. Destroy the browser's session tracking cookie entirely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Terminate the server session token instance
session_destroy();

// 4. Redirect cleanly back out to the administrative login sheet
header("Location: login.php");
exit();