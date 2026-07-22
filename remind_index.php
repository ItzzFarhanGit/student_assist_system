<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_login.php");
    exit();
}
require_once __DIR__ . '/remind_db.php';

$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$daysInMonth = cal_days_in_month(
	CAL_GREGORIAN,
	$currentMonth,
	$currentYear
);

$monthName = date('F', mktime(0, 0, 0, $currentMonth, 1, $currentYear));

$firstDayTimestamp = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$firstDayIndex = (int)date('w', $firstDayTimestamp);

$monthNames = [
	1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
	5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
	9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html>

<head>

<title>Calendar View</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
	box-sizing:border-box;
}

body{
	background: linear-gradient(135deg, #eef2ff, #fdf4ff, #fefce8);
	margin:0;
	padding:0;
	min-height:100vh;
	display:flex;
	align-items:center;
	justify-content:center;
	font-family:Arial,sans-serif;
}

.container{
	background:rgba(255,255,255,0.92);
	width:420px;
	max-width:95vw;
	border-radius:16px;
	padding:24px;
	box-shadow:0 8px 24px rgba(0,0,0,0.15);
}

.top-nav{
	display:flex;
	align-items:center;
	justify-content:space-between;
	margin-bottom:16px;
}

.top-nav a{
	text-decoration:none;
	font-size:13px;
	font-weight:600;
	color:#334155;
	background:#f1f5f9;
	padding:8px 14px;
	border-radius:10px;
	transition:0.2s;
}

.top-nav a:hover{
	background:#e2e8f0;
}

.top-nav a.primary{
	background:#3B82F6;
	color:white;
}

.top-nav a.primary:hover{
	background:#2563eb;
}

.calendar-page h1{
	margin:0 0 16px 0;
	font-size:24px;
	text-align:center;
	color:#1e293b;
}

.selector-bar{
	display:flex;
	gap:10px;
	margin-bottom:16px;
}

.selector-bar select{
	flex:1;
	padding:8px 10px;
	border-radius:10px;
	border:1px solid #ddd;
	background:#f8fafc;
	font-size:15px;
	color:#334155;
	font-family:Arial,sans-serif;
}

.month{
	margin:0 0 18px 0;
	font-size:18px;
	text-align:center;
	color:#64748b;
	font-weight:600;
}

.weekdays{
	display:grid;
	grid-template-columns:repeat(7,1fr);
	gap:10px;
	text-align:center;
	margin-bottom:8px;
}

.weekdays span{
	font-size:13px;
	font-weight:600;
	color:#1e293b;
}

.dates{
	display:grid;
	grid-template-columns:repeat(7,1fr);
	gap:10px;
	text-align:center;
}

.dates a{
	padding:12px 0;
	text-decoration:none;
	color:#334155;
	border-radius:10px;
	background:#f8fafc;
	transition:all 0.2s;
	font-size:15px;
}

.dates a:hover{
	background:#e2e8f0;
	transform:scale(1.08);
}

.dates span.empty{
	visibility:hidden;
}

.dates a.active,
.dates a.active:hover{
	background:#3B82F6;
	color:white;
	font-weight:600;
}

.dates a.today{
	border:2px solid #3B82F6;
}

.dates a{
	display:flex;
	flex-direction:column;
	align-items:center;
	justify-content:center;
	min-height:52px;
	gap:2px;
}

.dates a .day-num{
	font-size:15px;
	line-height:1;
}

.dates a .reminder-tag{
	font-size:9px;
	line-height:1.1;
	max-width:100%;
	overflow:hidden;
	text-overflow:ellipsis;
	white-space:nowrap;
	background:rgba(59,130,246,0.15);
	color:#1d4ed8;
	border-radius:4px;
	padding:1px 4px;
	font-weight:600;
}

.dates a.active .reminder-tag{
	background:rgba(255,255,255,0.25);
	color:#fff;
}

</style>

</head>

<body>

<div class="container">

<div class="calendar-page">

<div class="top-nav">
	<a href="dash_index.php">&larr; Dashboard</a>
	<h1 style="margin:0;">Calendar View</h1>
	<a href="remind_addreminder.php" class="primary">+ Add Reminder</a>
</div>

<form class="selector-bar" method="GET" action="remind_index.php" id="navForm">
	<select name="month" onchange="document.getElementById('navForm').submit()">
		<?php foreach($monthNames as $num => $name) { ?>
			<option value="<?php echo $num; ?>" <?php echo ($num == $currentMonth) ? 'selected' : ''; ?>>
				<?php echo $name; ?>
			</option>
		<?php } ?>
	</select>
	<select name="year" onchange="document.getElementById('navForm').submit()">
		<?php for($y = (int)date('Y') - 5; $y <= (int)date('Y') + 5; $y++) { ?>
			<option value="<?php echo $y; ?>" <?php echo ($y == $currentYear) ? 'selected' : ''; ?>>
				<?php echo $y; ?>
			</option>
		<?php } ?>
	</select>
</form>

<div class="page-body">

<div class="month">

<?php echo $monthName . ' ' . $currentYear; ?>

</div>

<div class="weekdays">
	<span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
</div>

<div class="dates">

<?php

$sql = "SELECT reminder_date, title FROM reminders ORDER BY reminder_time ASC";

$result = mysqli_query($conn, $sql);

// Group reminder titles by date so each day cell can show what's on it
$remindersByDate = [];

while($row = mysqli_fetch_assoc($result)) {
	$remindersByDate[$row['reminder_date']][] = $row['title'];
}

for($i = 0; $i < $firstDayIndex; $i++) {
	echo "<span class='empty'></span>";
}

for($d = 1; $d <= $daysInMonth; $d++) {

	$dateStr =
	$currentYear . '-' .
	str_pad($currentMonth,2,'0',STR_PAD_LEFT)
	. '-' .
	str_pad($d,2,'0',STR_PAD_LEFT);

	$classes = [];
	$dayReminders = isset($remindersByDate[$dateStr]) ? $remindersByDate[$dateStr] : [];

	if(!empty($dayReminders)) {
		$classes[] = 'active';
	}

	if($dateStr === $today) {
		$classes[] = 'today';
	}

	$classAttr = implode(' ', $classes);

	// Show the first reminder's title under the date; if there is more
	// than one reminder that day, add a "+N more" hint.
	$tagHtml = '';
	if(!empty($dayReminders)) {
		$firstTitle = htmlspecialchars($dayReminders[0]);
		$extraCount = count($dayReminders) - 1;
		$tagText = $extraCount > 0 ? "$firstTitle +$extraCount" : $firstTitle;
		$tagHtml = "<span class='reminder-tag' title='" . htmlspecialchars(implode(', ', $dayReminders)) . "'>$tagText</span>";
	}

	echo "
	<a href='remind_reminder.php?date=$dateStr'
	class='$classAttr'>
	<span class='day-num'>$d</span>
	$tagHtml
	</a>
	";

}

?>

</div>

</div>

</div>

</div>

<script>
	var REMINDER_NOTIFY_URL = 'reminder_notify.php';
</script>
<script src="remind_notify.js"></script>

</body>
</html>
