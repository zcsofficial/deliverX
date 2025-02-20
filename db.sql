-- Users Table (For Admins, Customers, and Drivers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    role ENUM('admin', 'customer', 'driver') DEFAULT 'customer',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table (Stores Order Details)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'In Transit', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    eta TIME DEFAULT NULL,
    driver_id INT NULL,
    driver_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Deliveries Table (Tracks Delivery Performance)
CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL,
    driver_id INT NOT NULL,
    driver_name VARCHAR(255) DEFAULT NULL,
    delivery_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rating DECIMAL(2,1) CHECK (rating BETWEEN 1.0 AND 5.0),
    feedback TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tracking Table (Real-time Order Status Updates)
CREATE TABLE tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL,
    driver_id INT NULL,
    status ENUM('Order Placed', 'Out for Pickup', 'In Transit', 'Delivered') DEFAULT 'Order Placed',
    location VARCHAR(255) DEFAULT NULL,
    current_location VARCHAR(255) DEFAULT NULL,
    estimated_time TIMESTAMP DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Payments Table (For Order Payments)
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Credit Card', 'Debit Card', 'PayPal', 'Cash on Delivery') NOT NULL,
    status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    transaction_id VARCHAR(50) UNIQUE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Analytics Table (For Admin Reports & Insights)
CREATE TABLE analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_orders INT DEFAULT 0,
    completed_orders INT DEFAULT 0,
    cancelled_orders INT DEFAULT 0,
    avg_delivery_time DECIMAL(5,2),
    top_driver INT DEFAULT NULL,
    month YEAR NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (top_driver) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications Table (For Sending Alerts to Users)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('Order Update', 'Promotional', 'System Alert') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reviews Table (Customer Feedback for Drivers)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    driver_id INT NOT NULL,
    rating DECIMAL(2,1) CHECK (rating BETWEEN 1.0 AND 5.0),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Support Tickets Table (For Customer Support)
CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Driver Availability Table (Tracking Online Drivers)
CREATE TABLE driver_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
);
