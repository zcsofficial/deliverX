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
$fullname = $user['fullname'] ?? 'Admin';

// Restrict access to non-admin users
if ($role !== 'admin') {
    header("Location: login.php");
    exit();
}

// Pagination setup
$orders_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $orders_per_page;

// Initial data fetch
function fetchDashboardData($conn, $offset, $limit) {
    $data = [];
    
    // Statistics
    $queries = [
        'total_orders' => "SELECT COUNT(*) AS count FROM orders",
        'in_transit' => "SELECT COUNT(*) AS count FROM orders WHERE status = 'In Transit'",
        'completed' => "SELECT COUNT(*) AS count FROM orders WHERE status = 'Delivered'",
        'performance' => "SELECT ROUND(AVG(rating), 1) AS avg_rating FROM deliveries WHERE rating IS NOT NULL"
    ];

    foreach ($queries as $key => $query) {
        $result = mysqli_query($conn, $query);
        $data[$key] = mysqli_fetch_assoc($result)['count'] ?? ($key === 'performance' ? "N/A" : 0);
    }

    // Total orders for pagination
    $total_orders_query = "SELECT COUNT(*) AS total FROM orders";
    $total_result = mysqli_query($conn, $total_orders_query);
    $data['total_orders_count'] = mysqli_fetch_assoc($total_result)['total'];

    // Recent orders with pagination
    $queryRecent = "SELECT order_id, customer_name, destination, status, driver_name, created_at 
                    FROM orders 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
    $stmtRecent = $conn->prepare($queryRecent);
    $stmtRecent->bind_param("ii", $limit, $offset);
    $stmtRecent->execute();
    $resultRecent = $stmtRecent->get_result();
    $data['recent_orders'] = [];
    while ($row = $resultRecent->fetch_assoc()) {
        $data['recent_orders'][] = $row;
    }
    $stmtRecent->close();

    // Delivery performance data (for line chart)
    $queryPerf = "SELECT DATE(created_at) AS date, COUNT(*) AS count 
                  FROM orders 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                  GROUP BY DATE(created_at) 
                  ORDER BY date";
    $resultPerf = mysqli_query($conn, $queryPerf);
    $data['performance_data'] = [];
    while ($row = mysqli_fetch_assoc($resultPerf)) {
        $data['performance_data'][] = [$row['date'], (int)$row['count']];
    }

    // Delivery distribution data (for pie chart)
    $queryDist = "SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
    $resultDist = mysqli_query($conn, $queryDist);
    $data['distribution_data'] = [];
    while ($row = $resultDist->fetch_assoc()) {
        $data['distribution_data'][] = ['name' => $row['status'], 'value' => (int)$row['count']];
    }

    return $data;
}

$dashboardData = fetchDashboardData($conn, $offset, $orders_per_page);
$total_orders = $dashboardData['total_orders_count'];
$total_pages = ceil($total_orders / $orders_per_page);

// Encode data for JavaScript
$performanceDataJson = json_encode($dashboardData['performance_data']);
$distributionDataJson = json_encode($dashboardData['distribution_data']);

