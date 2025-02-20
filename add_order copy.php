<?php
require 'config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = trim($_POST['customer_id']);
    $pickup_location = trim($_POST['pickup_location']);
    $destination = trim($_POST['destination']);
    $status = $_POST['status'] ?? 'Pending';
    $driver_id = !empty($_POST['driver_id']) ? trim($_POST['driver_id']) : NULL;

    // Validate required fields
    if (empty($customer_id) || empty($pickup_location) || empty($destination)) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit;
    }

    // Get customer name from users table
    $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ? AND role = 'customer'");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->bind_result($customer_name);
    $stmt->fetch();
    $stmt->close();

    if (!$customer_name) {
        echo "<script>alert('Invalid customer selected.'); window.history.back();</script>";
        exit;
    }

    // Validate driver_id (only if driver is assigned)
    if (!empty($driver_id)) {
        $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ? AND role = 'driver'");
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $stmt->bind_result($driver_name);
        $stmt->fetch();
        $stmt->close();

        if (!$driver_name) {
            echo "<script>alert('Invalid driver selected.'); window.history.back();</script>";
            exit;
        }
    } else {
        $driver_id = NULL;
        $driver_name = NULL;
    }

    // Generate a unique order ID (ORDDLX001 format)
    $result = $conn->query("SELECT order_id FROM orders ORDER BY id DESC LIMIT 1");
    $latest_order = $result->fetch_assoc();
    $last_order_number = isset($latest_order['order_id']) ? (int)substr($latest_order['order_id'], 6) : 0;
    $new_order_number = $last_order_number + 1;
    $order_id = 'ORDDLX' . str_pad($new_order_number, 3, '0', STR_PAD_LEFT);

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (order_id, customer_id, customer_name, pickup_location, destination, status, driver_id, driver_name) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sissssis", $order_id, $customer_id, $customer_name, $pickup_location, $destination, $status, $driver_id, $driver_name);

    if ($stmt->execute()) {
        echo "<script>alert('Order added successfully! Order ID: $order_id'); window.location.href = 'orders.php';</script>";
    } else {
        echo "<script>alert('Error adding order.'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>
