<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$is_admin = ($_SESSION['role'] === 'admin');
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $is_admin ? $_POST['customer_id'] : $user_id;
    $order_id = htmlspecialchars($_POST['order_id']);
    $pickup_location = htmlspecialchars($_POST['pickup_location']);
    $destination = htmlspecialchars($_POST['destination']);
    $status = $_POST['status'];
    $driver_id = $is_admin && !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;
    $is_emergency = 1;

    $query = "INSERT INTO orders (order_id, customer_id, pickup_location, destination, status, driver_id, is_emergency, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisssii", $order_id, $customer_id, $pickup_location, $destination, $status, $driver_id, $is_emergency);

    if ($stmt->execute()) {
        header("Location: orders.php?success=Emergency order created");
    } else {
        header("Location: orders.php?error=Failed to create emergency order");
    }
    $stmt->close();
}
$conn->close();
?>