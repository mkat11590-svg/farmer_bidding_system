<?php
// php/api/get_my_bids.php

// 1. Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Set Headers
header('Content-Type: application/json');

// 3. Connect to Database
require_once '../config/db_connect.php';

// 4. Security Check: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    // If accessed via AJAX, sending a 401 is better than a redirect
    http_response_code(401); 
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = [];

try {
    // 5. Query the 'bids' table
    $sql = "SELECT * FROM bids WHERE farmer_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $bids = [];
    while ($row = $result->fetch_assoc()) {
        // 6. Map Database Columns to JavaScript Object keys
        $bids[] = [
            'id'            => $row['bids_id'],          // Matches PK
            'item_name'     => $row['title'],            // Matches title
            'category'      => $row['category'],
            'current_bid'   => (float)$row['price'],
            'starting_bid'  => (float)($row['starting_bid'] ?? $row['price']),
            'image'         => !empty($row['image_path']) ? '../' . $row['image_path'] : null,
            'end_date'      => $row['end_time'],
            'total_bids'    => (int)($row['bids_count'] ?? 0),
            'location'      => $row['location'],
            'visibility'    => $row['visibility'] ?? 'public',
            'description'   => $row['description'],
            'min_increment' => (float)$row['min_increment'],
            'year'          => $row['year'],
            'condition'     => $row['condition_state'],  // Matches condition_state
            
            // Added these fields from your Schema for completeness:
            'quantity'      => (int)$row['quantity'],
            'unit'          => $row['unit'],
            'hours'         => (int)$row['hours_used'],  // Matches hours_used
            'color'         => $row['color'],
            'size'          => $row['size']
        ];
    }

    echo json_encode($bids);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>