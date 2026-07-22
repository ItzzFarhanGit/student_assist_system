<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: auth_login.php");
    exit();
}

require_once __DIR__ . '/admin_db.php';

$id  = intval($_GET['id']);
$sql = "DELETE FROM timetables WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    header("Location: admin_timetable_upload.php");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
