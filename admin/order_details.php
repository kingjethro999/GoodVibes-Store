<?php
session_start();
require '../db_connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}

$error = '';
$success = '';

// Handle receipt approval and status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['approve_receipt'])) {
    $orderId = intval($_POST['order_id']);
    $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    $stmt->bind_param('i', $orderId);
    if ($stmt->execute()) {
      $success = "Receipt approved! Order status updated to completed.";
    } else {
      $error = "Failed to approve receipt.";
    }
    $stmt->close();
  } elseif (isset($_POST['update_status'])) {
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
  } elseif (isset($_POST['cancel_order'])) {
    $orderId = intval($_POST['order_id']);
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param('i', $orderId);
    if ($stmt->execute()) {
      $success = "Order cancelled successfully!";
    } else {
      $error = "Failed to cancel order.";
    }
    $stmt->close();
  }
}

// Get order ID from URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId <= 0) {
  header('Location: process.php');
  exit;
}

// Fetch order details to determine product type
$orderInfo = $conn->query("SELECT product_type FROM orders WHERE id = $orderId")->fetch_assoc();

if (!$orderInfo) {
  header('Location: process.php');
  exit;
}

$productType = $orderInfo['product_type'] ?? 'product';
$productTable = ($productType === 'merch') ? 'merch' : 'products';

