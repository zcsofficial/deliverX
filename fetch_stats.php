<?php
require_once 'config.php';

$stats = [];
$queries = [
    'total_orders' => "SELECT COUNT(*) AS count FROM orders",
    'in_transit' => "SELECT COUNT(*) AS count FROM orders WHERE status = 'In Transit'",
    'completed' => "SELECT COUNT(*) AS count FROM orders WHERE status = 'Delivered'",
    'performance' => "SELECT ROUND(AVG(rating), 1) AS avg_rating FROM deliveries WHERE rating IS NOT NULL"
];

foreach ($queries as $key => $query) {
    $result = mysqli_query($conn, $query);
    $stats[$key] = mysqli_fetch_assoc($result)['count'] ?? ($key === 'performance' ? "N/A" : 0);
}

echo json_encode($stats);
mysqli_close($conn);