<?php
require_once __DIR__ . '/lms_admin_auth.php';
require_once __DIR__ . '/lms_db.php';

$message = '';
$edit_year = null;

if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $paper_files = mysqli_query($conn, "SELECT file_path FROM papers WHERE year_id = '$delete_id'");
    while ($paper = mysqli_fetch_assoc($paper_files)) {
        $file_to_delete = $paper['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    mysqli_query($conn, "DELETE FROM years WHERE id = '$delete_id'");
    $message = 'Year deleted successfully.';
}

if (isset($_GET['edit_id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM years WHERE id = '$edit_id'");
    $edit_year = mysqli_fetch_assoc($edit_result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year_name = mysqli_real_escape_string($conn, $_POST['year_name']);

    if (isset($_POST['update_id']) && $_POST['update_id'] != '') {
        $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);
        mysqli_query($conn, "UPDATE years SET year_name = '$year_name' WHERE id = '$update_id'");
        $message = 'Year updated successfully.';
        $edit_year = null;
    } else {
        mysqli_query($conn, "INSERT INTO years (year_name) VALUES ('$year_name')");
        $message = 'Year added successfully.';
    }
}

require_once 'lms_header.php';
$years = mysqli_query($conn, "SELECT * FROM years ORDER BY year_name DESC");
?>

<span class="eyebrow">Academic Setup</span>
<h2><?php if ($edit_year) { echo 'Update Year'; } else { echo 'Add Year'; } ?></h2>

<?php if ($message != '') { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">
    <input type="hidden" name="update_id" value="<?php if ($edit_year) { echo $edit_year['id']; } ?>">
    <div class="form-row">
        <label>Year Name</label>
        <input type="text" name="year_name" placeholder="Example: 2026" value="<?php if ($edit_year) { echo htmlspecialchars($edit_year['year_name']); } ?>" required>
    </div>
    <button class="btn" type="submit"><?php if ($edit_year) { echo 'Update Year'; } else { echo 'Save Year'; } ?></button>
    <?php if ($edit_year) { ?>
        <a class="btn secondary" href="lms_add_year.php">Cancel</a>
    <?php } ?>
</form>

<h3>Existing Years</h3>
<table>
    <tr>
        <th>ID</th>
        <th>Year</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($years)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['year_name']); ?></td>
            <td>
                <div class="table-actions">
                    <a class="btn secondary" href="lms_add_year.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    <a class="btn danger" href="lms_add_year.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this year? Related semesters and papers will also be removed.');">Delete</a>
                </div>
            </td>
        </tr>
    <?php } ?>
</table>

<?php require_once 'lms_footer.php'; ?>
