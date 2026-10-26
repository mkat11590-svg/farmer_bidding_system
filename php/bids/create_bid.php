<?php

// php/bids/create_bid.php

// 1. Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the timezone to Pakistan Standard Time (UTC+5)
date_default_timezone_set('Asia/Karachi');

header('Content-Type: application/json; charset=utf-8');

// Hide all display errors (still logs them)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// Optional: buffer output to catch anything accidental
ob_start();
require_once '../config/db_connect.php';

// 1. Check Auth
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. Sanitize Inputs
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['item_name']);
    $category = $_POST['category'];
    $starting_bid = (float)$_POST['starting_bid'];
    $min_increment = (float)$_POST['min_increment'];
    $duration = (int)$_POST['duration'];
    $location = trim($_POST['location']);
    $condition = $_POST['condition'];
    $year = (int)$_POST['year'];
    $hours = isset($_POST['hours']) ? (int)$_POST['hours'] : 0;
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $size  = isset($_POST['size'])  ? trim($_POST['size'])  : '';
    $quantity = (int)$_POST['quantity'];
    $description = trim($_POST['description']);
    $visibility = $_POST['visibility'];

    // Calculate End Date
    $current_time = date('Y-m-d H:i:s');
    $end_time = date('Y-m-d H:i:s', strtotime("+$duration days"));

    // 3. Handle Image Upload
    $imagePath = ""; // Default if no image

    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
        $uploadDir = "../../assets/images/uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['item_image']['name']);
        $targetFile = $uploadDir . $fileName;

        // Validate Image Type
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileType, $allowed)) {
            if (move_uploaded_file($_FILES['item_image']['tmp_name'], $targetFile)) {
                // Store relative path in DB
                $imagePath = "assets/images/uploads/" . $fileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file format. Only JPG, PNG, WEBP allowed.']);
            exit();
        }
    }

    // 4. Insert into DB
    // Note: We use 'price' column for current bid/starting price
    $stmt = $conn->prepare("INSERT INTO bids 
    (farmer_id, title, category, price, starting_bid, min_increment, quantity, description, image_path, end_time, year, condition_state, hours_used, color, size, visibility, location, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'DB Prepare Error: ' . $conn->error]);
        exit();
    }

    // Corrected Type String:
    // i = int, s = string, d = double/float
    $stmt->bind_param(
        "issdddisssisiissss", // Changed to match variables below
        $user_id,       // i
        $title,         // s
        $category,      // s
        $starting_bid,  // d
        $starting_bid,  // d
        $min_increment, // d
        $quantity,      // i
        $description,   // s
        $imagePath,     // s
        $end_time,      // s
        $year,          // i
        $condition,     // s
        $hours,         // i 
        $color,         // s
        $size,          // s
        $visibility,    // s
        $location,       // s
        $current_time   // s
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Auction created successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt->error]);
    }
    $stmt->close();
}
