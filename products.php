<?php
session_start();
include 'db_connect.php';
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
$merch = $conn->query("SELECT * FROM merch ORDER BY id DESC");

// Check URL parameter for active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'collections';
$collectionsActive = ($activeTab === 'collections') ? 'active' : '';
$merchActive = ($activeTab === 'merch') ? 'active' : '';
$collectionsShow = ($activeTab === 'collections') ? 'show active' : '';
$merchShow = ($activeTab === 'merch') ? 'show active' : '';
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

    /* Sticky Tab Navigation */
    .tab-navigation {
      position: sticky;
      top: 0;
      z-index: 999;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-top: 20px;
    }

    .tab-navigation .nav-tabs {
      border-bottom: none;
      display: flex;
      justify-content: center;
      padding: 15px 0;
    }

    .tab-navigation .nav-tabs .nav-link {
      border: none;
      color: #666;
      font-weight: 600;
      font-size: 1.1rem;
      padding: 12px 40px;
      margin: 0 10px;
      border-radius: 50px;
      transition: all 0.3s ease;
      background: transparent;
    }

    .tab-navigation .nav-tabs .nav-link:hover {
      background: #f3e8ff;
      color: #6a00b9;
    }

    .tab-navigation .nav-tabs .nav-link.active {
      background: linear-gradient(90deg, #6a00b9, #c30075);
      color: #fff;
      box-shadow: 0 4px 15px rgba(106, 0, 185, 0.3);
    }

    /* Tab content */
    .tab-pane {
      min-height: 400px;
    }

    .product-card {
      background: linear-gradient(145deg, #ffffff, #f3e8ff);
      border: none;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: all 0.4s ease;
      position: relative;
      height: 100%;
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
      text-decoration: none;
      display: inline-block;
    }
    .btn-buy:hover {
      background: var(--accent4);
      color: white;
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

    @media (max-width: 768px) {
      .tab-navigation .nav-tabs .nav-link {
        font-size: 1rem;
        padding: 10px 25px;
        margin: 0 5px;
      }
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
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
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

  <!-- Sticky Tab Navigation -->
  <div class="tab-navigation">
    <div class="container">
      <ul class="nav nav-tabs" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link <?php echo $collectionsActive; ?>" id="collections-tab" data-bs-toggle="tab" data-bs-target="#collections" type="button" role="tab" data-tab="collections">
            <i class="bi bi-grid"></i> Collections
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?php echo $merchActive; ?>" id="merch-tab" data-bs-toggle="tab" data-bs-target="#merch" type="button" role="tab" data-tab="merch">
            <i class="bi bi-bag"></i> Merch
          </button>
        </li>
      </ul>
    </div>
  </div>

  <!-- Tab Content -->
  <section class="py-5">
    <div class="container">
      <div class="tab-content" id="productTabContent">
        
        <!-- Collections Tab -->
        <div class="tab-pane fade <?php echo $collectionsShow; ?>" id="collections" role="tabpanel">
          <h2 class="fw-bold text-center mb-5" style="color:#3d0066;">Our Collections</h2>
          <div class="row g-4" id="collectionsGrid">
            <?php while($row = $products->fetch_assoc()): ?>
              <div class="col-md-4">
                <div class="product-card">
                  <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="view-img">
                  <div class="product-info">
                    <h5><?php echo htmlspecialchars($row['product_name']); ?></h5>
                    <p><?php echo htmlspecialchars($row['category'] ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($row['description'] ?? ''); ?></p>
                    <div class="price">₦<?php echo number_format($row['price']); ?></div>
                    <a href="product_details.php?id=<?php echo $row['id']; ?>&type=product" class="btn-buy mt-2">View Details</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

        <!-- Merch Tab -->
        <div class="tab-pane fade <?php echo $merchShow; ?>" id="merch" role="tabpanel">
          <h2 class="fw-bold text-center mb-5" style="color:#3d0066;">Our Merch</h2>
          <div class="row g-4" id="merchGrid">
            <?php while($item = $merch->fetch_assoc()): ?>
              <div class="col-md-4">
                <div class="product-card">
                  <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="view-img">
                  <div class="product-info">
                    <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                    <p><?php echo htmlspecialchars($item['category'] ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                    <div class="price">₦<?php echo number_format($item['price']); ?></div>
                    <a href="product_details.php?id=<?php echo $item['id']; ?>&type=merch" class="btn-buy mt-2">View Details</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Update URL when tab is clicked
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const tabName = this.getAttribute('data-tab');
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
      });
    });

    // Image viewer
    const imgCards = document.querySelectorAll('.view-img');
    const viewer = document.getElementById('imageViewer');
    const viewerImg = document.getElementById('viewerImg');
    const closeBtn = document.getElementById('closeBtn');

    imgCards.forEach(card => {
      card.addEventListener('click', () => {
        viewerImg.src = card.src;
        viewer.classList.add('active');
      });
    });
    closeBtn.addEventListener('click', () => viewer.classList.remove('active'));
    viewer.addEventListener('click', e => {
      if(e.target === viewer) viewer.classList.remove('active');
    });

    // Mobile swipe support for tabs
    let touchStartX = 0;
    let touchEndX = 0;
    const tabContent = document.getElementById('productTabContent');
    const collectionsTab = document.getElementById('collections-tab');
    const merchTab = document.getElementById('merch-tab');

    tabContent.addEventListener('touchstart', e => {
      touchStartX = e.changedTouches[0].screenX;
    }, false);

    tabContent.addEventListener('touchend', e => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, false);

    function handleSwipe() {
      const swipeThreshold = 50;
      const diff = touchStartX - touchEndX;

      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          // Swipe left - go to merch
          if (collectionsTab.classList.contains('active')) {
            merchTab.click();
          }
        } else {
          // Swipe right - go to collections
          if (merchTab.classList.contains('active')) {
            collectionsTab.click();
          }
        }
      }
    }
  </script>
</body>
</html>
