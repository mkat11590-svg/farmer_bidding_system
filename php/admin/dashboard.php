<?php
// php/admin/dashboard.php

require_once '../config/db_connect.php';
require_once '../auth/auth_check.php';

// --- 1. GET REAL DATA FROM DATABASE ---

// Count Users
$userCount = 0;
$userQuery = "SELECT COUNT(*) as count FROM users";
if ($result = $conn->query($userQuery)) {
    $userCount = $result->fetch_assoc()['count'];
}

// Count Bids (assuming table is named 'bids')
$bidCount = 0;
$bidQuery = "SELECT COUNT(*) as count FROM bids";
if ($conn->query($bidQuery)) { 
    if ($result = $conn->query($bidQuery)) {
        $bidCount = $result->fetch_assoc()['count'];
    }
}

// Count Bids (assuming table is named 'bids')
$bidCount = 0;
$bidQuery = "SELECT COUNT(*) as count FROM bids";
if ($conn->query($bidQuery)) { 
    if ($result = $conn->query($bidQuery)) {
        $bidCount = $result->fetch_assoc()['count'];
    }
}

// Get Recent Activity (New Users)
$recentUsers = [];
$recentQuery = "SELECT full_name, email, created_at, status FROM users ORDER BY created_at DESC LIMIT 5";
if ($result = $conn->query($recentQuery)) {
    while($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Farm Bid</title>
    
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
            <a href="dashboard.php" class="admin-nav-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="users.php" class="admin-nav-link">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="bids.php" class="admin-nav-link">
                <i class="fas fa-list"></i> Manage Bids
            </a>
            <a href="../../index.php" class="admin-nav-link mt-auto">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
            <a href="../auth/logout.php" class="admin-nav-link text-bs-white">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <main class="admin-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Overview</h2>
            <span class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?php echo date("F j, Y"); ?></span>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $userCount; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-icon-bg bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $bidCount; ?></h3>
                        <p>Active Bids</p>
                    </div>
                    <div class="stat-icon-bg bg-success bg-opacity-10 text-success">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $bidCount; ?></h3>
                        <p>Total Bids</p>
                    </div>
                    <div class="stat-icon-bg bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-gavel"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="admin-table-card">
                    <h5 class="card-title mb-4">New User Registrations</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentUsers) > 0): ?>
                                    <?php foreach($recentUsers as $u): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($u['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo date("M d, Y", strtotime($u['created_at'])); ?></td>
                                        <td>
                                            <?php if($u['status'] == 'active'): ?>
                                                <span class="badge bg-success bg-opacity-75">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Blocked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="users.php" class="btn btn-sm btn-outline-secondary">Manage</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-3">No users found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .stat-icon-bg {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</body>
</html>