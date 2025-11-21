<?php
// Start session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "ditresnarkoba";

// Connect to database
$db = mysqli_connect($hostname, $username, $password, $database_name, 3306);

// Check connection
if ($db->connect_error) {
    echo "Koneksi database rusak";
    die("Error: " . $db->connect_error);
}

// Set charset
mysqli_set_charset($db, "utf8");

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
