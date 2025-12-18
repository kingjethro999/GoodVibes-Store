<?php
session_start();
include 'db_connect.php';

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

// Get related products/merch (same category, same type, excluding current item)
$relatedProducts = $conn->query("
  SELECT * FROM $tableName 
  WHERE category = '" . $conn->real_escape_string($product['category']) . "' 
  AND id != $productId 
  LIMIT 4
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($product['product_name']); ?> | 3ED.I SOCIETY</title>
  
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
      background-color: #fff;
      color: #222;
      overflow-x: hidden;
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

    /* Product Detail Section */
    .product-detail-section {
      padding: 100px 0 80px;
    }

    .product-image {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      background: #f8f9fa;
    }

    .product-image img {
      width: 100%;
      height: auto;
      display: block;
    }

    .product-info {
      padding: 20px 0;
    }

    .product-info h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 2.5rem;
      color: #222;
      margin-bottom: 15px;
    }

    .product-category {
      display: inline-block;
      background: var(--gradient);
      color: #fff;
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .product-price {
      font-size: 2rem;
      font-weight: 700;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin: 20px 0;
    }

    .product-description {
      font-size: 1.1rem;
      line-height: 1.8;
      color: #555;
      margin: 30px 0;
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
      display: inline-block;
      text-decoration: none;
    }

    .btn-vibe:hover {
      opacity: 0.85;
      transform: translateY(-2px);
      color: #fff;
      box-shadow: 0 5px 15px rgba(106, 0, 244, 0.3);
    }

    .btn-outline-vibe {
      border: 2px solid var(--primary);
      color: var(--primary);
      background: transparent;
      padding: 15px 40px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: 0.3s;
    }

    .btn-outline-vibe:hover {
      background: var(--gradient);
      color: #fff;
      border-color: transparent;
    }

    /* Related Products */
    .related-section {
      padding: 80px 0;
      background: #faf7ff;
    }

    .section-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 2.5rem;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 50px;
    }

    .product-card {
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 100%;
    }

    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .product-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }

    .product-card-body {
      padding: 20px;
      text-align: center;
    }

    .product-card-body h5 {
      font-weight: 700;
      color: #222;
      margin-bottom: 10px;
    }

    .product-card-body .price {
      color: var(--primary);
      font-weight: 700;
      font-size: 1.2rem;
      margin: 10px 0;
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

    /* Breadcrumb */
    .breadcrumb-nav {
      padding: 20px 0;
      background: transparent;
    }

    .breadcrumb-nav a {
      color: var(--primary);
      text-decoration: none;
    }

    .breadcrumb-nav a:hover {
      text-decoration: underline;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    @media (max-width: 768px) {
      .product-info h1 {
        font-size: 2rem;
      }
      .product-price {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">3ED.I SOCIETY</a>
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

  <!-- Breadcrumb -->
  <div class="breadcrumb-nav">
    <div class="container">
      <a href="index.php">Home</a> / 
      <a href="products.php"><?php echo ($productType === 'merch') ? 'Merch' : 'Products'; ?></a> / 
      <span><?php echo htmlspecialchars($product['product_name']); ?></span>
    </div>
  </div>

  <!-- Product Detail Section -->
  <section class="product-detail-section">
    <div class="container">
      <div class="row g-5">
        <!-- Product Image -->
        <div class="col-md-6">
          <div class="product-image">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
          </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-6">
          <div class="product-info">
            <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
            <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
            <div class="product-price">₦<?php echo number_format($product['price'], 2); ?></div>
            <p class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <div class="product-actions mt-4">
              <a href="checkout.php?id=<?php echo $product['id']; ?>&type=<?php echo $productType; ?>" class="btn btn-vibe">
                <i class="bi bi-bag-check"></i> Buy Now
              </a>
            </div>

            <div class="product-meta mt-5">
              <p><i class="bi bi-calendar"></i> <strong>Added:</strong> <?php echo date('F j, Y', strtotime($product['created_at'])); ?></p>
              <p><i class="bi bi-shield-check"></i> <strong>Quality Guaranteed</strong></p>
              <p><i class="bi bi-truck"></i> <strong>Free Shipping</strong> on orders over ₦10,000</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Related Products -->
  <?php if ($relatedProducts && $relatedProducts->num_rows > 0): ?>
    <section class="related-section">
      <div class="container">
        <h2 class="section-title text-center">Related Products</h2>
        <div class="row g-4">
          <?php while ($related = $relatedProducts->fetch_assoc()): ?>
            <div class="col-md-3">
              <div class="product-card">
                <a href="product_details.php?id=<?php echo $related['id']; ?>&type=<?php echo $productType; ?>" style="text-decoration: none; color: inherit;">
                  <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['product_name']); ?>">
                  <div class="product-card-body">
                    <h5><?php echo htmlspecialchars($related['product_name']); ?></h5>
                    <div class="price">₦<?php echo number_format($related['price'], 2); ?></div>
                    <a href="product_details.php?id=<?php echo $related['id']; ?>&type=<?php echo $productType; ?>" class="btn btn-sm btn-vibe mt-2">View Details</a>
                  </div>
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>Connect With Us</p>
      <div class="social-icons mb-3">
        <a href="#"><i class="bi bi-instagram"></i></a>
        <a href="#"><i class="bi bi-tiktok"></i></a>
        <a href="#"><i class="bi bi-twitter-x"></i></a>
      </div>
      <p>&copy; <?php echo date('Y'); ?> 3ED.I SOCIETY. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