mysqli_stmt_close($stmtUser);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeliverX Admin Dashboard</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .chart-container { min-height: 300px; }
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
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            color: #1A1A1A;
            max-height: 70vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
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
            .grid-cols-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .lg\:grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            color: #1A1A1A;
            text-decoration: none;
        }
        .pagination a:hover {
            background-color: #f3f4f6;
        }
        .pagination .active {
            background-color: #1A1A1A;
            color: white;
            border-color: #1A1A1A;
        }
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #e5e5e5;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item span {
            display: block;
            font-size: 0.875rem;
            color: #777;
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="flex h-screen">
        <aside class="w-64 bg-primary text-white fixed h-full sidebar" id="sidebar">
            <div class="p-4">
                <h1 class="text-2xl font-['Pacifico'] mb-8">DeliverX</h1>
                <nav class="space-y-4">
                    <a href="admin_dashboard.php" class="flex items-center space-x-3 p-2 bg-white/10 rounded-button">
                        <span class="w-5 h-5 flex items-center justify-center"><i class="ri-dashboard-line"></i></span>
                        <span>Dashboard</span>
                    </a>
                    <a href="orders.php" class="flex items-center space-x-3 p-2 hover:bg-white/10 rounded-button">
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
                    <div class="relative ml-4">
                        <input type="text" id="searchInput" placeholder="Search orders..." class="pl-10 pr-4 py-2 rounded-button bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/20 w-64" aria-label="Search Orders">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center">
                            <i class="ri-search-line text-gray-400"></i>
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="notificationBtn" class="w-10 h-10 rounded-button bg-gray-100 flex items-center justify-center relative" aria-label="Notifications">
                        <i class="ri-notification-line"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full" id="notificationDot"></span>
                    </button>
                    <span class="hidden md:block text-sm text-gray-600" id="currentDateTime">Loading...</span>
                </div>
            </header>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="statsContainer">
                    <!-- Stats will be loaded via AJAX -->
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold">Delivery Performance</h2>
                            <select id="perfFilter" class="px-3 py-1 text-sm bg-gray-100 rounded-button">
                                <option value="week">Last 7 Days</option>
                                <option value="month">Last 30 Days</option>
                                <option value="year">Last Year</option>
                            </select>
                        </div>
                        <div id="performanceChart" class="chart-container"></div>
                    </div>
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold">Delivery Distribution</h2>
                            <select id="distFilter" class="px-3 py-1 text-sm bg-gray-100 rounded-button">
                                <option value="all">All Time</option>
                            </select>
                        </div>
                        <div id="distributionChart" class="chart-container"></div>
                    </div>
                </div>

                <div class="bg-white rounded shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <h2 class="text-lg font-semibold">Recent Orders</h2>
                            <div class="flex space-x-2">
                                <select id="orderFilter" class="px-3 py-1 text-sm bg-primary text-white rounded-button">
                                    <option value="all">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_transit">In Transit</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                                <button id="sortOrders" class="px-3 py-1 text-sm bg-gray-100 rounded-button">Sort by Date</button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full" id="ordersTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($dashboardData['recent_orders'] as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($order['order_id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['destination']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $order['status'] === 'Delivered' ? 'bg-green-100 text-green-800' : ($order['status'] === 'In Transit' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                                <?= htmlspecialchars($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['driver_name'] ?? 'N/A') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="orders.php" class="text-primary hover:text-primary/80 mr-3">View Details</a>
                                            <a href="track_order.php?order_id=<?= urlencode($order['order_id']) ?>" class="text-gray-400 hover:text-gray-500">Track</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Showing <?= count($dashboardData['recent_orders']) ?> of <?= $total_orders ?> orders
                        </div>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>">Previous</a>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Notifications -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeNotificationModal()">×</span>
            <h2 class="text-xl font-bold mb-4">Notifications</h2>
            <div id="notificationContent" class="space-y-2"></div>
            <div class="mt-4 text-right">
                <button id="markAllRead" class="px-3 py-1 text-sm bg-primary text-white rounded-button hover:bg-gray-800">Mark All as Read</button>
            </div>
        </div>
    </div>

    <script>
        let performanceChart, distributionChart;

        // Initial chart data from PHP
        const initialPerformanceData = <?php echo $performanceDataJson; ?>;
        const initialDistributionData = <?php echo $distributionDataJson; ?>;

        function updateDateTime() {
            const now = new Date();
            document.getElementById('currentDateTime').textContent = now.toLocaleString();
        }

        function fetchNotifications() {
            $.post('fetch_notifications.php', { user_id: <?php echo $user_id; ?> }, function(data) {
                let notifications;
                try {
                    notifications = JSON.parse(data);
                } catch (e) {
                    console.error('Error parsing notifications:', e);
                    return;
                }

                const notificationDot = document.getElementById('notificationDot');
                const notificationContent = document.getElementById('notificationContent');

                if (notifications.error) {
                    console.error('Fetch error:', notifications.error);
                    notificationContent.innerHTML = '<p>Error loading notifications.</p>';
                    notificationDot.style.display = 'none';
                } else if (notifications.length > 0) {
                    notificationDot.style.display = 'block';
                    notificationContent.innerHTML = notifications.map(n => `
                        <div class="notification-item">
                            <p>${n.message}</p>
                            <span>${new Date(n.created_at).toLocaleString()}</span>
                        </div>
                    `).join('');
                } else {
                    notificationDot.style.display = 'none';
                    notificationContent.innerHTML = '<p>No new notifications.</p>';
                }
            });
        }

        function markNotificationsAsRead() {
            $.post('mark_notifications_read.php', { user_id: <?php echo $user_id; ?> }, function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    fetchNotifications(); // Refresh notifications after marking as read
                } else {
                    console.error('Error marking notifications as read:', result.error);
                }
            });
        }

        function closeNotificationModal() {
            document.getElementById('notificationModal').style.display = 'none';
        }

        function fetchStats() {
            $.post('fetch_stats.php', {}, function(data) {
                const stats = JSON.parse(data);
                const container = document.getElementById('statsContainer');
                container.innerHTML = `
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500">Total Orders</div>
                                <div class="text-2xl font-semibold mt-1">${stats.total_orders}</div>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="ri-box-line text-primary text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500">In Transit</div>
                                <div class="text-2xl font-semibold mt-1">${stats.in_transit}</div>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="ri-truck-line text-primary text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500">Completed Today</div>
                                <div class="text-2xl font-semibold mt-1">${stats.completed}</div>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="ri-check-double-line text-primary text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500">Performance Score</div>
                                <div class="text-2xl font-semibold mt-1">${stats.performance}/5.0</div>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="ri-star-line text-primary text-xl"></i>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        function fetchOrders(filter = 'all', sort = 'desc', page = <?php echo $page; ?>) {
            $.post('fetch_orders.php', { filter, sort, page, limit: <?php echo $orders_per_page; ?> }, function(data) {
                const response = JSON.parse(data);
                const orders = response.orders;
                const total = response.total;
                const tbody = document.getElementById('ordersTable').getElementsByTagName('tbody')[0];
                tbody.innerHTML = orders.map(order => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.order_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.customer_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.destination}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${order.status === 'Delivered' ? 'bg-green-100 text-green-800' : order.status === 'In Transit' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'}">
                                ${order.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.driver_name || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="orders.php" class="text-primary hover:text-primary/80 mr-3">View Details</a>
                            <a href="track_order.php?order_id=${encodeURIComponent(order.order_id)}" class="text-gray-400 hover:text-gray-500">Track</a>
                        </td>
                    </tr>
                `).join('');
                const totalPages = Math.ceil(total / <?php echo $orders_per_page; ?>);
                let paginationHtml = '';
                if (page > 1) paginationHtml += `<a href="?page=${page - 1}">Previous</a>`;
                for (let i = 1; i <= totalPages; i++) {
                    paginationHtml += `<a href="?page=${i}" class="${i === page ? 'active' : ''}">${i}</a>`;
                }
                if (page < totalPages) paginationHtml += `<a href="?page=${page + 1}">Next</a>`;
                document.querySelector('.pagination').innerHTML = paginationHtml;
            });
        }

        function fetchPerformanceData(period) {
            let interval;
            switch (period) {
                case 'week': interval = 'INTERVAL 7 DAY'; break;
                case 'month': interval = 'INTERVAL 30 DAY'; break;
                case 'year': interval = 'INTERVAL 1 YEAR'; break;
                default: interval = 'INTERVAL 7 DAY';
            }
            $.post('fetch_performance.php', { period: interval }, function(data) {
                const perfData = JSON.parse(data);
                const dates = perfData.map(item => item[0]);
                const values = perfData.map(item => item[1]);
                performanceChart.setOption({
                    xAxis: { data: dates },
                    series: [{ data: values }]
                });
            });
        }

        function fetchDistributionData(filter) {
            distributionChart.setOption({
                series: [{ data: initialDistributionData }]
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            setInterval(updateDateTime, 1000);

            fetchStats();
            fetchOrders();
            fetchNotifications();
            setInterval(fetchStats, 30000);
            setInterval(fetchNotifications, 30000);

            // Initialize Performance Chart (Line Chart)
            performanceChart = echarts.init(document.getElementById('performanceChart'));
            const perfDates = initialPerformanceData.map(item => item[0]);
            const perfValues = initialPerformanceData.map(item => item[1]);
            performanceChart.setOption({
                title: { text: '' },
                tooltip: { trigger: 'axis' },
                xAxis: {
                    type: 'category',
                    data: perfDates,
                    axisLabel: { rotate: 45 }
                },
                yAxis: { type: 'value', name: 'Orders' },
                series: [{
                    name: 'Orders',
                    type: 'line',
                    data: perfValues,
                    smooth: true,
                    itemStyle: { color: '#1A1A1A' },
                    areaStyle: { opacity: 0.2 }
                }],
                grid: { left: '10%', right: '10%', bottom: '15%' }
            });

            // Initialize Distribution Chart (Pie Chart)
            distributionChart = echarts.init(document.getElementById('distributionChart'));
            distributionChart.setOption({
                title: { text: '' },
                tooltip: { trigger: 'item', formatter: '{a} <br/>{b}: {c} ({d}%)' },
                series: [{
                    name: 'Order Status',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    label: { show: false, position: 'center' },
                    emphasis: {
                        label: { show: true, fontSize: '16', fontWeight: 'bold' }
                    },
                    labelLine: { show: false },
                    data: initialDistributionData,
                    color: ['#34C759', '#00A3E0', '#FF9500', '#FF2D55']
                }]
            });

            // Event listeners
            document.getElementById('perfFilter').addEventListener('change', (e) => fetchPerformanceData(e.target.value));
            document.getElementById('distFilter').addEventListener('change', (e) => fetchDistributionData(e.target.value));
            document.getElementById('orderFilter').addEventListener('change', (e) => fetchOrders(e.target.value));
            document.getElementById('sortOrders').addEventListener('click', () => {
                const currentSort = document.getElementById('sortOrders').dataset.sort || 'desc';
                fetchOrders(document.getElementById('orderFilter').value, currentSort === 'desc' ? 'asc' : 'desc');
                document.getElementById('sortOrders').dataset.sort = currentSort === 'desc' ? 'asc' : 'desc';
                document.getElementById('sortOrders').textContent = currentSort === 'desc' ? 'Sort by Date (ASC)' : 'Sort by Date (DESC)';
            });

            document.getElementById('searchInput').addEventListener('input', function(e) {
                fetchOrders('search', e.target.value);
            });

            document.getElementById('notificationBtn').addEventListener('click', function() {
                document.getElementById('notificationModal').style.display = 'block';
                markNotificationsAsRead(); // Mark as read when opened
            });

            document.getElementsByClassName('close')[0].addEventListener('click', closeNotificationModal);

            document.getElementById('markAllRead').addEventListener('click', function() {
                markNotificationsAsRead();
            });

            window.addEventListener('click', function(event) {
                if (event.target == document.getElementById('notificationModal')) {
                    closeNotificationModal();
                }
            });

            // Mobile menu toggle
            document.getElementById('menuToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('open');
            });

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