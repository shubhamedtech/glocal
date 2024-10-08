<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['student_id']) || isset($_GET['student_ids'])) {
  
  require '../../../includes/db-config.php';
  session_start();
  date_default_timezone_set('Asia/Kolkata');
  if(isset($_GET['student_ids'])){
    $id = mysqli_real_escape_string($conn, $_GET['student_ids']);
  }else{


  $id = mysqli_real_escape_string($conn, $_GET['student_id']);
  $id = base64_decode($id);
  $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
  //$id='100';
  }

  $student = $conn->query("SELECT Students.*, Courses.Name as course_name, Admission_Sessions.Exam_Session as Session, Sub_Courses.Name as sub_course_name, Date_Sheets.Exam_date as exam_date, Date_Sheets.Start_time as start_time, Date_Sheets.End_time as end_time, Student_Documents.Location as photos 
                        FROM Students 
                        LEFT JOIN Student_Documents ON Students.ID = Student_Documents.Student_ID AND Student_Documents.Type = 'Photo'
                        LEFT JOIN Courses ON Students.Course_ID = Courses.ID 
                        LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
                        LEFT JOIN Syllabi ON Students.Sub_Course_ID = Syllabi.Sub_Course_ID 
                        LEFT JOIN Date_Sheets ON Syllabi.ID = Date_Sheets.Syllabus_ID 
                        LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID 
                        WHERE Students.Id = $id");


  if ($student->num_rows == 0) {
    header('Location: /dashboard');
  }

  $student = $student->fetch_assoc();
  // echo "<pre>"; print_r($student);

  $file_extensions = array('.png', '.jpg', '.jpeg');

  $photo = "";
  // $document = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = " . $student['ID'] . " AND `Type` = 'Photo'");
  // if ($document->num_rows > 0) {
  //   $photo = $document->fetch_assoc();
  
   $photo = "../../.." . $student['photos'];  
   $student_photo = base64_encode(file_get_contents($photo));
   $i = 0;
   $end = 3;
   while ($i < $end) {
     $data1 = base64_decode($student_photo);
     $filename1 = $student['ID'] . "_Photo" . $file_extensions[$i]; 
     file_put_contents($filename1, $data1); 
     $i++;
   }

  require_once('../../../extras/qrcode/qrlib.php');
  require_once('../../../extras/vendor/setasign/fpdf/fpdf.php');
  require_once('../../../extras/vendor/setasign/fpdi/src/autoload.php');

  $pdf = new Fpdi();

  $pdf->SetTitle('Admit Card');

  $pageCount = $pdf->setSourceFile('Glocal Admit Card.pdf');

  $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);

  $pdf->SetMargins(0, 0, 0);
  $pdf->SetAutoPageBreak(true, 1);

  $pdf->AddFont('Hondo', '', 'hondo.php');
  $pdf->SetFont('Hondo', '', 12);

  $pdf->SetXY(165, 25);
  $pdf->Write(1, $student['Enrollment_No']);

  // $student_id = empty($student['Unique_ID']) ? $student['ID'] : $student['Unique_ID'];
  // $pdf->SetXY(159, 45.5);
  // $pdf->Write(1, $student_id);

  $student_name = array($student['First_Name']." ".$student['Middle_Name'] ." ".$student['Last_Name']);
  $student_name = array_filter($student_name);
  $pdf->SetXY(29, 32);
  $pdf->Write(1, ucwords(strtolower(implode(" ", $student_name))));

  $pdf->SetXY(41, 39.5);
  $pdf->Write(1, "NA");

  $pdf->SetXY(42, 46.5);
  $pdf->Write(1, "NA");

  $pdf->SetXY(28, 52.5);
  $pdf->Write(1, $student['course_name']);

  if (filetype($photo) === 'file' && file_exists($photo)) {
    try {
      $filename = $student['ID'] . "_Photo" . $file_extensions[0];
      $image = $filename;
      $pdf->Image($image, 171, 29, 30, 32);
      $photo = $image;
    } catch (Exception $e) {
      try {
        $filename = $student['ID'] . "_Photo" . $file_extensions[1];
        $image = $filename;
        $pdf->Image($image, 171, 29, 30, 32);
        $photo = $image;
      } catch (Exception $e) {
        try {
          $filename = $student['ID'] . "_Photo" . $file_extensions[2];
          $image = $filename;
          $pdf->Image($image, 171, 29, 30, 32);
          $photo = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }

  $pdf->SetXY(120, 52.5);
  $pdf->Write(1, $student['sub_course_name']);

  $pdf->SetXY(130, 19.7);
  $pdf->Write(1, $student['Duration']);

  $pdf->SetXY(82, 19.5);
  $pdf->Write(1, $student['Session']);

  // Syllabus
  $pdf->SetFont('Hondo', '', 10.5);
  $y = 68.5;
  $counter = 1;
  // ECHO "SELECT Syllabi.*, Date_Sheets.Exam_Date as Exam_Date, Date_Sheets.Start_Time as Start_Time FROM Syllabi LEFT JOIN Date_Sheets ON Syllabi.ID = Date_Sheets.Syllabus_ID WHERE Sub_Course_ID = " . $student['Sub_Course_ID'] . " AND Semester= " . $student['Duration'] . " AND Date_Sheets.Syllabus_ID IS NOT NULL ORDER BY Code ASC";DIE;
  $syllabi = $conn->query("SELECT Syllabi.*, Date_Sheets.Exam_Date as Exam_Date, Date_Sheets.Start_Time as Start_Time FROM Syllabi LEFT JOIN Date_Sheets ON Syllabi.ID = Date_Sheets.Syllabus_ID WHERE Sub_Course_ID = " . $student['Sub_Course_ID'] . " AND Semester= " . $student['Duration'] . "  AND Date_Sheets.Syllabus_ID IS NOT NULL ORDER BY Code ASC");
  while ($syllabus = $syllabi->fetch_assoc()) {
    $pdf->SetXY(16, $y);
    $pdf->Write(1, $counter++);
    $pdf->SetXY(26, $y);
    $pdf->Write(1, $syllabus['Code']);
    if (strlen($syllabus['Name']) > 32) {
      $pdf->SetXY(50, $y);
      $pdf->Write(1, substr($syllabus['Name'], 0, 32));
    } else {
      $pdf->SetXY(50, $y);
      $pdf->Write(1, substr($syllabus['Name'], 0, 32));
    }
    $pdf->SetXY(152, $y);
    $pdf->Write(1, date("d-m-Y", strtotime($syllabus['Exam_Date'])));

    $pdf->SetXY(178, $y);
    $pdf->Write(1, date("h:i", strtotime($syllabus['Start_Time'])) . ((date("h:i", strtotime($syllabus['Start_Time'])) > date("h:i", 12)) ? " AM" : " PM"));
    $y += 5;
  }

  $i = 0;
  $end = 3;
  while ($i < $end) {
    // Delete Photos
    if (!empty($student_photo)) {
      $filename = $student['ID'] . "_Photo" . $file_extensions[$i]; //$file_extensions loops through the file extensions
      unlink($filename);
    }
    $i++;
  }

  $pdf->Output('I', ucwords(strtolower(implode(" ", $student_name))) . ' Admit Card.pdf');
}
