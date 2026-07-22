<?php
require_once __DIR__ . '/lms_admin_auth.php';
require_once __DIR__ . '/lms_db.php';

$message = '';
$edit_analysis = null;

if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM question_analysis WHERE id = '$delete_id'");
    $message = 'Analysis deleted successfully.';
}

if (isset($_GET['edit_id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM question_analysis WHERE id = '$edit_id'");
    $edit_analysis = mysqli_fetch_assoc($edit_result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topic = mysqli_real_escape_string($conn, $_POST['topic']);
    $appeared_count = mysqli_real_escape_string($conn, $_POST['appeared_count']);
    $difficulty = mysqli_real_escape_string($conn, $_POST['difficulty']);

    if (isset($_POST['update_id']) && $_POST['update_id'] != '') {
        $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);
        mysqli_query($conn, "UPDATE question_analysis SET topic = '$topic', appeared_count = '$appeared_count', difficulty = '$difficulty' WHERE id = '$update_id'");
        $message = 'Analysis updated successfully.';
        $edit_analysis = null;
    } else {
        mysqli_query($conn, "INSERT INTO question_analysis (topic, appeared_count, difficulty) VALUES ('$topic', '$appeared_count', '$difficulty')");
        $message = 'Analysis added successfully.';
    }
}

require_once 'lms_header.php';
$records = mysqli_query($conn, "SELECT * FROM question_analysis ORDER BY appeared_count DESC");
?>

<span class="eyebrow">Question Trends</span>
<h2><?php if ($edit_analysis) { echo 'Update Analysis'; } else { echo 'Add Analysis'; } ?></h2>

<?php if ($message != '') { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">
    <input type="hidden" name="update_id" value="<?php if ($edit_analysis) { echo $edit_analysis['id']; } ?>">
    <div class="form-row">
        <label>Topic</label>
        <input type="text" name="topic" value="<?php if ($edit_analysis) { echo htmlspecialchars($edit_analysis['topic']); } ?>" required>
    </div>
    <div class="form-row">
        <label>Appeared Count</label>
        <input type="number" name="appeared_count" min="0" value="<?php if ($edit_analysis) { echo $edit_analysis['appeared_count']; } ?>" required>
    </div>
    <div class="form-row">
        <label>Difficulty</label>
        <select name="difficulty" required>
            <option value="">Choose difficulty</option>
            <option value="Easy" <?php if ($edit_analysis && $edit_analysis['difficulty'] == 'Easy') { echo 'selected'; } ?>>Easy</option>
            <option value="Medium" <?php if ($edit_analysis && $edit_analysis['difficulty'] == 'Medium') { echo 'selected'; } ?>>Medium</option>
            <option value="Hard" <?php if ($edit_analysis && $edit_analysis['difficulty'] == 'Hard') { echo 'selected'; } ?>>Hard</option>
        </select>
    </div>
    <button class="btn" type="submit"><?php if ($edit_analysis) { echo 'Update Analysis'; } else { echo 'Save Analysis'; } ?></button>
    <?php if ($edit_analysis) { ?>
        <a class="btn secondary" href="lms_add_analysis.php">Cancel</a>
    <?php } ?>
</form>

<h3>Existing Analysis</h3>
<table>
    <tr>
        <th>Topic</th>
        <th>Appeared Count</th>
        <th>Difficulty</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($records)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['topic']); ?></td>
            <td><?php echo $row['appeared_count']; ?></td>
            <td><?php echo htmlspecialchars($row['difficulty']); ?></td>
            <td>
                <div class="table-actions">
                    <a class="btn secondary" href="lms_add_analysis.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    <a class="btn danger" href="lms_add_analysis.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this analysis record?');">Delete</a>
                </div>
            </td>
        </tr>
    <?php } ?>
</table>

<?php require_once 'lms_footer.php'; ?>
