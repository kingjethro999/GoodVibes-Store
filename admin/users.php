<?php
session_start();
require '../db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}

$err = '';
$success = '';

// Handle delete user
if (isset($_GET['delete'])) {
  $userId = intval($_GET['delete']);
  if ($userId != $_SESSION['user_id']) { // Prevent self-deletion
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
      $success = "User deleted successfully!";
    } else {
      $err = "Failed to delete user.";
    }
    $stmt->close();
  } else {
    $err = "You cannot delete your own account.";
  }
}

// Handle add/edit user
$isSuperAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? 'user';
  
  // Only super admin can assign super_admin role
  if (!$isSuperAdmin && $role === 'super_admin') {
    $role = 'user';
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = "Invalid email.";
  } else {
    // Check if email exists (for new user)
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
      $err = "Email already registered.";
    } else {
      if (empty($password)) {
        $err = "Password is required.";
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $insert->bind_param('ssss', $username, $email, $hash, $role);
        if ($insert->execute()) {
          $success = "User added successfully!";
          // Clear form by redirecting
          header("Location: users.php?success=1");
          exit;
        } else {
          $err = "Failed to add user.";
        }
        $insert->close();
      }
    }
    $checkStmt->close();
  }
}

// Get all users
$users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<?php include __DIR__ . '/../theme/header.php'; ?>

<style>
  :root {
    --gv-purple: #1c0935;
    --gv-magenta: #6a00f4;
    --gv-bg: #faf7ff;
  }

  body {
    background: var(--gv-bg);
    font-family: 'Poppins', sans-serif;
  }

  h2 {
    font-weight: 700;
    background: linear-gradient(90deg, var(--gv-purple), var(--gv-magenta));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .gv-card {
    background: #fff;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 20px;
  }

  .btn-danger {
    background: #dc3545;
    border: none;
  }

  .role-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
  }

  .role-super_admin {
    background: linear-gradient(90deg, var(--gv-purple), var(--gv-magenta));
    color: #fff;
  }

  .role-user {
    background: #e9ecef;
    color: #495057;
  }

  /* Responsive improvements */
  @media (max-width: 768px) {
    .gv-card {
      padding: 15px;
    }

    .gv-card form .row.g-3 > [class*="col-"] {
      flex: 0 0 100%;
      max-width: 100%;
    }

    .table {
      font-size: 0.875rem;
    }

    .table td, .table th {
      padding: 0.5rem;
      white-space: nowrap;
    }

    .role-badge {
      font-size: 0.75rem;
      padding: 3px 8px;
    }
  }
</style>

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="mb-0">Users Management</h2>
      <p class="text-muted">Manage all users and their roles</p>
    </div>
  </div>

  <?php if (isset($_GET['success']) || $success): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?php echo $success ?: "User added successfully!"; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($err): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?php echo htmlspecialchars($err); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Add User Form -->
  <div class="gv-card">
    <h5 class="mb-3">Add New User</h5>
    <form method="POST" action="">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Full Name</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" <?php echo !$isSuperAdmin ? 'disabled' : ''; ?>>
            <option value="user">User</option>
            <?php if ($isSuperAdmin): ?>
              <option value="super_admin">Super Admin</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary">Add User</button>
      </div>
    </form>
  </div>

  <!-- Users List -->
  <div class="gv-card">
    <h5 class="mb-3">All Users</h5>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($users && $users->num_rows > 0): ?>
            <?php while ($user = $users->fetch_assoc()): ?>
              <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                  <span class="role-badge role-<?php echo $user['role']; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                  </span>
                </td>
                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                <td>
                  <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <a href="users.php?delete=<?php echo $user['id']; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this user?');">
                      Delete
                    </a>
                  <?php else: ?>
                    <span class="text-muted">Current User</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No users found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../theme/footer.php'; ?>
