<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "student_assist";

// Connect without selecting a database first so we can create it (and
// its tables) automatically if they don't exist yet - avoids fatal
// "Table doesn't exist" errors on a fresh MySQL install.
$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database`")) {
    die("Failed to create database '$database': " . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $database)) {
    die("Failed to select database '$database': " . mysqli_error($conn));
}

// past_papers is shared with the HAMTHA module, which uses a column
// called "title" instead of "paper_title". Both columns are created
// so whichever module's page runs first doesn't break the other.
$pastPapersSql = "CREATE TABLE IF NOT EXISTS past_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course VARCHAR(100),
    semester VARCHAR(10),
    year VARCHAR(10),
    department VARCHAR(100),
    title VARCHAR(200),
    paper_title VARCHAR(200),
    file_name VARCHAR(200),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!mysqli_query($conn, $pastPapersSql)) {
    die("Failed to create past_papers table: " . mysqli_error($conn));
}

$timetablesSql = "CREATE TABLE IF NOT EXISTS timetables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    file_name VARCHAR(200),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!mysqli_query($conn, $timetablesSql)) {
    die("Failed to create timetables table: " . mysqli_error($conn));
}

// file_path stores each file's location as a path relative to the
// project root, so this student-facing viewer can build a correct
// link to a PDF that was actually uploaded from the HAMTHA admin
// panel (a different folder on disk). Added defensively so it also
// works on a database created before this column existed.
function saf_ensure_column($conn, $table, $column, $definition) {
    $result = mysqli_query($conn, "
        SELECT COUNT(*) AS total FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = '$table' AND column_name = '$column'
    ");
    $row = mysqli_fetch_assoc($result);
    if ((int) $row['total'] === 0) {
        mysqli_query($conn, "ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

saf_ensure_column($conn, 'past_papers', 'file_path', 'VARCHAR(500) NULL DEFAULT NULL');
saf_ensure_column($conn, 'timetables', 'file_path', 'VARCHAR(500) NULL DEFAULT NULL');

mysqli_query($conn, "
    UPDATE past_papers
    SET file_path = CONCAT('uploads/', file_name)
    WHERE (file_path IS NULL OR file_path = '') AND file_name IS NOT NULL AND file_name <> ''
");
mysqli_query($conn, "
    UPDATE timetables
    SET file_path = CONCAT('uploads/', file_name)
    WHERE (file_path IS NULL OR file_path = '') AND file_name IS NOT NULL AND file_name <> ''
");
?>
