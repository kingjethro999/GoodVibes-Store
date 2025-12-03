<?php
session_start();
include 'db_connect.php';
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products | Good Vibes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
  <style>
    :root {
      --primary-color: linear-gradient(90deg, #6a00b9, #c30075);
      --accent1: #fcd34d;
      --accent2: #ef4444;
      --accent3: #22c55e;
      --accent4: #3b82f6;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #faf7ff;
    }

    .navbar {
      background: linear-gradient(90deg, #6a00b9, #c30075);
    }
    .navbar .nav-link,
    .navbar-brand {
      color: #fff !important;
    }

    footer {
      background: #1b0033;
      color: #fff;
      text-align: center;
      padding: 15px 0;
    }

    .product-card {
      background: linear-gradient(145deg, #ffffff, #f3e8ff);
      border: none;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: all 0.4s ease;
      position: relative;
    }
    .product-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 15px 25px rgba(0,0,0,0.15);
    }
    .product-card img {
      width: 100%;
      height: 280px;
      object-fit: cover;
      border-bottom: 4px solid var(--accent1);
      cursor: pointer;
    }
    .product-info {
      padding: 20px;
      text-align: center;
    }
    .product-info h5 {
      font-weight: 700;
      color: #3d0066;
      margin-bottom: 10px;
    }
    .product-info p {
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 5px;
    }
    .price {
      color: var(--accent2);
      font-weight: bold;
      font-size: 1.1rem;
      margin-top: 8px;
    }
    .btn-buy {
      background: var(--accent3);
      color: white;
      border: none;
      border-radius: 30px;
      padding: 8px 18px;
      transition: background 0.3s ease;
    }
    .btn-buy:hover {
      background: var(--accent4);
    }

    /* Image viewer modal */
    .image-viewer {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      visibility: hidden;
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: 1000;
    }
    .image-viewer.active {
      visibility: visible;
      opacity: 1;
    }
    .image-viewer-content {
      position: relative;
      background: #fff;
      padding: 10px;
      border-radius: 12px;
      max-width: 80%;
      max-height: 85%;
      box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }
    .image-viewer-content img {
      width: 100%;
      height: auto;
      border-radius: 8px;
    }
    .close-btn {
      position: absolute;
      top: -12px;
      right: -12px;
      background: #6a00b9;
      color: #fff;
      border: none;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      font-size: 20px;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/index.php">Good Vibes</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username'] ?? 'User'); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <?php if (($_SESSION['role'] ?? '') === 'super_admin' || ($_SESSION['role'] ?? '') === 'admin'): ?>
                  <li><a class="dropdown-item" href="/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Products from DB -->
  <section class="py-5">
    <div class="container">
      <h2 class="fw-bold text-center mb-5" style="color:#3d0066;">Our Collection</h2>
      <div class="row g-4">
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="product-card">
              <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['product_name']; ?>" class="view-img">
              <div class="product-info">
                <h5><?php echo $row['product_name']; ?></h5>
                <p><?php echo $row['category']; ?></p>
                <p><?php echo $row['description']; ?></p>
                <div class="price">₦<?php echo number_format($row['price']); ?></div>
                <button class="btn-buy mt-2">Buy Now</button>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>

  <!-- Image Viewer -->
  <div class="image-viewer" id="imageViewer">
    <div class="image-viewer-content">
      <button class="close-btn" id="closeBtn">&times;</button>
      <img id="viewerImg" src="" alt="Expanded product">
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Good Vibes. All Rights Reserved.</p>
  </footer>

  <script>
    const cards = document.querySelectorAll('.view-img');
    const viewer = document.getElementById('imageViewer');
    const viewerImg = document.getElementById('viewerImg');
    const closeBtn = document.getElementById('closeBtn');

    cards.forEach(card => {
      card.addEventListener('click', () => {
        viewerImg.src = card.src;
        viewer.classList.add('active');
      });
    });
    closeBtn.addEventListener('click', () => viewer.classList.remove('active'));
    viewer.addEventListener('click', e => {
      if(e.target === viewer) viewer.classList.remove('active');
    });
  </script>
</body>
</html>
