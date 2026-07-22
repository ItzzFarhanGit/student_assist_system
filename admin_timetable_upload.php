<?php
// ============================================================
// TIMETABLE UPLOAD PAGE
// ============================================================
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: auth_login.php");
    exit();
}

require_once __DIR__ . '/admin_db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $file     = $_FILES['file'];
    $fileName = basename($file['name']);
    $uploadDir = __DIR__ . "/uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $savedName = time() . "_" . $fileName;
    $filePath  = $uploadDir . $savedName;

    // Path relative to the project root so the student-facing viewer in
    // Hamtha(New) can build a correct link to this same file.
    $rootRelativePath = 'uploads/' . $savedName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $sql = "INSERT INTO timetables (title, file_name, file_path) VALUES ('$title', '$savedName', '$rootRelativePath')";
        if (mysqli_query($conn, $sql)) {
            $message = "success";
        } else {
            $message = "db_error";
        }
    } else {
        $message = "file_error";
    }
}

$tables = mysqli_query($conn, "SELECT * FROM timetables ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Timetable Upload - Student Assist</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; display: flex; min-height: 100vh; background: #f0f2f5; }
        .sidebar {
            width: 230px; background: #3b5bdb; color: white;
            display: flex; flex-direction: column; padding: 20px 15px; min-height: 100vh;
        }
        .logo {
            background: white; color: #3b5bdb; width: 52px; height: 52px;
            border-radius: 10px; display: flex; align-items: center;
            justify-content: center; font-size: 26px; font-weight: bold; margin-bottom: 10px;
        }
        .sidebar h2 { font-size: 18px; }
        .sidebar p  { font-size: 12px; color: #c5cae9; margin-bottom: 30px; }
        .sidebar a  { color: white; text-decoration: none; display: block; padding: 10px 12px; border-radius: 8px; margin-bottom: 6px; font-size: 14px; }
        .sidebar a.active { background: rgba(255,255,255,0.25); }
        .sidebar a:hover  { background: rgba(255,255,255,0.15); }
        .logout { margin-top: auto; background: rgba(255,255,255,0.1); border: none; color: white; padding: 11px; border-radius: 8px; cursor: pointer; font-size: 14px; width: 100%; }
        .main  { flex: 1; padding: 30px; overflow-y: auto; }
        .card  { background: white; border-radius: 10px; padding: 28px 30px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
        .card h3 { font-size: 18px; margin-bottom: 20px; color: #333; }
        .row { display: flex; align-items: center; margin-bottom: 14px; }
        .row label { width: 140px; font-size: 14px; font-weight: bold; color: #444; }
        .row input { flex: 1; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; background: #fafafa; }
        .row input:focus { outline: none; border-color: #2196f3; }
        .btn-upload { width: 100%; padding: 13px; background: #2196f3; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin-top: 8px; }
        .btn-upload:hover { background: #1976d2; }
        .alert { padding: 12px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .alert.ok  { background: #e8f5e9; color: #2e7d32; }
        .alert.err { background: #ffebee; color: #c62828; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #2196f3; padding: 10px 8px; border-bottom: 2px solid #eee; font-size: 14px; }
        td { padding: 10px 8px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #444; }
        tr:hover td { background: #f9f9f9; }
        .btn-view { display: inline-block; background: #2196f3; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; font-size: 13px; text-decoration: none; margin-right: 6px; }
        .btn-view:hover { background: #1976d2; }
        .btn-del { background: #f44336; color: white; border: none; padding: 5px 12px; border-radius: 5px; cursor: pointer; font-size: 13px; }
        .btn-del:hover { background: #c62828; }
        .no-data { color: #999; text-align: center; padding: 20px; font-size: 14px; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">S</div>
    <h2>Student Assist</h2>
    <p>Your Study Partner</p>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="admin_timetable_upload.php" class="active">📅 Time Table Upload</a>
    <a href="lms_admin_dashboard.php">📚 Past Paper Upload</a>
    <button class="logout" onclick="location.href='admin_logout.php'">↩ Logout</button>
</div>

<div class="main">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; gap:12px; flex-wrap:wrap;">
        <h2 style="margin:0; color:#333;">Timetable Management</h2>
        <a href="admin_dashboard.php" style="display:inline-block; background:#3b5bdb; color:white; text-decoration:none; padding:8px 14px; border-radius:8px;">← Back to Dashboard</a>
    </div>
    <div class="card">
        <h3>Upload Time Table :</h3>

        <?php if ($message == "success"): ?>
            <div class="alert ok">✅ Timetable uploaded successfully!</div>
        <?php elseif ($message == "db_error"): ?>
            <div class="alert err">❌ Database error: <?php echo mysqli_error($conn); ?></div>
        <?php elseif ($message == "file_error"): ?>
            <div class="alert err">❌ File upload failed.</div>
        <?php endif; ?>

        <form action="admin_timetable_upload.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <label>Title</label>
                <input type="text" name="title" placeholder="Enter Timetable Title" required>
            </div>
            <div class="row">
                <label>Upload File :</label>
                <input type="file" name="file" accept=".pdf,.jpg,.png,.doc,.docx" required>
            </div>
            <button type="submit" class="btn-upload">Upload Timetable</button>
        </form>
    </div>

    <div class="card">
        <h3>Recent Uploaded Timetables :</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tables && mysqli_num_rows($tables) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($tables)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo $row['uploaded_at']; ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($row['file_path'] ? ($row['file_path']) : ('uploads/' . $row['file_name'])); ?>"
                                   target="_blank" class="btn-view">View</a>
                                <a href="admin_delete_timetable.php?id=<?php echo $row['id']; ?>"
                                   onclick="return confirm('Delete this timetable?')">
                                    <button class="btn-del">Delete</button>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="no-data">No timetables uploaded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
