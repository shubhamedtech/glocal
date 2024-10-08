<?php
ini_set('display_errors', 1);
require '../../includes/db-config.php';
$couseId = intval($_POST['couseId']);
$subcourses = "SELECT ID,Name from Sub_Courses WHERE Course_ID = $couseId ";
$subcourses = mysqli_query($conn, $subcourses);

$html = '<option value="">Select Sub Course</option>';
while ($row = mysqli_fetch_assoc($subcourses)) {
  $html = $html . '<option value="' . $row['ID'] . '">' . $row['Name'] . '</option>';
}

echo $html;
die;
