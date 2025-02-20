<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing user_id']);
    exit();
}

$user_id = (int)$_POST['user_id'];

$query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
$success = mysqli_stmt_execute($stmt);

echo json_encode(['success' => $success, 'error' => $success ? null : $conn->error]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>