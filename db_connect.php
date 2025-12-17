<?php
// db_connect.php — handles connection to the Good Vibes database

$servername = "localhost";   // XAMPP server
$username = "root";          // default username for XAMPP
$password = "";              // leave empty if you haven’t set one
$dbname = "goodvibes_db";    // your database name

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
