<?php
include 'config.php';

if (isset($_GET['order_id'])) {
    $order_id = $conn->real_escape_string($_GET['order_id']);

    $sql = "SELECT o.*, u.name AS customer_name, u.email, u.contact
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.order_id = '$order_id'";
    $result = $conn->query($sql);
    
    if ($row = $result->fetch_assoc()) {
        echo "<h3 class='text-lg font-semibold'>Order ID: #{$row['order_id']}</h3>";
        echo "<p><strong>Status:</strong> {$row['status']}</p>";
        echo "<p><strong>Date:</strong> " . date("M d, Y h:i A", strtotime($row['order_date'])) . "</p>";
        echo "<hr class='my-4'>";
        echo "<h3 class='text-lg font-semibold'>Customer Details</h3>";
        echo "<p><strong>Name:</strong> {$row['customer_name']}</p>";
        echo "<p><strong>Email:</strong> {$row['email']}</p>";
        echo "<p><strong>Contact:</strong> {$row['contact']}</p>";
    } else {
        echo "<p class='text-red-500'>Order not found.</p>";
    }

    $conn->close();
}
?>
