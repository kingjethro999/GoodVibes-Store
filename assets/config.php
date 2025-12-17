<?php
$conn = new mysqli("localhost", "root", "", "goodvibes_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>
