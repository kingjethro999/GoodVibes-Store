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

// Fetch user details for auto-fill
$userStmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();
$userStmt->close();

// Get product ID and type from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$productType = isset($_GET['type']) ? $_GET['type'] : 'product'; // Default to 'product' for backward compatibility

if ($productId <= 0) {
  header('Location: products.php');
  exit;
}

// Validate type
if (!in_array($productType, ['product', 'merch'])) {
  $productType = 'product';
}

// Determine which table to query
$tableName = ($productType === 'merch') ? 'merch' : 'products';

// Fetch product/merch details
$stmt = $conn->prepare("SELECT * FROM $tableName WHERE id = ?");
$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
  header('Location: products.php');
  exit;
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $quantity = intval($_POST['quantity']);
  $paymentMethod = $_POST['payment_method'];
  $shippingAddress = trim($_POST['shipping_address']);
  $shippingName = trim($_POST['shipping_name']);
  $shippingPhone = trim($_POST['shipping_phone']);
  $shippingEmail = trim($_POST['shipping_email']);
  
  // Optional second contact person
  $shippingName2 = trim($_POST['shipping_name2'] ?? '');
  $shippingPhone2 = trim($_POST['shipping_phone2'] ?? '');
  $shippingEmail2 = trim($_POST['shipping_email2'] ?? '');
  
  if ($quantity <= 0) {
    $error = "Quantity must be at least 1.";
  } elseif (empty($shippingAddress)) {
    $error = "Shipping address is required.";
  } elseif (empty($shippingName)) {
    $error = "Shipping name is required.";
  } elseif (empty($shippingPhone)) {
    $error = "Phone number is required.";
  } elseif (empty($shippingEmail)) {
    $error = "Email is required.";
  } elseif (!filter_var($shippingEmail, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email address.";
  } else {
    $totalPrice = $product['price'] * $quantity;
    $shippingCost = $totalPrice >= 10000 ? 0 : 500; // Free shipping over ₦10,000
    $grandTotal = $totalPrice + $shippingCost;
    
    // Insert order with all shipping fields
    $insertOrder = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price, shipping_address, shipping_name, shipping_phone, shipping_email, shipping_name2, shipping_phone2, shipping_email2, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $insertOrder->bind_param('iiidsssssss', $userId, $productId, $quantity, $grandTotal, $shippingAddress, $shippingName, $shippingPhone, $shippingEmail, $shippingName2, $shippingPhone2, $shippingEmail2);
    
    if ($insertOrder->execute()) {
      $orderId = $insertOrder->insert_id;
      $insertOrder->close();
      
      // Handle receipt upload if bank transfer
      if ($paymentMethod === 'bank_transfer') {
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
          $uploadDir = __DIR__ . '/uploads/receipts/';
          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }
          
          $tmp = $_FILES['receipt']['tmp_name'];
          $origName = basename($_FILES['receipt']['name']);
          $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
          $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
          
          if (in_array($ext, $allowed)) {
            $safe = preg_replace("/[^A-Za-z0-9\-]/", '-', pathinfo($origName, PATHINFO_FILENAME));
            $filename = 'receipt-' . $orderId . '-' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            
            if (move_uploaded_file($tmp, $dest)) {
              $receiptPath = 'uploads/receipts/' . $filename;
              $insertReceipt = $conn->prepare("INSERT INTO reciepts (order_id, receipt_image) VALUES (?, ?)");
              $insertReceipt->bind_param('is', $orderId, $receiptPath);
              $insertReceipt->execute();
              $insertReceipt->close();
            }
          }
        }
      }
      
      // Store shipping address (we'll need to add this to orders table or create shipping_addresses table)
      // For now, redirect to order details
      header("Location: order_details.php?id=" . $orderId);
      exit;
    } else {
      $error = "Failed to create order. Please try again.";
    }
  }
}

