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
    <title>Student Assist - Past Papers</title>
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

<div class="main" style="margin-left:0; max-width: 1100px; margin: 0 auto; padding: 30px;">
    <div class="page-header">
        <h2>Past Papers</h2>
        <a href="res_dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>

    <?php
    require_once __DIR__ . '/res_db.php';
    require_once __DIR__ . '/res_papers_helper.php';
    $all_papers = saf_get_all_past_papers($conn);
    ?>

    <div class="box">
        <h3>Past Papers Uploaded by Admin :</h3>
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
                <?php if (empty($all_papers)): ?>
                    <tr><td colspan="5" class="empty">No past papers uploaded yet.</td></tr>
                <?php else: foreach ($all_papers as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['semester']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td><?php echo htmlspecialchars($row['paper_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['year']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($row['link']); ?>" target="_blank" class="btn-view">View</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
