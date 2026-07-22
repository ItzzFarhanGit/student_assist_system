<?php
require_once __DIR__ . '/lms_admin_auth.php';
require_once __DIR__ . '/lms_db.php';

$message = '';
$edit_subject = null;

if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $paper_files = mysqli_query($conn, "SELECT file_path FROM papers WHERE subject_id = '$delete_id'");
    while ($paper = mysqli_fetch_assoc($paper_files)) {
        $file_to_delete = $paper['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    mysqli_query($conn, "DELETE FROM subjects WHERE id = '$delete_id'");
    $message = 'Subject deleted successfully.';
}

if (isset($_GET['edit_id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM subjects WHERE id = '$edit_id'");
    $edit_subject = mysqli_fetch_assoc($edit_result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $subject_code = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if (isset($_POST['update_id']) && $_POST['update_id'] != '') {
        $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);
        mysqli_query($conn, "UPDATE subjects SET subject_name = '$subject_name', subject_code = '$subject_code', description = '$description' WHERE id = '$update_id'");
        $message = 'Subject updated successfully.';
        $edit_subject = null;
    } else {
        mysqli_query($conn, "INSERT INTO subjects (subject_name, subject_code, description) VALUES ('$subject_name', '$subject_code', '$description')");
        $message = 'Subject added successfully.';
    }
}

require_once 'lms_header.php';
$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name ASC");
?>

<span class="eyebrow">Course Setup</span>
<h2><?php if ($edit_subject) { echo 'Update Subject'; } else { echo 'Add Subject'; } ?></h2>

<?php if ($message != '') { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">
    <input type="hidden" name="update_id" value="<?php if ($edit_subject) { echo $edit_subject['id']; } ?>">
    <div class="form-row">
        <label>Subject Name</label>
        <input type="text" name="subject_name" value="<?php if ($edit_subject) { echo htmlspecialchars($edit_subject['subject_name']); } ?>" required>
    </div>
    <div class="form-row">
        <label>Subject Code</label>
        <input type="text" name="subject_code" value="<?php if ($edit_subject) { echo htmlspecialchars($edit_subject['subject_code']); } ?>" required>
    </div>
    <div class="form-row">
        <label>Description</label>
        <input type="text" name="description" value="<?php if ($edit_subject) { echo htmlspecialchars($edit_subject['description']); } ?>" required>
    </div>
    <button class="btn" type="submit"><?php if ($edit_subject) { echo 'Update Subject'; } else { echo 'Save Subject'; } ?></button>
    <?php if ($edit_subject) { ?>
        <a class="btn secondary" href="lms_add_subject.php">Cancel</a>
    <?php } ?>
</form>

<h3>Existing Subjects</h3>
<table>
    <tr>
        <th>Code</th>
        <th>Subject</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($subjects)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td>
                <div class="table-actions">
                    <a class="btn secondary" href="lms_add_subject.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    <a class="btn danger" href="lms_add_subject.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this subject? Related papers will also be removed.');">Delete</a>
                </div>
            </td>
        </tr>
    <?php } ?>
</table>

<?php require_once 'lms_footer.php'; ?>
