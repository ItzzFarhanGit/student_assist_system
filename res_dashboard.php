<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_login.php");
    exit();
}

require_once __DIR__ . '/res_db.php';

// Older sessions (created before email was stored) won't have it yet -
// fall back to a quick lookup so the profile popup always has something to show.
$student_name  = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Student';
$student_email = $_SESSION['email'] ?? '';
if ($student_email === '') {
    $uid = (int) $_SESSION['user_id'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM users WHERE id = $uid"));
    $student_email = $row['email'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Assist - Dashboard</title>
    <link rel="stylesheet" href="res_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .profile-box {
            display: flex; align-items: center; gap: 8px; cursor: pointer;
            background: rgba(255,255,255,0.2); padding: 6px 14px; border-radius: 20px; font-size: 13px;
        }
        .profile-box i { font-size: 20px; }
        .profile-modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            align-items: center; justify-content: center; z-index: 100;
        }
        .profile-modal-overlay.open { display: flex; }
        .profile-modal {
            background: white; border-radius: 12px; padding: 28px 30px; width: 320px;
            position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .profile-modal h3 { margin-bottom: 18px; color: #223; }
        .profile-modal .field { margin-bottom: 14px; }
        .profile-modal label { display: block; font-size: 13px; color: #666; margin-bottom: 4px; }
        .profile-modal input {
            width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px;
            font-size: 14px; background: #fafafa; color: #333;
        }
        .profile-modal .close-btn {
            position: absolute; top: 12px; right: 16px; cursor: pointer; font-size: 20px; color: #999;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <div class="logo-icon">S</div>
        <div>
            <strong>Student Assist</strong>
            <p>Your Study Partner</p>
        </div>
    </div>
    <nav>
        <a href="res_dashboard.php" class="active">🏠 Dashboard</a>
        <a href="res_pastpaper.php">📄 Past Papers</a>
        <a href="res_timetable.php">🗓 Time Table</a>
        <a href="lms_index.php">📚 Past Papers</a>
    </nav>
    <div class="logout"><a href="res_logout.php">↩ Logout</a></div>
</div>

<div class="main">
    <div class="topbar">
        <h2>STUDENT RESOURCES</h2>
        <div class="profile-box" onclick="openProfile()">
            <i class="fa-solid fa-circle-user"></i>
            <span><?php echo htmlspecialchars($student_name); ?></span>
        </div>
    </div>

    <?php
    require_once __DIR__ . '/res_papers_helper.php';
    $all_papers = saf_get_all_past_papers($conn);
    $timetable_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM timetables"))[0];
    $papers = count($all_papers);
    $timetables = $timetable_count;
    $total = $papers + $timetables;
    ?>

    <div class="cards">
        <div class="card orange">
            <span class="count"><?php echo $total; ?></span>
            <span>Total Resources</span>
        </div>
        <div class="card blue">
            <span class="count"><?php echo $papers; ?></span>
            <span>Past Papers Available</span>
        </div>
        <div class="card green">
            <span class="count"><?php echo $timetables; ?></span>
            <span>Time Tables Available</span>
        </div>
    </div>

    <div class="welcome-box">
        Welcome to the Student Assist Resource Center. Use the menu to view past papers and time tables uploaded by the admin.
    </div>

    <div class="box" style="margin-top:20px;">
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

<!-- Profile popup, auto-filled from the logged-in student's account -->
<div class="profile-modal-overlay" id="profileOverlay" onclick="if(event.target===this) closeProfile()">
    <div class="profile-modal">
        <span class="close-btn" onclick="closeProfile()">&times;</span>
        <h3>My Profile</h3>
        <div class="field">
            <label>Name</label>
            <input type="text" value="<?php echo htmlspecialchars($student_name); ?>" readonly>
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" value="<?php echo htmlspecialchars($student_email); ?>" readonly>
        </div>
    </div>
</div>

<script>
function openProfile() {
    document.getElementById('profileOverlay').classList.add('open');
}
function closeProfile() {
    document.getElementById('profileOverlay').classList.remove('open');
}
</script>

</body>
</html>
