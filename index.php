<?php
session_start();
include 'assets/config.php'; // database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Good Vibes | Fashion & Art</title>

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

    /* HERO BACKGROUND IMAGE */
.hero-section {
    position: relative;
    width: 100%;
    height: 100vh; /* full screen height */
    background-image: url('IMG-20250509-WA0067.jpg'); /* your background image */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* dark gradient overlay for better readability */
.hero-section .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
      rgba(0, 0, 0, 0.3),
      rgba(0, 0, 0, 0.5)
    );
}

/* hero content */
.hero-content {
    position: relative;
    text-align: center;
    color: #fff;
    max-width: 700px;
    padding: 0 20px;
    z-index: 2;
}

.hero-content h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.hero-content p {
    font-size: 1.3rem;
    margin-bottom: 20px;
}

    .btn-vibe {
      background: var(--gradient);
      color: #fff;
      border: none;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      transition: 0.3s;
    }
    .btn-vibe:hover {
      opacity: 0.85;
      transform: translateY(-2px);
    }

    /* Spinning Logo */
    .spinning-logo {
      margin: 40px 0;
      text-align: center;
    }
    .spinning-logo img {
      width: 100px;
      animation: spin 6s linear infinite;
      cursor: pointer;
      transition: transform 0.3s;
    }
    .spinning-logo img:hover {
      transform: scale(1.2) rotate(20deg);
    }

    /* Section */
    section {
      padding: 80px 0;
    }
    .section-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 2.5rem;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    /* Product Cards */
    .card {
      border: none;
      border-radius: 20px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    .card img {
      height: 250px;
      object-fit: cover;
      border-radius: 15px;
    }

    /* Quote Section */
    .quote-section {
      background: var(--gradient);
      color: #fff;
      text-align: center;
      padding: 100px 20px;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      letter-spacing: 2px;
      font-size: 2.5rem;
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

    /* Animations */
    @keyframes spin {
      from { transform: rotateY(0deg); }
      to { transform: rotateY(360deg); }
    }
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    @media (max-width: 768px) {
      .hero h1 { font-size: 2.4rem; }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">GOOD VIBES</a>
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
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin'): ?>
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

  <!-- Hero Section -->
  <div class="hero-section">
    <div class="overlay"></div>
    <div class="hero-content">
        <h1>Welcome</h1>
        <p>Where Creative Energy Meets Style & Art.</p>
        <!-- <a href="projects.php" class="hero-btn">Explore Projects</a> -->
    </div>
</div>

  <!-- Spinning Logo -->
  <!-- <div class="spinning-logo">
    <img src="https://cdn-icons-png.flaticon.com/512/616/616554.png" alt="Good Vibes Logo" />
  </div> -->

  <!-- Our Vibes Section -->
  <section>
    <div class="container text-center">
      <h2 class="section-title mb-5">Our Vibes</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card p-4">
            <i class="bi bi-brush fs-1"></i>
            <h5 class="fw-bold mt-3">Artistic Fashion</h5>
            <p>Creative designs that blend color, culture, and individuality.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-4">
            <i class="bi bi-palette fs-1"></i>
            <h5 class="fw-bold mt-3">Handcrafted Pieces</h5>
            <p>Every piece is a statement — crafted with passion and precision.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-4">
            <i class="bi bi-stars fs-1"></i>
            <h5 class="fw-bold mt-3">Street & Studio Vibes</h5>
            <p>From the runway to the streets, we bring the energy wherever you go.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Products -->
  <section>
    <div class="container text-center">
      <h2 class="section-title mb-5">Featured Products</h2>
      <div class="row g-4">
        <?php
        $query = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 6");
        if ($query->num_rows > 0) {
          while ($row = $query->fetch_assoc()) {
            echo '
            <div class="col-md-4">
              <div class="card p-3 h-100">
                <img src="' . $row['image'] . '" alt="' . $row['product_name'] . '" class="img-fluid">
                <h5 class="fw-bold mt-3">' . $row['product_name'] . '</h5>
                <p class="text-muted mb-1">₦' . number_format($row['price']) . '</p>
                <p class="small">' . substr($row['description'], 0, 90) . '...</p>
                <a href="product_details.php?id=' . $row['id'] . '" class="btn btn-vibe mt-2">View Product</a>
              </div>
            </div>';
          }
        } else {
          echo "<p class='text-muted'>No products available yet.</p>";
        }
        ?>
      </div>
    </div>
  </section>

  <!-- Quote Section -->
  <section class="quote-section">
    “Good Vibes Only ✨”
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
