<?php
require_once 'config.php';

header('Content-Type: application/json');

$period = $_POST['period'] ?? 'INTERVAL 7 DAY';
$query = "SELECT DATE(created_at) AS date, COUNT(*) AS count 
          FROM orders 
          WHERE created_at >= DATE_SUB(NOW(), $period) 
          GROUP BY DATE(created_at) 
          ORDER BY date";
$result = mysqli_query($conn, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [$row['date'], (int)$row['count']];
}

echo json_encode($data);

mysqli_close($conn);
?>