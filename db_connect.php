<?php
// db_connect.php — handles connection to the 3ED.I SOCIETY database

$servername = "s4946.fra1.stableserver.net";   // Production server
$username = "edisocie_root";          // Production username
$password = "Seun2000*";              // Production password
$dbname = "edisocie_good_vibes";      // Production database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}

// Optional: set charset for proper encoding
$conn->set_charset("utf8mb4");

// Optional success message for testing only
// echo "✅ Connected successfully to 3ED.I SOCIETY DB";
?>
