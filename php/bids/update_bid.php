<?php
// php/bids/update_bid.php

header('Content-Type: application/json');

// Include DB connection
include '../config/db_connect.php';

// Security: Ensure user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to update a bid.']);
    exit();
}

$userId = $_SESSION['user_id'];

// Get POST data
$bidId = intval($_POST['bid_id'] ?? 0);
$itemName = trim($_POST['item_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$startingBid = floatval($_POST['starting_bid'] ?? 0);
$minIncrement = floatval($_POST['min_increment'] ?? 0);
$duration = intval($_POST['duration'] ?? 3);
$location = trim($_POST['location'] ?? '');
$condition = trim($_POST['condition'] ?? '');
$size = trim($_POST['size'] ?? '');
$year = intval($_POST['year'] ?? 0);
$hours = trim($_POST['hours'] ?? '');
$color = trim($_POST['color'] ?? '');
$quantity = intval($_POST['quantity'] ?? 1);
$description = trim($_POST['description'] ?? '');
$visibility = trim($_POST['visibility'] ?? 'public');

// Validation
$errors = [];

if ($bidId <= 0) {
    $errors[] = 'Invalid bid ID.';
}

if (strlen($itemName) < 3) {
    $errors[] = 'Item name must be at least 3 characters.';
}

if (empty($category)) {
    $errors[] = 'Category is required.';
}

if ($startingBid <= 0) {
    $errors[] = 'Starting bid must be positive.';
}

if ($minIncrement <= 0) {
    $errors[] = 'Minimum increment must be positive.';
}

if (empty($location)) {
    $errors[] = 'Location is required.';
}

if (empty($condition)) {
    $errors[] = 'Condition is required.';
}

$currentYear = date('Y');
if ($year < 1900 || $year > ($currentYear + 1)) {
    $errors[] = "Year must be between 1900 and " . ($currentYear + 1) . ".";
}

if ($quantity < 1) {
    $errors[] = 'Quantity must be at least 1.';
}

if (strlen($description) < 10) {
    $errors[] = 'Description must be at least 10 characters.';
}

if (!in_array($visibility, ['public', 'private'])) {
    $errors[] = 'Invalid visibility option.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Verify ownership and get current bid data
    $stmt = $conn->prepare("SELECT farmer_id, price, bids_count, image_path FROM bids WHERE bids_id = ? FOR UPDATE");
    $stmt->bind_param("i", $bidId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Auction not found.');
    }

    $currentBid = $result->fetch_assoc();
    $stmt->close();

    // Security: Ensure the logged-in user owns this auction
    if (intval($currentBid['farmer_id']) !== $userId) {
        throw new Exception('You do not have permission to edit this auction.');
    }

    $currentPrice = floatval($currentBid['price']);
    $bidsCount = intval($currentBid['bids_count']);
    $existingImage = $currentBid['image_path'];

    // 2. Prevent editing if bids have been placed
    if ($bidsCount > 0) {
        // If bids exist, only allow limited updates (description, visibility, etc.)
        // Do NOT allow changing starting_bid, min_increment, or other core auction parameters
        throw new Exception('Cannot modify core auction details after bids have been placed.');
    }

    // 3. Handle Image Upload (if new image provided)
    $imagePath = $existingImage; // Keep existing by default

    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['item_image'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

        if ($file['size'] > $maxFileSize) {
            throw new Exception('Image size must be less than 5MB.');
        }

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Only JPG, PNG, and WEBP images are allowed.');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'bid_' . $bidId . '_' . time() . '.' . $extension;
        $uploadDir = '../../uploads/bids/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $imagePath = '/uploads/bids/' . $newFileName;

            // Delete old image if it exists and is different
            if ($existingImage && $existingImage !== $imagePath) {
                $oldImagePath = '../../' . ltrim($existingImage, '/');
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    // 4. Calculate new end_time based on duration
    $endTime = date('Y-m-d H:i:s', strtotime("+{$duration} days"));

    // 5. Update the auction
    $stmtUpdate = $conn->prepare("
        UPDATE bids SET 
            title = ?,
            category = ?,
            price = ?,
            starting_bid = ?,
            min_increment = ?,
            location = ?,
            condition_state = ?,
            size = ?,
            year = ?,
            hours_used = ?,
            color = ?,
            quantity = ?,
            description = ?,
            visibility = ?,
            image_path = ?,
            end_time = ?
        WHERE bids_id = ?
    ");

    // Convert hours to integer or NULL
    $hoursValue = !empty($hours) ? intval($hours) : 0;

    $stmtUpdate->bind_param(
        "ssdddsssisisssssi",
        $itemName,
        $category,
        $startingBid,
        $startingBid,
        $minIncrement,
        $location,
        $condition,
        $size,
        $year,
        $hoursValue,
        $color,
        $quantity,
        $description,
        $visibility,
        $imagePath,
        $endTime,
        $bidId
    );

    if (!$stmtUpdate->execute()) {
        throw new Exception('Failed to update auction: ' . $stmtUpdate->error);
    }

    $stmtUpdate->close();

    // 6. Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Auction updated successfully!',
        'bid_id' => $bidId
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>