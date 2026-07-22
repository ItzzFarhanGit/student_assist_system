<?php
require_once __DIR__ . '/lms_db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATI</title>
    <link rel="stylesheet" href="lms_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="logo-mark"><i class="fa-solid fa-book-open"></i></div>
                <div>
                    <h1>Student Assist</h1>
                    <p>Your Study Partner</p>
                </div>
            </div>

            <nav class="side-nav">
                <button class="nav-btn active" data-page="lms_subject_viewer.php"><i class="fa-solid fa-magnifying-glass"></i> Subject Selection</button>
                <button class="nav-btn" data-page="lms_advanced_analysis.php"><i class="fa-solid fa-chart-line"></i> Advanced Analysis</button>
            </nav>
        </aside>

        <main class="content-area">
            <div class="topbar">
                <div>
                    <span class="eyebrow">Academic Resource Center</span>
                    <h2 id="page-title">Subject Selection</h2>
                </div>
                <div class="top-actions">
                    <a class="admin-icon-link" href="dash_index.php" title="Back to Student Dashboard"><i class="fa-solid fa-house"></i></a>
                </div>
            </div>

            <section id="page-content" class="page-content">
                <div class="loader">Loading...</div>
            </section>
        </main>
    </div>

    <script src="lms_app.js"></script>
</body>
</html>
