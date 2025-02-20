<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - DeliveryX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: black;
            color: white;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .message-container {
            background-color: #222;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 28rem;
            margin: 6rem auto 2rem;
            flex-grow: 1;
            text-align: center;
        }
        @media (max-width: 640px) {
            .message-container {
                margin: 4rem 1rem 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body class="bg-black">

    <nav class="fixed w-full bg-white/90 backdrop-blur-sm z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="index.php" class="font-['Pacifico'] text-2xl text-black">DeliveryX</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="message-container">
            <h2 class="text-3xl font-bold mb-6 font-['Pacifico'] text-white">Logging Out</h2>
            <p class="text-gray-300">You are being logged out. Redirecting to login page...</p>
        </div>
    </main>

    <script>
        // Normally, the redirect happens instantly, so this UI won't be seen long.
        // This is just a fallback in case the redirect is delayed.
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2000); // Redirect after 2 seconds if header() fails for some reason
    </script>

</body>
</html>