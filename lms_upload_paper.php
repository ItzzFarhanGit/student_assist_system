<?php
require_once __DIR__ . '/lms_admin_auth.php';
require_once __DIR__ . '/lms_db.php';

$message = '';
$error = '';
$edit_paper = null;

if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $paper_result = mysqli_query($conn, "SELECT file_path FROM papers WHERE id = '$delete_id'");
    $paper = mysqli_fetch_assoc($paper_result);

    if ($paper) {
        $file_to_delete = $paper['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        mysqli_query($conn, "DELETE FROM papers WHERE id = '$delete_id'");
        $message = 'Paper deleted successfully.';
    }
}

if (isset($_GET['edit_id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM papers WHERE id = '$edit_id'");
    $edit_paper = mysqli_fetch_assoc($edit_result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year_id = mysqli_real_escape_string($conn, $_POST['year_id']);
    $semester_id = mysqli_real_escape_string($conn, $_POST['semester_id']);
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
    $paper_name = mysqli_real_escape_string($conn, $_POST['paper_name']);

    $year_result = mysqli_query($conn, "SELECT year_name FROM years WHERE id = '$year_id'");
    $semester_result = mysqli_query($conn, "SELECT semester_name FROM semesters WHERE id = '$semester_id'");
    $year = mysqli_fetch_assoc($year_result);
    $semester = mysqli_fetch_assoc($semester_result);

    if (!$year || !$semester) {
        $error = 'Please select a valid year and semester.';
    } else {
        $database_path = '';
        $has_new_file = isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0;

        if ($has_new_file) {
            $file_name = $_FILES['pdf_file']['name'];
            $file_tmp = $_FILES['pdf_file']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_ext != 'pdf') {
                $error = 'Only PDF files are allowed.';
            } else {
                $safe_year = preg_replace('/[^A-Za-z0-9_-]/', '_', $year['year_name']);
                $safe_semester = preg_replace('/[^A-Za-z0-9_-]/', '_', $semester['semester_name']);
                $upload_dir = 'uploads/' . $safe_year . '/' . $safe_semester . '/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $new_file_name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file_name);
                $server_path = $upload_dir . $new_file_name;
                $database_path = 'uploads/' . $safe_year . '/' . $safe_semester . '/' . $new_file_name;

                if (!move_uploaded_file($file_tmp, $server_path)) {
                    $error = 'File upload failed.';
                }
            }
        }

        if ($error == '') {
            if (isset($_POST['update_id']) && $_POST['update_id'] != '') {
                $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);

                if ($has_new_file) {
                    $old_result = mysqli_query($conn, "SELECT file_path FROM papers WHERE id = '$update_id'");
                    $old_paper = mysqli_fetch_assoc($old_result);
                    if ($old_paper) {
                        $old_file = $old_paper['file_path'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    mysqli_query($conn, "UPDATE papers SET year_id = '$year_id', semester_id = '$semester_id', subject_id = '$subject_id', paper_name = '$paper_name', file_path = '$database_path' WHERE id = '$update_id'");
                } else {
                    mysqli_query($conn, "UPDATE papers SET year_id = '$year_id', semester_id = '$semester_id', subject_id = '$subject_id', paper_name = '$paper_name' WHERE id = '$update_id'");
                }

                $message = 'Paper updated successfully.';
                $edit_paper = null;
            } else {
                if ($has_new_file) {
                    mysqli_query($conn, "INSERT INTO papers (year_id, semester_id, subject_id, paper_name, file_path) VALUES ('$year_id', '$semester_id', '$subject_id', '$paper_name', '$database_path')");
                    $message = 'Paper uploaded successfully.';
                } else {
                    $error = 'Please choose a PDF file.';
                }
            }
        }
    }
}

require_once 'lms_header.php';
$years = mysqli_query($conn, "SELECT * FROM years ORDER BY year_name DESC");
$semesters = mysqli_query($conn, "SELECT semesters.*, years.year_name FROM semesters INNER JOIN years ON semesters.year_id = years.id ORDER BY years.year_name DESC, semesters.semester_name ASC");
$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name ASC");
$papers = mysqli_query($conn, "
    SELECT papers.*, years.year_name, semesters.semester_name, subjects.subject_name
    FROM papers
    INNER JOIN years ON papers.year_id = years.id
    INNER JOIN semesters ON papers.semester_id = semesters.id
    INNER JOIN subjects ON papers.subject_id = subjects.id
    ORDER BY papers.uploaded_at DESC
");
?>

<span class="eyebrow">Paper Library</span>
<h2><?php if ($edit_paper) { echo 'Update Paper'; } else { echo 'Upload Paper'; } ?></h2>

<?php if ($message != '') { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>
<?php if ($error != '') { ?>
    <div class="message error"><?php echo $error; ?></div>
<?php } ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="update_id" value="<?php if ($edit_paper) { echo $edit_paper['id']; } ?>">
    <div class="form-row">
        <label>Year</label>
        <select name="year_id" required>
            <option value="">Choose year</option>
            <?php while ($year = mysqli_fetch_assoc($years)) { ?>
                <option value="<?php echo $year['id']; ?>" <?php if ($edit_paper && $edit_paper['year_id'] == $year['id']) { echo 'selected'; } ?>><?php echo htmlspecialchars($year['year_name']); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-row">
        <label>Semester</label>
        <select name="semester_id" required>
            <option value="">Choose semester</option>
            <?php while ($semester = mysqli_fetch_assoc($semesters)) { ?>
                <option value="<?php echo $semester['id']; ?>" <?php if ($edit_paper && $edit_paper['semester_id'] == $semester['id']) { echo 'selected'; } ?>>
                    <?php echo htmlspecialchars($semester['year_name'] . ' - ' . $semester['semester_name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="form-row">
        <label>Subject</label>
        <select name="subject_id" required>
            <option value="">Choose subject</option>
            <?php while ($subject = mysqli_fetch_assoc($subjects)) { ?>
                <option value="<?php echo $subject['id']; ?>" <?php if ($edit_paper && $edit_paper['subject_id'] == $subject['id']) { echo 'selected'; } ?>><?php echo htmlspecialchars($subject['subject_name']); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-row">
        <label>Paper Name</label>
        <input type="text" name="paper_name" value="<?php if ($edit_paper) { echo htmlspecialchars($edit_paper['paper_name']); } ?>" required>
    </div>
    <div class="form-row">
        <label><?php if ($edit_paper) { echo 'Replace PDF File'; } else { echo 'PDF File'; } ?></label>
        <input type="file" name="pdf_file" accept="application/pdf" <?php if (!$edit_paper) { echo 'required'; } ?>>
        <?php if ($edit_paper) { ?>
            <p class="muted">Leave empty to keep the current PDF.</p>
        <?php } ?>
    </div>
    <button class="btn" type="submit"><?php if ($edit_paper) { echo 'Update Paper'; } else { echo 'Upload Paper'; } ?></button>
    <?php if ($edit_paper) { ?>
        <a class="btn secondary" href="lms_upload_paper.php">Cancel</a>
    <?php } ?>
</form>

<h3>Existing Papers</h3>
<table>
    <tr>
        <th>Year</th>
        <th>Semester</th>
        <th>Subject</th>
        <th>Paper</th>
        <th>PDF</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($papers)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['year_name']); ?></td>
            <td><?php echo htmlspecialchars($row['semester_name']); ?></td>
            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
            <td><?php echo htmlspecialchars($row['paper_name']); ?></td>
            <td><a class="btn secondary" href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">Open</a></td>
            <td>
                <div class="table-actions">
                    <a class="btn secondary" href="lms_upload_paper.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    <a class="btn danger" href="lms_upload_paper.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this paper and its PDF file?');">Delete</a>
                </div>
            </td>
        </tr>
    <?php } ?>
</table>

<?php require_once 'lms_footer.php'; ?>
