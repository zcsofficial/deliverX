<?php
require_once 'config.php';

$filter = $_POST['filter'] ?? 'all';
$sort = $_POST['sort'] ?? 'desc';
$search = $_POST['filter'] === 'search' ? $_POST['sort'] : '';

$query = "SELECT id, customer_name, destination, status, driver_name FROM orders";
if ($search) {
    $query .= " WHERE id LIKE ? OR customer_name LIKE ? OR destination LIKE ?";
    $stmt = mysqli_prepare($conn, $query);
    $searchTerm = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    if ($filter !== 'all') {
        $status = $filter === 'in_transit' ? 'In Transit' : ($filter === 'delivered' ? 'Delivered' : 'Pending');
        $query .= " WHERE status = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $status);
    } else {
        $stmt = mysqli_prepare($conn, $query);
    }
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

if ($sort === 'asc') {
    usort($orders, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
} else {
    usort($orders, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

echo json_encode($orders);
mysqli_stmt_close($stmt);
mysqli_close($conn);