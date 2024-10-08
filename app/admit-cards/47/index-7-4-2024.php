<?php

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['student_id'])) {
  // print_r("Sandip");
  require '../../../includes/db-config.php';
  session_start();

  $id = mysqli_real_escape_string($conn, $_GET['student_id']);
  // $id = base64_decode($id);
  // $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));

  $student = $conn->query("SELECT Exam_Students.*, Courses.Name as course_name, Sub_Courses.Name as sub_course_name, Date_Sheets.Exam_date as exam_date, Date_Sheets.Start_time as start_time, Date_Sheets.End_time as end_time FROM Exam_Students LEFT JOIN Courses ON Exam_Students.Course = Courses.ID LEFT JOIN Sub_Courses ON Exam_Students.Sub_Course = Sub_Courses.ID LEFT JOIN Syllabi ON Exam_Students.Sub_Course = Syllabi.Sub_Course_ID LEFT JOIN Date_Sheets ON Syllabi.ID = Date_Sheets.Syllabus_ID LEFT JOIN Admission_Sessions ON Exam_Students.Admission_Session = Admission_Sessions.ID WHERE Exam_Students.Id = $id");
  if ($student->num_rows == 0) {
    header('Location: /dashboard');
  }

  $student = $student->fetch_assoc();

  $file_extensions = array('.png', '.jpg', '.jpeg');

  $photo = "";
  // $document = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = " . $student['ID'] . " AND `Type` = 'Photo'");
  // if ($document->num_rows > 0) {
  //   $photo = $document->fetch_assoc();
  //   $photo = "../../.." . $photo['Location'];
  // }
  // $student_photo = base64_encode(file_get_contents($photo));
  // $i = 0;
  // $end = 3;
  // while ($i < $end) {
  //   $data1 = base64_decode($student_photo);
  //   $filename1 = $student['ID'] . "_Photo" . $file_extensions[$i]; //$file_extensions loops through the file extensions
  //   file_put_contents($filename1, $data1); //we save our new images to the path above
  //   $i++;
  // }

  require_once('../../../extras/qrcode/qrlib.php');
  require_once('../../../extras/vendor/setasign/fpdf/fpdf.php');
  require_once('../../../extras/vendor/setasign/fpdi/src/autoload.php');

  $pdf = new Fpdi();

  $pdf->SetTitle('Admit Card');

  $pageCount = $pdf->setSourceFile('OSGU-Admit-Card.pdf');

  $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);

  $pdf->SetMargins(0, 0, 0);
  $pdf->SetAutoPageBreak(true, 1);

  $pdf->AddFont('Hondo', '', 'hondo.php');
  $pdf->SetFont('Hondo', '', 12);

  $pdf->SetXY(165, 25);
  $pdf->Write(1, $student['Enrolment_Number']);

  // $student_id = empty($student['Unique_ID']) ? $student['ID'] : $student['Unique_ID'];
  // $pdf->SetXY(159, 45.5);
  // $pdf->Write(1, $student_id);

  $student_name = array($student['Name']);
  $student_name = array_filter($student_name);
  $pdf->SetXY(27.5, 33);
  $pdf->Write(1, ucwords(strtolower(implode(" ", $student_name))));

  $pdf->SetXY(41, 39.5);
  $pdf->Write(1, "NA");

  $pdf->SetXY(42, 46.5);
  $pdf->Write(1, "NA");

  $pdf->SetXY(28, 53.5);
  $pdf->Write(1, $student['course_name']);

  $pdf->SetXY(120, 53.5);
  $pdf->Write(1, $student['sub_course_name']);

  $pdf->SetXY(130, 20);
  $pdf->Write(1, $student['Duration']);

  // $pdf->SetXY(48, 79.3);
  // $pdf->Write(1, $student['Session']);

  // Syllabus
  $pdf->SetFont('Hondo', '', 10.5);
  $y = 68.5;
  $counter = 1;
  $syllabi = $conn->query("SELECT Syllabi.*, Date_Sheets.Exam_Date as Exam_Date, Date_Sheets.Start_Time as Start_Time FROM Syllabi LEFT JOIN Date_Sheets ON Syllabi.ID = Date_Sheets.Syllabus_ID WHERE Sub_Course_ID = " . $student['Sub_Course'] . " AND Semester = " . $student['Duration'] . " AND Date_Sheets.Syllabus_ID IS NOT NULL ORDER BY Code ASC");
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
    $pdf->Write(1, date("H:i:s", strtotime($syllabus['Start_Time'])));
    $y += 5.5;
  }

  // $pdf->SetY(52);
  // $pdf->SetX(143.5);
  // $pdf->SetLineWidth(.3);
  // $pdf->Cell(28, 33.7, '', 1, 1, 'C');

  // if (filetype($photo) === 'file' && file_exists($photo)) {
  //   try {
  //     $filename = $student['ID'] . "_Photo" . $file_extensions[0];
  //     $image = $filename;
  //     $pdf->Image($image, 94, 30.2, 26.7, 32.6);
  //     $photo = $image;
  //   } catch (Exception $e) {
  //     try {
  //       $filename = $student['ID'] . "_Photo" . $file_extensions[1];
  //       $image = $filename;
  //       $pdf->Image($image, 144.2, 52.5, 26.7, 32.6);
  //       $photo = $image;
  //     } catch (Exception $e) {
  //       try {
  //         $filename = $student['ID'] . "_Photo" . $file_extensions[2];
  //         $image = $filename;
  //         $pdf->Image($image, 94, 30.2, 26.7, 32.6);
  //         $photo = $image;
  //       } catch (Exception $e) {
  //         echo 'Message: ' . $e->getMessage();
  //       }
  //     }
  //   }
  // }

  // $pdf->Image('sign.png', 154, 250.2, 30, 19);

  // $i = 0;
  // $end = 3;
  // while ($i < $end) {
  //   // Delete Photos
  //   if (!empty($student_photo)) {
  //     $filename = $student['ID'] . "_Photo" . $file_extensions[$i]; //$file_extensions loops through the file extensions
  //     unlink($filename);
  //   }
  //   $i++;
  // }

  $pdf->Output('D', 'Admit Card.pdf');
}
