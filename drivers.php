<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$queryUser = "SELECT role, fullname FROM users WHERE id = ?";
$stmtUser = mysqli_prepare($conn, $queryUser);
mysqli_stmt_bind_param($stmtUser, "i", $user_id);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);
$role = $user['role'] ?? 'customer';
$fullname = $user['fullname'] ?? 'User';

// Restrict access to non-admin and non-driver users
if ($role !== 'admin' && $role !== 'driver') {
    header("Location: login.php");
    exit();
}

// Handle status update for drivers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'driver' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $conn->real_escape_string($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    $updateQuery = "UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ? AND driver_id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("ssi", $new_status, $order_id, $user_id);
    if ($stmtUpdate->execute()) {
        // Update tracking table
        $trackingQuery = "INSERT INTO tracking (order_id, driver_id, status, timestamp) VALUES (?, ?, ?, NOW())";
        $stmtTracking = $conn->prepare($trackingQuery);
        $stmtTracking->bind_param("sis", $order_id, $user_id, $new_status);
        $stmtTracking->execute();
        $stmtTracking->close();
    }
    $stmtUpdate->close();
}

// Fetch data based on role
if ($role === 'admin') {
    // Fetch all drivers
    $queryDrivers = "SELECT id, fullname, email, contact_number, created_at FROM users WHERE role = 'driver'";
    $resultDrivers = $conn->query($queryDrivers);
    if (!$resultDrivers) {
        die("Error fetching drivers: " . $conn->error);
    }
    $drivers = [];
    while ($row = $resultDrivers->fetch_assoc()) {
        $drivers[] = $row;
    }
} elseif ($role === 'driver') {
    // Fetch orders assigned to this driver
    $queryOrders = "SELECT order_id, customer_name, pickup_location, destination, status, created_at 
                    FROM orders 
                    WHERE driver_id = ? 
                    ORDER BY created_at DESC";
    $stmtOrders = $conn->prepare($queryOrders);
    $stmtOrders->bind_param("i", $user_id);
    $stmtOrders->execute();
    $resultOrders = $stmtOrders->get_result();
    $orders = [];
    while ($row = $resultOrders->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmtOrders->close();
}

mysqli_stmt_close($stmtUser);
// Do NOT close $conn here; keep it open for later use
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeliverX - Drivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1A1A1A',
                        secondary: '#4B5563'
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            color: #1A1A1A;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                width: 75%;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .header {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="flex h-screen">
        <aside class="w-64 bg-primary text-white fixed h-full sidebar" id="sidebar">
            <div class="p-4">
                <h1 class="text-2xl font-['Pacifico'] mb-8">DeliverX</h1>
                <nav class="space-y-4">
                    <a href="dashboard.php" class="flex items-center space-x-3 p-2 hover:bg-white/10 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-dashboard-line"></i></span>
                        <span>Dashboard</span>
                    </a>
                    <a href="orders.php" class="flex items-center space-x-3 p-2 hover:bg-white/10 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-box-line"></i></span>
                        <span>Orders</span>
                    </a>
                    <a href="drivers.php" class="flex items-center space-x-3 p-2 bg-white/10 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-user-line"></i></span>
                        <span>Drivers</span>
                    </a>
                    <a href="track.php" class="flex items-center space-x-3 p-2 hover:bg-white/10 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-map-pin-line"></i></span>
                        <span>Tracking</span>
                    </a>
                    <a href="logout.php" class="flex items-center space-x-3 p-2 hover:bg-red-600 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-logout-box-line"></i></span>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">
                        <i class="ri-user-line text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium"><?= htmlspecialchars($fullname) ?></div>
                        <div class="text-sm text-gray-400"><?= ucfirst($role) ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 main-content" id="mainContent">
            <header class="bg-white border-b border-gray-200 px-6 py-4 header flex items-center justify-between">
                <div class="flex items-center">
                    <button id="menuToggle" class="md:hidden w-10 h-10 flex items-center justify-center text-gray-600">
                        <i class="ri-menu-line text-xl"></i>
                    </button>
                    <h1 class="text-xl font-semibold ml-4"><?= $role === 'admin' ? 'Drivers Management' : 'My Orders' ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($role === 'admin'): ?>
                        <button onclick="openAddDriverModal()" class="px-4 py-2 bg-primary text-white rounded-button hover:bg-gray-800 flex items-center gap-2">
                            <i class="ri-add-line"></i> Add Driver
                        </button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="p-6 space-y-6">
                <?php if ($role === 'admin'): ?>
                    <!-- Admin View: List of Drivers -->
                    <div class="bg-white rounded shadow-sm">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold">All Drivers</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full" id="driversTable">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Orders</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($drivers as $driver): ?>
                                        <?php
                                        $orderCountQuery = "SELECT COUNT(*) as count FROM orders WHERE driver_id = ?";
                                        $stmtCount = $conn->prepare($orderCountQuery);
                                        if (!$stmtCount) {
                                            die("Error preparing order count query: " . $conn->error);
                                        }
                                        $stmtCount->bind_param("i", $driver['id']);
                                        $stmtCount->execute();
                                        $orderCountResult = $stmtCount->get_result();
                                        $orderCount = $orderCountResult->fetch_assoc()['count'];
                                        $stmtCount->close();
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($driver['fullname']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($driver['email']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($driver['contact_number']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $orderCount ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($driver['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif ($role === 'driver'): ?>
                    <!-- Driver View: Assigned Orders -->
                    <div class="bg-white rounded shadow-sm">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold">Assigned Orders</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full" id="ordersTable">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pickup</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($order['order_id']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['customer_name']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['pickup_location']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['destination']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $order['status'] === 'Delivered' ? 'bg-green-100 text-green-800' : ($order['status'] === 'In Transit' ? 'bg-blue-100 text-blue-800' : ($order['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) ?>">
                                                    <?= htmlspecialchars($order['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                                    <select name="status" onchange="this.form.submit()" class="px-2 py-1 text-sm bg-gray-100 rounded-button">
                                                        <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="In Transit" <?= $order['status'] === 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                                                        <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                        <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Driver Modal (Admin Only) -->
    <?php if ($role === 'admin'): ?>
        <div id="addDriverModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeAddDriverModal()">×</span>
                <h2 class="text-xl font-bold mb-4">Add New Driver</h2>
                <?php if (isset($_GET['success'])): ?>
                    <p class="text-green-600 mb-4"><?= htmlspecialchars($_GET['success']) ?></p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-600 mb-4"><?= htmlspecialchars($_GET['error']) ?></p>
                <?php endif; ?>
                <form action="add_driver.php" method="POST">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="fullname" class="mt-1 p-2 w-full border rounded-button" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" class="mt-1 p-2 w-full border rounded-button" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input type="text" name="contact_number" class="mt-1 p-2 w-full border rounded-button" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" class="mt-1 p-2 w-full border rounded-button" required>
                    </div>
                    <input type="hidden" name="role" value="driver">
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-button hover:bg-green-600">Save</button>
                        <button type="button" onclick="closeAddDriverModal()" class="px-4 py-2 bg-gray-500 text-white rounded-button hover:bg-gray-600">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function openAddDriverModal() {
            document.getElementById('addDriverModal').style.display = 'block';
        }

        function closeAddDriverModal() {
            document.getElementById('addDriverModal').style.display = 'none';
            // Optionally clear success/error messages from URL
            window.history.replaceState({}, document.title, "drivers.php");
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            document.getElementById('menuToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.getElementById('menuToggle');
                if (window.innerWidth <= 768 && sidebar.classList.contains('open') && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === document.getElementById('addDriverModal')) {
                    closeAddDriverModal();
                }
            });

            // Auto-open modal if success/error message is present
            <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
                openAddDriverModal();
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php
// Close the connection at the very end
$conn->close();
?>