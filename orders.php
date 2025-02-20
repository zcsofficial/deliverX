<?php
session_start();
require 'config.php'; // Include database connection

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$queryUser = "SELECT fullname FROM users WHERE id = ?";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$fullname = $user['fullname'] ?? 'Admin';
$stmtUser->close();

// Handle search, filter, and sorting with sanitized inputs
$search = htmlspecialchars($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$sort_by = $_GET['sort'] ?? 'created_at';

// Validate sort_by to prevent SQL injection
$valid_sort_columns = ['created_at', 'users.fullname']; // Explicitly use users.fullname to avoid ambiguity
$sort_by = in_array($sort_by, $valid_sort_columns) ? $sort_by : 'created_at';

// Query orders with customer details from the users table
$query = "SELECT orders.*, users.fullname AS customer_name, users.email 
          FROM orders 
          JOIN users ON orders.customer_id = users.id 
          WHERE users.role = 'customer'";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (users.fullname LIKE '%$search%' OR orders.pickup_location LIKE '%$search%' OR orders.destination LIKE '%$search%')";
}

if (!empty($filter_status)) {
    $filter_status = $conn->real_escape_string($filter_status);
    $query .= " AND orders.status = '$filter_status'";
}

$query .= " ORDER BY $sort_by DESC";

try {
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Handle export functionality
if (isset($_GET['export'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=orders_" . date('Ymd_His') . ".html");

    echo "<html><head><title>Orders Report</title></head><body>";
    echo "<h2>Orders Report - " . date('Y-m-d H:i:s') . "</h2><table border='1'><tr>
            <th>ID</th><th>Order ID</th><th>Customer</th><th>Email</th><th>Pickup</th><th>Destination</th>
            <th>Status</th><th>Driver</th><th>Created At</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['order_id']}</td>
                <td>{$row['customer_name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['pickup_location']}</td>
                <td>{$row['destination']}</td>
                <td>{$row['status']}</td>
                <td>" . ($row['driver_name'] ?? 'N/A') . "</td>
                <td>{$row['created_at']}</td>
              </tr>";
    }

    echo "</table></body></html>";
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeliverX - Order Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1a1a1a',
                        secondary: '#4a4a4a'
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
        .order-status-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            width: 2px;
            height: 100%;
            background-color: #e5e5e5;
            z-index: 0;
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
            .grid-cols-3 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="flex h-screen">
        <aside class="w-64 bg-primary text-white fixed h-full sidebar" id="sidebar">
            <div class="p-4">
                <h1 class="text-2xl font-['Pacifico'] mb-8">DeliverX</h1>
                <nav class="space-y-4">
                    <a href="dashboard.php" class="flex items-center space-x-3 p-2 hover:bg-white/10 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-dashboard-line"></i></span>
                        <span>Dashboard</span>
                    </a>
                    <a href="orders.php" class="flex items-center space-x-3 p-2 bg-white/10 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-box-line"></i></span>
                        <span>Orders</span>
                    </a>
                    <a href="drivers.php" class="flex items-center space-x-3 p-2 hover:bg-white/10 rounded-button">
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
                        <div class="text-sm text-gray-400">Admin</div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 main-content ml-64" id="mainContent">
            <header class="bg-white border-b border-gray-200 px-6 py-4 header flex items-center justify-between">
                <div class="flex items-center">
                    <button id="menuToggle" class="md:hidden w-10 h-10 flex items-center justify-center text-gray-600">
                        <i class="ri-menu-line text-xl"></i>
                    </button>
                    <div class="relative ml-4 flex-1 max-w-2xl">
                        <form method="GET" class="relative">
                            <input type="text" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>" class="w-full h-10 px-4 pr-10 text-gray-900 bg-gray-100 border-none !rounded-button">
                            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-gray-500">
                                <i class="ri-search-line text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <h1 class="text-2xl font-semibold text-gray-900">Orders</h1>
                        <div class="flex gap-2">
                            <form id="filterForm" method="GET">
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                <select name="status" onchange="this.form.submit()" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 !rounded-button hover:bg-gray-50 whitespace-nowrap flex items-center gap-2">
                                    <option value="">All Statuses</option>
                                    <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="In Transit" <?= $filter_status == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                                    <option value="Delivered" <?= $filter_status == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="Cancelled" <?= $filter_status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <select name="sort" onchange="this.form.submit()" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 !rounded-button hover:bg-gray-50 whitespace-nowrap flex items-center gap-2">
                                    <option value="created_at" <?= $sort_by == 'created_at' ? 'selected' : '' ?>>Sort by Date</option>
                                    <option value="users.fullname" <?= $sort_by == 'users.fullname' ? 'selected' : '' ?>>Sort by Customer</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="orders.php?export=1" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 !rounded-button hover:bg-gray-50 whitespace-nowrap flex items-center gap-2">
                            <i class="ri-download-line"></i>
                            Export
                        </a>
                        <button onclick="openModal()" class="px-4 py-2 bg-primary text-white !rounded-button hover:bg-gray-800 whitespace-nowrap flex items-center gap-2">
                            <i class="ri-add-line"></i>
                            New Order
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="bg-white p-4 rounded shadow-sm border border-gray-100">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="text-sm text-gray-500">Order ID</span>
                                    <h3 class="text-lg font-semibold">#<?= htmlspecialchars($row['order_id']) ?></h3>
                                </div>
                                <span class="px-2 py-1 <?= $row['status'] == 'Delivered' ? 'bg-green-100 text-green-800' : ($row['status'] == 'In Transit' ? 'bg-blue-100 text-blue-800' : ($row['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) ?> text-sm rounded-full"><?= htmlspecialchars($row['status']) ?></span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Customer</span>
                                    <span class="font-medium"><?= htmlspecialchars($row['customer_name']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pickup</span>
                                    <span class="font-medium"><?= htmlspecialchars($row['pickup_location']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Destination</span>
                                    <span class="font-medium"><?= htmlspecialchars($row['destination']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date</span>
                                    <span class="font-medium"><?= htmlspecialchars($row['created_at']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Driver</span>
                                    <span class="font-medium"><?= htmlspecialchars($row['driver_name'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button onclick="openDetails('<?= $row['order_id'] ?>')" class="flex-1 px-4 py-2 bg-primary text-white !rounded-button hover:bg-gray-800">View Details</button>
                                <button class="w-10 h-10 flex items-center justify-center border border-gray-200 !rounded-button hover:bg-gray-50">
                                    <i class="ri-map-pin-line"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Order Modal -->
    <div id="addOrderModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Add New Order</h3>
            <form action="add_order.php" method="POST">
                <select name="customer_id" class="p-2 border rounded w-full mb-2" required>
                    <option value="">Select Customer</option>
                    <?php
                    $customers = $conn->query("SELECT id, fullname FROM users WHERE role = 'customer'");
                    while ($customer = $customers->fetch_assoc()) {
                        echo "<option value='{$customer['id']}'>" . htmlspecialchars($customer['fullname']) . "</option>";
                    }
                    $customers->free();
                    ?>
                </select>
                <input type="text" name="order_id" placeholder="Order ID (e.g., ORD-20250220)" class="p-2 border rounded w-full mb-2" required>
                <input type="text" name="pickup_location" placeholder="Pickup Location" class="p-2 border rounded w-full mb-2" required>
                <input type="text" name="destination" placeholder="Destination" class="p-2 border rounded w-full mb-2" required>
                <select name="status" class="p-2 border rounded w-full mb-2">
                    <option value="Pending">Pending</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
                <select name="driver_id" class="p-2 border rounded w-full mb-2">
                    <option value="">Select Driver (optional)</option>
                    <?php
                    $drivers = $conn->query("SELECT id, fullname FROM users WHERE role = 'driver'");
                    while ($driver = $drivers->fetch_assoc()) {
                        echo "<option value='{$driver['id']}'>" . htmlspecialchars($driver['fullname']) . "</option>";
                    }
                    $drivers->free();
                    ?>
                </select>
                <div class="flex space-x-2">
                    <button type="submit" class="p-2 bg-green-500 text-white rounded">Save</button>
                    <button type="button" onclick="closeModal()" class="p-2 bg-gray-500 text-white rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Panel (Placeholder) -->
    <div id="orderDetailsPanel" class="fixed top-0 right-0 w-full md:w-[480px] h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">
        <div class="h-full flex flex-col">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-xl font-semibold">Order Details</h2>
                <button onclick="closeDetails()" class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded-full">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4" id="orderDetailsContent">
                <!-- Dynamic content will be loaded here via JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById("addOrderModal").classList.remove("hidden");
        }

        function closeModal() {
            document.getElementById("addOrderModal").classList.add("hidden");
        }

        function openDetails(orderId) {
            const panel = document.getElementById("orderDetailsPanel");
            const content = document.getElementById("orderDetailsContent");
            content.innerHTML = `<p>Loading details for Order #${orderId}...</p>`;
            panel.style.transform = "translateX(0)";
        }

        function closeDetails() {
            document.getElementById("orderDetailsPanel").style.transform = "translateX(100%)";
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
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>