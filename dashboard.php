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

// Initial data fetch (can be updated via AJAX)
function fetchDashboardData($conn) {
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

    // Recent orders (initial fetch, will be updated via AJAX)
    $queryRecent = "SELECT id, customer_name, destination, status, driver_name, created_at FROM orders ORDER BY created_at DESC LIMIT 5";
    $resultRecent = mysqli_query($conn, $queryRecent);
    $data['recent_orders'] = [];
    while ($row = mysqli_fetch_assoc($resultRecent)) {
        $data['recent_orders'][] = $row;
    }

    // Delivery performance data (for chart)
    $queryPerf = "SELECT DATE(created_at) AS date, COUNT(*) AS count FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date";
    $resultPerf = mysqli_query($conn, $queryPerf);
    $data['performance_data'] = [];
    while ($row = mysqli_fetch_assoc($resultPerf)) {
        $data['performance_data'][] = [$row['date'], (int)$row['count']];
    }

    // Delivery distribution data (for pie chart)
    $queryDist = "SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
    $resultDist = mysqli_query($conn, $queryDist);
    $data['distribution_data'] = [];
    while ($row = mysqli_fetch_assoc($resultDist)) {
        $data['distribution_data'][] = ['name' => $row['status'], 'value' => (int)$row['count']];
    }

    return $data;
}

$dashboardData = fetchDashboardData($conn);

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
        @media (max-width: 1024px) {
            .sidebar { width: 0; display: none; }
            main { margin-left: 0 !important; width: 100%; }
            .header { padding-left: 1rem; padding-right: 1rem; }
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="flex h-screen">
        <aside class="w-64 bg-primary text-white fixed h-full sidebar">
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
                        <div class="text-sm text-gray-400">Admin</div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 ml-64">
            <header class="bg-white border-b border-gray-200 px-6 py-4 header">
                <div class="flex items-center justify-between">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search orders..." class="pl-10 pr-4 py-2 rounded-button bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/20 w-64" aria-label="Search Orders">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center">
                            <i class="ri-search-line text-gray-400"></i>
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="notificationBtn" class="w-10 h-10 rounded-button bg-gray-100 flex items-center justify-center relative" aria-label="Notifications">
                            <i class="ri-notification-line"></i>
                            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full" id="notificationDot"></span>
                        </button>
                        <span class="hidden md:block" id="currentDateTime">Loading...</span>
                    </div>
                </div>
            </header>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="statsContainer">
                    <!-- Stats will be loaded via AJAX -->
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold">Delivery Performance</h2>
                            <select id="perfFilter" class="px-3 py-1 text-sm bg-gray-100 rounded-button">
                                <option value="week">Week</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                        </div>
                        <div id="performanceChart" class="chart-container"></div>
                    </div>
                    <div class="bg-white p-6 rounded shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold">Delivery Distribution</h2>
                            <select id="distFilter" class="px-3 py-1 text-sm bg-gray-100 rounded-button">
                                <option value="all">All</option>
                                <option value="status">By Status</option>
                                <option value="date">By Date</option>
                            </select>
                        </div>
                        <div id="distributionChart" class="chart-container"></div>
                    </div>
                </div>

                <div class="bg-white rounded shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
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
                            <tbody class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal for Notifications -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="text-xl font-bold mb-4">Notifications</h2>
            <div id="notificationContent"></div>
        </div>
    </div>

    <script>
        let performanceChart, distributionChart;
        const connData = {
            host: 'localhost',
            user: 'your_user',
            pass: 'your_password',
            db: 'your_database'
        };

        function updateDateTime() {
            const now = new Date();
            document.getElementById('currentDateTime').textContent = now.toLocaleString();
        }

        function fetchNotifications() {
            $.post('fetch_notifications.php', { user_id: <?php echo $user_id; ?> }, function(data) {
                const notifications = JSON.parse(data);
                if (notifications.length > 0) {
                    document.getElementById('notificationDot').style.display = 'block';
                    document.getElementById('notificationContent').innerHTML = notifications.map(n => `<p>${n.message} - ${new Date(n.created_at).toLocaleString()}</p>`).join('');
                } else {
                    document.getElementById('notificationContent').innerHTML = '<p>No new notifications.</p>';
                    document.getElementById('notificationDot').style.display = 'none';
                }
            });
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

        function fetchOrders(filter = 'all', sort = 'desc') {
            $.post('fetch_orders.php', { filter, sort }, function(data) {
                const orders = JSON.parse(data);
                const tbody = document.getElementById('ordersTable').getElementsByTagName('tbody')[0];
                tbody.innerHTML = orders.map(order => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.customer_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.destination}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${order.status === 'Delivered' ? 'bg-green-100 text-green-800' : order.status === 'In Transit' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'}">
                                ${order.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.driver_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button class="text-primary hover:text-primary/80 mr-3" onclick="showModal('${order.id}')">View Details</button>
                            <button class="text-gray-400 hover:text-gray-500" onclick="trackOrder('${order.id}')">Track</button>
                        </td>
                    </tr>
                `).join('');
            });
        }

        function fetchPerformanceData(period) {
            $.post('fetch_performance.php', { period }, function(data) {
                const perfData = JSON.parse(data);
                performanceChart.setOption({
                    xAxis: { data: perfData.dates },
                    series: [{ data: perfData.values }]
                });
            });
        }

        function fetchDistributionData(filter) {
            $.post('fetch_distribution.php', { filter }, function(data) {
                const distData = JSON.parse(data);
                distributionChart.setOption({
                    series: [{ data: distData }]
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            setInterval(updateDateTime, 1000);

            fetchStats();
            fetchOrders();
            fetchNotifications();
            setInterval(fetchStats, 30000); // Update stats every 30 seconds
            setInterval(fetchNotifications, 30000); // Update notifications every 30 seconds

            performanceChart = echarts.init(document.getElementById('performanceChart'));
            distributionChart = echarts.init(document.getElementById('distributionChart'));

            fetchPerformanceData('week');
            fetchDistributionData('all');

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
            });

            document.getElementsByClassName('close')[0].addEventListener('click', function() {
                document.getElementById('notificationModal').style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target == document.getElementById('notificationModal')) {
                    document.getElementById('notificationModal').style.display = 'none';
                }
            });
        });

        function showModal(orderId) {
            alert(`Showing details for Order #${orderId}`);
            // Implement modal logic here (e.g., AJAX call to get order details)
        }

        function trackOrder(orderId) {
            alert(`Tracking Order #${orderId}`);
            // Implement tracking logic here (e.g., open a new window or modal)
        }
    </script>

</body>
</html>