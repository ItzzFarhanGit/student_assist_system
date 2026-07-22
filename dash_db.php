<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "student_assist";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    $error = mysqli_connect_error();
    if (strpos($error, 'Unknown database') !== false) {
        die("Connection failed: Database '$db' does not exist. Please create it in phpMyAdmin and import student_assist/database.sql.");
    }
    die("Connection failed: " . $error);
}
?> 