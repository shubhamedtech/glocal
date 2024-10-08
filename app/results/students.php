<?php
// get courses
if (isset($_POST['sub_course_id']) && isset($_POST['course_id'])) {

  require '../../includes/db-config.php';
  $course_id = intval($_POST['course_id']);
  $sub_course_id = explode('|', intval($_POST['sub_course_id']))[0];
?>
  <option value="">Choose</option>
  <?php
  // echo "SELECT ID,CONCAT(First_Name,' ', Middle_Name,' ', Last_Name) as Name,Duration ,University_ID,Enrollment_No FROM Students WHERE Course_ID = $course_id AND Sub_Course_ID  = $sub_course_id AND Enrollment_No IS NOT NULL ORDER BY Name ASC";die;
  $students = $conn->query("SELECT ID,CONCAT(First_Name,' ', Middle_Name,' ', Last_Name) as Name,Duration ,University_ID,Enrollment_No FROM Students WHERE Course_ID = $course_id AND Sub_Course_ID  = $sub_course_id AND Enrollment_No IS NOT NULL ORDER BY Name ASC");
  while ($row = $students->fetch_assoc()) {
  ?>
    <option value='<?php echo $row['ID'] . '|' . $row['Duration'] . '|' . $row['University_ID'] . '|' . $row['Enrollment_No'] ?>'><?php echo $row['Name'] ?></option>
<?php
  }
}
