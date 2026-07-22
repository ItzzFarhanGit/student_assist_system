<?php
// ============================================================
// PAST PAPER UPLOAD PAGE
// - Handles form submission (save file + insert to database)
// - Shows recently uploaded papers from database
// ============================================================
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: auth_login.php");
    exit();
}

require_once __DIR__ . '/admin_db.php';

$message = "";  // Will hold "success", "db_error", or "file_error"

// ── HANDLE FORM SUBMIT ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get values from the form
    $course     = mysqli_real_escape_string($conn, $_POST['course']);
    $semester   = mysqli_real_escape_string($conn, $_POST['semester']);
    $year       = mysqli_real_escape_string($conn, $_POST['year']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $title      = mysqli_real_escape_string($conn, $_POST['title']);

    // File handling
    $file     = $_FILES['file'];
    $fileName = basename($file['name']);
    $uploadDir = __DIR__ . "/uploads/";   // absolute path to uploads folder

    // Create uploads folder if missing
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Give file a unique name using timestamp to avoid overwrite
    $savedName = time() . "_" . $fileName;
    $filePath  = $uploadDir . $savedName;

    // Path relative to the project root (folder that contains LMS_Trial,
    // aska, Aroofa, HAMTHA, Hamtha(New) ...) so the student-facing viewer
    // in Hamtha(New) can build a correct link to this same file.
    $rootRelativePath = 'uploads/' . $savedName;

    // Move uploaded file from temp folder to our uploads folder
    if (move_uploaded_file($file['tmp_name'], $filePath)) {

        // Insert record into past_papers table
        $sql = "INSERT INTO past_papers (course, semester, year, department, title, file_name, file_path)
                VALUES ('$course', '$semester', '$year', '$department', '$title', '$savedName', '$rootRelativePath')";

        if (mysqli_query($conn, $sql)) {
            $message = "success";
        } else {
            $message = "db_error";
        }

    } else {
        $message = "file_error";
    }
}

