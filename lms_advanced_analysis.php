<?php
require_once __DIR__ . '/lms_db.php';

$analysis = mysqli_query($conn, "SELECT * FROM question_analysis ORDER BY appeared_count DESC");
?>

<div class="glass-card">
    <span class="eyebrow">Question Intelligence</span>
    <h3>Advanced Analysis</h3>

    <?php if (mysqli_num_rows($analysis) == 0) { ?>
        <p class="muted">No analysis records found. Add topics from the admin panel.</p>
    <?php } ?>

    <?php while ($row = mysqli_fetch_assoc($analysis)) { ?>
        <?php
        $count = (int) $row['appeared_count'];
        $percent = $count * 10;
        if ($percent > 100) {
            $percent = 100;
        }
        ?>
        <div class="analysis-row">
            <strong><?php echo htmlspecialchars($row['topic']); ?></strong>
            <span><?php echo $count; ?> times</span>
            <span class="tag"><?php echo htmlspecialchars($row['difficulty']); ?></span>
            <div class="progress" title="<?php echo $percent; ?>%">
                <span style="width: <?php echo $percent; ?>%;"></span>
            </div>
        </div>
    <?php } ?>
</div>
