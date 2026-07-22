<?php
require_once __DIR__ . '/lms_admin_auth.php';
require_once __DIR__ . '/lms_db.php';

$message = '';
$edit_semester = null;

if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $paper_files = mysqli_query($conn, "SELECT file_path FROM papers WHERE semester_id = '$delete_id'");
    while ($paper = mysqli_fetch_assoc($paper_files)) {
        $file_to_delete = $paper['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    mysqli_query($conn, "DELETE FROM semesters WHERE id = '$delete_id'");
    $message = 'Semester deleted successfully.';
}

if (isset($_GET['edit_id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM semesters WHERE id = '$edit_id'");
    $edit_semester = mysqli_fetch_assoc($edit_result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year_id = mysqli_real_escape_string($conn, $_POST['year_id']);
    $semester_name = mysqli_real_escape_string($conn, $_POST['semester_name']);

    if (isset($_POST['update_id']) && $_POST['update_id'] != '') {
        $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);
        mysqli_query($conn, "UPDATE semesters SET year_id = '$year_id', semester_name = '$semester_name' WHERE id = '$update_id'");
        $message = 'Semester updated successfully.';
        $edit_semester = null;
    } else {
        mysqli_query($conn, "INSERT INTO semesters (year_id, semester_name) VALUES ('$year_id', '$semester_name')");
        $message = 'Semester added successfully.';
    }
}

require_once 'lms_header.php';
$years = mysqli_query($conn, "SELECT * FROM years ORDER BY year_name DESC");
$semesters = mysqli_query($conn, "SELECT semesters.*, years.year_name FROM semesters INNER JOIN years ON semesters.year_id = years.id ORDER BY years.year_name DESC, semesters.semester_name ASC");
?>

<span class="eyebrow">Academic Setup</span>
<h2><?php if ($edit_semester) { echo 'Update Semester'; } else { echo 'Add Semester'; } ?></h2>

<?php if ($message != '') { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">
    <input type="hidden" name="update_id" value="<?php if ($edit_semester) { echo $edit_semester['id']; } ?>">
    <div class="form-row">
        <label>Year</label>
        <select name="year_id" required>
            <option value="">Choose year</option>
            <?php while ($year = mysqli_fetch_assoc($years)) { ?>
                <option value="<?php echo $year['id']; ?>" <?php if ($edit_semester && $edit_semester['year_id'] == $year['id']) { echo 'selected'; } ?>><?php echo htmlspecialchars($year['year_name']); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-row">
        <label>Semester Name</label>
        <input type="text" name="semester_name" placeholder="Example: Semester 1" value="<?php if ($edit_semester) { echo htmlspecialchars($edit_semester['semester_name']); } ?>" required>
    </div>
    <button class="btn" type="submit"><?php if ($edit_semester) { echo 'Update Semester'; } else { echo 'Save Semester'; } ?></button>
    <?php if ($edit_semester) { ?>
        <a class="btn secondary" href="lms_add_semester.php">Cancel</a>
    <?php } ?>
</form>

<h3>Existing Semesters</h3>
<table>
    <tr>
        <th>Year</th>
        <th>Semester</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($semesters)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['year_name']); ?></td>
            <td><?php echo htmlspecialchars($row['semester_name']); ?></td>
            <td>
                <div class="table-actions">
                    <a class="btn secondary" href="lms_add_semester.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    <a class="btn danger" href="lms_add_semester.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this semester? Related papers will also be removed.');">Delete</a>
                </div>
            </td>
        </tr>
    <?php } ?>
</table>

<?php require_once 'lms_footer.php'; ?>
