<?php
ini_set('display_errors', 1);
require '../../includes/db-config.php';
$subCourseId = intval($_POST['subCourseId']);
$sql = "SELECT Name, Min_Duration FROM Sub_Courses WHERE ID = $subCourseId";
$result = $conn->query($sql);
$html = '<option value="">Select Semester</option>';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $duration = intval($row['Min_Duration']);
        if ($duration > 0) {
            for ($i = 1; $i <= $duration; $i++) {
                $html .= '<option value="' . $i . '">' . ' Semester ' . $i . '</option>';
            }
        }
    }
}

echo $html;
