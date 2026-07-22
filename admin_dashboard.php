<?php
// ============================================================
// DASHBOARD PAGE
// Shows count of total uploads, past papers, and timetables
// from the database.
// ============================================================
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: auth_login.php");
    exit();
}

require_once __DIR__ . '/admin_db.php';

// Count total past papers in database
$r1            = mysqli_query($conn, "SELECT COUNT(*) AS total FROM past_papers");
$total_papers  = mysqli_fetch_assoc($r1)['total'];

// Count total timetables in database
$r2               = mysqli_query($conn, "SELECT COUNT(*) AS total FROM timetables");
$total_timetables = mysqli_fetch_assoc($r2)['total'];

// Total = past papers + timetables
$total_uploads = $total_papers + $total_timetables;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Student Assist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; min-height: 100vh; background: #f4f7fb; }

        /* ── SIDEBAR (aska-style gradient with pill nav buttons) ── */
        .sidebar {
            width: 260px; background: linear-gradient(to bottom, #005eff, #4aa3ff); color: white;
            display: flex; flex-direction: column; padding: 25px 20px; min-height: 100vh;
        }
        .logo-box { text-align: center; margin-bottom: 30px; }
        .logo {
            width: 56px; height: 56px; background: white; color: #005eff; margin: auto;
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 26px;
        }
        .logo-box h2 { margin-top: 12px; font-size: 20px; }
        .logo-box p  { font-size: 13px; color: #e0e0e0; }

        .menu { margin-top: 10px; flex: 1; }
        .menu a {
            width: 100%; padding: 13px 15px; border: none; border-radius: 12px; margin-bottom: 12px;
            font-size: 15px; display: flex; align-items: center; gap: 12px; cursor: pointer;
            background: white; color: #333; text-decoration: none;
        }
        .menu a i { width: 18px; text-align: center; }
        .menu a:hover, .menu a.active { background: #003ecb; color: white; }

        .logout-box { margin-top: auto; }
        .logout-box button {
            width: 100%; padding: 12px; border: none; border-radius: 12px; cursor: pointer;
            font-size: 15px; background: rgba(255,255,255,0.15); color: white;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .logout-box button:hover { background: rgba(255,255,255,0.3); }

        /* ── MAIN ── */
        .main { flex: 1; display: flex; flex-direction: column; }
        .topbar {
            background: white; border-bottom: 1px solid #e5e9f2; color: #1c2b4a;
            padding: 20px 30px; font-size: 20px; font-weight: bold;
            display: flex; justify-content: space-between; align-items: center;
        }
        .admin-badge { background: #eef2ff; color: #003ecb; padding: 6px 14px; border-radius: 20px; font-size: 14px; }

        .content { padding: 30px; }

        .cards { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
        .card {
            flex: 1; min-width: 180px; color: white; border-radius: 14px; padding: 25px 20px;
            display: flex; align-items: center; gap: 18px; font-size: 16px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }
        .card .num { font-size: 40px; font-weight: bold; }
        .card.orange { background: #ff9800; }
        .card.blue   { background: #005eff; }
        .card.green  { background: #4caf50; }

        .welcome { background: white; padding: 22px 25px; border-radius: 14px; color: #555; font-size: 15px; box-shadow: 0 6px 16px rgba(0,0,0,0.05); }
        .quick-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
        .quick-link {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 10px;
            text-decoration: none; background: #005eff; color: white; font-size: 14px;
        }
        .quick-link:hover { background: #003ecb; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo-box">
        <div class="logo"><i class="fa-solid fa-book-open"></i></div>
        <h2>Student Assist</h2>
        <p>Admin Panel</p>
    </div>
    <div class="menu">
        <a href="admin_dashboard.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="admin_timetable_upload.php"><i class="fa-solid fa-calendar-plus"></i> Time Table Upload</a>
        <a href="lms_admin_dashboard.php"><i class="fa-solid fa-book"></i> Past Paper Upload</a>
        <a href="dash_index.php"><i class="fa-solid fa-arrow-left"></i> Student Portal</a>
    </div>
    <div class="logout-box">
        <button onclick="location.href='admin_logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
    </div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        ADMIN DASHBOARD
        <span class="admin-badge"><i class="fa-solid fa-user-shield"></i> ADMIN</span>
    </div>

    <div class="content">
        <!-- STAT CARDS — numbers come from PHP queries above -->
        <div class="cards">
            <div class="card orange">
                <span class="num"><?php echo $total_uploads; ?></span>
                <span>Total Uploads</span>
            </div>
            <div class="card blue">
                <span class="num"><?php echo $total_papers; ?></span>
                <span>Past Paper Uploaded</span>
            </div>
            <div class="card green">
                <span class="num"><?php echo $total_timetables; ?></span>
                <span>Time Table Uploaded</span>
            </div>
        </div>

        <div class="welcome">
            Welcome to the Student Assist System Dashboard.
            Use the sections below to manage uploads and system settings.
            <div class="quick-actions">
                <a class="quick-link" href="lms_admin_dashboard.php"><i class="fa-solid fa-file-arrow-up"></i> Upload Past Paper</a>
                <a class="quick-link" href="admin_timetable_upload.php"><i class="fa-solid fa-calendar-plus"></i> Upload Timetable</a>
            </div>
        </div>
    </div>
</div>

<p style="text-align:center; margin:16px 0; font-size:12px; color:#999;">
    &copy; <?php echo date('Y'); ?> Student Assist &mdash; Owned &amp; Built by Mohamed Farhan
</p>

</body>
</html>
