<?php
require_once 'config.php';

$filter = $_POST['filter'] ?? 'all';
$query = "SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
if ($filter === 'date') {
    $query = "SELECT DATE(created_at) AS name, COUNT(*) AS count FROM orders GROUP BY DATE(created_at)";
}

$result = mysqli_query($conn, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = ['name' => $row['name'] ? $row['name'] : $row['status'], 'value' => (int)$row['count']];
}

echo json_encode($data);
mysqli_close($conn);