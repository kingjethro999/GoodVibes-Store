<?php
// login.php
session_start();
require 'db_connect.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ? LIMIT 1");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res && $res->num_rows === 1) {
    $user = $res->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      // Redirect users to storefront (index.php)
      header('Location: index.php');
      exit;
    } else {
      $err = "Invalid credentials.";
    }
  } else {
    $err = "Invalid credentials.";
  }
  $stmt->close();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>User Login | Good Vibes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background: linear-gradient(135deg,#6a00f4,#ff0099); min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .card{ width:100%; max-width:420px; border-radius:12px; padding:24px; box-shadow:0 8px 30px rgba(0,0,0,0.2); }
    .brand{ color:#fff; font-weight:700; }
  </style>
</head>
<body>
  <div class="card bg-white">
    <h4 class="brand mb-3">Good Vibes — User Login</h4>
    <?php if($err): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required />
      </div>
      <button class="btn btn-primary w-100">Login</button>
      <div class="mt-3 text-center">
        <a href="/admin-login.php">Admin login</a> • <a href="/register.php">Register</a>
      </div>
    </form>
  </div>
</body>
</html>
