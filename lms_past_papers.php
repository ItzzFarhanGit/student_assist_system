<?php
require_once __DIR__ . '/lms_db.php';

$years = mysqli_query($conn, "SELECT * FROM years ORDER BY year_name DESC");
?>

<?php if (mysqli_num_rows($years) == 0) { ?>
    <div class="empty">No papers found. Please add years, semesters, subjects, and papers from the admin panel.</div>
<?php } ?>

<div class="grid">
<?php while ($year = mysqli_fetch_assoc($years)) { ?>
    <div class="year-card">
        <span class="eyebrow">Academic Year</span>
        <h3><?php echo htmlspecialchars($year['year_name']); ?></h3>

        <?php
        $year_id = $year['id'];
        $semesters = mysqli_query($conn, "SELECT * FROM semesters WHERE year_id = '$year_id' ORDER BY semester_name ASC");
        ?>

        <?php while ($semester = mysqli_fetch_assoc($semesters)) { ?>
            <div class="semester-block">
                <h4><?php echo htmlspecialchars($semester['semester_name']); ?></h4>

                <?php
                $semester_id = $semester['id'];
                $papers = mysqli_query($conn, "
                    SELECT papers.*, subjects.subject_name
                    FROM papers
                    INNER JOIN subjects ON papers.subject_id = subjects.id
                    WHERE papers.year_id = '$year_id' AND papers.semester_id = '$semester_id'
                    ORDER BY subjects.subject_name ASC
                ");
                ?>

                <?php if (mysqli_num_rows($papers) == 0) { ?>
                    <p class="muted">No papers uploaded yet.</p>
                <?php } ?>

                <div class="subject-list">
                    <?php while ($paper = mysqli_fetch_assoc($papers)) { ?>
                        <div class="subject-row">
                            <div>
                                <strong><?php echo htmlspecialchars($paper['subject_name']); ?></strong>
                                <p class="muted"><?php echo htmlspecialchars($paper['paper_name']); ?></p>
                            </div>
                            <div class="actions">
                                <a class="btn secondary" href="<?php echo htmlspecialchars($paper['file_path']); ?>" target="_blank">Open PDF</a>
                                <a class="btn" href="<?php echo htmlspecialchars($paper['file_path']); ?>" download>Download PDF</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
</div>
