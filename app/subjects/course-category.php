<?php

if (isset($_GET['id'])) {
  require '../../includes/db-config.php';
  session_start();

  $sub_course_id = intval($_GET['id']);
  
  if (empty($sub_course_id)) {
    echo '<option value="">Please add sub-course</option>';
    exit();
  }

  $course_categories = array();
  $course_Category_arr = array();



   $sub_course_sql  = $conn->query("SELECT Course_Category FROM Sub_Courses WHERE  ID = $sub_course_id");
   if ($sub_course_sql->num_rows > 0) {
    $course_Category_arr = $sub_course_sql->fetch_assoc();
    $course_categories = json_decode($course_Category_arr['Course_Category'], true);
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
