<?php
session_start();
require '../db_connect.php';

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin')) {
  header('Location: dashboard.php');
  exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $query->bind_param("s", $email);
  $query->execute();
  $result = $query->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      // Check if user is admin
      if ($user['role'] === 'super_admin' || $user['role'] === 'admin') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard.php");
        exit;
      } else {
        $error = "Access denied. Admin privileges required.";
      }
    } else {
      $error = "Incorrect password.";
    }
  } else {
    $error = "No user found with this email.";
  }
  $query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | 3ED.I SOCIETY</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --purple: #1c0935;
      --magenta: #282ed2;
      --cyan: #182cc3;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, var(--purple), var(--magenta), var(--cyan));
      background-size: 400% 400%;
      animation: gradientShift 8s ease infinite;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      color: #fff;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    h2 {
      font-weight: 700;
      text-align: center;
      margin-bottom: 30px;
      letter-spacing: 1px;
    }

    .form-control {
      border: none;
      border-radius: 50px;
      padding: 12px 20px;
    }

    .btn-login {
      width: 100%;
      border-radius: 50px;
      background: linear-gradient(90deg, var(--magenta), var(--cyan));
      border: none;
      font-weight: bold;
      padding: 12px;
      transition: transform 0.3s ease;
      color: #fff;
    }

    .btn-login:hover {
      transform: scale(1.05);
      background: linear-gradient(90deg, var(--cyan), var(--magenta));
      color: #fff;
    }

    .error {
      color: #ffb3b3;
      background: rgba(255, 0, 0, 0.1);
      border-radius: 10px;
      padding: 10px;
      text-align: center;
      margin-bottom: 15px;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
  </style>
</head>
<body>

  <div class="login-container">
    <h2><i class="bi bi-shield-lock"></i> Admin Login</h2>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <button type="submit" class="btn btn-login">Login</button>
    </form>

    <div class="text-center mt-3">
      <a href="../index.php" style="color:#fff; text-decoration:none;"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>
  </div>

</body>
</html>
