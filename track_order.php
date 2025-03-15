<?php
require 'config.php'; // Database connection

// Get order_id from URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$order_id) {
    die("Invalid Order ID.");
}

// Fetch order details
$query = "SELECT o.order_id, o.status, o.eta, 
                 t.current_location, t.estimated_time, 
                 d.fullname AS driver_name
          FROM orders o 
          LEFT JOIN tracking t ON o.order_id = t.order_id
          LEFT JOIN users d ON o.driver_id = d.id
          WHERE o.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();

// Order status mapping
$status_steps = [
    "Pending" => 1,
    "In Transit" => 2,
    "Out for Delivery" => 3,
    "Delivered" => 4,
    "Cancelled" => 0
];

$current_status_step = $status_steps[$order['status']] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pick Fast - Order Tracking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css">
    <style>
        .map-container {
            background-image: url('https://public.readdy.ai/gen_page/map_placeholder_1280x720.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="bg-white min-h-screen">

<header class="fixed top-0 left-0 right-0 bg-black text-white z-50 h-16">
    <div class="container mx-auto px-4 h-full flex items-center justify-between">
        <div class="font-['Pacifico'] text-2xl">Pick Fast</div>
        <div class="flex items-center gap-4">
            <div class="text-sm">Order ID: #<?= htmlspecialchars($order['order_id']) ?></div>
            <div class="text-sm">ETA: <?= htmlspecialchars($order['eta'] ?? 'N/A') ?></div>
            <li><a href="logout.php" class="text-gray-600 hover:text-primary">Logout</a></li>
        </div>
    </div>
</header>

<main class="container mx-auto px-4 pt-20 pb-24">
    <div class="map-container h-[400px] rounded relative mb-8"></div>

    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-8">
            <?php
            $steps = ["Pending", "In Transit", "Out for Delivery", "Delivered"];
            foreach ($steps as $index => $step) {
                $step_number = $index + 1;
                $is_active = $step_number <= $current_status_step;
                $bg_class = $is_active ? "bg-black text-white" : "bg-gray-200 text-gray-400";
                $icon = $is_active ? "ri-check-line" : "ri-checkbox-circle-line";
                ?>
                <div class="w-1/4 text-center">
                    <div class="w-8 h-8 rounded-full <?= $bg_class ?> flex items-center justify-center mx-auto mb-2">
                        <i class="<?= $icon ?>"></i>
                    </div>
                    <div class="text-xs <?= $is_active ? '' : 'text-gray-400' ?>"><?= $step ?></div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Transit Details</h2>
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-map-pin-line text-black"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Current Location</div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($order['current_location'] ?? 'Not Available') ?></div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-time-line text-black"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Estimated Time</div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($order['estimated_time'] ?? 'N/A') ?> minutes remaining</div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-user-line text-black"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Driver</div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($order['driver_name'] ?? 'Not Assigned') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Customer Support</h2>
            <div class="space-y-4">
                <button class="w-full flex items-center gap-2 p-3 border border-gray-200 rounded hover:bg-gray-50">
                    <i class="ri-phone-line text-black"></i>
                    <span>Call Support: +1 (800) 123-4567</span>
                </button>
                <button class="w-full flex items-center gap-2 p-3 border border-gray-200 rounded hover:bg-gray-50">
                    <i class="ri-message-2-line text-black"></i>
                    <span>Start Live Chat</span>
                </button>
            </div>
        </div>
    </div>
</main>

<footer class="fixed bottom-0 left-0 right-0 bg-black text-white h-16">
    <div class="container mx-auto px-4 h-full flex items-center justify-between">
        <button class="flex items-center gap-2 bg-white text-black px-4 py-2 rounded">
            <i class="ri-share-line"></i>
            <span>Share Tracking Link</span>
        </button>
        <button class="flex items-center gap-2 bg-white text-black px-4 py-2 rounded">
            <i class="ri-error-warning-line"></i>
            <span>Report Issue</span>
        </button>
    </div>
</footer>

</body>
</html>
