<?php
// php/bids/bid_detail.php

session_start();
require_once '../config/db_connect.php';

// --- SET TIMEZONE TO PAKISTAN ---
date_default_timezone_set('Asia/Karachi');

// 1. Get the ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<div class='container mt-5 alert alert-danger'>Invalid Product ID.</div>");
}

$bidId = $_GET['id'];

// 2. Fetch Bid Details
$sql = "SELECT b.*, u.full_name as farmer_name, u.city as farmer_city 
        FROM bids b 
        JOIN users u ON b.farmer_id = u.user_id 
        WHERE b.bids_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bidId);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("<div class='container mt-5 alert alert-warning'>Product not found.</div>");
}

// 3. Handle Time Calculation
// Pass the exact End Time to JavaScript for the ticking timer
$endTimeISO = date("Y-m-d\TH:i:s", strtotime($item['end_time'])); 

// 4. Handle Image Path
$imgSrc = $item['image_path'];
if (strpos($imgSrc, 'assets/') === 0) {
    $imgSrc = '../../' . $imgSrc;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> | Farm Bid</title>
    
    <link rel="stylesheet" href="../../css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">

    <style>
        .product-image-container {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-image {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .bid-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../../index.php">
                <i class="fas fa-tractor me-2"></i>Farm Bid
            </a>
            <div class="ms-auto">
                <a href="../../index.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row g-5">
            
            <div class="col-lg-6">
                <div class="product-image-container mb-3 shadow-sm">
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                         class="product-image">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="bg-white p-4 rounded-3 shadow-sm">
                    <div class="mb-3">
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($item['category']); ?></span>
                        <span class="badge bg-<?php echo ($item['condition_state'] == 'New') ? 'success' : 'warning text-dark'; ?>">
                            <?php echo htmlspecialchars($item['condition_state']); ?>
                        </span>
                    </div>

                    <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($item['title']); ?></h1>
                    
                    <div class="d-flex align-items-center text-muted mb-4 border-bottom pb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo htmlspecialchars($item['farmer_city'] ?? 'Pakistan'); ?>
                        <span class="mx-2">•</span>
                        Seller: <?php echo htmlspecialchars($item['farmer_name']); ?>
                    </div>

                    <div class="bid-card mb-4 bg-light">
                        <div class="row align-items-center">
                            <div class="col-6 border-end">
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Current Price</small>
                                <div class="text-success fw-bold fs-2">
                                    Rs. <?php echo number_format($item['price']); ?>
                                </div>
                            </div>
                            <div class="col-6 ps-4">
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Time Left</small>
                                <div id="countdown-timer" class="fw-bold fs-5 text-primary">
                                    Loading...
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <button class="btn btn-success w-100 btn-lg shadow-sm" disabled>
                            <i class="fas fa-gavel me-2"></i> Place Bid
                        </button>
                        <div class="text-center mt-2 small text-muted">
                            *Log in as a buyer to place a bid
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3">Specifications</h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th class="bg-light w-25">Quantity</th>
                                <td><?php echo $item['quantity'] . ' ' . $item['unit']; ?></td>
                            </tr>
                            <?php if(!empty($item['year'])): ?>
                            <tr>
                                <th class="bg-light">Year Model</th>
                                <td><?php echo $item['year']; ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <h5 class="fw-bold">Description</h5>
                        <p class="text-muted">
                            <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get the end time from PHP
        const endTimeString = "<?php echo $endTimeISO; ?>";
        const countDownDate = new Date(endTimeString).getTime();

        // Update the count down every 1 second
        const x = setInterval(function() {

            // Get today's date and time
            const now = new Date().getTime();

            // Find the distance between now and the count down date
            const distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="countdown-timer"
            const timerElement = document.getElementById("countdown-timer");
            
            if (distance < 0) {
                clearInterval(x);
                timerElement.innerHTML = "EXPIRED";
                timerElement.classList.remove("text-primary");
                timerElement.classList.add("text-danger");
            } else {
                timerElement.innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
            }
        }, 1000);
    </script>

</body>
</html>