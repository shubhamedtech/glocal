<?php
if (isset($_GET['admission_type_id']) && isset($_GET['sub_course_id'])) {
  require '../../includes/db-config.php';
  session_start();

  $admission_type_id = intval($_GET['admission_type_id']);
  $sub_course_id = intval($_GET['sub_course_id']);

  if (empty($admission_type_id) || empty($sub_course_id)) {
    echo '<option value="">Please add sub-course</option>';
    exit();
  }

  $admission_type = $conn->query("SELECT Name FROM Admission_Types WHERE ID = $admission_type_id");
  $admission_type = mysqli_fetch_assoc($admission_type);
  $admission_type = $admission_type['Name'];

  $column = "1";
  if (strcasecmp($admission_type, 'lateral') == 0) {
    $column = "LE_Start";
  }
  if (strcasecmp($admission_type, 'credit transfer') == 0) {
    $column = "CT_Start";
  }

  if ($_SESSION['university_id'] == 48) {
    $course_category = mysqli_real_escape_string($conn, $_GET['course_category']);
    $check = $conn->query("SELECT Duration FROM Sub_Center_Sub_Courses WHERE Sub_Course_ID = $sub_course_id AND User_ID = " . $_SESSION['ID'] . " AND Fee > 0");

    if ($check->num_rows > 0) {
      $durations = $conn->query("SELECT Min_Duration FROM Sub_Courses WHERE ID = $sub_course_id AND Course_Category = '$course_category'");
    }
    if ($check->num_rows == 0) {
      //$durations = $conn->query("SELECT Duration FROM Sub_Courses WHERE ID = $sub_course_id ");
      $check_center_subcourse = $conn->query("SELECT Duration FROM Center_Sub_Courses WHERE Sub_Course_ID = $sub_course_id AND Fee > 0");

      if ($check_center_subcourse->num_rows > 0) {
        $durations = $conn->query("SELECT Course_Category FROM Sub_Courses WHERE ID = $sub_course_id AND Course_Category = '$course_category'");
        //print_r($course_category);exit();

      }
      //$durations = $conn->query("SELECT Duration FROM Center_Sub_Courses WHERE Sub_Course_ID = $sub_course_id AND Fee > 0"); 
    }
    while ($duration = $durations->fetch_assoc()) {
      $all_durection = json_decode($duration['Min_Duration']);
    }
  } else {
    $duration = $conn->query("SELECT $column FROM Sub_Courses WHERE ID = $sub_course_id");
    $duration = mysqli_fetch_assoc($duration);
    $duration = $duration[$column];
    $all_durection = explode(',', $duration);
  }
  $option = "";
  // foreach($all_durection as $duration){
  if ($_SESSION['university_id'] == 48) {
    if ($course_category == 'certification') {
      //$option .= '<option value="'.$duration.'">'.$duration.'</option>';
      $option .= '<option value="3">3</option>';
    } else if ($course_category == 'advance_diploma') {
      $option .= '<option value="11/advance-diploma">11</option>';
    } else if ($course_category == 'certified') {
      $option .= '<option value="6">6</option>';
      $option .= '<option value="11/certified">11</option>';
    }
  } else {
    $option .= '<option value="' . $duration . '">' . $duration . '</option>';
  }
  //  }

  echo $option;
}
if (isset($_GET['sub_course_id']) && isset($_GET['univercity_type_filter'])) {
  require '../../includes/db-config.php';
  session_start();

  $course_category = mysqli_real_escape_string($conn, $_GET['course_category']);
  $sub_course_id = intval($_GET['sub_course_id']);
  $option = "";
  if ($course_category == 'certification') {
    $option .= '<option value="3">3</option>';
  } else if ($course_category == 'advance_diploma') {
    $option .= '<option value="11/advance-diploma">11</option>';
  } else if ($course_category == 'certified') {
    $option .= '<option value="6">6</option>';
    $option .= '<option value="11/certified">11</option>';
  }
  echo $option;
}
