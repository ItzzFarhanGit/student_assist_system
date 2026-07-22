-- ============================================================
--  STUDENT ASSIST -- SINGLE COMBINED DATABASE FILE
-- ============================================================
--  This ONE file sets up BOTH databases used by this project.
--  (Every page's own db.php file also auto-creates these same
--   tables on first run, so importing this is optional -- but
--   handy if you want everything ready before you even open
--   the site, or want to see the full schema in one place.)
--
--  DATABASE 1: `student_assist`
--      Used by : auth_db.php, dash_db.php, remind_db.php,
--                res_db.php, admin_db.php
--      Tables  : users, past_papers, timetables, reminders, subjects
--
--  DATABASE 2: `lms_ati`
--      Used by : lms_db.php (all lms_*.php pages)
--      Tables  : years, semesters, subjects, papers, question_analysis
--
--  HOW TO USE:
--   1. Open phpMyAdmin -> Import tab -> choose this file -> Go
--   OR run:  mysql -u root -p < database.sql
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;


-- ############################################################
-- DATABASE 1 : student_assist
-- ############################################################

CREATE DATABASE IF NOT EXISTS `student_assist`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `student_assist`;

-- ------------------------------------------------------------
-- TABLE: users
-- Used by: auth_login.php, auth_register.php, auth_profile.php,
--          auth_forgot_password.php, dash_index.php (session)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`    VARCHAR(100) NOT NULL,
  `email`       VARCHAR(150) NOT NULL,
  `password`    VARCHAR(255) NOT NULL,
  `role`        ENUM('admin','student') NOT NULL DEFAULT 'student',
  `full_name`   VARCHAR(150) NOT NULL DEFAULT '',
  `otp`         VARCHAR(10)  DEFAULT NULL,
  `otp_expires` DATETIME     DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email`    (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default accounts (username / password)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `role`, `full_name`) VALUES
('admin',    'admin@studentassist.com',   'admin123',   'admin',   'Admin User'),
('student1', 'student@studentassist.com', 'student123', 'student', 'John Doe');

-- ------------------------------------------------------------
-- TABLE: past_papers
-- Used by: res_pastpaper.php, res_dashboard.php,
--          admin_pastpaper_upload.php, admin_delete_paper.php
-- (both "title" and "paper_title" kept, plus "file_path", so
--  every page that reads/writes this table stays compatible)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `past_papers` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `course`      VARCHAR(100) DEFAULT NULL,
  `semester`    VARCHAR(50)  DEFAULT NULL,
  `year`        VARCHAR(10)  DEFAULT NULL,
  `department`  VARCHAR(100) DEFAULT NULL,
  `title`       VARCHAR(200) DEFAULT NULL,
  `paper_title` VARCHAR(200) DEFAULT NULL,
  `file_name`   VARCHAR(200) DEFAULT NULL,
  `file_path`   VARCHAR(500) DEFAULT NULL,
  `uploaded_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- TABLE: timetables
-- Used by: res_timetable.php, res_dashboard.php,
--          admin_timetable_upload.php, admin_delete_timetable.php
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `timetables` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(200) DEFAULT NULL,
  `file_name`   VARCHAR(200) DEFAULT NULL,
  `file_path`   VARCHAR(500) DEFAULT NULL,
  `uploaded_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- TABLE: reminders
