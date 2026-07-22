<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: auth_login.php");
    exit();
}

require_once __DIR__ . '/admin_db.php';

// Get the id from URL: delete_paper.php?id=5
$id = intval($_GET['id']);   // intval() for safety — prevents SQL injection

// Delete the record from database
$sql = "DELETE FROM past_papers WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    header("Location: admin_pastpaper_upload.php");  // Go back to upload page
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
