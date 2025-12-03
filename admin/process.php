<?php
session_start();
require '../db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $orderId = intval($_POST['order_id']);
  $newStatus = $_POST['status'];
  
  $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
  $stmt->bind_param('si', $newStatus, $orderId);
  if ($stmt->execute()) {
    $success = "Order status updated successfully!";
  } else {
    $error = "Failed to update order status.";
  }
  $stmt->close();
}

// Redirect to order details page if specific order requested
if (isset($_GET['order'])) {
  header('Location: order_details.php?id=' . intval($_GET['order']));
  exit;
}

// Get all orders with filters
$statusFilter = $_GET['status'] ?? 'all';
$whereClause = '';
if ($statusFilter !== 'all') {
  $statusFilter = $conn->real_escape_string($statusFilter);
  $whereClause = "WHERE o.status = '$statusFilter'";
}

$orders = $conn->query("
  SELECT o.*, u.username as customer_name, u.email as customer_email,
         p.product_name, p.image
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  LEFT JOIN products p ON o.product_id = p.id
  $whereClause
  ORDER BY o.order_date DESC
");
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

  .order-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
  }

  .filter-badge {
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 20px;
    margin-right: 10px;
    margin-bottom: 10px;
    display: inline-block;
  }

  .filter-badge.active {
    background: linear-gradient(90deg, var(--gv-purple), var(--gv-magenta));
    color: #fff;
  }

  .order-detail-card {
    background: #fff;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }

  /* Responsive improvements */
  @media (max-width: 768px) {
    .filter-badge {
      padding: 6px 12px;
      font-size: 0.875rem;
      margin-right: 5px;
      margin-bottom: 8px;
    }

    .table {
      font-size: 0.875rem;
    }

    .table td, .table th {
      padding: 0.5rem;
    }

    .order-img {
      width: 40px;
      height: 40px;
    }

    .order-detail-card {
      padding: 20px;
    }
  }

  @media (max-width: 576px) {
    .filter-badge {
      display: block;
      text-align: center;
      margin-right: 0;
    }
  }
</style>

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="mb-0">Orders Management</h2>
      <p class="text-muted">View and manage all orders</p>
    </div>
  </div>

  <?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?php echo $success; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?php echo $error; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Filters -->
  <div class="gv-card">
    <h5 class="mb-3">Filter by Status</h5>
    <a href="process.php?status=all" class="filter-badge <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">All Orders</a>
    <a href="process.php?status=pending" class="filter-badge <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
    <a href="process.php?status=completed" class="filter-badge <?php echo $statusFilter === 'completed' ? 'active' : ''; ?>">Completed</a>
    <a href="process.php?status=cancelled" class="filter-badge <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
  </div>

  <!-- Orders Table -->
  <div class="gv-card">
    <h5 class="mb-3">All Orders</h5>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders && $orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
              <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                <td>
                  <div class="d-flex align-items-center">
                    <?php if ($order['image']): ?>
                      <img src="../<?php echo htmlspecialchars($order['image']); ?>" alt="Product" class="order-img me-2">
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></span>
                  </div>
                </td>
                <td><?php echo $order['quantity']; ?></td>
                <td>₦<?php echo number_format($order['total_price'], 2); ?></td>
                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                <td>
                  <?php
                  $statusClass = 'secondary';
                  if ($order['status'] === 'completed') $statusClass = 'success';
                  elseif ($order['status'] === 'pending') $statusClass = 'warning text-dark';
                  elseif ($order['status'] === 'cancelled') $statusClass = 'danger';
                  ?>
                  <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
                </td>
                <td>
                  <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">No orders found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../theme/footer.php'; ?>