<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=healthcare-db', 'root', '');

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Get total users count
$totalUsers = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();

// Get paginated users with roles
$users = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT a.id) AS appointment_count,
           COUNT(DISTINCT o.id) AS order_count
    FROM user u
    LEFT JOIN appointments a ON u.id = a.patient_id
    LEFT JOIN lab_orders o ON u.id = o.patient_id
    GROUP BY u.id
    ORDER BY u.role, u.last_name
    LIMIT $perPage OFFSET $offset
")->fetchAll();

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $stmt = $pdo->prepare("
            INSERT INTO user 
            (first_name, last_name, email, phone, role, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['role']
        ]);
    } 
    elseif (isset($_POST['update_user'])) {
        $stmt = $pdo->prepare("
            UPDATE user SET 
            first_name = ?,
            last_name = ?,
            email = ?,
            phone = ?,
            role = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['role'],
            $_POST['user_id']
        ]);
    }
    header("Location: user-management.php?page=$page");
    exit;
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: user-management.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management | MediCare+ Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .role-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        .badge-admin { background-color: #e74a3b; }
        .badge-lab { background-color: #4e73df; }
        .badge-phleb { background-color: #36b9cc; }
        .badge-staff { background-color: #1cc88a; }
        .badge-patient { background-color: #6f42c1; }
        .user-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .table-responsive {
            min-height: 400px;
        }
        .stats-badge {
            font-size: 0.65rem;
            border-radius: 10rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-gray-800 mb-1">
                    <i class="fas fa-users-cog text-primary me-2"></i>User Management
                </h1>
                <p class="mb-0 text-muted">Showing <?= count($users) ?> of <?= $totalUsers ?> users</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                <i class="fas fa-plus me-1"></i> Add User
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= $user['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></strong>
                                    <div class="text-muted small">Joined: <?= date('M j, Y', strtotime($user['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($user['email']) ?></div>
                                    <div class="text-muted small"><?= $user['phone'] ? htmlspecialchars($user['phone']) : 'No phone' ?></div>
                                </td>
                                <td>
                                    <?php 
                                    $roleClass = match($user['role']) {
                                        'admin' => 'badge-admin',
                                        'lab' => 'badge-lab',
                                        'phlebotomist' => 'badge-phleb',
                                        'staff' => 'badge-staff',
                                        default => 'badge-patient'
                                    };
                                    ?>
                                    <span class="badge role-badge <?= $roleClass ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark stats-badge me-1">
                                        <i class="fas fa-calendar-check text-primary me-1"></i>
                                        <?= $user['appointment_count'] ?> apps
                                    </span>
                                    <span class="badge bg-light text-dark stats-badge">
                                        <i class="fas fa-vial text-success me-1"></i>
                                        <?= $user['order_count'] ?> tests
                                    </span>
                                </td>
                                <td class="user-actions">
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#userModal"
                                            data-user-id="<?= $user['id'] ?>"
                                            data-user-data='<?= json_encode($user) ?>'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="user-management.php?delete=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Permanently delete this user?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <nav aria-label="User pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <?php
                        $totalPages = ceil($totalUsers / $perPage);
                        $visiblePages = min(5, $totalPages);
                        $startPage = max(1, min($page - floor($visiblePages/2), $totalPages - $visiblePages + 1));
                        
                        // Previous button
                        if ($page > 1) {
                            echo '<li class="page-item">
                                    <a class="page-link" href="?page='.($page-1).'" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                  </li>';
                        }
                        
                        // Page numbers
                        for ($i = $startPage; $i < $startPage + $visiblePages; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<li class="page-item '.$active.'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
                        }
                        
                        // Next button
                        if ($page < $totalPages) {
                            echo '<li class="page-item">
                                    <a class="page-link" href="?page='.($page+1).'" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                  </li>';
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="user_id" id="userId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name*</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name*</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email*</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role*</label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Administrator</option>
                                <option value="lab">Lab Technician</option>
                                <option value="phlebotomist">Phlebotomist</option>
                                <option value="staff">Front Desk Staff</option>
                                <option value="patient">Patient</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User modal handling
        const userModal = document.getElementById('userModal');
        userModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const modal = this;
            
            if (button.hasAttribute('data-user-id')) {
                const userData = JSON.parse(button.getAttribute('data-user-data'));
                modal.querySelector('#modalTitle').textContent = `Edit ${userData.first_name} ${userData.last_name}`;
                modal.querySelector('#userId').value = userData.id;
                
                // Update form
                const form = modal.querySelector('form');
                form.querySelector('[name="first_name"]').value = userData.first_name;
                form.querySelector('[name="last_name"]').value = userData.last_name;
                form.querySelector('[name="email"]').value = userData.email;
                form.querySelector('[name="phone"]').value = userData.phone || '';
                form.querySelector('[name="role"]').value = userData.role;
                
                // Change submit button
                const submitBtn = form.querySelector('[type="submit"]');
                submitBtn.name = 'update_user';
                submitBtn.textContent = 'Update User';
            } else {
                // Reset for new user
                modal.querySelector('#modalTitle').textContent = 'Add New User';
                modal.querySelector('#userId').value = '';
                modal.querySelector('form').reset();
                const submitBtn = modal.querySelector('[type="submit"]');
                submitBtn.name = 'add_user';
                submitBtn.textContent = 'Add User';
            }
        });

        // Phone number formatting
        document.querySelector('[name="phone"]')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '')
                .replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        });
    </script>
</body>
</html>