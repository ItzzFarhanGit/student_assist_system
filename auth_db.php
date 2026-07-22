<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "student_assist";

$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$db`")) {
    die("Failed to create database '$db': " . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $db)) {
    die("Failed to select database '$db': " . mysqli_error($conn));
}

$usersTableSql = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') NOT NULL DEFAULT 'student',
    full_name VARCHAR(150) NOT NULL DEFAULT '',
    otp VARCHAR(10) DEFAULT NULL,
    otp_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username),
    UNIQUE KEY uq_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $usersTableSql)) {
    die("Failed to create users table: " . mysqli_error($conn));
}
?>
