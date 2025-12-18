<?php
// dashboard.php - 3ED.I SOCIETY admin overview
session_start();
require '../db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}

// Get stats from database
$productsCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$ordersCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];

// Calculate total sales
$totalSales = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'];

// Calculate this month's sales
$monthSales = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE status != 'cancelled' AND MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'];

// Get recent orders with user and product info
$recentOrders = $conn->query("
  SELECT o.id, o.total_price, o.status, o.order_date, 
         u.username as customer_name, 
         p.product_name
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  LEFT JOIN products p ON o.product_id = p.id
  ORDER BY o.order_date DESC
  LIMIT 10
");
?>
<?php include __DIR__ . '/../theme/header.php'; ?>

<style>
  :root {
    --gv-purple: #1c0935;
    --gv-magenta: #6a00f4;
    --gv-accent: #b5179e;
    --gv-bg: #faf7ff;
  }

  body {
    background: var(--gv-bg);
    font-family: 'Poppins', sans-serif;
    color: #222;
  }

  /* Dashboard Header */
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
    transition: all 0.3s ease;
  }

  .gv-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }

  .gv-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }

  .gv-stat {
    background: linear-gradient(145deg, #ffffff, #f5e8ff);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.05);
  }

  .gv-stat h3 {
    color: var(--gv-magenta);
    font-weight: 700;
  }

  .badge-gv {
    background: var(--gv-magenta);
    color: #fff;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 20px;
  }

  /* Quick actions buttons */
  .btn {
    border-radius: 30px !important;
    font-weight: 600;
  }

  .btn-primary {
    background: linear-gradient(90deg, var(--gv-purple), var(--gv-magenta));
    border: none;
  }

  .btn-outline-secondary:hover {
    background: var(--gv-purple);
    color: #fff;
  }

  /* Product Form Styling */
  form {
    background: #fff;
    border-radius: 18px;
    padding: 25px;
    margin-top: 30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  }

  form label {
    display: block;
    font-weight: 600;
    color: var(--gv-purple);
    margin-bottom: 8px;
  }

  form input, form textarea {
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    margin-bottom: 15px;
    font-size: 1rem;
  }

  form button {
    background: linear-gradient(90deg, var(--gv-purple), var(--gv-magenta));
    color: #fff;
    border: none;
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: 0.3s ease;
  }

  form button:hover {
    transform: scale(1.05);
  }

  .table thead th {
    background-color: #f8f0ff;
    color: var(--gv-purple);
  }

  /* Responsive improvements */
  @media (max-width: 768px) {
    .gv-stats {
      grid-template-columns: 1fr;
    }

    form .row.g-3 > [class*="col-"] {
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
      <h2 class="mb-0">Dashboard</h2>
      <p class="text-muted">Overview of sales, orders, and site stats — 3ED.I SOCIETY</p>
    </div>
  </div>

  <!-- Quick stats -->
  <div class="gv-card mb-4">
    <div class="gv-stats">
      <div class="gv-stat">
        <h6 class="text-muted">Total Sales</h6>
        <div class="d-flex align-items-center justify-content-between">
          <div><h3>₦<?php echo number_format($totalSales, 2); ?></h3><small class="text-muted">This month: ₦<?php echo number_format($monthSales, 2); ?></small></div>
        </div>
      </div>

      <div class="gv-stat">
        <h6 class="text-muted">Orders</h6>
        <div class="d-flex align-items-center justify-content-between">
          <div><h3><?php echo $ordersCount; ?></h3><small class="text-muted">Pending <?php echo $pendingOrders; ?></small></div>
          <?php if ($ordersCount > 0): ?>
            <div><span class="badge bg-success">Active</span></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="gv-stat">
        <h6 class="text-muted">Products</h6>
        <div class="d-flex align-items-center justify-content-between">
          <div><h3><?php echo $productsCount; ?></h3><small class="text-muted">Active</small></div>
          <div><i class="bi bi-box-seam" style="font-size:28px;color:#6a00f4"></i></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent orders / quick actions -->
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="gv-card">
        <h5>Recent Orders</h5>
        <!-- This table is static sample — replace with dynamic query -->
        <div class="table-responsive">
          <table class="table table-borderless mt-3">
            <thead>
              <tr class="text-muted small">
                <th>#</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                <?php while ($order = $recentOrders->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                    <td><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></td>
                    <td>₦<?php echo number_format($order['total_price'], 2); ?></td>
                    <td>
                      <?php
                      $statusClass = 'secondary';
                      if ($order['status'] === 'completed') $statusClass = 'success';
                      elseif ($order['status'] === 'pending') $statusClass = 'warning text-dark';
                      elseif ($order['status'] === 'cancelled') $statusClass = 'danger';
                      ?>
                      <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </td>
                    <td><a class="btn btn-sm btn-outline-primary" href="order_details.php?id=<?php echo $order['id']; ?>">View</a></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">No orders yet</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="col-lg-4">
      <div class="gv-card mb-3">
        <h5>Quick Actions</h5>
        <div class="d-grid gap-2 mt-3">
          <a href="../index.php" class="btn btn-outline-secondary">Open Storefront</a>
          <a href="users.php" class="btn btn-primary">Add New User</a>
          <a href="process.php" class="btn btn-success">Manage Orders</a>
        </div>
      </div>

      <div class="gv-card">
        <h6>Server</h6>
        <p class="small text-muted">PHP v<?php echo phpversion(); ?> &nbsp; • &nbsp; DB: MySQL (ok)</p>
      </div>
    </div>
  </div>
</div>

<h2 class="mt-5">Add New Product</h2>
<form action="../add_product.php" method="POST" enctype="multipart/form-data" class="mt-3">
  <div class="row g-3">
    <div class="col-md-6">
      <label>Product Name:</label>
      <input type="text" name="product_name" required>
    </div>

    <div class="col-md-6">
      <label>Price:</label>
      <input type="number" name="price" required>
    </div>

    <div class="col-md-6">
      <label>Category:</label>
      <input type="text" name="category" required>
    </div>

    <div class="col-md-6">
      <label>Image:</label>
      <input type="file" name="image" accept="image/*" required>
    </div>

    <div class="col-12">
      <label>Description:</label>
      <textarea name="description" rows="3" required></textarea>
    </div>

    <div class="col-12 text-end">
      <button type="submit">Add Product</button>
    </div>
  </div>
</form>



<?php include __DIR__ . '/../theme/footer.php'; ?>
