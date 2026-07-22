<?php
require_once __DIR__ . '/lms_db.php';

$selected_subject = '';
$subject = null;
$subject_papers = null;

if (isset($_GET['subject_id'])) {
    $selected_subject = mysqli_real_escape_string($conn, $_GET['subject_id']);
    $result = mysqli_query($conn, "SELECT * FROM subjects WHERE id = '$selected_subject'");
    $subject = mysqli_fetch_assoc($result);

    if ($subject) {
        $subject_papers = mysqli_query($conn, "
            SELECT papers.*, years.year_name, semesters.semester_name
            FROM papers
            INNER JOIN years ON papers.year_id = years.id
            INNER JOIN semesters ON papers.semester_id = semesters.id
            WHERE papers.subject_id = '$selected_subject'
            ORDER BY years.year_name DESC, semesters.semester_name ASC, papers.paper_name ASC
        ");
    }
}

$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name ASC");
?>

<div class="grid two">
    <div class="glass-card">
        <span class="eyebrow">Course Finder</span>
        <h3>Select a Subject</h3>
        <form method="GET" action="lms_subject_viewer.php" onsubmit="event.preventDefault(); loadSubjectDetails(this);">
            <div class="form-row">
                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" required>
                    <option value="">Choose subject</option>
                    <?php while ($row = mysqli_fetch_assoc($subjects)) { ?>
                        <option value="<?php echo $row['id']; ?>" <?php if ($selected_subject == $row['id']) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($row['subject_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button class="btn" type="submit">Submit</button>
        </form>
    </div>

    <div class="glass-card" id="subject-result">
        <?php if ($subject) { ?>
            <span class="eyebrow">Subject Details</span>
            <h3><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
            <p class="muted">Code: <?php echo htmlspecialchars($subject['subject_code']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($subject['description'])); ?></p>
        <?php } else { ?>
            <span class="eyebrow">Subject Details</span>
            <h3>Pick a subject to begin</h3>
            <p class="muted">Details will appear here after you select a subject and submit.</p>
        <?php } ?>
    </div>
</div>

<?php if ($subject) { ?>
    <div class="glass-card" style="margin-top: 18px;">
        <span class="eyebrow">Subject Past Papers</span>
        <h3><?php echo htmlspecialchars($subject['subject_name']); ?> Papers</h3>

        <?php if ($subject_papers && mysqli_num_rows($subject_papers) > 0) { ?>
            <div class="subject-list">
                <?php while ($paper = mysqli_fetch_assoc($subject_papers)) { ?>
                    <div class="subject-row">
                        <div>
                            <strong><?php echo htmlspecialchars($paper['paper_name']); ?></strong>
                            <p class="muted">
                                <?php echo htmlspecialchars($paper['year_name']); ?> -
                                <?php echo htmlspecialchars($paper['semester_name']); ?>
                            </p>
                        </div>
                        <div class="actions">
                            <a class="btn secondary" href="<?php echo htmlspecialchars($paper['file_path']); ?>" target="_blank">Open PDF</a>
                            <a class="btn" href="<?php echo htmlspecialchars($paper['file_path']); ?>" download>Download PDF</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="muted">No past papers have been uploaded for this subject yet.</p>
        <?php } ?>
    </div>
<?php } ?>
