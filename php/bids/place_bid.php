<?php
// php/bids/place_bid.php

// 1. SET HEADERS & CONFIG
header('Content-Type: application/json');
require_once '../config/db_connect.php';

date_default_timezone_set('Asia/Karachi'); 

session_start();

// ============================================
// DYNAMIC INCREMENT CALCULATION FUNCTION
// ============================================

/**
 * Calculate minimum bid increment based on current price
 * @param float $currentBid - The current highest bid
 * @return float - The minimum increment required
 */
function calculateMinIncrement($currentBid) {
    if ($currentBid < 1000) {
        return 100;           // For bids under 1,000
    } else if ($currentBid < 10000) {
        return 500;           // For 1,000 - 9,999
    } else if ($currentBid < 100000) {
        return 5000;          // For 10,000 - 99,999
    } else if ($currentBid < 1000000) {
        return 50000;         // For 100,000 - 999,999
    } else if ($currentBid < 10000000) {
        return 500000;        // For 1,000,000 - 9,999,999
    } else {
        return 5000000;       // For 10,000,000+
    }
}

// ============================================
// AUTHENTICATION CHECK
// ============================================

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to place a bid.']);
    exit();
}

$userId = intval($_SESSION['user_id']); 

// ============================================
// INPUT SANITIZATION
// ============================================

$data = json_decode(file_get_contents('php://input'), true);
$bidId = intval($data['bidId'] ?? 0);
$bidAmount = floatval($data['bidAmount'] ?? 0);

if ($bidId <= 0 || $bidAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid bid data provided.']);
    exit();
}

// ============================================
// START TRANSACTION
// ============================================

$conn->begin_transaction();

try {
    // --- STEP A: Fetch Auction & LOCK the Row ---
    $stmt = $conn->prepare("
        SELECT farmer_id, price, min_increment, end_time, status, title, bids_count 
        FROM bids 
        WHERE bids_id = ? AND visibility = 'public' 
        FOR UPDATE
    ");
    $stmt->bind_param("i", $bidId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Auction not found or is no longer active.');
    }

    $auction = $result->fetch_assoc();
    $stmt->close();

    // --- STEP B: Fetch Current Highest Bidder ---
    $stmtHistory = $conn->prepare("
        SELECT buyer_id 
        FROM bid_offers 
        WHERE listing_id = ? 
        ORDER BY bid_amount DESC 
        LIMIT 1
    ");
    $stmtHistory->bind_param("i", $bidId);
    $stmtHistory->execute();
    $historyResult = $stmtHistory->get_result();
    
    $currentHighestBidderId = null;
    if ($historyResult->num_rows > 0) {
        $row = $historyResult->fetch_assoc();
        $currentHighestBidderId = intval($row['buyer_id']);
    }
    $stmtHistory->close();

    // --- STEP C: Prepare Variables ---
    $currentPrice = floatval($auction['price']);
    
    // USE DYNAMIC INCREMENT instead of database min_increment
    $dynamicIncrement = calculateMinIncrement($currentPrice);
    
    $endTime = new DateTime($auction['end_time']);
    $now = new DateTime();

    // --- STEP D: Validations ---

    // 1. Is listing closed?
    if ($auction['status'] === 'closed') {
        throw new Exception('This auction has been closed.');
    }

    // 2. Is the bidder the owner?
    if ($userId === intval($auction['farmer_id'])) {
        throw new Exception('You cannot bid on your own farm equipment.');
    }

    // 3. Has time expired?
    if ($now >= $endTime) {
        throw new Exception('This auction has already ended.');
    }
    
    // 4. Is user already winning?
    if ($currentHighestBidderId === $userId) {
        throw new Exception('You are already the highest bidder!');
    }
    
    // 5. Is bid high enough? (USING DYNAMIC INCREMENT)
    $minRequired = $currentPrice + $dynamicIncrement;
    if ($bidAmount < $minRequired) {
        throw new Exception('Bid too low. Minimum required is Rs ' . number_format($minRequired) . ' (increment: Rs ' . number_format($dynamicIncrement) . ')');
    }

    // --- STEP E: Anti-Sniping Logic (Soft Close) ---
    $thresholdSeconds = 300; // 5 Minutes
    $secondsRemaining = $endTime->getTimestamp() - $now->getTimestamp();
    
    $isTimeExtended = false;
    $newEndTimeStr = $auction['end_time'];

    // If bid is placed within last 5 minutes, extend by 3 hours
    if ($secondsRemaining <= $thresholdSeconds && $secondsRemaining > 0) {
        $endTime->modify('+3 hours');
        $newEndTimeStr = $endTime->format('Y-m-d H:i:s');
        $isTimeExtended = true;
    }

    // --- STEP F: Execute Database Updates ---

    // 1. Insert into History (bid_offers)
    $stmtOffer = $conn->prepare("INSERT INTO bid_offers (listing_id, buyer_id, bid_amount, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
    $stmtOffer->bind_param("iid", $bidId, $userId, $bidAmount);
    if (!$stmtOffer->execute()) {
        throw new Exception('Database error: Failed to record bid.');
    }
    $stmtOffer->close();

    // 2. Update Main Auction Table (bids)
    // NOTE: We still update min_increment in DB to reflect the NEXT increment
    $nextIncrement = calculateMinIncrement($bidAmount);
    
    if ($isTimeExtended) {
        $stmtUpdate = $conn->prepare("UPDATE bids SET price = ?, min_increment = ?, bids_count = bids_count + 1, end_time = ? WHERE bids_id = ?");
        $stmtUpdate->bind_param("ddsi", $bidAmount, $nextIncrement, $newEndTimeStr, $bidId);
    } else {
        $stmtUpdate = $conn->prepare("UPDATE bids SET price = ?, min_increment = ?, bids_count = bids_count + 1 WHERE bids_id = ?");
        $stmtUpdate->bind_param("ddi", $bidAmount, $nextIncrement, $bidId);
    }
    
    if (!$stmtUpdate->execute()) {
        throw new Exception('Database error: Failed to update auction price.');
    }
    $stmtUpdate->close();

    // --- STEP G: Calculate the NEW bid count ---
    $newBidsCount = intval($auction['bids_count']) + 1;

    // --- STEP H: Commit & Respond ---
    $conn->commit();

    $message = $isTimeExtended 
        ? 'Bid accepted! Auction extended by 3 hours due to last-minute bidding.' 
        : 'Bid placed successfully!';

    echo json_encode([
        'success' => true, 
        'message' => $message, 
        'newPrice' => $bidAmount,
        'newEndTime' => $newEndTimeStr,
        'bidsCount' => $newBidsCount,
        'itemId' => $bidId,
        'timeExtended' => $isTimeExtended,
        'nextMinIncrement' => $nextIncrement  // Send back the next increment for reference
    ]);

} catch (Exception $e) {
    // Rollback changes if anything went wrong
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>