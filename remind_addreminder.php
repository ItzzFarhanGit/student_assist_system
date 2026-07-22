<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_login.php");
    exit();
}
require_once __DIR__ . '/remind_db.php';

if(isset($_POST['save'])) {

    $title = $_POST['title'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    $notify = isset($_POST['notify'])
    ? $_POST['notify']
    : 0;

    $notes = $_POST['notes'];

    $sql = "INSERT INTO reminders
    (title, reminder_date, reminder_time, notify_minutes, notes)

    VALUES
    ('$title','$date','$time','$notify','$notes')";

    if(mysqli_query($conn, $sql)) {

        echo "<script>
        alert('Reminder Saved Successfully!');
        window.location.href='remind_reminder.php';
        </script>";

    } else {

        echo "Error: " . mysqli_error($conn);

    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Add Reminder</title>

	<link rel="stylesheet" href="remind_style.css">

	<link rel="stylesheet"
	href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<div class="container">

	<div class="add-page">

	<h1 class="page-title">
	<i class="fa-solid fa-bell"></i>
	Add Assignment
	</h1>

<form class="form-container" method="POST">

	<label>Title</label>

	<input
	type="text"
	id="title"
	name="title"
	placeholder="Assignment Title"
	required>

	<label>Date</label>

	<input
	type="date"
	id="date"
	name="date"
	oninput="autoSetNotification()"
	required>

	<label>Time</label>

	<input
	type="time"
	id="time"
	name="time"
	oninput="autoSetNotification()"
	required>

	<label>Notification Reminder</label>

	<select id="notify" name="notify" disabled>

	<option value="">
	Select Date and Time first
	</option>

	</select>

	<div class="description">

	<label>Description</label>

	<textarea
	id="notes"
	name="notes"
	placeholder="Add notes..."></textarea>

	</div>

	<div class="button-wrapper">

	<button
	type="submit"
	name="save"
	class="save-btn">

	Save Reminder

	</button>

	</div>

	<a href="remind_reminder.php" class="back-link">
	← Back to Reminder
	</a>

</form>

	</div>

</div>

<script>

function autoSetNotification() {

	let date = document.getElementById('date').value;

	let time = document.getElementById('time').value;

	let notify = document.getElementById('notify');

	if(date && time) {

	notify.disabled = false;

	notify.innerHTML =
	'<option value="0">At time of event</option>' +

	'<option value="5">5 min before</option>' +

	'<option value="15">15 min before</option>';

	}
	else {

	notify.disabled = true;

	notify.innerHTML =
	'<option value="">Select Date and Time first</option>';

	}
}

</script>

</body>
</html>