// Calculate shipping cost
$defaultQuantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$subtotal = $product['price'] * $defaultQuantity;
$shippingCost = $subtotal >= 10000 ? 0 : 500;
$grandTotal = $subtotal + $shippingCost;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout | Good Vibes</title>
  
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

    .checkout-section {
      padding: 100px 0 80px;
      margin-top: 56px;
    }

    .checkout-card {
      background: #fff;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .product-summary {
      display: flex;
      gap: 20px;
      align-items: center;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 15px;
      margin-bottom: 20px;
    }

    .product-summary img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
    }

    .form-label {
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
    }

    .form-control, .form-select {
      border-radius: 10px;
      padding: 12px;
      border: 1px solid #ddd;
    }

    .btn-vibe {
      background: var(--gradient);
      color: #fff;
      border: none;
      padding: 15px 40px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: 0.3s;
      width: 100%;
    }

    .btn-vibe:hover {
      opacity: 0.85;
      transform: translateY(-2px);
      color: #fff;
      box-shadow: 0 5px 15px rgba(106, 0, 244, 0.3);
    }

    .payment-info {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      margin-top: 15px;
    }

    .bank-info {
      background: #fff;
      border: 2px solid var(--primary);
      padding: 15px;
      border-radius: 10px;
      margin-top: 10px;
    }

    .price-summary {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
    }

    .price-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .price-total {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      border-top: 2px solid var(--primary);
      padding-top: 10px;
      margin-top: 10px;
    }

    .user-info-card {
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 15px;
      padding: 20px;
      transition: all 0.3s ease;
    }

    .user-info-card:hover {
      border-color: var(--primary);
      box-shadow: 0 4px 15px rgba(106, 0, 244, 0.1);
    }

    .user-info-display .info-item {
      display: flex;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #dee2e6;
    }

    .user-info-display .info-item:last-child {
      border-bottom: none;
    }

    .info-label {
      font-weight: 600;
      color: #495057;
      min-width: 120px;
      margin-right: 15px;
    }

    .info-label i {
      margin-right: 5px;
      color: var(--primary);
    }

    .info-value {
      color: #222;
      font-size: 1rem;
    }

    .user-info-edit {
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
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

  <!-- Checkout Section -->
  <section class="checkout-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-8">
          <div class="checkout-card">
            <h2 class="mb-4"><i class="bi bi-cart-check"></i> Checkout</h2>

            <?php if ($error): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Product Summary -->
            <div class="product-summary">
              <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
              <div>
                <h5><?php echo htmlspecialchars($product['product_name']); ?></h5>
                <p class="text-muted mb-0">₦<?php echo number_format($product['price'], 2); ?> each</p>
              </div>
            </div>

            <form method="POST" action="" enctype="multipart/form-data">
              <!-- Quantity -->
              <div class="mb-4">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" value="<?php echo $defaultQuantity; ?>" min="1" required onchange="calculateTotal()">
              </div>

              <!-- Shipping Information -->
              <h5 class="mb-3 mt-4">Shipping Information</h5>
              
              <!-- User Info Card -->
              <div class="user-info-card mb-4" id="userInfoCard">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h6 class="mb-2"><i class="bi bi-person-circle"></i> Your Information</h6>
                    <p class="text-muted small mb-0">Pre-filled from your account</p>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleEditMode()">
                    <i class="bi bi-pencil"></i> <span id="editModeText">Edit</span>
                  </button>
                </div>
                
                <div class="user-info-display" id="userInfoDisplay">
                  <div class="info-item">
                    <span class="info-label"><i class="bi bi-person"></i> Full Name:</span>
                    <span class="info-value" id="displayName"><?php echo htmlspecialchars($userData['username'] ?? ''); ?></span>
                  </div>
                  <div class="info-item">
                    <span class="info-label"><i class="bi bi-envelope"></i> Email:</span>
                    <span class="info-value" id="displayEmail"><?php echo htmlspecialchars($userData['email'] ?? ''); ?></span>
                  </div>
                </div>
                
                <div class="user-info-edit" id="userInfoEdit" style="display: none;">
                  <div class="mb-3">
                    <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="shipping_name_edit" class="form-control form-control-sm" value="<?php echo isset($_POST['shipping_name']) ? htmlspecialchars($_POST['shipping_name']) : htmlspecialchars($userData['username'] ?? ''); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                    <input type="email" id="shipping_email_edit" class="form-control form-control-sm" value="<?php echo isset($_POST['shipping_email']) ? htmlspecialchars($_POST['shipping_email']) : htmlspecialchars($userData['email'] ?? ''); ?>" required>
                  </div>
                </div>
              </div>
              
              <!-- Hidden inputs for form submission when in display mode -->
              <input type="hidden" name="shipping_name" id="shipping_name_hidden" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>">
              <input type="hidden" name="shipping_email" id="shipping_email_hidden" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>">

              <div class="mb-4">
                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" name="shipping_phone" class="form-control" placeholder="Enter your phone number" value="<?php echo isset($_POST['shipping_phone']) ? htmlspecialchars($_POST['shipping_phone']) : ''; ?>" required>
              </div>

              <div class="mb-4">
                <label class="form-label">Shipping Address <span class="text-danger">*</span></label>
                <textarea name="shipping_address" class="form-control" rows="3" placeholder="Enter your complete shipping address" required><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
              </div>

              <!-- Optional Second Contact Person -->
              <div class="mb-4">
                <h6 class="mb-3">Alternative Contact Person (Optional)</h6>
                <p class="text-muted small mb-3">If you want us to contact another person regarding this order</p>
                
                <div class="mb-3">
                  <label class="form-label">Name</label>
                  <input type="text" name="shipping_name2" class="form-control" placeholder="Enter alternative contact name" value="<?php echo isset($_POST['shipping_name2']) ? htmlspecialchars($_POST['shipping_name2']) : ''; ?>">
                </div>

                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="shipping_email2" class="form-control" placeholder="Enter alternative contact email" value="<?php echo isset($_POST['shipping_email2']) ? htmlspecialchars($_POST['shipping_email2']) : ''; ?>">
                </div>

                <div class="mb-3">
                  <label class="form-label">Phone Number</label>
                  <input type="tel" name="shipping_phone2" class="form-control" placeholder="Enter alternative contact phone" value="<?php echo isset($_POST['shipping_phone2']) ? htmlspecialchars($_POST['shipping_phone2']) : ''; ?>">
                </div>
              </div>

              <!-- Payment Method -->
              <div class="mb-4">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select" required onchange="toggleReceiptUpload()">
                  <option value="cash_on_delivery">Cash on Delivery</option>
                  <option value="bank_transfer">Bank Transfer</option>
                </select>
              </div>

              <!-- Bank Transfer Info -->
              <div id="bankTransferInfo" class="payment-info" style="display: none;">
                <h6 class="mb-3"><i class="bi bi-bank"></i> Bank Transfer Details</h6>
                <div class="bank-info">
                  <p class="mb-2"><strong>Bank:</strong> GTBank</p>
                  <p class="mb-2"><strong>Account Number:</strong> 0140150361</p>
                  <p class="mb-0"><strong>Account Name:</strong> INYANG DAVID NSIKAK</p>
                </div>
                <div class="mt-3">
                  <label class="form-label">Upload Payment Receipt</label>
                  <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                  <small class="text-muted">Upload screenshot or PDF of your bank transfer receipt</small>
                </div>
              </div>

              <button type="submit" class="btn btn-vibe mt-4">
                <i class="bi bi-check-circle"></i> Place Order
              </button>
            </form>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
          <div class="checkout-card">
            <h5 class="mb-4">Order Summary</h5>
            <div class="price-summary">
              <div class="price-row">
                <span>Subtotal:</span>
                <span id="subtotal">₦<?php echo number_format($subtotal, 2); ?></span>
              </div>
              <div class="price-row">
                <span>Shipping:</span>
                <span id="shipping">₦<?php echo number_format($shippingCost, 2); ?></span>
              </div>
              <div class="price-row price-total">
                <span>Total:</span>
                <span id="total">₦<?php echo number_format($grandTotal, 2); ?></span>
              </div>
            </div>
            
            <?php if ($grandTotal >= 10000): ?>
              <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle"></i> Free Shipping Applied!
              </div>
            <?php else: ?>
              <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i> Add ₦<?php echo number_format(10000 - $grandTotal, 2); ?> more for free shipping!
              </div>
            <?php endif; ?>

            <div class="mt-4">
              <p class="small text-muted"><i class="bi bi-shield-check"></i> Secure checkout</p>
              <p class="small text-muted"><i class="bi bi-truck"></i> Estimated delivery: 3-5 business days</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function calculateTotal() {
      const quantity = parseInt(document.querySelector('input[name="quantity"]').value) || 1;
      const unitPrice = <?php echo $product['price']; ?>;
      const subtotal = quantity * unitPrice;
      const shippingCost = subtotal >= 10000 ? 0 : 500;
      const total = subtotal + shippingCost;

      document.getElementById('subtotal').textContent = '₦' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      document.getElementById('shipping').textContent = '₦' + shippingCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      document.getElementById('total').textContent = '₦' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function toggleReceiptUpload() {
      const paymentMethod = document.querySelector('select[name="payment_method"]').value;
      const bankInfo = document.getElementById('bankTransferInfo');
      if (paymentMethod === 'bank_transfer') {
        bankInfo.style.display = 'block';
      } else {
        bankInfo.style.display = 'none';
      }
    }

    function toggleEditMode() {
      const displayDiv = document.getElementById('userInfoDisplay');
      const editDiv = document.getElementById('userInfoEdit');
      const editText = document.getElementById('editModeText');
      const hiddenName = document.getElementById('shipping_name_hidden');
      const hiddenEmail = document.getElementById('shipping_email_hidden');
      const nameInput = document.getElementById('shipping_name_edit');
      const emailInput = document.getElementById('shipping_email_edit');
      
      if (displayDiv.style.display !== 'none') {
        // Switch to edit mode
        displayDiv.style.display = 'none';
        editDiv.style.display = 'block';
        editText.textContent = 'Cancel';
        nameInput.value = hiddenName.value;
        emailInput.value = hiddenEmail.value;
        nameInput.focus();
      } else {
        // Switch back to display mode
        displayDiv.style.display = 'block';
        editDiv.style.display = 'none';
        editText.textContent = 'Edit';
        // Update hidden fields with current values
        hiddenName.value = nameInput.value;
        hiddenEmail.value = emailInput.value;
        // Update display
        document.getElementById('displayName').textContent = nameInput.value;
        document.getElementById('displayEmail').textContent = emailInput.value;
      }
    }

    // Update hidden fields when edit inputs change and before form submission
    document.addEventListener('DOMContentLoaded', function() {
      const nameInput = document.getElementById('shipping_name_edit');
      const emailInput = document.getElementById('shipping_email_edit');
      const form = document.querySelector('form');
      
      if (nameInput) {
        nameInput.addEventListener('input', function() {
          document.getElementById('shipping_name_hidden').value = this.value;
        });
      }
      
      if (emailInput) {
        emailInput.addEventListener('input', function() {
          document.getElementById('shipping_email_hidden').value = this.value;
        });
      }
      
      // Update hidden fields before form submission
      if (form) {
        form.addEventListener('submit', function() {
          const displayDiv = document.getElementById('userInfoDisplay');
          if (displayDiv.style.display !== 'none') {
            // Using display values
            document.getElementById('shipping_name_hidden').value = document.getElementById('displayName').textContent;
            document.getElementById('shipping_email_hidden').value = document.getElementById('displayEmail').textContent;
          } else {
            // Using edit values
            document.getElementById('shipping_name_hidden').value = nameInput.value;
            document.getElementById('shipping_email_hidden').value = emailInput.value;
          }
        });
      }
    });
  </script>
</body>
</html>