<?php
// register.php
session_start();
require 'db_connect.php';

$err = '';
$success = '';

$isSuperAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  // Determine role: only super admin session can create super_admin users
  $role = 'user';
  if ($isSuperAdmin && isset($_POST['role']) && $_POST['role'] === 'super_admin') {
    $role = 'super_admin';
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = "Invalid email.";
  } else {
    // check exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
      $err = "Email already registered.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
      $insert->bind_param('ssss', $username, $email, $hash, $role);
      if ($insert->execute()) {
        $success = "Registered successfully. You can now login.";
      } else {
        $err = "Registration failed.";
      }
      $insert->close();
    }
    $stmt->close();
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register | 3ED.I SOCIETY</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background: linear-gradient(135deg,#6a00f4,#8c005a); min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .card{ width:100%; max-width:520px; border-radius:12px; padding:24px; box-shadow:0 8px 30px rgba(0,0,0,0.2); background:#fff; }
    .brand{ color:#6a00f4; font-weight:700; }
  </style>
</head>
<body>
  <div class="card">
    <h3 class="brand mb-3">Create an account</h3>
    <?php if($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <form method="post" novalidate>
      <div class="mb-3">
        <label>Full name</label>
        <input name="username" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Email</label>
        <input name="email" type="email" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input name="password" type="password" class="form-control" required />
      </div>

      <?php if($isSuperAdmin): ?>
        <div class="mb-3">
          <label>Role</label>
          <select name="role" class="form-control">
            <option value="user">User</option>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>
      <?php endif; ?>

      <button class="btn btn-primary w-100">Register</button>
    </form>
    <div class="mt-3 text-center"><a href="/login.php">Login</a></div>
  </div>
</body>
</html>
