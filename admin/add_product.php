<?php
// add_product.php
session_start();
require '../db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['product_name']);
  $price = floatval($_POST['price']);
  $category_name = trim($_POST['category']);
  $description = trim($_POST['description']);

  // Ensure uploads dir
  $uploadDir = __DIR__ . '/uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  // Handle file upload
  if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die('Image upload failed.');
  }

  $tmp = $_FILES['image']['tmp_name'];
  $origName = basename($_FILES['image']['name']);
  $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
  $allowed = ['jpg','jpeg','png','webp','gif'];
  if (!in_array($ext, $allowed)) die('Invalid image type.');

  // sanitize and create unique filename
  $safe = preg_replace("/[^A-Za-z0-9\-]/", '-', pathinfo($origName, PATHINFO_FILENAME));
  $filename = $safe . '-' . time() . '.' . $ext;
  $dest = $uploadDir . $filename;
  if (!move_uploaded_file($tmp, $dest)) die('Could not move upload.');

  // store relative path to use in HTML
  $dbPath = 'uploads/' . $filename;

  // Ensure category exists (or create)
  $catId = null;
  $stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ? LIMIT 1");
  $stmt->bind_param('s', $category_name);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res && $res->num_rows === 1) {
    $cat = $res->fetch_assoc();
    $catId = $cat['id'];
  } else {
    $ins = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $ins->bind_param('s', $category_name);
    $ins->execute();
    $catId = $ins->insert_id;
    $ins->close();
  }
  $stmt->close();

  // Insert product (prepared)
  $insert = $conn->prepare("INSERT INTO products (product_name, category_id, price, description, image) VALUES (?,?,?,?,?)");
  $insert->bind_param('sidss', $name, $catId, $price, $description, $dbPath);
  if ($insert->execute()) {
    header('Location: dashboard.php?added=1');
    exit;
  } else {
    echo "DB error: " . $conn->error;
  }
}
?>
