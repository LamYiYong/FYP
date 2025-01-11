<?php
$servername = "localhost"; // Replace with your server address
$username = "root";        // Replace with your database username
$password = "";            // Replace with your database password
$database = "user_system"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
