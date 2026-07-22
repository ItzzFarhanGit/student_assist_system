<?php
// ============================================================
// DATABASE CONNECTION FILE
// ============================================================
// This file connects to MySQL using XAMPP default settings.
// Include this file in every page that needs database access.

$host     = "localhost";   // XAMPP MySQL runs on localhost
$username = "root";        // XAMPP default username
$password = "";            // XAMPP default password is empty
$database = "student_assist";

// Connect without selecting a database first so we can create it (and
// its tables) automatically if they don't exist yet - avoids fatal
// "Table doesn't exist" errors on a fresh MySQL install.
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database`")) {
    die("Failed to create database '$database': " . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $database)) {
    die("Failed to select database '$database': " . mysqli_error($conn));
}

// past_papers is shared with the Hamtha(New) module, which uses a
// column called "paper_title" instead of "title". Both columns are
// created so whichever module's page runs first doesn't break the other.
$pastPapersSql = "CREATE TABLE IF NOT EXISTS past_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course VARCHAR(100) NOT NULL DEFAULT '',
    semester VARCHAR(50) NOT NULL DEFAULT '',
    year VARCHAR(10) NOT NULL DEFAULT '',
    department VARCHAR(100) NOT NULL DEFAULT '',
    title VARCHAR(200) NOT NULL DEFAULT '',
    paper_title VARCHAR(200) NOT NULL DEFAULT '',
    file_name VARCHAR(200) NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!mysqli_query($conn, $pastPapersSql)) {
    die("Failed to create past_papers table: " . mysqli_error($conn));
}

$timetablesSql = "CREATE TABLE IF NOT EXISTS timetables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    file_name VARCHAR(200) NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!mysqli_query($conn, $timetablesSql)) {
    die("Failed to create timetables table: " . mysqli_error($conn));
}

// ------------------------------------------------------------------
// file_path column: stores the location of the uploaded file as a
// path relative to the project root (the folder that contains
// LMS_Trial, aska, Aroofa, Anjum Safa, HAMTHA, Hamtha(New) ...).
// This is what lets the Hamtha(New) student-facing viewer pages find
// and display a PDF that was actually uploaded from this admin panel,
// since the two modules live in different folders on disk.
// Added defensively via information_schema so it also works on a
// database that was created before this column existed.
// ------------------------------------------------------------------
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

// Backfill any papers/timetables that were uploaded through this admin
// panel before file_path existed, so they immediately become visible
// in the Hamtha(New) student viewer too (their files really do live in
// this module's own uploads/ folder).
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
