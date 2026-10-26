<?php
// php/admin/bids.php

require_once '../config/db_connect.php';
require_once '../auth/auth_check.php';

$message = "";

// --- 1. HANDLE POST ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION A: PERMANENTLY DELETE
    if (isset($_POST['delete_bid_id'])) {
        $idToDelete = $_POST['delete_bid_id'];
        $stmt = $conn->prepare("DELETE FROM bids WHERE bids_id = ?");
        $stmt->bind_param("i", $idToDelete);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Bid #$idToDelete has been permanently removed.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error removing bid: " . $conn->error . "</div>";
        }
    }
    
    // ACTION B: CANCEL/CLOSE LISTING
    elseif (isset($_POST['close_bid_id'])) {
        $idToClose = $_POST['close_bid_id'];
        $stmt = $conn->prepare("UPDATE bids SET status = 'closed' WHERE bids_id = ?");
        $stmt->bind_param("i", $idToClose);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-warning'>Listing #$idToClose has been closed by Admin.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error closing bid: " . $conn->error . "</div>";
        }
    }

    // ACTION C: REOPEN LISTING (New Feature)
    elseif (isset($_POST['reopen_bid_id'])) {
        $idToReopen = $_POST['reopen_bid_id'];
        $stmt = $conn->prepare("UPDATE bids SET status = 'open' WHERE bids_id = ?");
        $stmt->bind_param("i", $idToReopen);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Listing #$idToReopen has been successfully reopened.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error reopening bid: " . $conn->error . "</div>";
        }
    }
}

// --- 2. FETCH BIDS WITH STATUS & TRANSACTION INFO ---
$bids = [];
$sql = "SELECT l.bids_id, l.title, l.price, l.created_at, l.status as listing_status, 
               u.full_name as seller_name, u.email as seller_email,
               (SELECT MAX(bid_amount) FROM bid_offers WHERE listing_id = l.bids_id) as highest_offer,
               (SELECT status FROM bid_offers WHERE listing_id = l.bids_id AND status = 'accepted' LIMIT 1) as transaction_status
        FROM bids l 
        LEFT JOIN users u ON l.farmer_id = u.user_id 
        ORDER BY l.created_at DESC";

$result = $conn->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $bids[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bids | Admin Panel</title>
    <link rel="stylesheet" href="../../css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body class="admin-body">

    <div class="admin-sidebar">
        <div class="admin-brand">
            <i class="fas fa-tractor text-warning"></i> 
            <span class="ms-2">Admin Panel</span>
        </div>
        
        <div class="admin-profile mb-4 pb-3 border-bottom border-secondary">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6">System Admin</div>
                    <small class="text-white-50" style="font-size: 0.8rem;">root@farm.com</small>
                </div>
            </div>
        </div>

        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="users.php" class="admin-nav-link">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="bids.php" class="admin-nav-link active">
                <i class="fas fa-list"></i> Manage Bids
            </a>
            <a href="../../index.php" class="admin-nav-link mt-auto">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
            <a href="../auth/logout.php" class="admin-nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <main class="admin-main">
        <h2 class="mb-4">Manage Bids & Transactions</h2>

        <?php if (!empty($message)) echo $message; ?>

        <div class="admin-table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">All Listings (<?php echo count($bids); ?>)</h5>
            </div>

            <?php if (count($bids) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Item Details</th>
                            <th>Seller</th>
                            <th>Listing Status</th>
                            <th>Transaction Status</th>
                            <th class="text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bids as $item): ?>
                        <tr>
                            <td>#<?php echo $item['bids_id']; ?></td>
                            
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></div>
                                <a href="../../php/bids/bid_detail.php?id=<?php echo $item['bids_id']; ?>" 
                                   target="_blank" class="small text-primary text-decoration-none">
                                   <i class="fas fa-external-link-alt"></i> View Item
                                </a>
                            </td>

                            <td>
                                <div><?php echo htmlspecialchars($item['seller_name'] ?? 'Unknown'); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($item['seller_email'] ?? '-'); ?></small>
                            </td>

                            <td>
                                <?php if($item['listing_status'] == 'open'): ?>
                                    <span class="badge bg-success">Open</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Closed</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if($item['transaction_status'] == 'accepted'): ?>
                                    <span class="badge bg-success mb-1">Accepted</span>
                                    <div class="small text-success fw-bold">
                                        Sold for Rs. <?php echo number_format($item['highest_offer']); ?>
                                    </div>
                                <?php elseif($item['highest_offer'] > 0): ?>
                                    <span class="badge bg-warning text-dark mb-1">Pending</span>
                                    <div class="small text-muted">
                                        High Bid: Rs. <?php echo number_format($item['highest_offer']); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">No Bids Yet</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    
                                    <?php if($item['listing_status'] == 'open'): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to CLOSE this listing? Buyers will no longer be able to bid.');">
                                            <input type="hidden" name="close_bid_id" value="<?php echo $item['bids_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Cancel/Close Listing">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to REOPEN this listing? Buyers will be able to bid again.');">
                                            <input type="hidden" name="reopen_bid_id" value="<?php echo $item['bids_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Reopen Listing">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" onsubmit="return confirm('WARNING: Are you sure you want to PERMANENTLY delete this bid?');">
                                        <input type="hidden" name="delete_bid_id" value="<?php echo $item['bids_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Permanently">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p>No bids found in the database.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>