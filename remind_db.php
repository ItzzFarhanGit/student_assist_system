<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "student_assist";

// Connect without selecting a database first, so we can create it
// automatically if it doesn't exist yet (same approach used by the
// other modules that share this database, e.g. Aroofa/db.php).
$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database`")) {
    die("Failed to create database '$database': " . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $database)) {
    die("Failed to select database '$database': " . mysqli_error($conn));
}

// Auto-create the reminders table so pages in this folder (index.php
// calendar, reminder.php list, addreminder.php, reminder_notify.php)
// never crash with "Table 'student_assist.reminders' doesn't exist"
// even on a fresh MySQL install where the .sql file wasn't imported.
$remindersTableSql = "CREATE TABLE IF NOT EXISTS reminders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    reminder_date DATE NOT NULL,
    reminder_time TIME NOT NULL,
    notify_minutes INT(11) DEFAULT 0,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!mysqli_query($conn, $remindersTableSql)) {
    die("Failed to create reminders table: " . mysqli_error($conn));
}

?>