// ── LOAD RECENT PAPERS ──────────────────────────────────────
// Fetch last 10 uploaded papers to show in the table below
$papers = mysqli_query($conn, "SELECT * FROM past_papers ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Past Paper Upload - Student Assist</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; display: flex; min-height: 100vh; background: #f0f2f5; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 230px; background: #3b5bdb; color: white;
            display: flex; flex-direction: column; padding: 20px 15px;
            min-height: 100vh;
        }
        .logo {
            background: white; color: #3b5bdb;
            width: 52px; height: 52px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; font-weight: bold; margin-bottom: 10px;
        }
        .sidebar h2 { font-size: 18px; }
        .sidebar p  { font-size: 12px; color: #c5cae9; margin-bottom: 30px; }
        .sidebar a {
            color: white; text-decoration: none; display: block;
            padding: 10px 12px; border-radius: 8px; margin-bottom: 6px; font-size: 14px;
        }
        .sidebar a.active { background: rgba(255,255,255,0.25); }
        .sidebar a:hover  { background: rgba(255,255,255,0.15); }
        .logout {
            margin-top: auto; background: rgba(255,255,255,0.1);
            border: none; color: white; padding: 11px;
            border-radius: 8px; cursor: pointer; font-size: 14px; width: 100%;
        }

        /* ── MAIN ── */
        .main { flex: 1; padding: 30px; overflow-y: auto; }

        /* ── CARD ── */
        .card {
            background: white; border-radius: 10px;
            padding: 28px 30px; margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }
        .card h3 { font-size: 18px; margin-bottom: 20px; color: #333; }

        /* ── FORM ROWS ── */
        .row {
            display: flex; align-items: center; margin-bottom: 14px;
        }
        .row label {
            width: 140px; font-size: 14px;
            font-weight: bold; color: #444;
        }
        .row input,
        .row select {
            flex: 1; padding: 10px 14px;
            border: 1px solid #ddd; border-radius: 6px;
            font-size: 14px; background: #fafafa;
        }
        .row input:focus,
        .row select:focus { outline: none; border-color: #2196f3; }

        /* ── BUTTON ── */
        .btn-upload {
            width: 100%; padding: 13px;
            background: #2196f3; color: white;
            border: none; border-radius: 8px;
            font-size: 16px; cursor: pointer; margin-top: 8px;
        }
        .btn-upload:hover { background: #1976d2; }

        /* ── ALERTS ── */
        .alert { padding: 12px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .alert.ok  { background: #e8f5e9; color: #2e7d32; }
        .alert.err { background: #ffebee; color: #c62828; }

        /* ── TABLE ── */
        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left; color: #2196f3;
            padding: 10px 8px; border-bottom: 2px solid #eee; font-size: 14px;
        }
        td { padding: 10px 8px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #444; }
        tr:hover td { background: #f9f9f9; }
        .btn-view {
            display: inline-block; background: #2196f3; color: white; border: none;
            padding: 5px 12px; border-radius: 5px; cursor: pointer; font-size: 13px;
            text-decoration: none; margin-right: 6px;
        }
        .btn-view:hover { background: #1976d2; }
        .btn-del {
            background: #f44336; color: white; border: none;
            padding: 5px 12px; border-radius: 5px; cursor: pointer; font-size: 13px;
        }
        .btn-del:hover { background: #c62828; }
        .no-data { color: #999; text-align: center; padding: 20px; font-size: 14px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">S</div>
    <h2>Student Assist</h2>
    <p>Your Study Partner</p>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="admin_timetable_upload.php">📅 Time Table Upload</a>
    <a href="lms_admin_dashboard.php" class="active">📚 Past Paper Upload</a>
    <button class="logout" onclick="location.href='admin_logout.php'">↩ Logout</button>
</div>

<!-- MAIN -->
<div class="main">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; gap:12px; flex-wrap:wrap;">
        <h2 style="margin:0; color:#333;">Past Paper Management</h2>
        <a href="admin_dashboard.php" style="display:inline-block; background:#3b5bdb; color:white; text-decoration:none; padding:8px 14px; border-radius:8px;">← Back to Dashboard</a>
    </div>

    <!-- UPLOAD FORM -->
    <div class="card">
        <h3>Upload Past Paper :</h3>

        <!-- Show result message after form submit -->
        <?php if ($message == "success"): ?>
            <div class="alert ok">✅ Paper uploaded successfully!</div>
        <?php elseif ($message == "db_error"): ?>
            <div class="alert err">❌ Database error: <?php echo mysqli_error($conn); ?></div>
        <?php elseif ($message == "file_error"): ?>
            <div class="alert err">❌ File upload failed. Check that the uploads/ folder exists.</div>
        <?php endif; ?>

        <!--
            IMPORTANT: enctype="multipart/form-data" is required
            whenever a form uploads a file.
        -->
        <form action="admin_pastpaper_upload.php" method="POST" enctype="multipart/form-data">

            <div class="row">
                <label>Select Course</label>
                <select name="course" required>
                    <option value="">Choose Course</option>
                    <option value="HNDIT">HNDIT</option>
                    <option value="HND Engineering">HND Engineering</option>
                    <option value="HND Business">HND Business</option>
                </select>
            </div>

            <div class="row">
                <label>Semester</label>
                <input type="text" name="semester" placeholder="Enter Semester" required>
            </div>

            <div class="row">
                <label>Year</label>
                <input type="text" name="year" placeholder="Enter Year e.g. 2024" required>
            </div>

            <div class="row">
                <label>Department</label>
                <input type="text" name="department" placeholder="Enter Department" required>
            </div>

            <div class="row">
                <label>Paper Title</label>
                <input type="text" name="title" placeholder="Enter Paper Title" required>
            </div>

            <div class="row">
                <label>Upload File :</label>
                <input type="file" name="file" accept=".pdf,.doc,.docx" required>
            </div>

            <button type="submit" class="btn-upload">Upload Paper</button>

        </form>
    </div>

    <!-- RECENT PAPERS TABLE -->
    <div class="card">
        <h3>Recent Uploaded Papers :</h3>
        <table>
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Course</th>
                    <th>Paper Title</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($papers && mysqli_num_rows($papers) > 0): ?>
                    <!-- Loop through database rows and display each one -->
                    <?php while ($row = mysqli_fetch_assoc($papers)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td><?php echo htmlspecialchars($row['course']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td>
                                <!-- View opens the uploaded PDF; Delete removes the record + file -->
                                <a href="<?php echo htmlspecialchars($row['file_path'] ? ($row['file_path']) : ('uploads/' . $row['file_name'])); ?>"
                                   target="_blank" class="btn-view">View</a>
                                <a href="admin_delete_paper.php?id=<?php echo $row['id']; ?>"
                                   onclick="return confirm('Are you sure you want to delete this paper?')">
                                    <button class="btn-del">Delete</button>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No papers uploaded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div><!-- end main -->
</body>
</html>
