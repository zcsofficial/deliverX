<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = trim($_POST['order_id']);

    if (empty($order_id)) {
        echo "<script>alert('Please enter a valid tracking number.'); window.history.back();</script>";
        exit;
    }

    // Redirect to track_order.php with the order_id as a query parameter
    header("Location: track_order.php?order_id=" . urlencode($order_id));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeliverX - Track Your Package</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#000000',
                        secondary: '#4B5563'
                    },
                    borderRadius: {
                        'button': '8px'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white min-h-screen">
    <header class="border-b">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-3xl font-['Pacifico'] text-primary">DeliverX</h1>
            <nav>
                <ul class="flex space-x-6 text-sm">
                    <li><a href="#" class="text-gray-600 hover:text-primary">Track</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-primary">Services</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-primary">Support</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto">
            <section class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Track Your Package</h2>
                <p class="text-gray-600 mb-8">Enter your tracking number to get real-time updates on your delivery</p>
                
                <form action="" method="POST" class="relative">
                    <div class="flex gap-4">
                        <div class="flex-1 relative">
                            <input type="text" name="order_id" id="tracking-input" placeholder="Enter tracking number" 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-button focus:border-primary focus:outline-none text-lg">
                        </div>
                        <button type="submit" class="bg-primary text-white px-8 py-3 !rounded-button hover:bg-gray-800 transition-colors whitespace-nowrap">
                            Track Package
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
