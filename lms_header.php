<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Admin</title>
    <link rel="stylesheet" href="lms_style.css">
</head>
<body>
<div class="admin-page">
    <div class="admin-layout">
        <aside class="admin-card">
            <div class="brand">
                <img class="logo-image" src="lms_university_logo.svg" alt="University logo">
                <div>
                    <h1>Admin</h1>
                    <p>Control Panel</p>
                </div>
            </div>
            <nav class="admin-menu">
                <a href="admin_dashboard.php">← Main Admin Dashboard</a>
                <a href="lms_admin_dashboard.php">LMS Overview</a>
                <a href="lms_add_year.php">Add Year</a>
                <a href="lms_add_semester.php">Add Semester</a>
                <a href="lms_add_subject.php">Add Subject</a>
                <a href="lms_upload_paper.php">Upload Paper</a>
                <a href="lms_add_analysis.php">Add Analysis</a>
                <a href="lms_index.php">View LMS</a>
                <a href="auth_logout.php">Logout</a>
            </nav>
        </aside>
        <main class="admin-card">
