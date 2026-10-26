<?php
// php/components/header.php

// 1. LOGIC: Session & Path Management
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check Login & Role
$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'Guest';
$userName = $_SESSION['user_name'] ?? 'Guest User';
$userEmail = $_SESSION['user_email'] ?? '';

// Calculate Initials
$userInitials = "G";
if ($isLoggedIn && isset($_SESSION['user_name'])) {
  $words = explode(" ", $_SESSION['user_name']);
  $userInitials = "";
  $count = 0;
  foreach ($words as $w) {
    if (!empty($w) && $count < 2) {
      $userInitials .= strtoupper($w[0]);
      $count++;
    }
  }
}

// Define Base Path
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$basePath = ($currentDir == 'php') ? '../' : './';
?>

<header class="header-main">
  <div class="container">
    <div class="row align-items-center">

      <div class="col-lg-9 col-md-4 col-6 navbar-brand">
        <a href="<?php echo $basePath; ?>index.php" class="d-flex align-items-center text-decoration-none" id="headerLogoLink">
          <img src="<?php echo $basePath; ?>assets/images/logo.webp" onerror="this.style.display='none'" class="header-brand-logo me-2" alt="logo" id="headerLogoImg" />
          <span class="fw-bold fs-5" style="color: var(--primary-green);">Farm Bidding System</span>
        </a>
      </div>

      <div class="col-lg-3 col-md-3 col-6 d-flex justify-content-end">

        <?php if (!$isLoggedIn): ?>
          <a href="<?php echo $basePath; ?>html/login.html" id="loginBtn" class="btn btn-outline-success">
            <i class="fas fa-sign-in-alt me-1"></i> Login
          </a>&nbsp;
          <a href="<?php echo $basePath; ?>html/signup.html" id="loginBtn" class="btn btn-outline-success">
            <i class="fas fa-sign-in-alt me-1"></i> SignUp
          </a>

        <?php else: ?>
          <div class="dropdown" id="userDropdown">
            <button class="btn p-0 border-0" data-bs-toggle="dropdown">
              <img src="https://ui-avatars.com/api/?name=<?php echo $userInitials; ?>&background=52b788&color=fff" id="userAvatar" class="header-user-avatar" alt="User" />
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 250px">
              <li class="px-3 py-2 border-bottom">
                <div class="fw-bold" id="userName"><?php echo htmlspecialchars($userName); ?></div>
                <small class="text-muted" id="userEmail"><?php echo htmlspecialchars($userEmail); ?></small>
                <div class="mt-2">
                  <span class="header-user-badge" id="userRole"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
                </div>
              </li>
              <li><a class="dropdown-item" href="<?php echo $basePath; ?>php/my_bid.php">My Bids</a></li>
              <li><a class="dropdown-item" href="#">Settings</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="<?php echo $basePath; ?>php/auth/logout.php" id="logoutLink">Logout</a></li>
            </ul>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</header>

<nav class="navbar navbar-expand-lg header-main-nav">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto">

        <li class="nav-item header-nav-item">
          <a class="nav-link" href="<?php echo $basePath; ?>index.php" id="navHome">
            <i class="fas fa-home me-1"></i> Home
          </a>
        </li>

        <!-- <li class="nav-item header-nav-item dropdown position-static">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-th me-1"></i> Categories
          </a>
          <div class="dropdown-menu header-mega-menu">
            <div class="row">
              <div class="col-md-3">
                <h6><i class="fas fa-clock me-2"></i>Auctions</h6>
                <a class="dropdown-item" href="#"><i class="fas fa-fire text-danger me-2"></i>Closing Soon</a>
                <a class="dropdown-item" href="#"><i class="fas fa-calendar-day me-2"></i>Today's Events</a>
                <a class="dropdown-item" href="#"><i class="fas fa-plus-circle me-2"></i>New Auctions</a>
              </div>
              <div class="col-md-3">
                <h6><i class="fas fa-star me-2"></i>Popular</h6>
                <a class="dropdown-item" href="#"><i class="fas fa-fire me-2"></i>Hot Auctions</a>
                <a class="dropdown-item" href="#"><i class="fas fa-crown me-2"></i>Featured Lots</a>
                <a class="dropdown-item" href="#"><i class="fas fa-trophy me-2"></i>Most Bids</a>
              </div>
              <div class="col-md-3">
                <h6><i class="fas fa-tractor me-2"></i>Equipment</h6>
                <a class="dropdown-item" href="#">Tractors</a>
                <a class="dropdown-item" href="#">Harvesters</a>
                <a class="dropdown-item" href="#">Plows & Tillers</a>
              </div>
              <div class="col-md-3">
                <h6><i class="fas fa-seedling me-2"></i>More</h6>
                <a class="dropdown-item" href="#">Livestock Equipment</a>
                <a class="dropdown-item" href="#">Crop Protection</a>
                <a class="dropdown-item" href="#">Storage & Handling</a>
              </div>
            </div>
          </div>
        </li> -->

        <li class="nav-item header-nav-item">
          <a class="nav-link" href="<?php echo $basePath; ?>php/my_bid.php" id="navMyBids">
            <i class="fas fa-hand-holding-usd me-1"></i> My Bids
          </a>
        </li>

        <!-- <li class="nav-item header-nav-item">
          <a class="nav-link" href="<?php echo $basePath; ?>php/watchlist.php" id="navWatchlist">
            <i class="fas fa-eye me-1"></i> Watchlist
          </a>
        </li> -->

        <li class="nav-item header-nav-item">
          <a class="nav-link" href="<?php echo $basePath; ?>php/contact_us.php" id="navContact">
            <i class="fas fa-envelope me-1"></i> Contact Us
          </a>
        </li>

        <li class="nav-item header-nav-item">
          <a class="nav-link" href="<?php echo $basePath; ?>php/about_us.php" id="navAbout">
            <i class="fas fa-info-circle me-1"></i> About Us
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>