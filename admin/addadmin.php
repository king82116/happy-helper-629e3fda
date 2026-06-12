<?php
session_start();
if(empty($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit();
}

include("conn.php");

// Security enhancements
function sanitizeInput($conn, $data) {
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}

// Delete Admin
if(isset($_POST['delete_id'])) {
    $delete_id = sanitizeInput($conn, $_POST['delete_id']);
    
    // Prevent deleting own account
    if($delete_id == $_SESSION['unohs']) {
        $_SESSION['error'] = "You cannot delete your own account";
        header("Location: addadmin.php");
        exit();
    }
    
    $query = "DELETE FROM nirvahaka_shonu WHERE unohs = '$delete_id'";
    if(mysqli_query($conn, $query)) {
        $_SESSION['msg'] = "Admin deleted successfully";
    } else {
        $_SESSION['error'] = "Delete failed: " . mysqli_error($conn);
    }
    header("Location: addadmin.php");
    exit();
}

// Add/Update Admin
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serial'])) {
    $serial = sanitizeInput($conn, $_POST['serial']);
    $password = isset($_POST['password']) ? sanitizeInput($conn, $_POST['password']) : '';
    $dashboard = isset($_POST['dashboard']) ? 1 : 0;
    $wingomanager = isset($_POST['wingo1min']) ? 1 : 0;
    $k3manager = isset($_POST['k3manager']) ? 1 : 0;
    $d5manager = isset($_POST['d5manager']) ? 1 : 0;
    $finance = isset($_POST['finance']) ? 1 : 0;
    $managegame = isset($_POST['managegame']) ? 1 : 0;
    $status = 1;

    if(isset($_POST['update_id'])) { // Update
        $update_id = sanitizeInput($conn, $_POST['update_id']);
        $status = sanitizeInput($conn, $_POST['status']);
        
        $password_update = "";
        if(!empty($password)) {
            // Using MD5 for password hashing as requested
            $password_update = ", guptapada = '".md5($password)."'";
        }

        $sql = "UPDATE nirvahaka_shonu SET 
                dashboard = '$dashboard',
                wingomanager = '$wingomanager',
                k3manager = '$k3manager',
                `5dmanager` = '$d5manager',
                finance = '$finance',
                managegame = '$managegame',
                sthiti = '$status'
                $password_update
                WHERE unohs = '$update_id'";

        if(mysqli_query($conn, $sql)) {
            $_SESSION['msg'] = "Admin updated successfully";
        } else {
            $_SESSION['error'] = "Update failed: " . mysqli_error($conn);
        }
    } else { // Add
        if(empty($password)) {
            $_SESSION['error'] = "Password is required for new admin";
            header("Location: addadmin.php");
            exit();
        }
        
        $check = mysqli_query($conn, "SELECT * FROM nirvahaka_shonu WHERE nirvahaka_hesaru = '$serial'");
        if(mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = "Username already exists";
        } else {
            // Using MD5 for password hashing as requested
            $hashed_password = md5($password);
            
            $sql = "INSERT INTO nirvahaka_shonu 
                    (hesaru, nirvahaka_hesaru, guptapada, sthiti, dashboard, wingomanager, k3manager, `5dmanager`, finance, managegame) 
                    VALUES 
                    ('$serial', '$serial', '$hashed_password', '$status', '$dashboard', '$wingomanager', '$k3manager', '$d5manager', '$finance', '$managegame')";
            
            if(mysqli_query($conn, $sql)) {
                $_SESSION['msg'] = "Admin added successfully";
            } else {
                $_SESSION['error'] = "Add failed: " . mysqli_error($conn);
            }
        }
    }
    header("Location: addadmin.php");
    exit();
}

