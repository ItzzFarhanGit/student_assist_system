<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Assist - Time Table</title>
    <link rel="stylesheet" href="res_style.css">
    <style>
        .btn-view {
            display: inline-block; background: #3a4ec1; color: white; border: none;
            padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px;
            text-decoration: none;
        }
        .btn-view:hover { background: #2f3fa0; }
        .empty { text-align: center; color: #999; padding: 20px; }
        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 18px; gap: 10px; flex-wrap: wrap;
        }
        .page-header h2 { color: #223; }
        .btn-back {
            display: inline-block; background: #3a4ec1; color: white; text-decoration: none;
            padding: 8px 14px; border-radius: 6px; font-size: 14px;
        }
        .btn-back:hover { background: #2f3fa0; }
    </style>
</head>
<body>

<div class="main" style="margin-left:0; max-width: 1000px; margin: 0 auto; padding: 30px;">
    <div class="page-header">
        <h2>Time Tables</h2>
        <a href="res_dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>

    <?php require_once __DIR__ . '/res_db.php'; ?>

    <div class="box">
        <h3>Time Tables Uploaded by Admin :</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM timetables ORDER BY uploaded_at DESC");
                if (!$result || mysqli_num_rows($result) == 0) {
                    echo "<tr><td colspan='3' class='empty'>No time tables uploaded yet.</td></tr>";
                } else {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // file_path is a path relative to the project root
                        // (set by the admin upload page). Fall back to this
                        // module's own uploads/ folder for any legacy rows.
                        $link = !empty($row['file_path']) ? ($row['file_path']) : ('uploads/' . $row['file_name']);

                        echo "<tr>
                            <td>" . htmlspecialchars($row['title']) . "</td>
                            <td>" . htmlspecialchars($row['uploaded_at']) . "</td>
                            <td><a href='" . htmlspecialchars($link) . "' target='_blank' class='btn-view'>View</a></td>
                        </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
