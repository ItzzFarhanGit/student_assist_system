<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'lms_ati';

// Connect without selecting a database first, so we can create it
// automatically if it doesn't exist yet (avoids "Unknown database" errors
// when database.sql hasn't been imported manually).
$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database`")) {
    die('Failed to create database "' . $database . '": ' . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $database)) {
    die('Failed to select database "' . $database . '": ' . mysqli_error($conn));
}

// Auto-create the schema (matches LMS_Trial/database.sql) so the app
// works even on a fresh MySQL install with no manual import step.
$schemaSql = "
CREATE TABLE IF NOT EXISTS years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_id INT NOT NULL,
    semester_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(150) NOT NULL,
    subject_code VARCHAR(50) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_id INT NOT NULL,
    semester_id INT NOT NULL,
    subject_id INT NOT NULL,
    paper_name VARCHAR(180) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS question_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic VARCHAR(180) NOT NULL,
    appeared_count INT NOT NULL,
    difficulty VARCHAR(50) NOT NULL
);
";

foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $stmt) {
    mysqli_query($conn, $stmt);
}

// Seed a bit of starter data only on a brand new, empty database.
$hasYears = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM years"))[0];
if ($hasYears == 0) {
    mysqli_query($conn, "INSERT INTO years (year_name) VALUES ('2026'), ('2025')");
    mysqli_query($conn, "INSERT INTO semesters (year_id, semester_name) VALUES (1, 'Semester 1'), (1, 'Semester 2'), (2, 'Semester 1')");
    mysqli_query($conn, "INSERT INTO subjects (subject_name, subject_code, description) VALUES
        ('Database Management Systems', 'DBS101', 'Introduction to relational databases, SQL, normalization, and database design.'),
        ('Web Application Development', 'WAD201', 'HTML, CSS, JavaScript, PHP, forms, sessions, and simple web application workflows.'),
        ('Software Engineering', 'SWE301', 'Requirements, design, testing, project planning, and software quality practices.')");
    mysqli_query($conn, "INSERT INTO question_analysis (topic, appeared_count, difficulty) VALUES
        ('SQL Joins', 8, 'Medium'),
        ('Normalization', 6, 'Hard'),
        ('PHP Sessions', 5, 'Easy'),
        ('Software Testing', 4, 'Medium')");
}

// Top up the Question Analysis list with 10 extra topics. This runs on every
// request but only inserts a topic if it isn't already in the table, so it
// works both for brand new installs and for databases that were already
// created before these topics existed (no duplicates get created).
$extraTopics = [
    ['ER Diagrams & Schema Design', 7, 'Medium'],
    ['Database Transactions & ACID Properties', 5, 'Hard'],
    ['Indexing & Query Optimization', 6, 'Hard'],
    ['HTML Forms & Validation', 7, 'Easy'],
    ['CSS Flexbox & Grid Layout', 6, 'Medium'],
    ['JavaScript DOM Manipulation', 8, 'Medium'],
    ['PHP Array Functions', 5, 'Easy'],
    ['Requirements Elicitation Techniques', 6, 'Medium'],
    ['Agile & Scrum Methodology', 7, 'Easy'],
    ['Unit Testing vs Integration Testing', 5, 'Medium'],
];

foreach ($extraTopics as $topicRow) {
    list($topic, $count, $difficulty) = $topicRow;
    $topicEsc = mysqli_real_escape_string($conn, $topic);
    $existsRow = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM question_analysis WHERE topic = '$topicEsc'"));
    if ($existsRow && $existsRow[0] == 0) {
        $difficultyEsc = mysqli_real_escape_string($conn, $difficulty);
        mysqli_query($conn, "INSERT INTO question_analysis (topic, appeared_count, difficulty) VALUES ('$topicEsc', " . (int) $count . ", '$difficultyEsc')");
    }
}

// --------------------------------------------------------------------
// Register past-paper PDFs that were dropped straight into
// LMS_Trial/uploads/... instead of going through the admin upload form.
// Without a row in `papers`, those files are invisible to the LMS pages.
// This block finds/creates the matching year, semester and subject, then
// links each known file so it shows up (with proper details) on the
// Subject Selection / Past Paper Library pages. Safe to run every request:
// it looks each piece up before inserting, so nothing is duplicated.
// --------------------------------------------------------------------

function saf_ensure_year($conn, $yearName) {
    $esc = mysqli_real_escape_string($conn, $yearName);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM years WHERE year_name = '$esc'"));
    if ($row) return $row['id'];
    mysqli_query($conn, "INSERT INTO years (year_name) VALUES ('$esc')");
    return mysqli_insert_id($conn);
}

function saf_ensure_semester($conn, $yearId, $semesterName) {
    $esc = mysqli_real_escape_string($conn, $semesterName);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM semesters WHERE year_id = '$yearId' AND semester_name = '$esc'"));
    if ($row) return $row['id'];
    mysqli_query($conn, "INSERT INTO semesters (year_id, semester_name) VALUES ('$yearId', '$esc')");
    return mysqli_insert_id($conn);
}

function saf_ensure_subject($conn, $name, $code, $description) {
    $escCode = mysqli_real_escape_string($conn, $code);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM subjects WHERE subject_code = '$escCode'"));
    if ($row) return $row['id'];
    $escName = mysqli_real_escape_string($conn, $name);
    $escDesc = mysqli_real_escape_string($conn, $description);
    mysqli_query($conn, "INSERT INTO subjects (subject_name, subject_code, description) VALUES ('$escName', '$escCode', '$escDesc')");
    return mysqli_insert_id($conn);
}

function saf_ensure_paper($conn, $yearId, $semesterId, $subjectId, $paperName, $filePath) {
    $escPath = mysqli_real_escape_string($conn, $filePath);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM papers WHERE file_path = '$escPath'"));
    if ($row) return;
    $escName = mysqli_real_escape_string($conn, $paperName);
    mysqli_query($conn, "INSERT INTO papers (year_id, semester_id, subject_id, paper_name, file_path) VALUES ('$yearId', '$semesterId', '$subjectId', '$escName', '$escPath')");
}

$safSubjMIS = saf_ensure_subject($conn, 'Management Information Systems', 'MIS101', 'Role of information systems in supporting business decisions and management.');
$safSubjCNS = saf_ensure_subject($conn, 'Computer Networks & Security', 'CNS101', 'Networking fundamentals, protocols, and security principles.');
$safSubjVIS = saf_ensure_subject($conn, 'Visual Communication', 'VIS101', 'Visual design principles, media, and communication techniques.');
$safSubjCOM = saf_ensure_subject($conn, 'Communication Skills', 'COM101', 'Written and verbal communication skills for academic and professional settings.');
$safSubjWAD = saf_ensure_subject($conn, 'Web Application Development', 'WAD201', 'HTML, CSS, JavaScript, PHP, forms, sessions, and simple web application workflows.');

$safExistingUploads = [
    ['2021', $safSubjMIS, 'Management Information Systems - 2021 Past Paper', 'uploads/2021/Semester_1/1782495358_MIS2021.pdf'],
    ['2021', $safSubjCNS, 'Computer Networks & Security - 2021 Past Paper', 'uploads/2021/Semester_1/1782497354_CNS2021.pdf'],
    ['2021', $safSubjVIS, 'Visual Communication - 2021 Past Paper', 'uploads/2021/Semester_1/1782499006_Visual2021.pdf'],
    ['2021', $safSubjWAD, 'Web Application Development - 2021 Past Paper', 'uploads/2021/Semester_1/1782499265_Web.pdf'],
    ['2021', $safSubjCOM, 'Communication Skills - 2021 Past Paper', 'uploads/2021/Semester_1/1782499427_communication2021.pdf'],

    ['2022', $safSubjMIS, 'Management Information Systems - 2022 Past Paper', 'uploads/2022/Semester_1/1782496601_MIS2022_.pdf'],
    ['2022', $safSubjCNS, 'Computer Networks & Security - 2022 Past Paper', 'uploads/2022/Semester_1/1782498886_CNS2022.pdf'],
    ['2022', $safSubjVIS, 'Visual Communication - 2022 Past Paper', 'uploads/2022/Semester_1/1782499078_Visual2022.pdf'],
    ['2022', $safSubjWAD, 'Web Design - 2022 Past Paper', 'uploads/2022/Semester_1/1782499310_Web_design2022_.pdf'],
    ['2022', $safSubjCOM, 'Communication Skills - 2022 Past Paper', 'uploads/2022/Semester_1/1782499481_Communication2022.pdf'],

    ['2023', $safSubjMIS, 'Management Information Systems - 2023 Past Paper', 'uploads/2023/Semester_1/1782496667_MIS2023.pdf'],
    ['2023', $safSubjVIS, 'Visual Communication - 2023 Past Paper (Part 1)', 'uploads/2023/Semester_1/1782498944_Visual2023.pdf'],
    ['2023', $safSubjVIS, 'Visual Communication - 2023 Past Paper (Part 2)', 'uploads/2023/Semester_1/1782499127_Visual2023.pdf'],
    ['2023', $safSubjWAD, 'Web Design - 2023 Past Paper', 'uploads/2023/Semester_1/1782499360_web_design2023.pdf'],
    ['2023', $safSubjCOM, 'Communication Skills - 2023 Past Paper', 'uploads/2023/Semester_1/1782499516_communication2023.pdf'],
];

foreach ($safExistingUploads as $safUpload) {
    list($safYearName, $safSubjectId, $safPaperName, $safFilePath) = $safUpload;
    $safYearId = saf_ensure_year($conn, $safYearName);
    $safSemesterId = saf_ensure_semester($conn, $safYearId, 'Semester 1');
    saf_ensure_paper($conn, $safYearId, $safSemesterId, $safSubjectId, $safPaperName, $safFilePath);
}
?>
