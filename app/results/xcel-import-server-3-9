<?php
if (isset($_FILES['file'])) {
  require '../../includes/db-config.php';
  require('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
  require('../../extras/vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php');
  session_start();

  $export_data = array();
  $header = array('Course', 'Sub-Course', 'Enrollment Number', 'Subject Code', 'Obtained External Marks', 'Obtained Internal Marks', 'Semester', 'Exam Month', 'Exam Year', 'Paper Code', 'Remark');
  $export_data[] = $header;
  $mimes = ['application/vnd.ms-excel', 'text/xls', 'text/xlsx', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

  if (in_array($_FILES["file"]["type"], $mimes)) {
    $uploadFilePath = basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $uploadFilePath);
    $reader = new SpreadsheetReader($uploadFilePath);
    $totalSheet = count($reader->sheets());
    for ($i = 0; $i < $totalSheet; $i++) {
      $reader->ChangeSheet($i);

      foreach ($reader as $row) {
        $course = mysqli_real_escape_string($conn, $row[0]);
        $sub_course = mysqli_real_escape_string($conn, trim($row[1]));
        $enrollment = mysqli_real_escape_string($conn, $row[2]);
        $subject_code = mysqli_real_escape_string($conn, $row[3]);
        $obt_ext_marks = mysqli_real_escape_string($conn, $row[4]);
        $obt_int_marks = mysqli_real_escape_string($conn, $row[5]);
        $semester = mysqli_real_escape_string($conn, $row[6]);
        $exam_month = mysqli_real_escape_string($conn, $row[7]);
        $exam_year = mysqli_real_escape_string($conn, $row[8]);
        $paper_code = mysqli_real_escape_string($conn, $row[9]);
        $created_at = date("Y-m-d:H:i:s");

        $course = $conn->query("SELECT ID FROM Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name LIKE '$course' OR Short_Name LIKE '$course')");

        if ($course->num_rows == 0) {
          $export_data[] = array_merge($row, ['Course not found!']);
          continue;
        }

        $course_ids = array();
        while ($course_id = $course->fetch_assoc()) {
          $course_ids[] = $course_id['ID'];
        }
        $sub_course = $conn->query("SELECT ID, Course_ID FROM Sub_Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name LIKE '%$sub_course%' OR Short_Name LIKE '%$sub_course') AND Course_ID IN (" . implode(',', $course_ids) . ")");
        if ($sub_course->num_rows == 0) {
          $export_data[] = array_merge($row, ['Sub-Course not found!']);
          continue;
        }

        $sub_course = $sub_course->fetch_assoc();
        $course_id = $sub_course['Course_ID'];
        $sub_course_id = $sub_course['ID'];
        $subjects = $conn->query("SELECT ID, Min_Marks FROM Syllabi WHERE University_ID = " . $_SESSION['university_id'] . " AND (Code LIKE '$subject_code') AND Semester = '" . $semester . "'  AND Course_ID = $course_id AND Sub_Course_ID = $sub_course_id");
        if ($subjects->num_rows == 0) {
          $export_data[] = array_merge($row, ['Subject not found!']);
          continue;
        }

        $subject_ids = array();
        $subject_arr = $subjects->fetch_assoc();
        $subject_ids = $subject_arr['ID'];
        $min_marks = isset($subject_arr['Min_Marks']) ? $subject_arr['Min_Marks'] : 0;

        if ($obt_ext_marks >= $min_marks || $obt_int_marks >= $min_marks) {
          $remarks = "Passed";
        } else {
          $remarks = "Fail";
        }

        $total = $obt_ext_marks + $obt_int_marks;
        $add = $conn->query("INSERT INTO `marksheets`(`enrollment_no`, `subject_id`, `obt_marks_ext`, `obt_marks_int`, `obt_marks`, `remarks`, `status`, `exam_month`,`exam_year`,`created_at`,`paper_code`) VALUES ('" . $enrollment . "', " . $subject_ids . ", " . $obt_ext_marks . ", $obt_int_marks, '" . $total . "', '" . $remarks . "',1, '" . $exam_month . "', '$exam_year','$created_at','" . $paper_code . "')");

        if ($add) {
          $export_data[] = array_merge($row, ['Result added successfully!']);
        } else {
          $export_data[] = array_merge($row, ['Something went wrong!']);
        }
      }
    }
    unlink($uploadFilePath);
    $xlsx = SimpleXLSXGen::fromArray($export_data)->downloadAs('Result Status ' . date('h m s') . '.xlsx');
  }
}
