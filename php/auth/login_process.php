<?php
// php/auth/login_process.php

session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Prepare Query
    // UPDATED: Added 'email' and 'status' to the SELECT list
    $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        
        // 3. CHECK STATUS (New Feature)
        // If user is blocked, deny access immediately
        if ($user['status'] === 'blocked') {
            header("Location: ../../html/login.html?error=blocked");
            exit();
        }

        // 4. Verify Password
        if (password_verify($password, $user['password_hash'])) {
            
            // 5. Set Session Variables
            $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email']; // Useful for admin checks
            $_SESSION['role']       = $user['role'];

            // 6. ROLE-BASED REDIRECTION (New Feature)
            if ($user['role'] === 'admin') {
                // Admin -> Admin Dashboard
                header("Location: ../admin/dashboard.php");
            } else {
                // Regular User -> Homepage
                header("Location: ../../index.php");
            }
            exit();

        } else {
            // Wrong Password
            header("Location: ../../html/login.html?error=wrong_pass");
            exit();
        }
    } else {
        // User Not Found
        header("Location: ../../html/login.html?error=not_found");
        exit();
    }
} else {
    // Direct access to file forbidden
    header("Location: ../../html/login.html");
    exit();
}
?>