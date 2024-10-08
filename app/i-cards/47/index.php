<?php

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['id'])) {
  require '../../../includes/db-config.php';
  session_start();

  $id = mysqli_real_escape_string($conn, $_GET['id']);
  $id = base64_decode($id);
  $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));

  $student = $conn->query("SELECT Students.ID, Students.Roll_No, Students.Duration, Students.First_Name, Students.Middle_Name, Students.Last_Name, Students.Father_Name, Students.Enrollment_No, Students.Contact, Students.Email, Students.Unique_ID, Students.DOB, Students.Address, Sub_Courses.Short_Name,  Sub_Courses.Name as Sub_Cour_name, Courses.Short_Name as Course, Admission_Sessions.Name as Session FROM Students LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID WHERE Students.ID = $id AND Students.University_ID = " . $_SESSION['university_id'] . "");
  if ($student->num_rows == 0) {
    header('Location: /dashboard');
  }

  $student = $student->fetch_assoc();

  $file_extensions = array('.png', '.jpg', '.jpeg');

  $photo = "";
  $document = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = " . $student['ID'] . " AND `Type` = 'Photo'");
  if ($document->num_rows > 0) {
    $photo = $document->fetch_assoc();
    $photo = "../../.." . $photo['Location'];
  }
  $student_photo = base64_encode(file_get_contents($photo));
  $i = 0;
  $end = 3;
  while ($i < $end) {
    $data1 = base64_decode($student_photo);
    $filename1 = $student['ID'] . "_Photo" . $file_extensions[$i]; //$file_extensions loops through the file extensions
    file_put_contents($filename1, $data1); //we save our new images to the path above
    $i++;
  }

  require_once('../../../extras/qrcode/qrlib.php');
  require_once('../../../extras/vendor/setasign/fpdf/fpdf.php');
  require_once('../../../extras/vendor/setasign/fpdi/src/autoload.php');

  $pdf = new Fpdi('P', 'mm', array(222, 140));

  $pdf->SetTitle('ID Card');

  $pageCount = $pdf->setSourceFile('Glocal-ID-Card3.pdf');

  $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 140);

  $pdf->SetMargins(0, 0, 0);
  $pdf->SetAutoPageBreak(true, 1);

  $pdf->AddFont('Helvetica-Bold', '', 'helveticab.php');
  $pdf->SetFont('Helvetica-Bold', '', 14);

  $student_id = empty($student['Unique_ID']) ? $student['ID'] : $student['Unique_ID'];

  // echo('<pre>');print_r($student_id);die;      
  $student_name = array($student['First_Name'], $student['Middle_Name'], $student['Last_Name']);
  $student_name = array_filter($student_name);
  $pdf->SetXY(10, 122);
  $pdf->Write(0,  'Name :' . ' ' . ucwords(strtolower(implode(" ", $student_name))), 0, 0, 'C', 0);

  // Programme
  $pdf->SetFont('Helvetica-Bold', '', 12);

  $pdf->SetXY(10, 137);
  $pdf->Write(1, 'Course :' . ' ' . $student['Course'] . ' (' . $student['Sub_Cour_name'] . ')');
  $pdf->SetFont('Helvetica-Bold', '', 12);

  $pdf->SetXY(10, 129);
  $pdf->Write(1, 'Reg. no. :' . ' ' . $student_id);
  // $pdf->SetXY(65, 192.4);
  // $pdf->Write(1, ucfirst($student['Session']).'-'. ucfirst(strstr($student['Session'], '-', true)).'-'.(int)preg_replace('/\D+/', '', $student['Session'])+$student['Duration']);

  $pdf->SetXY(10, 145);
  $pdf->Write(1, 'Father Name :' . ' ' . ucwords(strtolower($student['Father_Name'])));

  $pdf->SetXY(10, 155);

$formattedDate = date("d/m/Y", strtotime($student['DOB']));

$pdf->Write(1, 'Date Of Birth : ' . $formattedDate);

  $pdf->SetXY(10, 165);
  $pdf->Write(1, 'Contact :' . ' ' . $student['Contact']);
  if (filetype($photo) === 'file' && file_exists($photo)) {
    try {
      $filename = $student['ID'] . "_Photo" . $file_extensions[0];
      $image = $filename;
      $pdf->Image($image, 42, 46, 65, 63);
      $photo = $image;
    } catch (Exception $e) {
      try {
        $filename = $student['ID'] . "_Photo" . $file_extensions[1];
        $image = $filename;
        $pdf->Image($image, 46, 52, 48, 60);
        $photo = $image;
      } catch (Exception $e) {
        try {
          $filename = $student['ID'] . "_Photo" . $file_extensions[2];
          $image = $filename;
          $pdf->Image($image, 46, 52, 48, 60);
          $photo = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }

  $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 140);

  // $pdf->SetXY(47, 25);
  // $pdf->Write(1, ucwords(strtolower($student['Father_Name'])));

  // $pdf->SetXY(47, 35);
  // $pdf->Write(1, $student['DOB']);

  // $pdf->SetXY(47, 45);
  // $pdf->Write(1, $student['Contact']);

  $pdf->SetXY(10, 15);
  $pdf->Write(1, 'Emergency No. :' . ' ' . $student['Contact']);

  $pdf->SetXY(10, 23);
  $pdf->Write(1, 'Email :' . ' ' . $student['Email']);

  $address = json_decode($student['Address'], true);

  // Permanent Address
  $nameParts = explode("\n", wordwrap($address['present_address'], 40));


  if (isset($nameParts[0])) {
    $pdf->SetXY(10, 35);
    $pdf->Write(1, 'Address :' . ' ' . $nameParts[0]);
  }
  if (isset($nameParts[1])) {
    $pdf->SetXY(31, 40);
    $pdf->Write(1, $nameParts[1]);
  }
  if (isset($nameParts[2])) {
    $pdf->SetXY(31, 45);
    $pdf->Write(1, $nameParts[2]);
  }
  // City
  $pdf->SetXY(10, 55);
  $pdf->Write(1, 'City :' . ' ' . $address['present_city']);


  $pdf->SetXY(10, 65);
  $pdf->Write(1, 'State :' . ' ' . $address['present_state']);


  $pdf->SetXY(40, 197);
  $pdf->Write(1, strtoupper($student['Session']));

  $pdf->SetXY(65, 207.5);
  $pdf->Write(1, " :  NA");

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

  $pdf->Output('I', 'ID Card.pdf');
}
