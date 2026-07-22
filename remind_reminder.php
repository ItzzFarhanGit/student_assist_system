<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_login.php");
    exit();
}
require_once __DIR__ . '/remind_db.php';

$selectedDate = isset($_GET['date']) ? $_GET['date'] : "";

if($selectedDate != "") {
    $safeDate = mysqli_real_escape_string($conn, $selectedDate);
    $sql = "SELECT * FROM reminders WHERE reminder_date='$safeDate' ORDER BY reminder_date ASC, reminder_time ASC";
} else {
    $sql = "SELECT * FROM reminders ORDER BY reminder_date ASC, reminder_time ASC";
}

$result = mysqli_query($conn, $sql);
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reminders - Student Assist</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  :root {
    --accent: #7c3aed;
    --accent-soft: rgba(124,58,237,0.12);
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: linear-gradient(135deg, #eef2ff, #fdf4ff, #fefce8);
    min-height: 100vh;
    display: flex;
  }

  /* SIDEBAR */
  .sidebar {
    width: 240px;
    background: linear-gradient(to bottom, #005eff, #4aa3ff);
    color: white;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    flex-shrink: 0;
  }
  .logo-box { text-align: center; margin-bottom: 32px; }
  .logo-box .logo {
    width: 56px; height: 56px; background: white;
    border-radius: 12px; margin: 0 auto 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #005eff;
  }
  .logo-box h2 { font-size: 20px; }
  .logo-box p { font-size: 12px; color: #dbeafe; margin-top: 4px; }
  .menu { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; }
  .menu a {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; border-radius: 10px;
    color: white; text-decoration: none; font-size: 14px;
    transition: 0.2s;
  }
  .menu a:hover { background: rgba(255,255,255,0.2); }
  .menu a.active { background: rgba(255,255,255,0.3); font-weight: 600; }
  .logout { margin-top: auto; }
  .logout a {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 14px; border-radius: 10px;
    color: white; text-decoration: none; font-size: 14px;
    background: rgba(255,255,255,0.12); transition: 0.2s;
  }
  .logout a:hover { background: rgba(255,255,255,0.22); }

  /* MAIN */
  .main {
    flex: 1;
    padding: 32px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
  }
  .page-header h1 {
    font-size: 26px;
    font-weight: 700;
    color: #2b2b45;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .page-header h1 i { color: var(--accent); }

  .filter-bar {
    display: flex;
    gap: 10px;
    background: white;
    padding: 6px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    width: fit-content;
  }
  .filter-btn {
    border: none; padding: 10px 22px;
    border-radius: 10px; background: transparent;
    cursor: pointer; font-weight: 600; font-size: 14px;
    color: #555; transition: 0.25s;
  }
  .filter-btn:hover { background: #ede9fe; color: var(--accent); }
  .filter-btn.active { background: var(--accent); color: white; }

  .reminder-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
  }

  .card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    border-left: 5px solid #60a5fa;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    transition: 0.25s;
    cursor: pointer;
  }
  .card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,0.12); }
  .card.is-today { border-left-color: #f59e0b; }
  .card h2 { font-size: 16px; color: #1f2937; margin-bottom: 10px; }
  .card p { font-size: 13px; color: #555; margin: 4px 0; }
  .card p b { color: #1e1b2e; }
  .card .badge {
    display: inline-block; font-size: 11px; padding: 3px 9px;
    border-radius: 20px; background: #fef3c7; color: #92400e;
    font-weight: 600; margin-bottom: 8px;
  }

  .empty-state {
    text-align: center; padding: 60px 20px; color: #999;
    grid-column: 1 / -1;
  }
  .empty-state i { font-size: 40px; margin-bottom: 12px; color: #ddd; display: block; }

  .add-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 26px;
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: white; border: none; border-radius: 12px;
    font-size: 15px; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: 0.25s; width: fit-content;
  }
  .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(124,58,237,0.3); }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="logo-box">
    <div class="logo"><i class="fa-solid fa-book-open"></i></div>
    <h2>Student Assist</h2>
    <p>Your Study Partner</p>
  </div>
  <nav class="menu">
    <a href="dash_index.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="res_timetable.php"><i class="fa-solid fa-calendar"></i> Time Table</a>
    <a href="res_pastpaper.php"><i class="fa-solid fa-file"></i> View Paper</a>
    <a href="lms_index.php"><i class="fa-solid fa-book"></i> Past Papers (LMS)</a>
    <a href="remind_reminder.php" class="active"><i class="fa-solid fa-bell"></i> Reminder</a>
  </nav>
  <div class="logout">
    <a href="auth_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>
</div>

<!-- MAIN -->
<div class="main">

  <div class="page-header">
    <h1>
      <i class="fa-solid fa-bell"></i>
      <?php
        if($selectedDate != "") {
          echo "Reminders for " . htmlspecialchars($selectedDate);
        } else {
          echo "Upcoming Reminders";
        }
      ?>
    </h1>

    <div class="filter-bar">
      <button class="filter-btn <?php echo ($selectedDate == '' && !isset($_GET['filter'])) ? 'active' : ''; ?>" onclick="showAll()">All</button>
      <button class="filter-btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'today') ? 'active' : ''; ?>" onclick="showToday()">Today</button>
    </div>
  </div>

  <a href="remind_addreminder.php<?php echo $selectedDate ? '?date='.urlencode($selectedDate) : ''; ?>" class="add-btn">
    <i class="fa-solid fa-plus"></i> Add Reminder
  </a>

  <div class="reminder-grid" id="reminderList">
    <?php
    if($result && mysqli_num_rows($result) > 0) {
      while($row = mysqli_fetch_assoc($result)) {
        $isToday = ($row['reminder_date'] === $today);
        echo "
        <div class='card " . ($isToday ? 'is-today' : '') . "'>
          " . ($isToday ? "<span class='badge'>📅 Today</span>" : "") . "
          <h2>" . htmlspecialchars($row['title']) . "</h2>
          <p><b>Date:</b> " . htmlspecialchars($row['reminder_date']) . "</p>
          <p><b>Time:</b> " . htmlspecialchars($row['reminder_time']) . "</p>
          " . ($row['notes'] ? "<p>" . htmlspecialchars($row['notes']) . "</p>" : "") . "
        </div>";
      }
    } else {
      echo "<div class='empty-state'><i class='fa-regular fa-bell-slash'></i>No reminders found. Add one!</div>";
    }
    ?>
  </div>

</div>

<script>
function showAll() { window.location.href = "reminder.php"; }
function showToday() {
  let today = new Date().toISOString().split('T')[0];
  window.location.href = "reminder.php?date=" + today + "&filter=today";
}

var REMINDER_NOTIFY_URL = 'reminder_notify.php';
</script>
<script src="remind_notify.js"></script>

</body>
</html>
