<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
        $role = 'customer'; // Default role

        if (empty($fullname) || empty($email) || empty($contact_number) || empty($_POST['password'])) {
            throw new Exception("All fields are required.");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Validate contact number (simple check for numbers only)
        if (!preg_match("/^[0-9]{10}$/", $contact_number)) {
            throw new Exception("Invalid contact number. Use 10 digits.");
        }

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            throw new Exception("Email already registered.");
        }

        $checkStmt->close();

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, contact_number, role, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $contact_number, $role, $password);

        if ($stmt->execute()) {
            $success = "Registration successful! Redirecting to login...";
            header("refresh:3;url=login.php"); // Redirect after 3 seconds
        } else {
            throw new Exception("Registration failed. Please try again.");
        }

        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DeliveryX</title>
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
        .register-container {
            background-color: #222;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 28rem;
            margin: 6rem auto 2rem; /* Increased margin-top to avoid navbar overlap */
            flex-grow: 1;
        }
        .error, .success {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 0.375rem;
        }
        .error {
            color: red;
            background-color: rgba(255, 0, 0, 0.1);
        }
        .success {
            color: green;
            background-color: rgba(0, 255, 0, 0.1);
        }
        button:hover {
            background-color: gray;
        }
        @media (max-width: 640px) {
            .register-container {
                margin: 4rem 1rem 1rem;
                padding: 1.5rem;
            }
            .nav-links a {
                font-size: 0.875rem; /* Smaller font on mobile */
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
                    <div class="hidden md:flex items-center space-x-8 ml-10 nav-links">
                        <a href="index.php#features" class="text-gray-900 hover:text-black">Features</a>
                        <a href="index.php#benefits" class="text-gray-900 hover:text-black">Benefits</a>
                        <a href="index.php#pricing" class="text-gray-900 hover:text-black">Pricing</a>
                        <a href="index.php#contact" class="text-gray-900 hover:text-black">Contact</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="md:hidden p-2 text-gray-900" id="menu-toggle" aria-label="Toggle menu">
                        <i class="ri-menu-line text-black ri-2x"></i>
                    </button>
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="login.php">
                            <button class="text-gray-900 hover:text-black px-4 py-2 rounded-button whitespace-nowrap" aria-label="Log In">Log In</button>
                        </a>
                        <a href="register.php">
                            <button class="bg-black text-white px-4 py-2 rounded-button hover:bg-gray-800 whitespace-nowrap" aria-label="Sign Up">Sign Up</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="md:hidden bg-white shadow-md mt-16" id="mobile-menu" style="display: none;">
                <div class="px-4 py-4 space-y-4">
                    <a href="index.php#features" class="block text-gray-900 hover:text-black">Features</a>
                    <a href="index.php#benefits" class="block text-gray-900 hover:text-black">Benefits</a>
                    <a href="index.php#pricing" class="block text-gray-900 hover:text-black">Pricing</a>
                    <a href="index.php#contact" class="block text-gray-900 hover:text-black">Contact</a>
                    <a href="login.php">
                        <button class="text-gray-900 hover:text-black px-4 py-2 rounded-button w-full whitespace-nowrap mt-2">Log In</button>
                    </a>
                    <a href="register.php">
                        <button class="bg-black text-white px-4 py-2 rounded-button hover:bg-gray-800 w-full whitespace-nowrap">Sign Up</button>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="register-container">
            <h2 class="text-3xl font-bold mb-6 text-center font-['Pacifico'] text-white">Register for DeliveryX</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form action="" method="POST" class="space-y-4" id="registerForm">
                <div>
                    <label for="fullname" class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-white focus:border-white bg-white text-black" placeholder="Enter your full name" aria-label="Full Name">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-white focus:border-white bg-white text-black" placeholder="Enter your email" aria-label="Email">
                </div>
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-300 mb-2">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" required pattern="[0-9]{10}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-white focus:border-white bg-white text-black" placeholder="Enter your 10-digit contact number" aria-label="Contact Number">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-white focus:border-white bg-white text-black" placeholder="Enter your password" aria-label="Password">
                </div>
                <button type="submit" class="w-full bg-white text-black px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors" aria-label="Register">Register</button>
            </form>
            <p class="mt-4 text-center text-gray-400">Already have an account? <a href="login.php" class="text-white hover:underline" aria-label="Login">Login</a></p>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            menuToggle.addEventListener('click', function() {
                mobileMenu.style.display = mobileMenu.style.display === 'block' ? 'none' : 'block';
            });

            // Form submission
            const form = document.getElementById('registerForm');
            form.addEventListener('submit', function(e) {
                const button = form.querySelector('button');
                button.disabled = true;
                button.textContent = 'Registering...';
            });
        });
    </script>

</body>
</html>