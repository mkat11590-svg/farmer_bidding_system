<?php
// php/my_bid.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db_connect.php'; // Include DB Connection

// Security: Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

$userId = $_SESSION['user_id'];

// --- FETCH USER'S BIDS ---
$sql = "SELECT * FROM bids WHERE farmer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$myBids = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <title>My Bids - Farm Auction System</title>
    <?php include 'components/head_css.php'; ?>
    <link rel="stylesheet" href="../css/my_bid.css" />
    <style>
        /* Small inline style for the bid cards */
        .bid-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
        }

        .bid-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .bid-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .bid-body {
            padding: 15px;
            flex-grow: 1;
        }

        .bid-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            background: #f9f9f9;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-open {
            background: #d4edda;
            color: #155724;
        }

        .status-closed {
            background: #e2e3e5;
            color: #383d41;
        }
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>

    <div class="container" style="margin-top: 100px; margin-bottom: 50px;">
        <div class="page-header mb-4">
            <h1 class="page-title"><i class="fas fa-gavel"></i> My Bids</h1>
            <p class="page-subtitle text-muted">Manage your farm equipment auctions</p>
        </div>

        <div class="bids-grid" id="bidsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">

            <div class="plus-card" onclick="openCreateBidModal()" style="min-height: 350px; cursor: pointer; border: 2px dashed #ccc; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px; background: #f8f9fa;">
                <i class="fas fa-plus-circle plus-icon" style="font-size: 3rem; color: #28a745; margin-bottom: 15px;"></i>
                <p class="plus-text fw-bold text-secondary">Create New Listing</p>
            </div>

            <?php if ($myBids->num_rows > 0): ?>
                <?php while ($row = $myBids->fetch_assoc()): ?>
                    <div class="bid-card">
                        <?php
                        // Image handling (fallback if empty)
                        $imgSrc = !empty($row['image']) ? "../" . htmlspecialchars($row['image']) : "../images/default-tractor.jpg";
                        ?>
                        <img src="<?php echo $imgSrc; ?>" alt="Item Image" class="bid-img">

                        <div class="bid-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge badge-status <?php echo ($row['status'] == 'open') ? 'status-open' : 'status-closed'; ?>">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                                <small class="text-muted">#<?php echo $row['bids_id']; ?></small>
                            </div>

                            <h5 class="fw-bold text-truncate"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="text-success fw-bold mb-1">Rs. <?php echo number_format($row['price']); ?></p>
                            
                            <small class="text-muted"><i class="far fa-clock"></i> Ends: <?php echo date("M d, Y", strtotime($row['end_time'])); ?></small>
                        </div>

                        <div class="bid-footer d-grid gap-2">
                            <a href="farmer/manage_bids.php?id=<?php echo $row['bids_id']; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list-ul"></i> Evaluate Offers
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

        </div>
    </div>

    <div id="bidFormModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeBidFormModal()">&times;</button>
            <h2 class="modal-title" id="formModalTitle">
                <i class="fas fa-plus-circle"></i> Create New Auction
            </h2>

            <form id="bidForm" action="bids/create_bid.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="bid_id" id="bidId" value="">
                <input type="hidden" name="user_id" id="userId" value="<?php echo $_SESSION['user_id']; ?>">

                <div class="form-group mb-3">
                    <label class="form-label required fw-bold" for="itemName">Item Name</label>
                    <input type="text" class="form-control" id="itemName" name="item_name" required placeholder="e.g., John Deere 8R Tractor">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required fw-bold" for="category">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="Tractors">Tractors</option>
                        <option value="Harvesters">Harvesters</option>
                        <option value="Plows & Tillers">Plows & Tillers</option>
                        <option value="Livestock Equipment">Livestock Equipment</option>
                        <option value="Crop Protection">Crop Protection</option>
                        <option value="Storage & Handling">Storage & Handling</option>
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required fw-bold" for="startingBid">Starting Bid (Rs.)</label>
                        <input type="number" class="form-control" id="startingBid" name="starting_bid" required min="1" step="1" placeholder="10000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required fw-bold" for="minIncrement">Min Increment (Rs.)</label>
                        <input type="number" class="form-control" id="minIncrement" name="min_increment" required min="1" step="1" placeholder="100">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required fw-bold" for="duration">Auction Duration</label>
                    <select class="form-select" id="duration" name="duration" required>
                        <option value="1">1 Day</option>
                        <option value="3">3 Days</option>
                        <option value="5">5 Days</option>
                        <option value="7">7 Days</option>
                        <option value="14">14 Days</option>
                        <option value="30">30 Days</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required fw-bold" for="location">Location</label>
                    <input type="text" class="form-control" id="location" name="location" required placeholder="e.g. Lahore, Punjab">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required fw-bold" for="condition">Condition</label>
                        <select class="form-select" id="condition" name="condition" required>
                            <option value="">Select condition</option>
                            <option value="New">New</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Very Good">Very Good</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="size">Size</label>
                        <select class="form-select" id="size" name="size">
                            <option value="">Select size</option>
                            <option value="Small">Small</option>
                            <option value="Medium">Medium</option>
                            <option value="Large">Large</option>
                            <option value="Extra Large">Extra Large</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required fw-bold" for="year">Year</label>
                        <input type="number" class="form-control" id="year" name="year" required min="1900" max="2026" placeholder="2023">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="hours">Operating Hours</label>
                        <input type="number" class="form-control" id="hours" name="hours" min="0" placeholder="e.g., 1500">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="color">Color</label>
                        <input type="text" class="form-control" id="color" name="color" placeholder="e.g., Green">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required fw-bold" for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required min="1" value="1">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="form-label required fw-bold" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Detailed description..."></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required fw-bold">Item Image</label>
                    <div class="image-upload-area p-4 border border-2 border-dashed text-center rounded bg-light" id="imageUploadArea" onclick="document.getElementById('imageInput').click()" style="cursor: pointer;">
                        <input type="file" id="imageInput" name="item_image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                        <div id="uploadPlaceholder">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p>Click to upload image</p>
                            <small class="form-help">Supported: JPG, PNG, WEBP (Max 5MB)</small>
                        </div>
                        <img id="imagePreview" class="preview-image" style="display: none; max-width: 100%; height: auto;">
                    </div>
                </div>

                <!-- Visibility -->
                <div class="form-group mb-3">
                    <label class="form-label required fw-bold">Visibility</label>
                    <div class="visibility-options">
                        <div class="radio-option">
                            <input class="form-check-input radio" type="radio" name="visibility" id="visibilityPublic" value="public" checked>
                            <label class="radio-label" for="visibilityPublic">
                                <i class="fas fa-globe"></i><br>
                                <strong>Public</strong><br>
                                <small>Visible to everyone</small>
                            </label>
                        </div>
                        <div class="radio-option">
                            <input class="form-check-input radio" type="radio" name="visibility" id="visibilityPrivate" value="private">
                            <label class="radio-label" for="visibilityPrivate">
                                <i class="fas fa-lock"></i><br>
                                <strong>Private</strong><br>
                                <small>Only visible to you</small>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                    <i class="fas fa-rocket"></i> Publish Auction
                </button>
            </form>
        </div>
    </div>

    <script src="../js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../js/my_bid.js"></script>
</body>

</html>