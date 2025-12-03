<?php
// db_connect.php — handles connection to the Good Vibes database

$servername = "localhost";   // XAMPP server
$username = "edisocie_root ";          // default username for XAMPP
$password = "Seun2000*";              // leave empty if you haven't set one
$dbname = "edisocie_good_vibes";    // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}

// Optional: set charset for proper encoding
$conn->set_charset("utf8mb4");

// Optional success message for testing only
// echo "✅ Connected successfully to Good Vibes DB";
?>