-- Used by: remind_index.php, remind_reminder.php,
--          remind_addreminder.php, remind_reminder_notify.php
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reminders` (
  `id`             INT NOT NULL AUTO_INCREMENT,
  `title`          VARCHAR(255) NOT NULL,
  `reminder_date`  DATE NOT NULL,
  `reminder_time`  TIME NOT NULL,
  `notify_minutes` INT DEFAULT 0,
  `notes`          TEXT DEFAULT NULL,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `reminders` (`id`, `title`, `reminder_date`, `reminder_time`, `notify_minutes`, `notes`, `created_at`) VALUES
(3, 'exam',        '2026-05-26', '10:30:00', 0, 'wertyui',  '2026-05-23 04:57:33'),
(4, 'exam',        '2026-06-03', '09:06:00', 0, 'qwertyui', '2026-06-03 03:36:26'),
(5, 'CXC',         '2026-06-04', '03:23:00', 5, 'DX',       '2026-06-03 09:53:36'),
(6, 'qwertyui',    '2026-06-14', '09:41:00', 0, 'asdfg',    '2026-06-05 14:11:10'),
(7, 'Assessment',  '2026-06-29', '10:30:00', 5, 'Hi',       '2026-06-27 07:05:18'),
(8, 'Assessment 1','2026-06-27', '14:40:00', 5, 'Hi',       '2026-06-27 08:07:32'),
(9, 'Sports',      '2026-06-30', '15:42:00', 5, 'Hi HI',    '2026-06-27 08:09:47');

-- ------------------------------------------------------------
-- TABLE: subjects (reserved for future subject-tagging feature)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subjects` (
  `id`           INT NOT NULL AUTO_INCREMENT,
  `subject_name` VARCHAR(150) NOT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ############################################################
-- DATABASE 2 : lms_ati  (LMS -- Past Paper Library & Analysis)
-- ############################################################

CREATE DATABASE IF NOT EXISTS `lms_ati`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `lms_ati`;

CREATE TABLE IF NOT EXISTS `years` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year_name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `semesters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year_id` INT NOT NULL,
    `semester_name` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`year_id`) REFERENCES `years`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_name` VARCHAR(150) NOT NULL,
    `subject_code` VARCHAR(50) NOT NULL,
    `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `papers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year_id` INT NOT NULL,
    `semester_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `paper_name` VARCHAR(180) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`year_id`) REFERENCES `years`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`semester_id`) REFERENCES `semesters`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `question_analysis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `topic` VARCHAR(180) NOT NULL,
    `appeared_count` INT NOT NULL,
    `difficulty` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data (years / semesters / subjects)
INSERT INTO `years` (`year_name`)
SELECT * FROM (SELECT '2026' UNION SELECT '2025' UNION SELECT '2021' UNION SELECT '2022' UNION SELECT '2023') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `years` LIMIT 1);

INSERT INTO `semesters` (`year_id`, `semester_name`)
SELECT * FROM (
    SELECT 1, 'Semester 1' UNION SELECT 1, 'Semester 2' UNION SELECT 2, 'Semester 1'
    UNION SELECT 3, 'Semester 1' UNION SELECT 4, 'Semester 1' UNION SELECT 5, 'Semester 1'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `semesters` LIMIT 1);

INSERT INTO `subjects` (`subject_name`, `subject_code`, `description`)
SELECT * FROM (SELECT
    'Database Management Systems' AS n, 'DBS101' AS c, 'Introduction to relational databases, SQL, normalization, and database design.' AS d
    UNION SELECT 'Web Application Development', 'WAD201', 'HTML, CSS, JavaScript, PHP, forms, sessions, and simple web application workflows.'
    UNION SELECT 'Software Engineering', 'SWE301', 'Requirements, design, testing, project planning, and software quality practices.'
    UNION SELECT 'Management Information Systems', 'MIS101', 'Role of information systems in supporting business decisions and management.'
    UNION SELECT 'Computer Networks & Security', 'CNS101', 'Networking fundamentals, protocols, and security principles.'
    UNION SELECT 'Visual Communication', 'VIS101', 'Visual design principles, media, and communication techniques.'
    UNION SELECT 'Communication Skills', 'COM101', 'Written and verbal communication skills for academic and professional settings.'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `subjects` LIMIT 1);

