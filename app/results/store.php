<?php
ini_set('display_errors', 1);
require '../../includes/db-config.php';
// echo "<pre>";print_r($_POST);die;
list($student_id, $duration, $university_id, $enrollment_no) = explode('|', $_POST['student_id']);
$created_at = date("Y-m-d:H:i:s");
$obt_marks = array_filter($_POST['obt_marks']);
$subject_ids = array_filter($_POST['subject_id']);
$max_marks_ints = array_filter($_POST['max_marks_int']);
$max_marks_exts = array_filter($_POST['max_marks_ext']);
$updated_enrollment_no = isset($_POST['enrollment_no']) ? $_POST['enrollment_no'] : '';
$inserted_count = 0;
$updated_count = 0;

foreach ($obt_marks as $i => $obt_mark) {
  $subject_id = isset($subject_ids[$i]) ? $subject_ids[$i] : '';
  $max_marks_int = isset($max_marks_ints[$i]) ? $max_marks_ints[$i] : 0;
  $max_marks_ext = isset($max_marks_exts[$i]) ? $max_marks_exts[$i] : 0;
  if ($updated_enrollment_no) {
    $result = $conn->query("UPDATE `marksheets` SET `max_marks_ext` = '" . $max_marks_ext . "', `max_marks_int` = '" . $max_marks_int . "',  `obt_marks` = '" . $obt_mark . "', `created_at` = '" . $created_at . "' WHERE `enrollment_no` = '" . $updated_enrollment_no . "' AND `subject_id` = '" . $subject_id . "'");
    if ($result) {
      $updated_count++;
    }
  } else {
     $result = $conn->query("INSERT INTO `marksheets`(`enrollment_no`, `subject_id`, `max_marks_ext`, `max_marks_int`,`obt_marks`,`status`, `created_at`) VALUES ('" . $enrollment_no . "', '" . $subject_id . "', '" . $max_marks_ext . "', '" . $max_marks_int . "','" . $obt_mark . "', '1', '" . $created_at . "' )");
    if ($result) {
      $inserted_count++;
    }
  }
}
if ($inserted_count > 0 || $updated_count > 0) {
  echo json_encode(['status' => 200, 'message' => "Result added succefully!!"]);
} else {
  echo json_encode(['status' => 400, 'message' => 'Something went to wrong!!']);
}
