<?php
require_once 'config.php';

$user_id = $_POST['user_id'];
$query = "SELECT message, created_at FROM notifications WHERE user_id = ? AND seen = 0 ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

echo json_encode($notifications);
mysqli_stmt_close($stmt);
mysqli_close($conn);