<?php
// ── DB Connection Test Script ──────────────────────
$host = "localhost";
$user = "root";
$pass = "";
$db   = "student_assist";

echo "<h2>Student Assist – Database Connection Test</h2>";
echo "<pre style='font-family:monospace; font-size:14px; background:#f4f6fb; padding:16px; border-radius:8px;'>";

// 1. Test raw connection (no DB selected)
$conn_raw = mysqli_connect($host, $user, $pass);
if ($conn_raw) {
    echo "[OK]  MySQL server reachable at '$host'\n";
    echo "      Server version : " . mysqli_get_server_info($conn_raw) . "\n\n";
} else {
    echo "[FAIL] Cannot connect to MySQL: " . mysqli_connect_error() . "\n";
    echo "</pre>"; exit();
}

// 2. Test database selection
$conn = mysqli_connect($host, $user, $pass, $db);
if ($conn) {
    echo "[OK]  Database '$db' selected successfully\n\n";
} else {
    echo "[FAIL] Cannot select database '$db': " . mysqli_connect_error() . "\n";
    echo "       → Make sure you ran database.sql first.\n";
    echo "</pre>"; exit();
}

// 3. Check each required table
$required_tables = ['users', 'past_papers', 'timetables', 'reminders'];
echo "── Table Check ───────────────────────────────\n";
foreach ($required_tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        // Count rows
        $cnt = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $table"));
        echo "[OK]  Table '$table' exists  ({$cnt[0]} row" . ($cnt[0] != 1 ? 's' : '') . ")\n";
    } else {
        echo "[FAIL] Table '$table' NOT FOUND – run database.sql\n";
    }
}

// 4. Check default users
echo "\n── Default User Check ────────────────────────\n";
$users_to_check = [
    ['admin',    'admin',   'admin123'],
    ['student1', 'student', 'student123'],
];
foreach ($users_to_check as $u) {
    $uname = $u[0]; $role = $u[1]; $pass_check = $u[2];
    $r = mysqli_query($conn, "SELECT id, username, role FROM users WHERE username='$uname' AND password='$pass_check' AND role='$role'");
    if (mysqli_num_rows($r) == 1) {
        $row = mysqli_fetch_assoc($r);
        echo "[OK]  User '{$row['username']}' (role: {$row['role']}, id: {$row['id']}) found\n";
    } else {
        echo "[FAIL] Default user '$uname' not found or password mismatch\n";
    }
}

// 5. Test a write then rollback (INSERT + DELETE)
echo "\n── Write / Delete Test ───────────────────────\n";
$test_insert = mysqli_query($conn, "INSERT INTO reminders (title, reminder_date, reminder_time) VALUES ('__test_connection__', '2000-01-01', '00:00:00')");
if ($test_insert) {
    $ins_id = mysqli_insert_id($conn);
    mysqli_query($conn, "DELETE FROM reminders WHERE id = $ins_id");
    echo "[OK]  INSERT and DELETE work correctly\n";
} else {
    echo "[FAIL] Write test failed: " . mysqli_error($conn) . "\n";
}

// 6. Upload folder check
echo "\n── Upload Directory Check ────────────────────\n";
$dirs = ['uploads/', 'uploads/papers/', 'uploads/timetables/'];
foreach ($dirs as $dir) {
    $full = __DIR__ . '/' . $dir;
    if (is_dir($full)) {
        $writable = is_writable($full) ? "writable" : "NOT writable";
        echo "[OK]  $dir  ($writable)\n";
    } else {
        echo "[FAIL] $dir does not exist\n";
    }
}

echo "\n── All Checks Complete ──────────────────────\n";
echo "</pre>";

mysqli_close($conn);
?>
