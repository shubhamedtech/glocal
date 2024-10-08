<?php

if (isset($_GET['id'], $_GET['userId'])) {
  require '../../includes/db-config.php';
  session_start();

  $sub_course_id = intval($_GET['id']);
  $userId = intval($_GET['userId']);
  //print_r($sub_course_id);die;
  if (empty($sub_course_id)) {
    echo '<option value="">Please add sub-course</option>';
    exit();
  }

  //$admission_type = $conn->query("SELECT Name FROM Admission_Types WHERE ID = $admission_type_id");
  //$admission_type = mysqli_fetch_assoc($admission_type);
  //$admission_type = $admission_type['Name'];

  // $column = "1";
  //if(strcasecmp($admission_type, 'lateral')==0){
  //  $column = "LE_Start";
  // }
  // if(strcasecmp($admission_type, 'credit transfer')==0){
  // $column = "CT_Start";
  // }

  $course_categories = array();
  if ($_SESSION['university_id'] == 48) {
    $table = "Center_Sub_Courses";
    $checkIsSubCenter = $conn->query("SELECT ID FROM Users WHERE Role = 'Sub-Center' AND ID = $userId");
    if ($checkIsSubCenter->num_rows > 0) {
      $table = "Sub_Center_Sub_Courses";
    }
    $durationMapping = array('3' => 'certification', '11/advance-diploma' => 'advance_diploma', '6' => 'certified', '11/certified' => 'certified');
    $durations = $conn->query("SELECT Duration FROM $table WHERE Sub_Course_ID = $sub_course_id AND User_ID = $userId");
    while ($duration = $durations->fetch_assoc()) {
      $course_categories[] = $durationMapping[$duration['Duration']];
    }

    $course_categories = array_filter(array_unique($course_categories));
  }

  if (!empty($course_categories) && is_array($course_categories)) {
    $option = "<option>Select Choose Category</option>";
    foreach ($course_categories as $course_category) {
      $course_category1 = $course_category;
      $option .= '<option value="' . $course_category1 . '">' . $course_category1 . '</option>';
    }
  } else {
    $option = "<option>No Categories found</option>";
  }

  echo $option;
}
