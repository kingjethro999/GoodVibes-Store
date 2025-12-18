<?php
// add_merch.php - Add new merch items
session_start();
require '../db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['product_name']);
  $price = floatval($_POST['price']);
  $category = trim($_POST['category']);
  $description = trim($_POST['description']);

  // Ensure uploads dir
  $uploadDir = __DIR__ . '/uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  // Handle file upload
  if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error = 'Image upload failed.';
  } else {
    $tmp = $_FILES['image']['tmp_name'];
    $origName = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    
    if (!in_array($ext, $allowed)) {
      $error = 'Invalid image type.';
    } else {
      // sanitize and create unique filename
      $safe = preg_replace("/[^A-Za-z0-9\\-]/", '-', pathinfo($origName, PATHINFO_FILENAME));
      $filename = $safe . '-' . time() . '.' . $ext;
      $dest = $uploadDir . $filename;
      
      if (!move_uploaded_file($tmp, $dest)) {
        $error = 'Could not move upload.';
      } else {
        // store relative path to use in HTML
        $dbPath = 'uploads/' . $filename;

        // Insert merch (prepared statement)
        $insert = $conn->prepare("INSERT INTO merch (product_name, category, price, description, image) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param('ssdss', $name, $category, $price, $description, $dbPath);
        
        if ($insert->execute()) {
          $success = 'Merch item added successfully!';
          // Clear form by redirecting
          header('Location: merch.php?added=1');
          exit;
        } else {
          $error = "Database error: " . $conn->error;
        }
        $insert->close();
      }
    }
  }
}
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
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 20px;
  }

  @media (max-width: 768px) {
    .gv-card form .row.g-3 > [class*="col-"] {
      flex: 0 0 100%;
      max-width: 100%;
    }
  }
</style>

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="mb-0">Add New Merch</h2>
      <p class="text-muted">Upload a new merch item to the store</p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?php echo htmlspecialchars($success); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?php echo htmlspecialchars($error); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="gv-card">
    <form method="POST" action="" enctype="multipart/form-data">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Product Name <span class="text-danger">*</span></label>
          <input type="text" name="product_name" class="form-control" required 
                 placeholder="e.g. 3ED.I SOCIETY Cap">
        </div>

        <div class="col-md-3">
          <label class="form-label">Category</label>
          <input type="text" name="category" class="form-control" 
                 placeholder="e.g. Accessories">
        </div>

        <div class="col-md-3">
          <label class="form-label">Price (₦) <span class="text-danger">*</span></label>
          <input type="number" step="0.01" name="price" class="form-control" required 
                 placeholder="0.00">
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" rows="4" class="form-control" 
                    placeholder="Describe the merch item..."></textarea>
        </div>

        <div class="col-12">
          <label class="form-label">Product Image <span class="text-danger">*</span></label>
          <input type="file" name="image" class="form-control" accept="image/*" required>
          <small class="text-muted">Accepted formats: JPG, PNG, WEBP, GIF</small>
        </div>

        <div class="col-12">
          <div class="d-flex justify-content-between">
            <a href="merch.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left"></i> Back to Merch
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-lg"></i> Add Merch Item
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../theme/footer.php'; ?>
