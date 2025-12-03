<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us | Good Vibes</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">

  <style>
    :root {
      --purple: #6a00f4;
      --magenta: #ff0099;
      --yellow: #ffe600;
      --cyan: #00e6ff;
      --orange: #ff6600;
      --lime: #7fff00;
    }

    body { font-family: 'Poppins', sans-serif; color: #333; overflow-x: hidden; }

    /* Navbar */
    .navbar {
      background: linear-gradient(90deg, var(--purple), var(--magenta), var(--yellow), var(--cyan));
      background-size: 400% 400%;
      animation: gradientShift 10s ease infinite;
    }
    .navbar .nav-link, .navbar-brand { color: #fff !important; font-weight: 600; }

    /* Hero */
    .hero-about {
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://source.unsplash.com/1600x500/?fashion,art,colors');
      background-size: cover;
      background-position: center;
      color: white; text-align: center; padding: 100px 20px;
    }
    .hero-about h1 {
      font-size: 3rem;
      font-weight: 700;
      background: linear-gradient(90deg, var(--purple), var(--magenta), var(--cyan), var(--lime));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: gradientShift 8s linear infinite;
    }

    /* Reveal animation */
    .reveal { opacity: 0; transform: translateY(50px); transition: all 0.8s ease-in-out; }
    .reveal.active { opacity: 1; transform: translateY(0); }

    /* Section Titles */
    h2.section-title {
      background: linear-gradient(90deg, var(--purple), var(--magenta), var(--cyan));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-size: 400%;
      animation: gradientShift 8s linear infinite;
      font-weight: 700;
    }

    /* Cards */
    .card {
      border: none;
      border-radius: 20px;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .card:hover { transform: translateY(-10px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }

    /* Footer */
    footer {
      background: linear-gradient(90deg, var(--magenta), var(--cyan), var(--yellow));
      background-size: 400% 400%;
      animation: gradientShift 10s ease infinite;
      color: #000;
      text-align: center;
      padding: 30px 0;
      font-weight: 600;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    @media (max-width:768px) {
      .hero-about h1 { font-size: 2.3rem; }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">GOOD VIBES</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
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

  <!-- Hero -->
  <section class="hero-about">
    <div class="container">
      <h1>About 3ED.I SOCIETY</h1>
      <p class="lead">Where Fashion Meets Art — Created by 3rdeye</p>
    </div>
  </section>

  <!-- Story -->
  <section class="py-5 reveal">
    <div class="container text-center">
      <h2 class="section-title mb-4">Our Story</h2>
      <p class="px-3">
        <strong>Good Vibes</strong> is a creative fusion of fashion and art — a movement that celebrates self-expression, color, and individuality.  
        Founded by <strong>David Inyang (3rdeye)</strong>, a visionary artist and designer, Good Vibes was born out of a passion to blend street energy with artistic elegance.  
        Every collection tells a story — bold, vibrant, and unapologetically original.
      </p>
    </div>
  </section>

  <!-- Mission & Vision -->
  <section class="py-5 bg-light reveal">
    <div class="container text-center">
      <h2 class="section-title mb-4">Mission & Vision</h2>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card p-4 shadow-sm h-100">
            <i class="bi bi-lightning-charge display-4 text-warning"></i>
            <h5 class="mt-3">Our Mission</h5>
            <p>To empower creativity through fashion and art — spreading color, culture, and confidence worldwide.</p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-4 shadow-sm h-100">
            <i class="bi bi-eye-fill display-4 text-info"></i>
            <h5 class="mt-3">Our Vision</h5>
            <p>To become a global creative hub where artistic minds and style enthusiasts connect through good vibes and bold expression.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Founder -->
  <section class="py-5 reveal">
    <div class="container text-center">
      <h2 class="section-title mb-4">Meet the Creator</h2>
      <div class="card mx-auto shadow p-4" style="max-width:400px;">
        <img src="IMG-20250509-WA0067.jpg" class="card-img-top rounded-circle mb-3" alt="Iyang 3ED">
        <div class="card-body">
          <h5 class="card-title">3rdeye</h5>
          <p class="card-text">
            David Inyang (3rdeye)  is a visionary graffiti artist whose passion for fashion and style, expression, and identity inspired the Good Vibes brand. His creative hand-painting technique ensures every hat is a story worth telling, with style that resonates with authenticity.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Core Values -->
  <section class="py-5 bg-light reveal">
    <div class="container text-center">
      <h2 class="section-title mb-4">Our Core Values</h2>
      <div class="row g-4">
        <div class="col-md-3"><i class="bi bi-brush display-5 text-primary"></i><p>Creativity</p></div>
        <div class="col-md-3"><i class="bi bi-heart-fill display-5 text-danger"></i><p>Passion</p></div>
        <div class="col-md-3"><i class="bi bi-stars display-5 text-warning"></i><p>Excellence</p></div>
        <div class="col-md-3"><i class="bi bi-emoji-smile display-5 text-success"></i><p>Positivity</p></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="py-5 reveal">
    <div class="container text-center">
      <h2 class="section-title mb-4">Join the Good Vibes Movement</h2>
      <p>Step into a world of art, color, and creativity. Let your outfit speak your energy.</p>
      <a href="products.php" class="btn btn-lg text-dark fw-bold" style="background: linear-gradient(90deg,var(--magenta),var(--yellow),var(--cyan)); border:none;">Explore Gallery</a>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Good Vibes. Designed by Iyang 3ED. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const reveals = document.querySelectorAll(".reveal");
    const revealOnScroll = () => {
      reveals.forEach(el => {
        const windowHeight = window.innerHeight;
        const elementTop = el.getBoundingClientRect().top;
        if (elementTop < windowHeight - 100) el.classList.add("active");
      });
    };
    window.addEventListener("scroll", revealOnScroll);
    revealOnScroll();
  </script>
</body>
</html>
