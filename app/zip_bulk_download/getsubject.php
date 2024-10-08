<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../../includes/db-config.php';

// Retrieve and sanitize input
$sub_course_id = intval($_POST['sub_course_id']);
$semester = intval($_POST['semester']);

// Prepare SQL query
$studentSubjects = "SELECT Syllabi.ID as subject_id, Syllabi.Name FROM Syllabi WHERE Syllabi.Sub_Course_ID = $sub_course_id AND Semester=$semester";
$subjects = mysqli_query($conn, $studentSubjects);

// Build HTML response
$html = '<option value="">Select</option>';
while ($row = mysqli_fetch_assoc($subjects)) {
    $html .= '<option value="' . $row['subject_id'] . '">' . $row['Name'] . '</option>';
}
echo $html;
exit;