INSERT INTO `question_analysis` (`topic`, `appeared_count`, `difficulty`)
SELECT * FROM (
    SELECT 'SQL Joins' AS t, 8 AS c, 'Medium' AS diff
    UNION SELECT 'Normalization', 6, 'Hard'
    UNION SELECT 'PHP Sessions', 5, 'Easy'
    UNION SELECT 'Software Testing', 4, 'Medium'
    UNION SELECT 'ER Diagrams & Schema Design', 7, 'Medium'
    UNION SELECT 'Database Transactions & ACID Properties', 5, 'Hard'
    UNION SELECT 'Indexing & Query Optimization', 6, 'Hard'
    UNION SELECT 'HTML Forms & Validation', 7, 'Easy'
    UNION SELECT 'CSS Flexbox & Grid Layout', 6, 'Medium'
    UNION SELECT 'JavaScript DOM Manipulation', 8, 'Medium'
    UNION SELECT 'PHP Array Functions', 5, 'Easy'
    UNION SELECT 'Requirements Elicitation Techniques', 6, 'Medium'
    UNION SELECT 'Agile & Scrum Methodology', 7, 'Easy'
    UNION SELECT 'Unit Testing vs Integration Testing', 5, 'Medium'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `question_analysis` LIMIT 1);

-- Past papers that were already sitting inside the LMS uploads folder,
-- registered here so they show up on the Subject Selection /
-- Past Paper Library pages with proper year/semester/subject details.
INSERT INTO `papers` (`year_id`, `semester_id`, `subject_id`, `paper_name`, `file_path`)
SELECT y.id, s.id, sub.id, p.paper_name, p.file_path
FROM (
    SELECT '2021' AS yr, 'MIS101' AS code, 'Management Information Systems - 2021 Past Paper' AS paper_name, 'uploads/2021/Semester_1/1782495358_MIS2021.pdf' AS file_path
    UNION SELECT '2021', 'CNS101', 'Computer Networks & Security - 2021 Past Paper', 'uploads/2021/Semester_1/1782497354_CNS2021.pdf'
    UNION SELECT '2021', 'VIS101', 'Visual Communication - 2021 Past Paper', 'uploads/2021/Semester_1/1782499006_Visual2021.pdf'
    UNION SELECT '2021', 'WAD201', 'Web Application Development - 2021 Past Paper', 'uploads/2021/Semester_1/1782499265_Web.pdf'
    UNION SELECT '2021', 'COM101', 'Communication Skills - 2021 Past Paper', 'uploads/2021/Semester_1/1782499427_communication2021.pdf'
    UNION SELECT '2022', 'MIS101', 'Management Information Systems - 2022 Past Paper', 'uploads/2022/Semester_1/1782496601_MIS2022_.pdf'
    UNION SELECT '2022', 'CNS101', 'Computer Networks & Security - 2022 Past Paper', 'uploads/2022/Semester_1/1782498886_CNS2022.pdf'
    UNION SELECT '2022', 'VIS101', 'Visual Communication - 2022 Past Paper', 'uploads/2022/Semester_1/1782499078_Visual2022.pdf'
    UNION SELECT '2022', 'WAD201', 'Web Design - 2022 Past Paper', 'uploads/2022/Semester_1/1782499310_Web_design2022_.pdf'
    UNION SELECT '2022', 'COM101', 'Communication Skills - 2022 Past Paper', 'uploads/2022/Semester_1/1782499481_Communication2022.pdf'
    UNION SELECT '2023', 'MIS101', 'Management Information Systems - 2023 Past Paper', 'uploads/2023/Semester_1/1782496667_MIS2023.pdf'
    UNION SELECT '2023', 'VIS101', 'Visual Communication - 2023 Past Paper (Part 1)', 'uploads/2023/Semester_1/1782498944_Visual2023.pdf'
    UNION SELECT '2023', 'WAD201', 'Web Design - 2023 Past Paper', 'uploads/2023/Semester_1/1782499360_web_design2023.pdf'
    UNION SELECT '2023', 'COM101', 'Communication Skills - 2023 Past Paper', 'uploads/2023/Semester_1/1782499516_communication2023.pdf'
) AS p
JOIN `years` y ON y.year_name = p.yr
JOIN `semesters` s ON s.year_id = y.id AND s.semester_name = 'Semester 1'
JOIN `subjects` sub ON sub.subject_code = p.code
WHERE NOT EXISTS (SELECT 1 FROM `papers` WHERE `papers`.`file_path` = p.file_path);

SET FOREIGN_KEY_CHECKS = 1;
