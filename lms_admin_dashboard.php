<?php
require_once __DIR__ . '/lms_admin_auth.php';
require_once __DIR__ . '/lms_db.php';
require_once __DIR__ . '/lms_header.php';

$year_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM years"));
$semester_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM semesters"));
$subject_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM subjects"));
$paper_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM papers"));
?>

<span class="eyebrow">Overview</span>
<h2>Dashboard</h2>

<div class="grid">
    <div class="glass-card">
        <h3><?php echo $year_count['total']; ?></h3>
        <p class="muted">Years</p>
    </div>
    <div class="glass-card">
        <h3><?php echo $semester_count['total']; ?></h3>
        <p class="muted">Semesters</p>
    </div>
    <div class="glass-card">
        <h3><?php echo $subject_count['total']; ?></h3>
        <p class="muted">Subjects</p>
    </div>
    <div class="glass-card">
        <h3><?php echo $paper_count['total']; ?></h3>
        <p class="muted">Papers</p>
    </div>
</div>

<?php require_once 'lms_footer.php'; ?>
