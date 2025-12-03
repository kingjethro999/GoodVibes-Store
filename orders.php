<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = $_SESSION['user_id'];

// Get status filter
$statusFilter = $_GET['status'] ?? 'all';
$whereClause = "WHERE o.user_id = $userId";
if ($statusFilter !== 'all') {
  $statusFilter = $conn->real_escape_string($statusFilter);
  $whereClause .= " AND o.status = '$statusFilter'";
}

// Get user's orders with product details
$orders = $conn->query("
  SELECT o.*, p.product_name, p.image, p.price as unit_price, r.receipt_image
  FROM orders o
  LEFT JOIN products p ON o.product_id = p.id
  LEFT JOIN reciepts r ON o.id = r.order_id
  $whereClause
  ORDER BY o.order_date DESC
");

// Get order statistics
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $userId")->fetch_assoc()['count'];
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $userId AND status = 'pending'")->fetch_assoc()['count'];
$completedOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $userId AND status = 'completed'")->fetch_assoc()['count'];
$totalSpent = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE user_id = $userId AND status != 'cancelled'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders | Good Vibes</title>
  
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans&display=swap" rel="stylesheet" />
  
  <style>
    :root {
      --primary: #6a00f4;
      --secondary: #a0004b;
      --gradient: linear-gradient(135deg, #6a00f4, #a0004b);
    }

    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #faf7ff;
      color: #222;
    }

    /* Navbar */
    .navbar {
      background: var(--gradient);
      background-size: 200% 200%;
      animation: gradientShift 8s ease infinite;
    }
    .navbar .nav-link, .navbar-brand {
      color: #fff !important;
      font-weight: 600;
    }
    .navbar .nav-link:hover {
      color: #ffd6f0 !important;
    }

    /* Page Header */
    .page-header {
      background: var(--gradient);
      color: #fff;
      padding: 100px 0 60px;
      margin-top: 56px;
      text-align: center;
    }

    .page-header h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 10px;
    }

    /* Stats Cards */
    .stats-card {
      background: #fff;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
      transition: transform 0.3s ease;
      height: 100%;
    }

    .stats-card:hover {
      transform: translateY(-5px);
    }

    .stats-card h3 {
      font-size: 2rem;
      font-weight: 700;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 10px;
    }

    .stats-card p {
      color: #666;
      margin: 0;
    }

    /* Orders Section */
    .orders-section {
      padding: 60px 0;
    }

    .order-card {
      background: #fff;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .order-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .order-id {
      font-weight: 700;
      font-size: 1.2rem;
      color: var(--primary);
    }

    .order-date {
      color: #666;
      font-size: 0.9rem;
    }

    .order-product {
      display: flex;
      gap: 20px;
      align-items: center;
      margin-bottom: 15px;
    }

    .order-product img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
    }

    .order-product-info h5 {
      font-weight: 700;
      color: #222;
      margin-bottom: 5px;
    }

    .order-product-info p {
      color: #666;
      margin: 0;
    }

    .order-details {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 15px;
      border-top: 1px solid #eee;
      flex-wrap: wrap;
      gap: 15px;
    }

    .order-total {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--primary);
    }

    .status-badge {
      padding: 8px 20px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .status-pending {
      background: #fff3cd;
      color: #856404;
    }

    .status-completed {
      background: #d4edda;
      color: #155724;
    }

    .status-cancelled {
      background: #f8d7da;
      color: #721c24;
    }

    /* Filter Buttons */
    .filter-buttons {
      margin-bottom: 30px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .filter-btn {
      padding: 10px 25px;
      border-radius: 25px;
      border: 2px solid var(--primary);
      background: transparent;
      color: var(--primary);
      font-weight: 600;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .filter-btn:hover {
      background: var(--gradient);
      color: #fff;
      border-color: transparent;
    }

    .filter-btn.active {
      background: var(--gradient);
      color: #fff;
      border-color: transparent;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #666;
    }

    .empty-state i {
      font-size: 4rem;
      color: #ddd;
      margin-bottom: 20px;
    }

    /* Footer */
    footer {
      background: var(--gradient);
      color: #fff;
      text-align: center;
      padding: 40px 0 20px;
      font-weight: 600;
    }
    footer .social-icons a {
      color: #fff;
      margin: 0 10px;
      font-size: 1.5rem;
      transition: transform 0.3s;
    }
    footer .social-icons a:hover {
      transform: scale(1.2);
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    @media (max-width: 768px) {
      .page-header h1 {
        font-size: 2rem;
      }
      .order-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .order-details {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">GOOD VIBES</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username'] ?? 'User'); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="orders.php"><i class="bi bi-box-seam"></i> My Orders</a></li>
                <?php if (($_SESSION['role'] ?? '') === 'super_admin' || ($_SESSION['role'] ?? '') === 'admin'): ?>
                  <li><a class="dropdown-item" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="container">
      <h1><i class="bi bi-box-seam"></i> My Orders</h1>
      <p class="lead">Track and manage your orders</p>
    </div>
  </div>

  <!-- Orders Section -->
  <section class="orders-section">
    <div class="container">
      <!-- Stats Cards -->
      <div class="row g-4 mb-5">
        <div class="col-md-3">
          <div class="stats-card">
            <h3><?php echo $totalOrders; ?></h3>
            <p>Total Orders</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card">
            <h3><?php echo $pendingOrders; ?></h3>
            <p>Pending</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card">
            <h3><?php echo $completedOrders; ?></h3>
            <p>Completed</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card">
            <h3>₦<?php echo number_format($totalSpent, 2); ?></h3>
            <p>Total Spent</p>
          </div>
        </div>
      </div>

      <!-- Filter Buttons -->
      <div class="filter-buttons">
        <a href="orders.php?status=all" class="filter-btn <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
          All Orders
        </a>
        <a href="orders.php?status=pending" class="filter-btn <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
          Pending
        </a>
        <a href="orders.php?status=completed" class="filter-btn <?php echo $statusFilter === 'completed' ? 'active' : ''; ?>">
          Completed
        </a>
        <a href="orders.php?status=cancelled" class="filter-btn <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">
          Cancelled
        </a>
      </div>

      <!-- Orders List -->
      <?php if ($orders && $orders->num_rows > 0): ?>
        <?php while ($order = $orders->fetch_assoc()): ?>
          <div class="order-card">
            <div class="order-header">
              <div>
                <div class="order-id">Order #<?php echo $order['id']; ?></div>
                <div class="order-date">
                  <i class="bi bi-calendar"></i> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?>
                </div>
              </div>
              <span class="status-badge status-<?php echo $order['status']; ?>">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </div>

            <div class="order-product">
              <?php if ($order['image']): ?>
                <img src="<?php echo htmlspecialchars($order['image']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>">
              <?php endif; ?>
              <div class="order-product-info">
                <h5><?php echo htmlspecialchars($order['product_name'] ?? 'Product Deleted'); ?></h5>
                <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                <?php if (isset($order['unit_price'])): ?>
                  <p><strong>Unit Price:</strong> ₦<?php echo number_format($order['unit_price'], 2); ?></p>
                <?php endif; ?>
              </div>
            </div>

            <div class="order-details">
              <div>
                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary mb-1">
                  <i class="bi bi-eye"></i> View Details
                </a>
                <?php if ($order['status'] === 'pending' && empty($order['receipt_image'])): ?>
                  <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-success ms-1 mb-1">
                    <i class="bi bi-upload"></i> Upload Receipt
                  </a>
                <?php endif; ?>
              </div>
              <div class="order-total">
                Total: ₦<?php echo number_format($order['total_price'], 2); ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <h3>No orders found</h3>
          <p>You haven't placed any orders yet.</p>
          <a href="products.php" class="btn btn-vibe mt-3">Browse Products</a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>Connect With Us</p>
      <div class="social-icons mb-3">
        <a href="#"><i class="bi bi-instagram"></i></a>
        <a href="#"><i class="bi bi-tiktok"></i></a>
        <a href="#"><i class="bi bi-twitter-x"></i></a>
      </div>
      <p>&copy; <?php echo date('Y'); ?> Good Vibes. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>