<?php
ini_set('display_errors', 1);
require '../../includes/db-config.php';
session_start();
if (isset($_GET['course_id']) && !empty($_GET['course_id']) && isset($_GET['semester']) && !empty($_GET['semester'])) {
  $semester = ''; $scheme = '';
  if ($_SESSION['Role'] == 'Student'){
    $sub_course_id = intval($_SESSION['Sub_Course_ID']);
    $semester = intval($_SESSION['Duration']);
    $scheme = $conn->query("SELECT Scheme_ID FROM Sub_Courses WHERE ID = $sub_course_id");
    $scheme = $scheme->fetch_assoc();
    $scheme = $scheme['Scheme_ID'];
  } else {
    $sub_course_id = intval($_GET['course_id']);
    $semesters = explode("|", $_GET['semester']);
    $scheme = $semesters[0];
    $semester = $semesters[1];
  }

  $syllabus_ids = array();
  $codes = $conn->query("SELECT ID FROM Syllabi WHERE Sub_Course_ID = " . $sub_course_id . " AND Semester = " . $semester . " AND Scheme_ID = " . $scheme . "");
  
  if ($codes->num_rows > 0) {
    while ($row = $codes->fetch_assoc()) {
      $syllabus_ids[] = $row['ID'];
    }

    $date_sheets = $conn->query("SELECT Date_Sheets.*, Exam_Sessions.Name as Exam_Session, Syllabi.Name, Syllabi.Code FROM Date_Sheets LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID WHERE Syllabus_ID IN (" . implode(",", $syllabus_ids) . ") ORDER BY Exam_Date ASC");
     
    if ($date_sheets->num_rows == 0) { ?>
      <tr><td colspan="5" style="text-align: center;">Date Sheet Not Available<tr>
    <?php } else { 
      while ($date_sheet = $date_sheets->fetch_assoc()) { ?>
        <tr>
          <td><?= $date_sheet['Exam_Session'] ?></td>
          <td><?= $date_sheet['Code'] ?></td>
          <td><?= $date_sheet['Name'] ?></td>
          <td><?= date("l, dS M, Y", strtotime($date_sheet['Exam_Date'])) ?></td>
          <td><?= date("h:i A", strtotime($date_sheet['Start_Time'])) . " to " . date("h:i A", strtotime($date_sheet['End_Time'])) ?></td>
        </tr>
      <?php } 
      } 
  } else { ?>
      <tr><td colspan="5" style="text-align: center;">NO Syllebus Available</td></tr>
  <?php } ?>
<?php } ?>