// Fetch for editing
$edit_data = null;
if(isset($_GET['edit_id'])) {
    $edit_id = sanitizeInput($conn, $_GET['edit_id']);
    $result = mysqli_query($conn, "SELECT * FROM nirvahaka_shonu WHERE unohs = '$edit_id'");
    $edit_data = mysqli_fetch_assoc($result);
    
    if(!$edit_data) {
        $_SESSION['error'] = "Admin not found";
        header("Location: addadmin.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Admin Management | Prime Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/mobile-responsive.css">
    <style>
        :root {
            --sidebar-bg-1: #4e73df;
            --sidebar-bg-2: #224abe;
            --sidebar-bg-3: #1e3a8a;
            --sidebar-text: rgba(255, 255, 255, 0.9);
            --sidebar-hover: rgba(255, 255, 255, 0.15);
            --sidebar-active: rgba(255, 255, 255, 0.3);
            --primary: #4e73df;
            --primary-dark: #3a5ccc;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
            --info: #36b9cc;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* Sidebar with gradient */
        .sidebar-container {
            width: 250px;
            background: linear-gradient(135deg, var(--sidebar-bg-1), var(--sidebar-bg-2), var(--sidebar-bg-3));
            color: var(--sidebar-text);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            background-color: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding-top: 1rem;
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: var(--sidebar-text);
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            margin: 0.2rem 1rem;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover {
            background-color: var(--sidebar-hover);
            transform: translateX(5px);
        }
        
        .sidebar-menu .active a {
            background-color: var(--sidebar-active);
            font-weight: 600;
            box-shadow: 3px 0 0 var(--sidebar-text) inset;
        }
        
        .sidebar-menu i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0 !important;
        }
        
        /* Form elements */
        .form-control {
            border-radius: 6px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn {
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        /* Permissions grid */
        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f8f9fc;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        
        .permission-item:hover {
            background-color: #f0f2f7;
        }
        
        .permission-item input {
            margin-right: 8px;
        }
        
        /* Table styles */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .table th {
            background-color: #f8f9fc;
            font-weight: 600;
            padding: 12px 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table tr:hover td {
            background-color: #f8f9fc;
        }
        
        /* Badges */
        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .badge-active {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .badge-inactive {
            background-color: #f8d7da;
            color: #842029;
        }
        
        /* Password toggle */
        .password-toggle {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .permission-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .sidebar-container {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar-container.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
        }
        
        /* Three box layout */
        .admin-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .summary-card i {
            font-size: 2.5rem;
            margin-right: 20px;
            opacity: 0.8;
        }
        
        .summary-card .count {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .summary-card .title {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .card-1 {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }
        
        .card-2 {
            background: linear-gradient(135deg, var(--success), #17a673);
        }
        
        .card-3 {
            background: linear-gradient(135deg, var(--info), #2c9faf);
        }
        
        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 99;
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar-container" id="sidebar">
            <div class="sidebar-header">
                Prime Tech
            </div>
            <ul class="sidebar-menu list-unstyled">
                <li>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="active">
                    <a href="addadmin.php"><i class="fas fa-user-shield"></i> Admin Management</a>
                </li>
                <li>
                    <a href="wingo1min.php"><i class="fas fa-trophy"></i> Wingo Manager</a>
                </li>
                <li>
                    <a href="k31min.php"><i class="fas fa-dice"></i> K3 Manager</a>
                </li>
                <li>
                    <a href="5d1min.php"><i class="fas fa-dice-d20"></i> 5D Manager</a>
                </li>
                <li>
                    <a href="addupi.php"><i class="fas fa-wallet"></i> Add UPI</a>
                </li>
                <li>
                    <a href="manage_user.php"><i class="fas fa-gamepad"></i> Manage User</a>
                </li>
                <li>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content w-100">
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Admin Management</h1>
                </div>

                <!-- Admin Summary Cards -->
                <?php
                $total_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM nirvahaka_shonu"))['count'];
                $active_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM nirvahaka_shonu WHERE sthiti = 1"))['count'];
                $inactive_admins = $total_admins - $active_admins;
                ?>
                <div class="admin-summary">
                    <div class="summary-card card-1">
                        <i class="fas fa-users"></i>
                        <div>
                            <div class="count"><?= $total_admins ?></div>
                            <div class="title">Total Admins</div>
                        </div>
                    </div>
                    <div class="summary-card card-2">
                        <i class="fas fa-user-check"></i>
                        <div>
                            <div class="count"><?= $active_admins ?></div>
                            <div class="title">Active Admins</div>
                        </div>
                    </div>
                    <div class="summary-card card-3">
                        <i class="fas fa-user-times"></i>
                        <div>
                            <div class="count"><?= $inactive_admins ?></div>
                            <div class="title">Inactive Admins</div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <?php if(isset($_SESSION['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold"><?= $edit_data ? 'Edit Admin' : 'Add New Admin'; ?></h6>
                                <?php if($edit_data): ?>
                                    <span class="badge bg-primary">Editing: <?= htmlspecialchars($edit_data['nirvahaka_hesaru']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST" autocomplete="off">
                                    <?php if($edit_data): ?>
                                        <input type="hidden" name="update_id" value="<?= $edit_data['unohs'] ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input name="serial" type="text" class="form-control" 
                                               value="<?= htmlspecialchars($edit_data['nirvahaka_hesaru'] ?? '') ?>" 
                                               <?= $edit_data ? 'readonly' : 'required' ?>>
                                    </div>
                                    
                                    <div class="mb-3 password-toggle">
                                        <label class="form-label">Password</label>
                                        <input name="password" type="password" id="passwordField" class="form-control" 
                                               <?= !$edit_data ? 'required' : '' ?>>
                                        <span class="password-toggle-icon" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                        <?php if($edit_data): ?>
                                            <small class="text-muted">Leave blank to keep current password</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Permissions</label>
                                        <div class="permission-grid">
                                            <div class="permission-item">
                                                <input type="checkbox" name="dashboard" id="dashboard" value="1" <?= ($edit_data['dashboard'] ?? 0) ? 'checked' : '' ?>>
                                                <label for="dashboard">Dashboard</label>
                                            </div>
                                            <div class="permission-item">
                                                <input type="checkbox" name="wingomanager" id="wingomanager" value="1" <?= ($edit_data['wingomanager'] ?? 0) ? 'checked' : '' ?>>
                                                <label for="wingomanager">Wingo</label>
                                            </div>
                                            <div class="permission-item">
                                                <input type="checkbox" name="k3manager" id="k3manager" value="1" <?= ($edit_data['k3manager'] ?? 0) ? 'checked' : '' ?>>
                                                <label for="k3manager">K3</label>
                                            </div>
                                            <div class="permission-item">
                                                <input type="checkbox" name="d5manager" id="d5manager" value="1" <?= ($edit_data['5dmanager'] ?? 0) ? 'checked' : '' ?>>
                                                <label for="d5manager">5D</label>
                                            </div>
                                            <div class="permission-item">
                                                <input type="checkbox" name="finance" id="finance" value="1" <?= ($edit_data['finance'] ?? 0) ? 'checked' : '' ?>>
                                                <label for="finance">Finance</label>
                                            </div>
                                            <div class="permission-item">
                                                <input type="checkbox" name="managegame" id="managegame" value="1" <?= ($edit_data['managegame'] ?? 0) ? 'checked' : '' ?>>
                                                <label for="managegame">Game</label>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if($edit_data): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-control">
                                                <option value="1" <?= ($edit_data['sthiti'] == 1) ? 'selected' : '' ?>>Active</option>
                                                <option value="0" <?= ($edit_data['sthiti'] == 0) ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> <?= $edit_data ? 'Update' : 'Add Admin' ?>
                                        </button>
                                        <?php if($edit_data): ?>
                                            <a href="addadmin.php" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i> Cancel
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold">Admin List</h6>
                                <div>
                                    <span class="badge bg-primary me-2">Total: <?= $total_admins ?></span>
                                    <span class="badge bg-success">Active: <?= $active_admins ?></span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Permissions</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = mysqli_query($conn, "SELECT * FROM nirvahaka_shonu ORDER BY nirvahaka_hesaru");
                                            while($row = mysqli_fetch_assoc($result)):
                                                $permissions = [];
                                                if($row['dashboard']) $permissions[] = 'Dashboard';
                                                if($row['wingomanager']) $permissions[] = 'Wingo';
                                                if($row['k3manager']) $permissions[] = 'K3';
                                                if($row['5dmanager']) $permissions[] = '5D';
                                                if($row['finance']) $permissions[] = 'Finance';
                                                if($row['managegame']) $permissions[] = 'Game';
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['nirvahaka_hesaru']) ?></td>
                                                <td>
                                                    <?php if(!empty($permissions)): ?>
                                                        <div class="d-flex flex-wrap gap-1">
                                                            <?php foreach($permissions as $perm): ?>
                                                                <span class="badge bg-light text-dark"><?= $perm ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row['sthiti'] ? 'badge-active' : 'badge-inactive' ?>">
                                                        <?= $row['sthiti'] ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if($row['unohs'] != $_SESSION['unohs']): ?>
                                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                                                <input type="hidden" name="delete_id" value="<?= $row['unohs'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('passwordField');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
        document.addEventListener('click', function(e) {
            var sidebar = document.getElementById('sidebar');
            var btn = document.getElementById('mobileMenuBtn');
            if (window.innerWidth <= 768 && sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) && !btn.contains(e.target)) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
        
        // Clear form when not in edit mode
        <?php if(!isset($_GET['edit_id'])): ?>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelector("form").reset();
            });
        <?php endif; ?>
        
        // Prevent form resubmission on refresh
        if(window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>