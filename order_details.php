<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Get order ID from URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId <= 0) {
  header('Location: orders.php');
  exit;
}

// Handle receipt upload from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_receipt'])) {
  if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    $error = "Please select a valid receipt file to upload.";
  } else {
    $uploadDir = __DIR__ . '/uploads/receipts/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    $tmp = $_FILES['receipt']['tmp_name'];
    $origName = basename($_FILES['receipt']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array($ext, $allowed, true)) {
      $error = "Invalid file type. Please upload JPG, PNG, or PDF.";
    } else {
      $safe = preg_replace("/[^A-Za-z0-9\-]/", '-', pathinfo($origName, PATHINFO_FILENAME));
      $filename = 'receipt-' . $orderId . '-' . time() . '.' . $ext;
      $dest = $uploadDir . $filename;

      if (!move_uploaded_file($tmp, $dest)) {
        $error = "Failed to upload receipt. Please try again.";
      } else {
        $receiptPath = 'uploads/receipts/' . $filename;

        // Check if a receipt already exists
        $check = $conn->prepare("SELECT id FROM reciepts WHERE order_id = ? LIMIT 1");
        $check->bind_param('i', $orderId);
        $check->execute();
        $res = $check->get_result();

        if ($res && $res->num_rows > 0) {
          $existing = $res->fetch_assoc();
          $update = $conn->prepare("UPDATE reciepts SET receipt_image = ?, created_at = NOW() WHERE id = ?");
          $update->bind_param('si', $receiptPath, $existing['id']);
          $ok = $update->execute();
          $update->close();
        } else {
          $insert = $conn->prepare("INSERT INTO reciepts (order_id, receipt_image) VALUES (?, ?)");
          $insert->bind_param('is', $orderId, $receiptPath);
          $ok = $insert->execute();
          $insert->close();
        }

        $check->close();

        if (isset($ok) && $ok) {
          $success = "Receipt uploaded successfully. We will confirm your payment soon.";
        } else {
          $error = "Could not save receipt. Please try again.";
        }
      }
    }
  }
}

