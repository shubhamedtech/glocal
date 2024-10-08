<?php
if (isset($_FILES['file'])) {
  require '../../includes/db-config.php';
  require('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
  require('../../extras/vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php');

  session_start();

  $export_data = array();

  // Header

  if ($_SESSION['university_id'] == 48) {
    $header = array('Scheme','Course', 'Specialization', 'Category', 'Duration', 'Subject Code', 'Subject Name','Type (Theory/Practical)', 'Credit', 'Minimum Marks', 'Maximum Marks');
  } else {
    $header = array('Scheme', 'Course', 'Sub-Course', 'Semester', 'Subject Code', 'Subject Name', 'Type (Theory/Practical)', 'Credit', 'Minimum Marks', 'Maximum Marks', 'Remark');
  }
  $export_data[] = $header;

  $mimes = ['application/vnd.ms-excel','text/csv', 'text/xls', 'text/xlsx', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];


  if (in_array($_FILES["file"]["type"], $mimes)) {
 
    // Upload File
    $uploadFilePath = basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $uploadFilePath);

    // Read File
    $reader = new SpreadsheetReader($uploadFilePath);

    // Sheet Count
    $totalSheet = count($reader->sheets());

    /* For Loop for all sheets */
    for ($i = 0; $i < $totalSheet; $i++) {
      $reader->ChangeSheet($i);
      if ($_SESSION['university_id'] == 48) {
        foreach ($reader as $row) {
          echo "<pre>"; 
          // print_r($reader);
          $scheme = mysqli_real_escape_string($conn, $row[0]);
          $course = mysqli_real_escape_string($conn, $row[1]);
          $sub_course = mysqli_real_escape_string($conn, $row[2]);
          $category = mysqli_real_escape_string($conn, $row[3]);
          $duration = mysqli_real_escape_string($conn, $row[4]);
          $subject_code = mysqli_real_escape_string($conn, $row[5]);
          $subject_name = mysqli_real_escape_string($conn, $row[6]);
          $paper_type = mysqli_real_escape_string($conn, $row[7]);
          $credit = intval($row[8]);
          $min_marks = intval($row[9]);
          $max_marks = intval($row[10]);

          if ($scheme == 'Scheme') {
            continue;
          }

          if ($min_marks > $max_marks) {
            $export_data[] = array_merge($row, ['Min Marks cannot be greater than Max Marks.']);
            continue;
          }

          $scheme = $conn->query("SELECT ID FROM Schemes WHERE University_ID = " . $_SESSION['university_id'] . " AND Name LIKE '$scheme' AND Status =1");
          if ($scheme->num_rows == 0) {
            $export_data[] = array_merge($row, ['Scheme not found!']);
            continue;
          }

          $scheme = $scheme->fetch_assoc();

          $scheme_id = $scheme['ID'];

          $course = $conn->query("SELECT ID FROM Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name LIKE '$course' OR Short_Name LIKE '$course')");

          if ($course->num_rows == 0) {
            $export_data[] = array_merge($row, ['Course not found!']);
            continue;
          }

          $course_ids = array();
          while ($course_id = $course->fetch_assoc()) {
            $course_ids[] = $course_id['ID'];
          }
          $sub_course = $conn->query("SELECT ID, Course_ID FROM Sub_Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name LIKE '%$sub_course%' OR Short_Name LIKE '%$sub_course%') AND Scheme_ID = $scheme_id AND Course_ID IN (" . implode(',', $course_ids) . ")");

          if ($sub_course->num_rows == 0) {
            $export_data[] = array_merge($row, ['Sub-Course not found!']);
            continue;
          }
          $sub_course = $sub_course->fetch_assoc();
          $course_id = $sub_course['Course_ID'];
          $sub_course_id = $sub_course['ID'];
// print_r($course_ids);
          $check = $conn->query("SELECT ID FROM Syllabi WHERE University_ID = " . $_SESSION['university_id'] . " AND Course_ID = $course_id AND Sub_Course_ID = $sub_course_id AND Scheme_ID = $scheme_id AND Code = '" . $subject_code . "'");

          if ($check->num_rows > 0) {
            $export_data[] = array_merge($row, ['Subject Code already exists!']);
            continue;
          }

          $check = $conn->query("SELECT ID FROM Syllabi WHERE University_ID = " . $_SESSION['university_id'] . " AND Course_ID = $course_id AND Sub_Course_ID = $sub_course_id AND Scheme_ID = $scheme_id AND Name = '" . $subject_name . "'");

          if ($check->num_rows > 0) {
            $export_data[] = array_merge($row, ['Subject Name already exists!']);
            continue;
          }
         echo "INSERT INTO `Syllabi`(`University_ID`, `Course_ID`, `Sub_Course_ID`, `Scheme_ID`, `Code`, `Name`, `Paper_Type`, `Credit`, `Min_Marks`, `Max_Marks`,`Duration`) VALUES (" . $_SESSION['university_id'] . ", " . $course_id . ", " . $sub_course_id . ", " . $scheme_id . ",'" . $subject_code . "', '" . $subject_name . "', '" . $paper_type . "', " . $credit . ", " . $min_marks . ", " . $max_marks . "," . $duration . ")";
          // $add = $conn->query("INSERT INTO `Syllabi`(`University_ID`, `Course_ID`, `Sub_Course_ID`, `Scheme_ID`, `Code`, `Name`, `Paper_Type`, `Credit`, `Min_Marks`, `Max_Marks`,`Semester`) VALUES ('" . $_SESSION['university_id'] . "', '" . $course_id . "', '" . $sub_course_id . "', '" . $scheme_id . "','" . $subject_code . "', '" . $subject_name . "', '" . $paper_type . "', '" . $credit . "', '" . $min_marks . "', '" . $max_marks . "','" . $duration . "')");

          if ($add) {
            $export_data[] = array_merge($row, ['Subject added successfully!']);
          } else {
            $export_data[] = array_merge($row, ['Something went wrong!']);
          }

        }die;


      } else {

        foreach ($reader as $row) {

          // Data
          $remark = [];
          $scheme = mysqli_real_escape_string($conn, $row[0]);
          $course = mysqli_real_escape_string($conn, $row[1]);
          $sub_course = mysqli_real_escape_string($conn, $row[2]);
          $semester = intval($row[3]);
          $subject_code = mysqli_real_escape_string($conn, $row[4]);
          $subject_name = mysqli_real_escape_string($conn, $row[5]);
          $paper_type = mysqli_real_escape_string($conn, $row[6]);
          $credit = intval($row[7]);
          $min_marks = intval($row[8]);
          $max_marks = intval($row[9]);

          if ($scheme == 'Scheme') {
            continue;
          }

          if ($min_marks > $max_marks) {
            $export_data[] = array_merge($row, ['Min Marks cannot be greater than Max Marks.']);
            continue;
          }

          $scheme = $conn->query("SELECT ID FROM Schemes WHERE University_ID = " . $_SESSION['university_id'] . " AND Name LIKE '$scheme'");

          if ($scheme->num_rows == 0) {
            $export_data[] = array_merge($row, ['Scheme not found!']);
            continue;
          }

          $scheme = $scheme->fetch_assoc();
          $scheme_id = $scheme['ID'];

          $course = $conn->query("SELECT ID FROM Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name LIKE '$course' OR Short_Name LIKE '$course')");

          if ($course->num_rows == 0) {
            $export_data[] = array_merge($row, ['Course not found!']);
            continue;
          }

          $course_ids = array();
          while ($course_id = $course->fetch_assoc()) {
            $course_ids[] = $course_id['ID'];
          }
          $sub_course = $conn->query("SELECT ID, Course_ID FROM Sub_Courses WHERE University_ID = " . $_SESSION['university_id'] . " AND (Name LIKE '$sub_course' OR Short_Name LIKE '$sub_course') AND Scheme_ID = $scheme_id AND Course_ID IN (" . implode(',', $course_ids) . ")");

          if ($sub_course->num_rows == 0) {
            $export_data[] = array_merge($row, ['Sub-Course not found!']);
            continue;
          }

          $sub_course = $sub_course->fetch_assoc();

          $course_id = $sub_course['Course_ID'];
          $sub_course_id = $sub_course['ID'];

          $check = $conn->query("SELECT ID FROM Syllabi WHERE University_ID = " . $_SESSION['university_id'] . " AND Course_ID = $course_id AND Sub_Course_ID = $sub_course_id AND Scheme_ID = $scheme_id AND Code = '" . $subject_code . "'");

          if ($check->num_rows > 0) {
            $export_data[] = array_merge($row, ['Subject Code already exists!']);
            continue;
          }
          $check = $conn->query("SELECT ID FROM Syllabi WHERE University_ID = " . $_SESSION['university_id'] . " AND Course_ID = $course_id AND Sub_Course_ID = $sub_course_id AND Scheme_ID = $scheme_id AND Name = '" . $subject_name . "'");

          if ($check->num_rows > 0) {
            $export_data[] = array_merge($row, ['Subject Name already exists!']);
            continue;
          }

          $add = $conn->query("INSERT INTO `Syllabi`(`University_ID`, `Course_ID`, `Sub_Course_ID`, `Scheme_ID`, `Semester`, `Code`, `Name`, `Paper_Type`, `Credit`, `Min_Marks`, `Max_Marks`) VALUES (" . $_SESSION['university_id'] . ", " . $course_id . ", " . $sub_course_id . ", " . $scheme_id . ", $semester, '" . $subject_code . "', '" . $subject_name . "', '" . $paper_type . "', " . $credit . ", " . $min_marks . ", " . $max_marks . ")");

          if ($add) {
            $export_data[] = array_merge($row, ['Subject added successfully!']);
          } else {
            $export_data[] = array_merge($row, ['Something went wrong!']);
          }
        }
      }
    }
    unlink($uploadFilePath);
    $xlsx = SimpleXLSXGen::fromArray($export_data)->downloadAs('Subjects Status ' . date('h m s') . '.xlsx');
  }
}
?>