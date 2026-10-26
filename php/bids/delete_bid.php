<?php
// php/bids/delete_bid.php

header('Content-Type: application/json');

// Include DB connection
include '../config/db_connect.php';

// Security: Ensure user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a bid.']);
    exit();
}

$userId = $_SESSION['user_id'];

// Get POST data (from JS fetch with JSON body)
$data = json_decode(file_get_contents('php://input'), true);
$bidId = intval($data['id'] ?? 0);

if ($bidId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid bid ID.']);
    exit();
}

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Verify ownership and get bid data
    $stmt = $conn->prepare("SELECT farmer_id, bids_count, image_path FROM bids WHERE bids_id = ? FOR UPDATE");
    $stmt->bind_param("i", $bidId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Auction not found.');
    }

    $bid = $result->fetch_assoc();
    $stmt->close();

    // Security: Ensure the logged-in user owns this auction
    if (intval($bid['farmer_id']) !== $userId) {
        throw new Exception('You do not have permission to delete this auction.');
    }

    $bidsCount = intval($bid['bids_count']);
    $imagePath = $bid['image_path'];

    // 2. Check if bids have been placed
    if ($bidsCount > 0) {
        throw new Exception('Cannot delete auction with existing bids. Please contact support if you need to remove this listing.');
    }

    // 3. Delete from bid_history table (if any records exist - cleanup)
    $stmtDeleteHistory = $conn->prepare("DELETE FROM bid_history WHERE bids_id = ?");
    $stmtDeleteHistory->bind_param("i", $bidId);
    $stmtDeleteHistory->execute();
    $stmtDeleteHistory->close();

    // 4. Delete the auction
    $stmtDelete = $conn->prepare("DELETE FROM bids WHERE bids_id = ?");
    $stmtDelete->bind_param("i", $bidId);
    
    if (!$stmtDelete->execute()) {
        throw new Exception('Failed to delete auction from database.');
    }
    
    $stmtDelete->close();

    // 5. Commit Transaction
    $conn->commit();

    // 6. Delete image file (after successful database deletion)
    if (!empty($imagePath)) {
        $imageFilePath = '../../' . ltrim($imagePath, '/');
        if (file_exists($imageFilePath)) {
            unlink($imageFilePath);
        }
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Auction deleted successfully!'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>