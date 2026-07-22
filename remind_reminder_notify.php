<?php
/**
 * reminder_notify.php
 *
 * JSON endpoint consumed by notify.js. Returns:
 *   - "today": reminders whose reminder_date is today (used to trigger the
 *              browser Notification API and light up the bell badge)
 *   - "upcoming": the next few reminders from today onward (used to fill
 *              the notification dropdown on the dashboard)
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

require_once __DIR__ . '/remind_db.php';

$today = date('Y-m-d');
$safeToday = mysqli_real_escape_string($conn, $today);

$todayReminders = [];
$todaySql = "SELECT id, title, reminder_date, reminder_time, notes
             FROM reminders
             WHERE reminder_date = '$safeToday'
             ORDER BY reminder_time ASC";
$todayResult = mysqli_query($conn, $todaySql);
if ($todayResult) {
    while ($row = mysqli_fetch_assoc($todayResult)) {
        $row['id'] = (int) $row['id'];
        $todayReminders[] = $row;
    }
}

$upcomingReminders = [];
$upcomingSql = "SELECT id, title, reminder_date, reminder_time, notes
                FROM reminders
                WHERE reminder_date >= '$safeToday'
                ORDER BY reminder_date ASC, reminder_time ASC
                LIMIT 5";
$upcomingResult = mysqli_query($conn, $upcomingSql);
if ($upcomingResult) {
    while ($row = mysqli_fetch_assoc($upcomingResult)) {
        $row['id'] = (int) $row['id'];
        $upcomingReminders[] = $row;
    }
}

echo json_encode([
    'today' => $todayReminders,
    'upcoming' => $upcomingReminders,
]);
