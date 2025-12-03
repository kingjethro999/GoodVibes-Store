<?php
session_start();

// Redirect based on login status and role
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
  } else {
    header('Location: ../index.php');
    exit;
  }
} else {
  header('Location: login.php');
  exit;
}
?>