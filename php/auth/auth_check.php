<?php
// 1. Start/Resume the Session
// We need this to access the $_SESSION variables set during login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. The Condition (The "Check")
// IF user is NOT logged in (no user_id)
// OR
// IF user is NOT an admin (role is not 'admin')
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    
    // 3. The Rejection (Redirect)
    // Send them back to the login page immediately.
    header("Location: ../../html/login.html");
    exit(); // Stop the script here so the protected page below doesn't load.
}
?>