// Fetch order details with product, user, and receipt info
$order = $conn->query("
  SELECT o.*, u.username as customer_name, u.email as customer_email,
         p.product_name, p.price as product_price, p.image, p.description,
         r.receipt_image, r.created_at as receipt_date
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  LEFT JOIN $productTable p ON o.product_id = p.id
  LEFT JOIN reciepts r ON o.id = r.order_id
  WHERE o.id = $orderId
")->fetch_assoc();

if (!$order) {
  header('Location: process.php');
  exit;
}

// Get order history (status changes could be tracked in a separate table, but for now we'll show order details)
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

  .order-detail-header {
    border-bottom: 2px solid #eee;
    padding-bottom: 20px;
    margin-bottom: 30px;
  }

  .status-badge {
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    display: inline-block;
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

  .receipt-preview {
    max-width: 400px;
    border-radius: 10px;
    margin-top: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }

  .action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn-approve {
    background: #28a745;
    color: #fff;
    border: none;
  }

  .btn-cancel {
    background: #dc3545;
    color: #fff;
    border: none;
  }

  /* Responsive improvements */
  @media (max-width: 991.98px) {
    .col-lg-8, .col-lg-4 {
      flex: 0 0 100%;
      max-width: 100%;
    }
  }

  @media (max-width: 768px) {
    .gv-card {
      padding: 15px;
    }

    .order-detail-header {
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .order-detail-header h4 {
      font-size: 1.25rem;
    }

    .status-badge {
      padding: 8px 16px;
      font-size: 0.875rem;
    }

    .receipt-preview {
      max-width: 100%;
    }

    h5 {
      font-size: 1.1rem;
    }
  }

  @media (max-width: 576px) {
    .order-detail-header .d-flex {
      flex-direction: column;
      align-items: flex-start !important;
      gap: 10px;
    }

    .col-md-3, .col-md-6, .col-md-9 {
      flex: 0 0 100%;
      max-width: 100%;
      margin-bottom: 15px;
    }

    .btn {
      font-size: 0.875rem;
      padding: 0.5rem 0.75rem;
    }
  }
</style>

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="mb-0">Order Details</h2>
      <p class="text-muted">Order #<?php echo $order['id']; ?></p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?php echo $success; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?php echo $error; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row">
    <!-- Order Information -->
    <div class="col-lg-8">
      <div class="gv-card">
        <div class="order-detail-header">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4>Order #<?php echo $order['id']; ?></h4>
              <p class="text-muted mb-0">Placed on <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
            </div>
            <span class="status-badge status-<?php echo $order['status']; ?>">
              <?php echo ucfirst($order['status']); ?>
            </span>
          </div>
        </div>

        <!-- Customer Information -->
        <h5 class="mb-3">Customer Information</h5>
        <div class="row mb-4">
          <div class="col-md-6">
            <p><strong>Account Name:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></p>
            <p><strong>Account Email:</strong> <?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></p>
          </div>
        </div>

        <hr>

        <!-- Shipping Information -->
        <h5 class="mb-3">Shipping Information</h5>
        <div class="row mb-4">
          <div class="col-md-6">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['shipping_name'] ?? 'N/A'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['shipping_email'] ?? 'N/A'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone'] ?? 'N/A'); ?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Address:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'N/A')); ?></p>
          </div>
        </div>

        <!-- Alternative Contact Person -->
        <?php if (!empty($order['shipping_name2']) || !empty($order['shipping_phone2']) || !empty($order['shipping_email2'])): ?>
          <hr>
          <h5 class="mb-3">Alternative Contact Person</h5>
          <div class="row mb-4">
            <div class="col-md-6">
              <?php if (!empty($order['shipping_name2'])): ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['shipping_name2']); ?></p>
              <?php endif; ?>
              <?php if (!empty($order['shipping_email2'])): ?>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['shipping_email2']); ?></p>
              <?php endif; ?>
              <?php if (!empty($order['shipping_phone2'])): ?>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone2']); ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <hr>

        <!-- Product Details -->
        <h5 class="mb-3">Product Details</h5>
        <div class="row mb-4">
          <div class="col-md-3">
            <?php if ($order['image']): ?>
              <img src="../<?php echo htmlspecialchars($order['image']); ?>" alt="Product" class="img-fluid rounded">
            <?php endif; ?>
          </div>
          <div class="col-md-9">
            <h5><?php echo htmlspecialchars($order['product_name']); ?></h5>
            <p class="text-muted"><?php echo htmlspecialchars($order['description']); ?></p>
            <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
            <p><strong>Unit Price:</strong> ₦<?php echo number_format($order['product_price'], 2); ?></p>
            <p><strong>Total Price:</strong> ₦<?php echo number_format($order['total_price'], 2); ?></p>
          </div>
        </div>

        <!-- Receipt Section -->
        <?php if ($order['receipt_image']): ?>
          <hr>
          <h5 class="mb-3">Payment Receipt</h5>
          <p><strong>Uploaded:</strong> <?php echo date('F j, Y g:i A', strtotime($order['receipt_date'])); ?></p>
          <img src="../<?php echo htmlspecialchars($order['receipt_image']); ?>" alt="Receipt" class="receipt-preview img-fluid">
          <br>
          <a href="../<?php echo htmlspecialchars($order['receipt_image']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
            <i class="bi bi-download"></i> View Full Receipt
          </a>
        <?php else: ?>
          <hr>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> No receipt uploaded yet. Waiting for customer to upload payment receipt.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Actions Panel -->
    <div class="col-lg-4">
      <div class="gv-card">
        <h5 class="mb-4">Order Actions</h5>

        <!-- Approve Receipt -->
        <?php if ($order['receipt_image'] && $order['status'] === 'pending'): ?>
          <form method="POST" action="" class="mb-3">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
            <button type="submit" name="approve_receipt" class="btn btn-approve w-100 mb-2">
              <i class="bi bi-check-circle"></i> Approve Receipt & Complete Order
            </button>
          </form>
        <?php endif; ?>

        <!-- Update Status -->
        <form method="POST" action="" class="mb-3">
          <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
          <label class="form-label"><strong>Update Order Status:</strong></label>
          <select name="status" class="form-select mb-3" required>
            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          </select>
          <button type="submit" name="update_status" class="btn btn-primary w-100 mb-2">
            <i class="bi bi-arrow-repeat"></i> Update Status
          </button>
        </form>

        <!-- Cancel Order -->
        <?php if ($order['status'] !== 'cancelled'): ?>
          <form method="POST" action="" onsubmit="return confirm('Are you sure you want to cancel this order?');">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
            <button type="submit" name="cancel_order" class="btn btn-cancel w-100">
              <i class="bi bi-x-circle"></i> Cancel Order
            </button>
          </form>
        <?php endif; ?>

        <hr class="my-4">

        <div class="text-center">
          <a href="process.php" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left"></i> Back to Orders
          </a>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="gv-card">
        <h5 class="mb-3">Order Summary</h5>
        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal:</span>
          <strong>₦<?php echo number_format($order['product_price'] * $order['quantity'], 2); ?></strong>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Shipping:</span>
          <strong>₦<?php echo number_format($order['total_price'] - ($order['product_price'] * $order['quantity']), 2); ?></strong>
        </div>
        <hr>
        <div class="d-flex justify-content-between">
          <span><strong>Total:</strong></span>
          <strong class="text-primary" style="font-size: 1.2rem;">₦<?php echo number_format($order['total_price'], 2); ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../theme/footer.php'; ?>