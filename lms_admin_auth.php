<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin access is now shared with the rest of the system: you must log in
// once via the main login page (as an admin) to reach any admin section,
// instead of having a separate login just for this LMS.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: auth_login.php');
    exit;
}
?>
