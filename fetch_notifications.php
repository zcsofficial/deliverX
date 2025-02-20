<?php
require_once 'config.php';

// Set content type header
header('Content-Type: application/json');

// Check if user_id is provided via POST
if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo json_encode(['error' => 'Invalid or missing user_id']);
    exit();
}

$user_id = (int)$_POST['user_id'];

// Prepare and execute query to fetch unread notifications
$query = "SELECT message, created_at 
          FROM notifications 
          WHERE user_id = ? AND is_read = 0 
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare statement: ' . $conn->error]);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = [
        'message' => $row['message'],
        'created_at' => $row['created_at']
    ];
}

// Return notifications as JSON
echo json_encode($notifications);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>