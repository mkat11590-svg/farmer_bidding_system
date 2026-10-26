<?php
// php/admin/users.php

require_once '../config/db_connect.php';
require_once '../auth/auth_check.php';

// --- 1. HANDLE POST REQUESTS (Block/Unblock OR Delete) ---
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CASE A: DELETE USER
    if (isset($_POST['delete_user_id'])) {
        $idToDelete = $_POST['delete_user_id'];

        // Prevent deleting yourself
        if ($idToDelete == $_SESSION['user_id']) {
            $message = "<div class='alert alert-danger'>You cannot delete your own account.</div>";
        } else {
            // SQL to delete user (Cascade will handle their listings/bids if set up in DB)
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $idToDelete);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>User #$idToDelete has been permanently deleted.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error deleting user: " . $conn->error . "</div>";
            }
        }
    }

    // CASE B: UPDATE STATUS (Block/Unblock)
    elseif (isset($_POST['user_id']) && isset($_POST['new_status'])) {
        $userIdToUpdate = $_POST['user_id'];
        $newStatus = $_POST['new_status']; // 'active' or 'blocked'

        if ($userIdToUpdate == $_SESSION['user_id']) {
            $message = "<div class='alert alert-danger'>You cannot block your own account.</div>";
        } else {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->bind_param("si", $newStatus, $userIdToUpdate);

            if ($stmt->execute()) {
                $statusLabel = ($newStatus == 'blocked') ? "Blocked" : "Activated";
                $message = "<div class='alert alert-success'>User has been successfully $statusLabel.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating user: " . $conn->error . "</div>";
            }
        }
    }
}

// --- 2. FETCH USERS ---
$users = [];
$sql = "SELECT user_id, full_name, email, role, status, created_at, phone, city, address FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin Panel</title>
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
            <a href="users.php" class="admin-nav-link active">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="bids.php" class="admin-nav-link">
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
        <h2 class="mb-4">User Management</h2>

        <?php if (!empty($message)) echo $message; ?>

        <div class="admin-table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Registered Users (<?php echo count($users); ?>)</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User Details</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?php echo ($user['status'] == 'blocked') ? 'table-danger' : ''; ?>">
                                <td>#<?php echo $user['user_id']; ?></td>

                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                    <div class="small text-muted">
                                        <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                </td>

                                <td>
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="badge bg-primary">Admin</span>
                                    <?php elseif ($user['role'] == 'farmer'): ?>
                                        <span class="badge bg-success">Farmer</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Buyer</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Blocked</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo date("M d, Y", strtotime($user['created_at'])); ?></td>

                                <td class="text-end">
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <div class="d-flex gap-2 justify-content-end">

                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewUserModal"
                                                data-name="<?php echo htmlspecialchars($user['full_name']); ?>"
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                data-role="<?php echo ucfirst($user['role']); ?>"
                                                data-phone="<?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?>"
                                                data-city="<?php echo htmlspecialchars($user['city'] ?? 'N/A'); ?>"
                                                data-address="<?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?>"
                                                data-status="<?php echo ucfirst($user['status']); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <form method="POST" onsubmit="return confirm('Change status for this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <?php if ($user['status'] == 'active'): ?>
                                                    <input type="hidden" name="new_status" value="blocked">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Block User">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="new_status" value="active">
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activate User">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                            <form method="POST" onsubmit="return confirm('WARNING: This will permanently delete the user and ALL their data. Are you sure?');">
                                                <input type="hidden" name="delete_user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i> User Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-3x text-secondary"></i>
                        </div>
                        <h4 id="modalName" class="mt-2 fw-bold"></h4>
                        <span id="modalRole" class="badge bg-secondary"></span>
                        <span id="modalStatus" class="badge"></span>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold text-muted">Email:</span>
                            <span id="modalEmail"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold text-muted">Phone:</span>
                            <span id="modalPhone"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold text-muted">City:</span>
                            <span id="modalCity"></span>
                        </li>
                        <li class="list-group-item">
                            <span class="fw-bold text-muted d-block mb-1">Address:</span>
                            <p id="modalAddress" class="mb-0 text-muted small"></p>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/bootstrap/bootstrap.bundle.min.js"></script>

    <script>
        var viewUserModal = document.getElementById('viewUserModal');
        viewUserModal.addEventListener('show.bs.modal', function(event) {
            // Button that triggered the modal
            var button = event.relatedTarget;

            // Extract info from data-* attributes
            var name = button.getAttribute('data-name');
            var email = button.getAttribute('data-email');
            var role = button.getAttribute('data-role');
            var phone = button.getAttribute('data-phone');
            var city = button.getAttribute('data-city');
            var address = button.getAttribute('data-address');
            var status = button.getAttribute('data-status');

            // Update the modal's content
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalRole').textContent = role;
            document.getElementById('modalPhone').textContent = phone;
            document.getElementById('modalCity').textContent = city;
            document.getElementById('modalAddress').textContent = address;

            // Handle Status Badge Color
            var statusBadge = document.getElementById('modalStatus');
            statusBadge.textContent = status;
            if (status === 'Active') {
                statusBadge.className = 'badge bg-success ms-1';
            } else {
                statusBadge.className = 'badge bg-danger ms-1';
            }
        });
    </script>
</body>

</html>