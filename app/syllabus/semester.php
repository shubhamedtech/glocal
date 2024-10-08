<?php
if (isset($_GET['id']) && isset($_GET['university_id'])) {
  require '../../includes/db-config.php';
  session_start();
  $id = intval($_GET['id']);
  $university_id = intval($_GET['university_id']);
  // if (isset($_POST['duration']) && $_POST['duration'] == "semester") {
  //   $sub_course = $conn->query("SELECT Scheme_ID, Min_Duration FROM Sub_Courses WHERE ID = $id AND University_ID =   $university_id");
  //   $sub_course = $sub_course->fetch_assoc();
  //   $sub_course['Min_Duration'] = $_SESSION['Role'] == 'Student' ? $_SESSION['Duration'] : $sub_course['Min_Duration'];

  //   echo '<option value="">Choose</option>';
  //   for ($i = 1; $i <= $sub_course['Min_Duration']; $i++) {
  //     echo '<option value="' . $sub_course['Scheme_ID'] . '|' . $i . '">' . $i . '</option>';
  //   }
  // } else {

    $categorySql = $conn->query("SELECT Semester FROM Syllabi WHERE Sub_Course_ID = $id AND University_ID =   $university_id GROUP BY Semester ORDER BY Semester");
    echo '<option value="">Choose</option>';
    if ($categorySql->num_rows > 0) {
      while ($row = $categorySql->fetch_assoc()) {
        echo '<option value="' . $row['Semester'] .'">' . ucwords(strtolower($row['Semester'])) . '</option>';
      }
    }

  // }
}