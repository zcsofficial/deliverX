<?php
require_once 'config.php';

$period = $_POST['period'] ?? 'week';
$interval = $period === 'month' ? 'MONTH' : ($period === 'year' ? 'YEAR' : 'DAY');
$query = "SELECT DATE(created_at) AS date, COUNT(*) AS count FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 $interval) GROUP BY DATE(created_at) ORDER BY date";

$result = mysqli_query($conn, $query);
$data = ['dates' => [], 'values' => []];
while ($row = mysqli_fetch_assoc($result)) {
    $data['dates'][] = $row['date'];
    $data['values'][] = (int)$row['count'];
}

echo json_encode($data);
mysqli_close($conn);