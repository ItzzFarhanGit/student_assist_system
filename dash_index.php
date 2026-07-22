<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_login.php");
    exit();
}

require_once __DIR__ . '/dash_db.php';

$username = $_SESSION['username'] ?? 'Student';
$full_name = $_SESSION['full_name'] ?? $username;

// Older sessions (before email was stored) fall back to a quick DB lookup
// so the profile popup always has the student's email to show.
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Assist Dashboard</title>
  <link rel="stylesheet" href="dash_style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>

<body>
<div class="container">

  <!-- SIDEBAR -->
  <div class="sidebar" id="sidebar">
    <div class="logo-box">
      <div class="logo">
        <i class="fa-solid fa-book-open"></i>
      </div>
      <h2>Student Assist</h2>
      <p>Your Study Partner</p>
    </div>

    <div class="menu">
      <button class="active" onclick="window.location.href='dash_index.php'">
        <i class="fa-solid fa-house"></i> Dashboard
      </button>
      <button onclick="window.location.href='res_timetable.php'">
        <i class="fa-solid fa-calendar"></i> Time Table Viewer
      </button>
      <button onclick="window.location.href='res_dashboard.php'">
        <i class="fa-solid fa-graduation-cap"></i> Student Resources
      </button>
      <button onclick="window.location.href='remind_index.php'">
        <i class="fa-solid fa-bell"></i> Reminder
      </button>
      <button onclick="window.location.href='lms_index.php'">
        <i class="fa-solid fa-book"></i> Past Papers
      </button>
    </div>

    <div class="logout">
      <a href="auth_logout.php">
        <button type="button">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </a>
    </div>
  </div>

  <!-- MAIN -->
  <div class="main">
    <div class="topbar">
      <div class="right-section">
        <div class="notification" id="notifBell" style="cursor:pointer;">
          <i class="fa-regular fa-bell"></i>
          <span id="notifBadge"></span>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-empty">Loading...</div>
          </div>
        </div>
        <div class="profile" onclick="openForm()" style="cursor:pointer;">
          <i class="fa-solid fa-circle-user"></i>
          <p>Hello, <?php echo htmlspecialchars($full_name); ?></p>
        </div>
      </div>
    </div>

    <div class="banner">
      <div class="banner-text">
        <h1>Welcome Back, <?php echo htmlspecialchars($full_name); ?>! 👋</h1>
        <p>Stay Organized and achieve your goals.</p>
      </div>
      <img src="dash_assist.jpeg" alt="Student">
    </div>

    <p class="site-credit" style="text-align:center; margin-top:20px; font-size:12px; color:#999;">
      &copy; <?php echo date('Y'); ?> Student Assist &mdash; Owned &amp; Built by Mohamed Farhan
    </p>

<!-- Student Info Modal - auto-filled from the logged-in student's account -->
<div class="modal" id="formModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeForm()">×</span>
    <h3>Student Info</h3>
    <input type="text" name="name" value="<?php echo htmlspecialchars($full_name); ?>" readonly>
    <input type="email" name="email" value="<?php echo htmlspecialchars($student_email); ?>" readonly>
  </div>
</div>

<script>
  // Where the shared reminder-notification helper should fetch today's / upcoming reminders from.
  var REMINDER_NOTIFY_URL = 'remind_reminder_notify.php';
</script>
<script src="remind_notify.js"></script>
<script src="dash_script.js"></script>
<script>
  // Toggle the notification dropdown when the bell is clicked.
  document.getElementById('notifBell').addEventListener('click', function (e) {
    document.getElementById('notifDropdown').classList.toggle('open');
    e.stopPropagation();
  });
  document.addEventListener('click', function () {
    document.getElementById('notifDropdown').classList.remove('open');
  });
</script>
</body>
</html>
