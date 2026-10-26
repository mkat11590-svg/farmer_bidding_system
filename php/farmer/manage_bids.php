<?php
// php/farmer/manage_bids.php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../html/login.html");
    exit();
}

// --- INTEGRATION CHECK ---
// Ensure an ID is passed from the JS page
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div class='container mt-5'><h3>Error: No Listing ID provided.</h3><a href='../my_bid.php'>Return to My Bids</a></div>");
}

$listingId = intval($_GET['id']); // Sanitize input
$farmerId = $_SESSION['user_id'];
$message = "";

// --- HANDLE ACCEPT BID ACTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_offer_id'])) {
    $offerId = $_POST['accept_offer_id'];
    $listingIdPost = $_POST['listing_id'];

    // 1. Mark THIS offer as Accepted
    $stmt1 = $conn->prepare("UPDATE bid_offers SET status = 'accepted' WHERE offer_id = ?");
    $stmt1->bind_param("i", $offerId);

    // 2. Mark ALL OTHER offers for this item as Rejected
    $stmt2 = $conn->prepare("UPDATE bid_offers SET status = 'rejected' WHERE listing_id = ? AND offer_id != ?");
    $stmt2->bind_param("ii", $listingIdPost, $offerId);

    // 3. Close the Listing (Item is sold)
    $stmt3 = $conn->prepare("UPDATE bids SET status = 'closed' WHERE bids_id = ?");
    $stmt3->bind_param("i", $listingIdPost);

    if ($stmt1->execute() && $stmt2->execute() && $stmt3->execute()) {
        $message = "<div class='alert alert-success'>Bid Accepted! Listing is now closed.</div>";
        // Refresh listing status data
        $listing['status'] = 'closed';
    } else {
        $message = "<div class='alert alert-danger'>Error updating status: " . $conn->error . "</div>";
    }
}

// --- FETCH LISTING DETAILS ---
// Note: We use 'bids_id' as the column name based on your query structure
$stmt = $conn->prepare("SELECT title, price, status, farmer_id FROM bids WHERE bids_id = ?");
$stmt->bind_param("i", $listingId);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

// Security: Ensure the listing exists and belongs to the logged-in farmer
if (!$listing) {
    die("Listing not found.");
}
if ($listing['farmer_id'] != $farmerId) {
    die("Access Denied: You do not own this listing.");
}

// --- FETCH OFFERS ---
$sqlArgs = "SELECT o.*, u.full_name, u.email, u.phone 
            FROM bid_offers o 
            JOIN users u ON o.buyer_id = u.user_id 
            WHERE o.listing_id = ? 
            ORDER BY o.bid_amount DESC";
$stmtOffers = $conn->prepare($sqlArgs);
$stmtOffers->bind_param("i", $listingId);
$stmtOffers->execute();
$offers = $stmtOffers->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Evaluate Bids | Farm Bid</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .bg-white-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
    <?php include '../components/head_css.php'; ?>

</head>

<body>

    <?php include '../components/header.php'; ?>

    <div class="container" style="margin-top: 100px; margin-bottom: 50px;">

        <a href="../my_bid.php" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left"></i> Back to My Bids
        </a>

        <?php echo $message; ?>

        <div class="bg-white-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($listing['title']); ?></h2>
                    <p class="text-muted mb-0">Base Price: <strong class="text-success">Rs. <?php echo number_format($listing['price']); ?></strong></p>
                </div>
                <div>
                    <?php if ($listing['status'] == 'closed'): ?>
                        <span class="badge bg-secondary fs-6 px-3 py-2">CLOSED / SOLD</span>
                    <?php else: ?>
                        <span class="badge bg-success fs-6 px-3 py-2">OPEN FOR BIDS</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h4 class="mb-3">Received Offers</h4>

        <div class="bg-white-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Buyer</th>
                            <th>Offer Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($offers->num_rows > 0): ?>
                            <?php while ($offer = $offers->fetch_assoc()): ?>
                                <tr class="<?php echo ($offer['status'] == 'accepted') ? 'table-success' : ''; ?>">
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo htmlspecialchars($offer['full_name']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($offer['email']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($offer['phone']); ?></div>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rs. <?php echo number_format($offer['bid_amount']); ?>
                                    </td>
                                    <td><?php echo date("M d, Y", strtotime($offer['created_at'])); ?></td>
                                    <td>
                                        <?php if ($offer['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($offer['status'] == 'accepted'): ?>
                                            <span class="badge bg-success">Accepted</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($listing['status'] == 'open' && $offer['status'] == 'pending'): ?>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to accept this offer? This will close the listing.');">
                                                <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
                                                <input type="hidden" name="accept_offer_id" value="<?php echo $offer['offer_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                            </form>
                                        <?php elseif ($offer['status'] == 'accepted'): ?>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Winner</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No offers received yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>