<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact_number = $conn->real_escape_string($_POST['contact_number']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = 'driver';

    $query = "INSERT INTO users (fullname, email, contact_number, role, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $fullname, $email, $contact_number, $role, $password);

    if ($stmt->execute()) {
        header("Location: drivers.php?success=Driver+added+successfully");
    } else {
        header("Location: drivers.php?error=Failed+to+add+driver");
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>