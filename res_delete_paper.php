<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: auth_login.php");
    exit();
}
include 'res_db.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM past_papers WHERE id=$id");
header("Location: res_pastpaper.php");
?>
