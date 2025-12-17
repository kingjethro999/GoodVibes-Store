<?php
// merch.php - Admin merch management
session_start();
require '../db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}

$error = '';
$success = '';
$editMerch = null;

// Handle delete merch
if (isset($_GET['delete'])) {
  $merchId = intval($_GET['delete']);

  if ($merchId > 0) {
    $stmt = $conn->prepare("DELETE FROM merch WHERE id = ?");
    $stmt->bind_param('i', $merchId);
    if ($stmt->execute()) {
      $success = "Merch item deleted successfully.";
    } else {
      $error = "Failed to delete merch item.";
    }
    $stmt->close();
  }
}

// Handle update merch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_merch'])) {
  $id = intval($_POST['merch_id'] ?? 0);
  $name = trim($_POST['product_name'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $price = floatval($_POST['price'] ?? 0);
  $description = trim($_POST['description'] ?? '');

  if ($id <= 0 || $name === '' || $price <= 0) {
    $error = "Please provide a valid name and price.";
  } else {
    $stmt = $conn->prepare("UPDATE merch SET product_name = ?, category = ?, price = ?, description = ? WHERE id = ?");
    $stmt->bind_param('ssdsi', $name, $category, $price, $description, $id);
    if ($stmt->execute()) {
      $success = "Merch item updated successfully.";
      // Refresh edit data so the form shows latest values if staying on same merch
      $_GET['edit'] = $id;
    } else {
      $error = "Failed to update merch item.";
    }
    $stmt->close();
  }
}

// If editing, fetch that merch
if (isset($_GET['edit'])) {
  $editId = intval($_GET['edit']);
  if ($editId > 0) {
    $stmt = $conn->prepare("SELECT * FROM merch WHERE id = ?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editMerch = $result->fetch_assoc() ?: null;
    $stmt->close();
  }
}

// Fetch all merch for listing
$merchItems = $conn->query("SELECT id, product_name, category, price, created_at FROM merch ORDER BY created_at DESC");
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

  .table thead th {
    background-color: #f8f0ff;
    color: var(--gv-purple);
  }

  @media (max-width: 768px) {
    .gv-card form .row.g-3 > [class*="col-"] {
      flex: 0 0 100%;
      max-width: 100%;
    }

    .table {
      font-size: 0.875rem;
    }

    .table td, .table th {
      padding: 0.5rem;
    }
  }
</style>

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="mb-0">Merch Management</h2>
      <p class="text-muted">Manage and edit store merch items</p>
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

  <!-- Edit Merch Form -->
  <?php if ($editMerch): ?>
    <div class="gv-card">
      <h5 class="mb-3">Edit Merch: <?php echo htmlspecialchars($editMerch['product_name']); ?></h5>
      <form method="POST" action="">
        <input type="hidden" name="merch_id" value="<?php echo $editMerch['id']; ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Product Name</label>
            <input type="text" name="product_name" class="form-control" required
                   value="<?php echo htmlspecialchars($editMerch['product_name']); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control"
                   value="<?php echo htmlspecialchars($editMerch['category'] ?? ''); ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Price (₦)</label>
            <input type="number" step="0.01" name="price" class="form-control" required
                   value="<?php echo htmlspecialchars($editMerch['price']); ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control"><?php echo htmlspecialchars($editMerch['description'] ?? ''); ?></textarea>
          </div>
        </div>
        <div class="mt-3 d-flex justify-content-between">
          <a href="merch.php" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" name="update_merch" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- Merch List -->
  <div class="gv-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">All Merch Items</h5>
      <a href="add_merch.php" class="btn btn-success btn-sm">
        <i class="bi bi-plus-lg"></i> Add New Merch
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price (₦)</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($merchItems && $merchItems->num_rows > 0): ?>
            <?php while ($merch = $merchItems->fetch_assoc()): ?>
              <tr>
                <td><?php echo $merch['id']; ?></td>
                <td><?php echo htmlspecialchars($merch['product_name']); ?></td>
                <td><?php echo htmlspecialchars($merch['category'] ?? ''); ?></td>
                <td>₦<?php echo number_format($merch['price'], 2); ?></td>
                <td><?php echo isset($merch['created_at']) ? date('M j, Y', strtotime($merch['created_at'])) : ''; ?></td>
                <td>
                  <a href="merch.php?edit=<?php echo $merch['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                    Edit
                  </a>
                  <a href="merch.php?delete=<?php echo $merch['id']; ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Are you sure you want to delete this merch item?');">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No merch items found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../theme/footer.php'; ?>