// Fetch order details with product and receipt info
$order = $conn->query("
  SELECT o.*, p.product_name, p.image, p.price as unit_price, p.description,
         r.receipt_image, r.created_at as receipt_date
  FROM orders o
  LEFT JOIN products p ON o.product_id = p.id
  LEFT JOIN reciepts r ON o.id = r.order_id
  WHERE o.id = $orderId AND o.user_id = $userId
")->fetch_assoc();

if (!$order) {
  header('Location: orders.php');
  exit;
}

// Calculate estimated delivery date (3-5 business days)
$orderDate = new DateTime($order['order_date']);
$estimatedDelivery = clone $orderDate;
$estimatedDelivery->modify('+5 days');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Details | Good Vibes</title>
  
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

    .order-details-section {
      padding: 100px 0 80px;
      margin-top: 56px;
    }

    .order-card {
      background: #fff;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .order-header {
      border-bottom: 2px solid #eee;
      padding-bottom: 20px;
      margin-bottom: 30px;
    }

    .order-id {
      font-size: 1.8rem;
      font-weight: 700;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .status-badge {
      padding: 10px 25px;
      border-radius: 25px;
      font-weight: 600;
      display: inline-block;
      margin-top: 10px;
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

    .product-detail {
      display: flex;
      gap: 20px;
      align-items: center;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 15px;
      margin-bottom: 20px;
    }

    .product-detail img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .receipt-preview {
      max-width: 300px;
      border-radius: 10px;
      margin-top: 10px;
    }

    .btn-vibe {
      background: var(--gradient);
      color: #fff;
      border: none;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
    }

    .btn-vibe:hover {
      opacity: 0.85;
      color: #fff;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
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

  <!-- Order Details Section -->
  <section class="order-details-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-8">
          <div class="order-card">
            <?php if ($success): ?>
              <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
              </div>
            <?php endif; ?>

            <?php if ($error): ?>
              <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
              </div>
            <?php endif; ?>

            <div class="order-header">
              <div class="order-id">Order #<?php echo $order['id']; ?></div>
              <span class="status-badge status-<?php echo $order['status']; ?>">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </div>

            <!-- Product Details -->
            <h5 class="mb-3">Product Details</h5>
            <div class="product-detail">
              <?php if ($order['image']): ?>
                <img src="<?php echo htmlspecialchars($order['image']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>">
              <?php endif; ?>
              <div>
                <h5><?php echo htmlspecialchars($order['product_name']); ?></h5>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($order['description']); ?></p>
                <p class="mb-0"><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                <p class="mb-0"><strong>Unit Price:</strong> ₦<?php echo number_format($order['unit_price'], 2); ?></p>
              </div>
            </div>

            <!-- Order Information -->
            <h5 class="mb-3 mt-4">Order Information</h5>
            <div class="info-row">
              <span><i class="bi bi-calendar"></i> Order Date</span>
              <strong><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-box-seam"></i> Quantity</span>
              <strong><?php echo $order['quantity']; ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-cash-coin"></i> Subtotal</span>
              <strong>₦<?php echo number_format($order['unit_price'] * $order['quantity'], 2); ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-truck"></i> Shipping</span>
              <strong>₦<?php echo number_format($order['total_price'] - ($order['unit_price'] * $order['quantity']), 2); ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-currency-exchange"></i> Total Amount</span>
              <strong class="text-primary" style="font-size: 1.2rem;">₦<?php echo number_format($order['total_price'], 2); ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-calendar-event"></i> Estimated Delivery</span>
              <strong><?php echo $estimatedDelivery->format('F j, Y'); ?></strong>
            </div>

            <!-- Bank transfer instructions -->
            <h5 class="mb-3 mt-4">Payment Instructions</h5>
            <div class="info-row">
              <span><i class="bi bi-bank"></i> Bank</span>
              <strong>GTBank</strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-credit-card"></i> Account Number</span>
              <strong>0140150361</strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-person-badge"></i> Account Name</span>
              <strong>INYANG DAVID NSIKAK</strong>
            </div>
            <p class="mt-2 text-muted">
              Please transfer the total amount to the account above and upload your payment receipt below so we can verify and approve your order.
            </p>

            <!-- Shipping Information -->
            <h5 class="mb-3 mt-4">Shipping Information</h5>
            <div class="info-row">
              <span><i class="bi bi-person"></i> Name</span>
              <strong><?php echo htmlspecialchars($order['shipping_name'] ?? 'N/A'); ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-envelope"></i> Email</span>
              <strong><?php echo htmlspecialchars($order['shipping_email'] ?? 'N/A'); ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-telephone"></i> Phone</span>
              <strong><?php echo htmlspecialchars($order['shipping_phone'] ?? 'N/A'); ?></strong>
            </div>
            <div class="info-row">
              <span><i class="bi bi-geo-alt"></i> Address</span>
              <strong><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'N/A')); ?></strong>
            </div>

            <!-- Alternative Contact Person -->
            <?php if (!empty($order['shipping_name2']) || !empty($order['shipping_phone2']) || !empty($order['shipping_email2'])): ?>
              <h6 class="mb-3 mt-4">Alternative Contact Person</h6>
              <?php if (!empty($order['shipping_name2'])): ?>
                <div class="info-row">
                  <span><i class="bi bi-person"></i> Name</span>
                  <strong><?php echo htmlspecialchars($order['shipping_name2']); ?></strong>
                </div>
              <?php endif; ?>
              <?php if (!empty($order['shipping_email2'])): ?>
                <div class="info-row">
                  <span><i class="bi bi-envelope"></i> Email</span>
                  <strong><?php echo htmlspecialchars($order['shipping_email2']); ?></strong>
                </div>
              <?php endif; ?>
              <?php if (!empty($order['shipping_phone2'])): ?>
                <div class="info-row">
                  <span><i class="bi bi-telephone"></i> Phone</span>
                  <strong><?php echo htmlspecialchars($order['shipping_phone2']); ?></strong>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <!-- Receipt Section -->
            <?php if ($order['receipt_image']): ?>
              <h5 class="mb-3 mt-4">Payment Receipt</h5>
              <div class="info-row">
                <span><i class="bi bi-receipt"></i> Receipt Uploaded</span>
                <span><?php echo date('F j, Y g:i A', strtotime($order['receipt_date'])); ?></span>
              </div>
              <div class="mt-3">
                <img src="<?php echo htmlspecialchars($order['receipt_image']); ?>" alt="Receipt" class="receipt-preview img-fluid">
                <br>
                <a href="<?php echo htmlspecialchars($order['receipt_image']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                  <i class="bi bi-download"></i> View Full Receipt
                </a>
              </div>
            <?php elseif ($order['status'] === 'pending'): ?>
              <h5 class="mb-3 mt-4">Upload Payment Receipt</h5>
              <p class="text-muted">
                After making your transfer, upload a clear screenshot or PDF of your payment receipt so we can confirm your order.
              </p>
              <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                  <label class="form-label">Receipt File (JPG, PNG, or PDF)</label>
                  <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
                </div>
                <button type="submit" name="upload_receipt" class="btn btn-vibe">
                  <i class="bi bi-upload"></i> Upload Receipt
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <!-- Order Status Card -->
        <div class="col-lg-4">
          <div class="order-card">
            <h5 class="mb-4">Order Status</h5>
            <div class="status-timeline">
              <div class="info-row">
                <span><i class="bi bi-check-circle-fill text-success"></i> Order Placed</span>
                <span class="text-success">✓</span>
              </div>
              <?php if ($order['status'] === 'pending'): ?>
                <div class="info-row">
                  <span><i class="bi bi-clock text-warning"></i> Payment Confirmation</span>
                  <span class="text-warning">Pending</span>
                </div>
                <div class="info-row">
                  <span><i class="bi bi-hourglass-split"></i> Processing</span>
                  <span class="text-muted">-</span>
                </div>
                <div class="info-row">
                  <span><i class="bi bi-truck"></i> Shipped</span>
                  <span class="text-muted">-</span>
                </div>
                <div class="info-row">
                  <span><i class="bi bi-check-circle"></i> Delivered</span>
                  <span class="text-muted">-</span>
                </div>
              <?php elseif ($order['status'] === 'completed'): ?>
                <div class="info-row">
                  <span><i class="bi bi-check-circle-fill text-success"></i> Payment Confirmed</span>
                  <span class="text-success">✓</span>
                </div>
                <div class="info-row">
                  <span><i class="bi bi-check-circle-fill text-success"></i> Processed</span>
                  <span class="text-success">✓</span>
                </div>
                <div class="info-row">
                  <span><i class="bi bi-check-circle-fill text-success"></i> Shipped</span>
                  <span class="text-success">✓</span>
                </div>
                <div class="info-row">
                  <span><i class="bi bi-check-circle-fill text-success"></i> Delivered</span>
                  <span class="text-success">✓</span>
                </div>
              <?php else: ?>
                <div class="info-row">
                  <span><i class="bi bi-x-circle-fill text-danger"></i> Order Cancelled</span>
                  <span class="text-danger">✗</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="mt-4">
              <a href="orders.php" class="btn btn-vibe w-100">
                <i class="bi bi-arrow-left"></i> Back to Orders
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>