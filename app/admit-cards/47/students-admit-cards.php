<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if ((isset($_GET['student_id']) || isset($_GET['student_ids'])) && isset($_GET['duration'])) {

    require '../../../includes/db-config.php';
    session_start();
    date_default_timezone_set('Asia/Kolkata');
    if (isset($_GET['student_ids'])) {
        $id = mysqli_real_escape_string($conn, $_GET['student_ids']);
    } else {

        $id = mysqli_real_escape_string($conn, $_GET['student_id']);
        $id = base64_decode($id);
        $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
    }

    if(isset($_GET['duration'])) {
    $duration   = mysqli_real_escape_string($conn, $_GET['duration']);
        // list($scheme, $duration) = explode('|', $_GET['duration']);
    }else{
        $duration = "1";
    }

    $student = $conn->query("SELECT *  ,Sem as Duration FROM Admit_Card  WHERE Enrollment_No = '".$_SESSION['Enrollment_No']."' AND  Sem = $duration  LIMIT 1");
    if ($student->num_rows == 0) {
        header('Location: /dashboard');
    }

    $student = $student->fetch_assoc();
    // echo "<pre>"; print_r($student);

    $file_extensions = array('.png', '.jpg', '.jpeg');

    $photo = "";


    $student_data = $conn->query("SELECT Students.* , Courses.Name as course_name,Student_Documents.Location as photos 
                                FROM Students 
                                LEFT JOIN Student_Documents ON Students.ID = Student_Documents.Student_ID AND Student_Documents.Type = 'Photo'
                                LEFT JOIN Courses ON Students.Course_ID = Courses.ID 
                                WHERE Students.Id = $id ");
     if ($student_data->num_rows == 0) {
        header('Location: /dashboard');
    }

    $student_arr = $student_data->fetch_assoc();

    $photo = "../../.." . $student_arr['photos'];

    $student_photo = base64_encode(file_get_contents($photo));
    $i = 0;
    $end = 3;
    while ($i < $end) {
        $data1 = base64_decode($student_photo);
        $filename1 = $student_arr['ID'] . "_Photo" . $file_extensions[$i];
        file_put_contents($filename1, $data1);
        $i++;
    }
// ECHO $filename1; die;
    require_once ('../../../extras/qrcode/qrlib.php');
    require_once ('../../../extras/vendor/setasign/fpdf/fpdf.php');
    require_once ('../../../extras/vendor/setasign/fpdi/src/autoload.php');

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


    $student_name = array($student['Student_Name']);
    $student_name = array_filter($student_name);
    $pdf->SetXY(29, 32);
    $pdf->Write(1, ucwords(strtolower(implode(" ", $student_name))));

    $pdf->SetXY(41, 39.5);
    $pdf->Write(1, ucwords(strtolower($student["Father_Name"])));

    $pdf->SetXY(42, 46.5);
    $pdf->Write(1,ucwords(strtolower($student["Mother_Name"])) );

    $pdf->SetXY(28, 52.5);
    $pdf->Write(1, $student_arr['course_name']);

    if (filetype($photo) === 'file' && file_exists($photo)) {
        try {
            $filename = $student_arr['ID'] . "_Photo" . $file_extensions[0];
            $image = $filename;
            $pdf->Image($image, 171, 29, 30, 32);
            $photo = $image;
        } catch (Exception $e) {
            try {
                $filename = $student_arr['ID'] . "_Photo" . $file_extensions[1];
                $image = $filename;
                $pdf->Image($image, 171, 29, 30, 32);
                $photo = $image;
            } catch (Exception $e) {
                try {
                    $filename = $student_arr['ID'] . "_Photo" . $file_extensions[2];
                    $image = $filename;
                    $pdf->Image($image, 171, 29, 30, 32);
                    $photo = $image;
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            }
        }
    }

    $pdf->SetXY(116, 52.5);
    $pdf->Write(1, ucwords(strtolower($student['Sub_Course'])));

    $pdf->SetXY(130, 19.7);
    $pdf->Write(1, $student['Sem']);

    $pdf->SetXY(82, 19.5);
    $pdf->Write(1, $student['E_Session']);

    // Syllabus
    $pdf->SetFont('Hondo', '', 10.5);
    $y = 68.5;
    $counter = 1;
    $syllabi = $conn->query("SELECT Subject_Name AS Name ,Subject_Code AS Code,Date AS Exam_Date , Time, Sem as Duration FROM Admit_Card  WHERE Enrollment_No = '".$student['Enrollment_No']."' AND  Sem = $duration  ORDER BY ID ASC");
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
        $pdf->Write(1, $syllabus['Exam_Date']);

        $pdf->SetXY(178, $y);
        $exam_time = explode(' ', $syllabus['Time']);

        $pdf->Write(1,  $exam_time[0] );
        $y += 5;
    }

    $i = 0;
    $end = 3;
    while ($i < $end) {
        // Delete Photos
        if (!empty($student_photo)) {
            $filename = $student_arr['ID'] . "_Photo" . $file_extensions[$i]; //$file_extensions loops through the file extensions
            unlink($filename);
        }
        $i++;
    }

    $pdf->Output('I', ucwords(strtolower(implode(" ", $student_name))) . ' Admit Card.pdf');
}
