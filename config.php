<?php
$servername = "localhost";  // Change if needed
$username = "adnan";         // Change to your database username
$password = "Adnan@66202";             // Change to your database password
$database = "deliverx";     // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
