<?php
// ============================================================
// AUTO SETUP FILE
// Open this FIRST: http://localhost/student_assist/setup.php
// This will create the database and tables automatically.
// ============================================================

// Connect WITHOUT selecting a database (because it doesn't exist yet)
$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("<h2 style='color:red'>❌ Cannot connect to MySQL. Make sure XAMPP is running!</h2>");
}

$errors   = [];
$success  = [];

// Step 1: Create Database
$sql = "CREATE DATABASE IF NOT EXISTS student_assist";
if (mysqli_query($conn, $sql)) {
    $success[] = "✅ Database 'student_assist' created (or already exists)";
} else {
    $errors[] = "❌ Failed to create database: " . mysqli_error($conn);
}

// Step 2: Select the database
mysqli_select_db($conn, "student_assist");

// Step 3: Create past_papers table
$sql = "CREATE TABLE IF NOT EXISTS past_papers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    course      VARCHAR(100) NOT NULL,
    semester    VARCHAR(50)  NOT NULL,
    year        VARCHAR(10)  NOT NULL,
    department  VARCHAR(100) NOT NULL,
    title       VARCHAR(200) NOT NULL,
    file_name   VARCHAR(200) NOT NULL,
    uploaded_at DATETIME     DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
    $success[] = "✅ Table 'past_papers' created (or already exists)";
} else {
    $errors[] = "❌ Failed to create past_papers table: " . mysqli_error($conn);
}

// Step 4: Create timetables table
$sql = "CREATE TABLE IF NOT EXISTS timetables (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    file_name   VARCHAR(200) NOT NULL,
    uploaded_at DATETIME     DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
    $success[] = "✅ Table 'timetables' created (or already exists)";
} else {
    $errors[] = "❌ Failed to create timetables table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup - Student Assist</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; margin: 0;
        }
        .box {
            background: white; border-radius: 12px;
            padding: 40px 50px; max-width: 500px; width: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center;
        }
        h2 { color: #3b5bdb; margin-bottom: 25px; }
        .msg { padding: 10px 15px; border-radius: 6px; margin: 8px 0; text-align: left; font-size: 15px; }
        .ok  { background: #e8f5e9; color: #2e7d32; }
        .err { background: #ffebee; color: #c62828; }
        .btn {
            display: inline-block; margin-top: 25px;
            background: #3b5bdb; color: white; text-decoration: none;
            padding: 12px 30px; border-radius: 8px; font-size: 16px;
        }
        .btn:hover { background: #2f4ac4; }
        .note { margin-top: 15px; font-size: 13px; color: #888; }
    </style>
</head>
<body>
<div class="box">
    <h2>🛠️ Student Assist — Setup</h2>

    <?php foreach ($success as $s): ?>
        <div class="msg ok"><?php echo $s; ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $e): ?>
        <div class="msg err"><?php echo $e; ?></div>
    <?php endforeach; ?>

    <?php if (empty($errors)): ?>
        <a class="btn" href="admin_dashboard.php">🚀 Go to Dashboard</a>
        <p class="note">Setup complete! You can delete setup.php after this.</p>
    <?php else: ?>
        <p class="note" style="color:red;">Fix the errors above and refresh this page.</p>
    <?php endif; ?>
</div>
</body>
</html>
