<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DeliveryX - Optimize your logistics with AI-powered route planning, real-time tracking, and customer communication tools.">
    <meta property="og:title" content="DeliveryX - Modern Logistics Management Platform">
    <meta property="og:description" content="Streamline your delivery operations with DeliveryX.">
    <meta property="og:image" content="https://public.readdy.ai/ai/img_res/8edca68043f326dee6d761a88ff28951.jpg">
    <title>DeliveryX - Modern Logistics Management Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .hero-section {
            background-image: url('https://public.readdy.ai/ai/img_res/8edca68043f326dee6d761a88ff28951.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        @media (max-width: 768px) {
            .hero-section { min-height: 60vh; }
            .text-6xl { font-size: 2.5rem; }
            .text-xl { font-size: 1rem; }
            .text-4xl { font-size: 2rem; }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#000000',
                        secondary: '#ffffff'
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
</head>
<body class="bg-white font-['Inter']">
    <nav class="fixed w-full bg-white/90 backdrop-blur-sm z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="#" class="font-['Pacifico'] text-2xl text-primary">DeliveryX</a>
                    <div class="hidden md:flex items-center space-x-8 ml-10">
                        <a href="#features" class="text-gray-900 hover:text-primary">Features</a>
                        <a href="#benefits" class="text-gray-900 hover:text-primary">Benefits</a>
                        <a href="#pricing" class="text-gray-900 hover:text-primary">Pricing</a>
                        <a href="#contact" class="text-gray-900 hover:text-primary">Contact</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="md:hidden p-2 text-gray-900" id="menu-toggle" aria-label="Toggle menu">
                        <i class="ri-menu-line text-primary ri-2x"></i>
                    </button>
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="login.php">
                            <button class="text-gray-900 hover:text-primary px-4 py-2 rounded-button whitespace-nowrap" aria-label="Log In">Log In</button>
                        </a>
                        <a href="register.php">
                            <button class="bg-primary text-white px-4 py-2 rounded-button hover:bg-gray-800 whitespace-nowrap" aria-label="Sign Up">Sign Up</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="md:hidden bg-white shadow-md mt-16" id="mobile-menu" style="display: none;">
                <a href="#features" class="block px-4 py-2 text-gray-900 hover:text-primary">Features</a>
                <a href="#benefits" class="block px-4 py-2 text-gray-900 hover:text-primary">Benefits</a>
                <a href="#pricing" class="block px-4 py-2 text-gray-900 hover:text-primary">Pricing</a>
                <a href="#contact" class="block px-4 py-2 text-gray-900 hover:text-primary">Contact</a>
                <div class="px-4 py-4">
                    <a href="login.php">
                        <button class="text-gray-900 hover:text-primary px-4 py-2 rounded-button w-full whitespace-nowrap">Log In</button>
                    </a>
                    <a href="register.php">
                        <button class="bg-primary text-white px-4 py-2 rounded-button hover:bg-gray-800 w-full mt-2 whitespace-nowrap">Sign Up</button>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section class="relative min-h-screen flex items-center pt-16 overflow-hidden hero-section">
            <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-transparent"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="max-w-2xl">
                    <h1 class="text-6xl font-bold text-primary mb-6">Transform Your Logistics Operations</h1>
                    <p class="text-xl text-gray-700 mb-8">Streamline your delivery management with AI-powered solutions. Optimize routes, track shipments, and delight customers with real-time updates.</p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <button class="bg-primary text-white px-8 py-4 rounded-button text-lg hover:bg-gray-800 whitespace-nowrap w-full sm:w-auto" aria-label="Get Started Free">Get Started Free</button>
                        <button class="border border-primary text-primary px-8 py-4 rounded-button text-lg hover:bg-gray-50 whitespace-nowrap w-full sm:w-auto" aria-label="Schedule Demo">Schedule Demo</button>
                    </div>
                    <div class="mt-12 flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-8">
                        <div class="flex items-center">
                            <i class="ri-shield-check-line text-primary ri-2x"></i>
                            <span class="ml-2 text-gray-700">99.9% Uptime</span>
                        </div>
                        <div class="flex items-center">
                            <i class="ri-customer-service-2-line text-primary ri-2x"></i>
                            <span class="ml-2 text-gray-700">24/7 Support</span>
                        </div>
                        <div class="flex items-center">
                            <i class="ri-global-line text-primary ri-2x"></i>
                            <span class="ml-2 text-gray-700">Global Coverage</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-primary mb-4">Powerful Features</h2>
                    <p class="text-xl text-gray-600">Everything you need to manage your logistics operations efficiently</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                            <i class="ri-route-line text-primary ri-2x"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Smart Route Optimization</h3>
                        <p class="text-gray-600">AI-powered algorithms calculate the most efficient delivery routes, saving time and fuel costs.</p>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                            <i class="ri-map-pin-time-line text-primary ri-2x"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Real-time Tracking</h3>
                        <p class="text-gray-600">Monitor your fleet and shipments in real-time with precise GPS tracking and status updates.</p>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                            <i class="ri-customer-service-2-line text-primary ri-2x"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Customer Communications</h3>
                        <p class="text-gray-600">Automated notifications and real-time updates keep your customers informed at every step.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="benefits" class="py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-primary mb-4">Why Choose DeliveryX</h2>
                    <p class="text-xl text-gray-600">Join thousands of businesses optimizing their delivery operations</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                    <div class="order-2 md:order-1">
                        <img src="https://public.readdy.ai/ai/img_res/f27577ccd810e983af4e10154986e938.jpg" alt="Platform Interface" class="rounded-lg shadow-lg w-full" loading="lazy">
                    </div>
                    <div class="order-1 md:order-2 space-y-8">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="ri-timer-line text-primary ri-2x"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold mb-2">Save Time and Resources</h3>
                                <p class="text-gray-600">Reduce manual work by up to 80% with automated dispatch and route planning.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="ri-line-chart-line text-primary ri-2x"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold mb-2">Increase Efficiency</h3>
                                <p class="text-gray-600">Optimize delivery routes and reduce fuel consumption by up to 30%.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="ri-heart-line text-primary ri-2x"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-semibold mb-2">Improve Customer Satisfaction</h3>
                                <p class="text-gray-600">Provide accurate ETAs and real-time updates to enhance customer experience.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-primary mb-4">Simple, Transparent Pricing</h2>
                    <p class="text-xl text-gray-600">Choose the plan that fits your business needs</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-2xl font-bold mb-4">Starter</h3>
                        <div class="text-4xl font-bold mb-6">$49<span class="text-lg text-gray-500">/month</span></div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Up to 500 deliveries/month</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Basic route optimization</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Email support</span>
                            </li>
                        </ul>
                        <button class="w-full bg-primary text-white px-6 py-3 rounded-button hover:bg-gray-800" aria-label="Get Started - Starter">Get Started</button>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-lg border-2 border-primary relative">
                        <div class="absolute top-0 right-0 bg-primary text-white px-4 py-1 text-sm rounded-button -mt-3">Popular</div>
                        <h3 class="text-2xl font-bold mb-4">Professional</h3>
                        <div class="text-4xl font-bold mb-6">$149<span class="text-lg text-gray-500">/month</span></div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Up to 2000 deliveries/month</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Advanced route optimization</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Priority support</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Real-time tracking</span>
                            </li>
                        </ul>
                        <button class="w-full bg-primary text-white px-6 py-3 rounded-button hover:bg-gray-800" aria-label="Get Started - Professional">Get Started</button>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-2xl font-bold mb-4">Enterprise</h3>
                        <div class="text-4xl font-bold mb-6">Custom</div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Unlimited deliveries</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Custom integration</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">24/7 dedicated support</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-primary ri-lg"></i>
                                <span class="ml-2">Advanced analytics</span>
                            </li>
                        </ul>
                        <button class="w-full border border-primary text-primary px-6 py-3 rounded-button hover:bg-gray-50" aria-label="Contact Sales - Enterprise">Contact Sales</button>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-4xl font-bold text-primary mb-4">Ready to Transform Your Delivery Operations?</h2>
                        <p class="text-xl text-gray-600 mb-8">Get in touch with our team and discover how DeliveryX can help optimize your logistics.</p>
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                                    <i class="ri-mail-line text-primary ri-2x"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Email Us</h3>
                                    <p class="text-gray-600">support@deliveryx.com</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                                    <i class="ri-phone-line text-primary ri-2x"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Call Us</h3>
                                    <p class="text-gray-600">+1 (555) 123-4567</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <form class="space-y-6" id="contactForm">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="name">Name</label>
                                <input type="text" id="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Your name" aria-label="Your name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="email">Email</label>
                                <input type="email" id="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="your@email.com" aria-label="Your email">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="message">Message</label>
                                <textarea id="message" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" rows="4" placeholder="How can we help you?" aria-label="Your message"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-primary text-white px-6 py-3 rounded-button hover:bg-gray-800" aria-label="Send Message">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-gray-50 py-12 mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <a href="#" class="font-['Pacifico'] text-2xl text-primary mb-4 block">DeliveryX</a>
                    <p class="text-gray-600 mb-4">Transforming logistics management with innovative solutions.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary" aria-label="Twitter">
                            <i class="ri-twitter-line ri-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary" aria-label="LinkedIn">
                            <i class="ri-linkedin-line ri-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary" aria-label="Facebook">
                            <i class="ri-facebook-line ri-lg"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Features">Features</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Pricing">Pricing</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Integration">Integration</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="API">API</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="About">About</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Blog">Blog</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Careers">Careers</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Contact">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Privacy">Privacy</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Terms">Terms</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary" aria-label="Security">Security</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-200 mt-12 pt-8 text-center text-gray-600">
                <p>© 2025 DeliveryX. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            menuToggle.addEventListener('click', function() {
                mobileMenu.style.display = mobileMenu.style.display === 'block' ? 'none' : 'block';
            });

            // Form submission
            const form = document.getElementById('contactForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const button = form.querySelector('button');
                button.disabled = true;
                button.textContent = 'Sending...';

                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                // Simulate API call (replace with actual backend endpoint)
                setTimeout(() => {
                    const notification = document.createElement('div');
                    notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300';
                    notification.textContent = 'Message sent successfully!';
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                    }, 3000);

                    button.disabled = false;
                    button.textContent = 'Send Message';
                    form.reset();
                }, 2000);
            });
        });
    </script>
</body>
</html>