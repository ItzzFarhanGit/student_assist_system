<?php
// ============================================================
// Merges past papers from BOTH admin sources into one list:
//   1) student_assist.past_papers   - uploaded via the old simple
//                                      HAMTHA "Past Paper Upload" form
//   2) lms_ati.papers (+ years/semesters/subjects) - uploaded via the
//                                      LMS_Trial admin (Year > Semester
//                                      > Subject > Paper)
// Both databases live on the same MySQL server, so we can reach the
// LMS tables with a fully-qualified `lms_ati.papers` name using the
// same connection - no second mysqli_connect() needed.
// ============================================================
function saf_get_all_past_papers($conn) {
    $papers = [];

    // --- Source 1: the older shared past_papers table ---
    $result = mysqli_query($conn, "SELECT * FROM past_papers ORDER BY uploaded_at DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $displayTitle = !empty($row['paper_title']) ? $row['paper_title'] : $row['title'];
            $link = !empty($row['file_path']) ? ($row['file_path']) : ('uploads/' . $row['file_name']);

            $papers[] = [
                'semester'    => $row['semester'],
                'course'      => $row['course'],
                'paper_title' => $displayTitle,
                'year'        => $row['year'],
                'link'        => $link,
                'uploaded_at' => $row['uploaded_at'],
            ];
        }
    }

    // --- Source 2: LMS_Trial's structured papers table ---
    $lmsResult = mysqli_query($conn, "
        SELECT p.paper_name, p.file_path, p.uploaded_at,
               y.year_name, s.semester_name, sub.subject_name
        FROM lms_ati.papers p
        INNER JOIN lms_ati.years y ON p.year_id = y.id
        INNER JOIN lms_ati.semesters s ON p.semester_id = s.id
        INNER JOIN lms_ati.subjects sub ON p.subject_id = sub.id
        ORDER BY p.uploaded_at DESC
    ");
    if ($lmsResult) {
        while ($row = mysqli_fetch_assoc($lmsResult)) {
            $papers[] = [
                'semester'    => $row['semester_name'],
                'course'      => $row['subject_name'],
                'paper_title' => $row['paper_name'],
                'year'        => $row['year_name'],
                'link'        => $row['file_path'],
                'uploaded_at' => $row['uploaded_at'],
            ];
        }
    }

    // Newest first, regardless of which admin panel it came from.
    usort($papers, function ($a, $b) {
        return strtotime($b['uploaded_at']) <=> strtotime($a['uploaded_at']);
    });

    return $papers;
}
