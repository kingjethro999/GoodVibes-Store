<?php
require 'db_connect.php';
session_start();

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
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['username'];
      $_SESSION['role'] = $user['role'];

      // Redirect based on role
      if ($user['role'] === 'super_admin' || $user['role'] === 'admin') {
        header("Location: admin/dashboard.php");
      } else {
        header("Location: index.php");
      }
      exit;
    } else {
      $error = "Incorrect password.";
    }
  } else {
    $error = "No user found with this email.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>3ED.I SOCIETY | Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
      height: 100vh;
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
    }

    .btn-login:hover {
      transform: scale(1.05);
      background: linear-gradient(90deg, var(--cyan), var(--magenta));
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
      0% {
        background-position: 0% 50%;
      }

      50% {
        background-position: 100% 50%;
      }

      100% {
        background-position: 0% 50%;
      }
    }
  </style>
</head>

<body>

  <div class="login-container">
    <h2>3ED.I SOCIETY Login</h2>

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
      <a href="register.php" style="color:#fff; text-decoration:none;">Create an account</a>
    </div>
  </div>

</body>

</html>