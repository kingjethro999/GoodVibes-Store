<?php
// header.php - 3ED.I SOCIETY admin header
// include this at top of dashboard pages
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>3ED.I SOCIETY Admin</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Admin CSS -->
  <link href="../assets/admin.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">

</head>
<body>
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg admin-topbar">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="/index.php">
        <img src="/images/gv-logo-small.png" alt="3ED.I SOCIETY" class="me-2" style="height:34px;">
        <span class="brand-text">3ED.I SOCIETY</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="topNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-3">
            <a class="nav-link" href="/index.php" target="_blank"><i class="bi bi-house-door"></i> View Site</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> Admin
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/basics.php">Profile</a></li>
              <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="admin-wrapper d-flex">
    <aside class="admin-sidebar">
      <ul class="nav flex-column p-2">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-shop me-2"></i> Storefront</a></li>
      <li class="nav-item"><a class="nav-link" href="products.php"><i class="bi bi-boxes me-2"></i> Products</a></li>
        <li class="nav-item"><a class="nav-link" href="merch.php"><i class="bi bi-bag me-2"></i> Merch</a></li>
        <li class="nav-item"><a class="nav-link" href="process.php"><i class="bi bi-box-seam me-2"></i> Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people me-2"></i> Users</a></li>
        <li class="nav-item"><a class="nav-link" href="../theme/header.php" target="_blank"><i class="bi bi-code-square me-2"></i> Theme</a></li>
      </ul>
    </aside>

    <!-- Main content area opens here; footer.php will close tags -->
    <main class="admin-main p-4">
