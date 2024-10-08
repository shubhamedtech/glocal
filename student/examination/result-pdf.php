<?php
require '../../includes/db-config.php';
require '../../includes/helpers.php';

session_start();
$username = $_GET['user_id'];
$password  = $_GET['password'];
$url = "https://erpglocal.iitseducation.org";
$passFail = "PASS";

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

ob_end_clean();

require_once('../../extras/TCPDF/tcpdf.php');
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');
if (isset($_GET['id'])) {

  $student = $conn->query("SELECT Students.*, Courses.Name as program_Type, Sub_Courses.Name as course,Modes.Name as mode, Course_Types.Name as Course_Type, Admission_Sessions.Name as Admission_Session,Admission_Sessions.Exam_Session, Admission_Types.Name as Admission_Type,CONCAT(Courses.Short_Name, ' (',Sub_Courses.Name,')') as Course_Sub_Course, TIMESTAMPDIFF(YEAR, DOB, CURDATE()) AS Age FROM Students LEFT JOIN Modes on Students.University_ID=Modes.University_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Course_Types ON Courses.Course_Type_ID = Course_Types.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.ID = " . $_GET['id']);

  // Initialize an empty array to store data
  $data = [];
  $data = $student->fetch_assoc();
  $typoArr = ["th", "st", "nd", "rd", "th", "th", "th", "th", "th"];

  $total_obt = 0;
  $total_max = 0;
  $html_marks = '';
  
$durations_query = "";
if ($data['University_ID'] == 47) {
	$durations_query = " AND Syllabi.Semester = " . $data['Duration'];
}
  $temp_subjects = $conn->query("SELECT * FROM Syllabi WHERE Syllabi.Sub_Course_ID = " . $data['Sub_Course_ID'] . " $durations_query");
  // echo "<pre>"; print_r($data);die;
  $resultPublishDay = "";
  if ($temp_subjects->num_rows > 0) {

    while ($temp_subject = $temp_subjects->fetch_assoc()) {
      $temp_subject_data[] = $temp_subject;
      $mark_subjects = $conn->query("SELECT marksheets.obt_marks_ext, marksheets.obt_marks_int,marksheets.obt_marks,marksheets.status,marksheets.remarks,marksheets.created_at,Syllabi.Code,Syllabi.Max_Marks,Syllabi.Min_Marks, Syllabi.Name as subject_name FROM marksheets LEFT JOIN Syllabi ON marksheets.subject_id = Syllabi.ID WHERE enrollment_no = '" . $data['Enrollment_No'] . "' AND subject_id = " . $temp_subject['ID'] . " $durations_query ");
      $mark_subjects = $mark_subjects->fetch_assoc();

      if ($mark_subjects != null) {
        $resultPublishDay = date("d/m/Y", strtotime($mark_subjects['created_at']));
        if ($mark_subjects['remarks'] != "Pass") {
          $passFail = "FAIL";
        }
        $obt_marks_ext = $mark_subjects['obt_marks_ext'];
        $obt_marks_int = $mark_subjects['obt_marks_int'];
        // $total_obt = $total_obt + $obt_marks_ext + $obt_marks_int;
        $total_obt = $total_obt + $obt_marks_ext + $obt_marks_int;

        if ($data['University_ID'] == 47) {
          $total_max = $total_max + $mark_subjects['Min_Marks'] + $mark_subjects['Max_Marks'];
        } else {
          $total_max = $total_max + $mark_subjects['Max_Marks'];
        }
        $data['marks'][] = $mark_subjects;
      }
    }
    $data['total_max'] = $total_max;
    $data['total_obt'] = $total_obt;
    $percentage = 0;

    if ($total_max !== 0) {
      $percentage = ($total_obt / $total_max) * 100;
    } else {
      $percentage = 0;
    }
// echo  $percentage;die;
    // $data['temp_subject'] = $temp_subject_data;



    $marksWords =  ucwords(strtolower(numberToWordFunc($total_obt)));

    $html_marks .= '<tr>
                    <th colspan="3" style="border: 1.5px solid #05519E;" > <span style="display: inline-block; float: left; margin-left: 10px; text-transform: capitalize">
                        <span style="color: #05519E">In Words: </span>';

    $html_marks .= '<span id="inwords" class="res">' . $marksWords . '</span> </span> <span style="display: inline-block; float: right; margin-right: 10px;">
                        <span style="color: #05519E">Total Obtained Marks:</span></span>
                    </th>
                    <th colspan="2" class="text-center" style="border: 1.5px solid #05519E;" >' . $total_obt . '</th>
                    </tr>
                </table>
              ';
    $html_marks .= '
                <table class="text-center" style="border-collapse: collapse;border: 1.5px solid #05519E;width: 100%;">
      
                  <tr style="color: #05519E; font-weight: 700;">
                    <th style="border: 1.5px solid #05519E;">Marks</th>
                    <th style="border: 1.5px solid #05519E;">GRAND TOTAL</th>
                    <th style="border: 1.5px solid #05519E;">RESULT</th>
                    <th style="border: 1.5px solid #05519E;">PERCENTAGE</th>
                  </tr>
      
                  <tr>
                    <th style="border: 1.5px solid #05519E;"> Maximum Mark</th>
                    <td style="border: 1.5px solid #05519E;">' . $total_max . '</td>
                    <td rowspan="2" style="border: 1.5px solid #05519E;">' . $passFail . '</td>
                    <td rowspan="2" style="border: 1.5px solid #05519E;">' . round($percentage, 2) . '%</td>
                  </tr>
      
                  <tr>
                    <th style="border: 1.5px solid #05519E;">Obtained Mark</th>
                    <td style="border: 1.5px solid #05519E;">' . $total_obt . '</td>
                  </tr>
      
                </table>
             
             ';
  }

  if ($data['University_ID'] == 48) {
    $data['university_name'] = "Skill Education Development";
  } else {
    $data['university_name'] = "Glocal School Of Vocational Studies";
  }


  $durations = '';
  if ($data['University_ID'] == 48) {
    if ($data['Duration'] == 3) {
      $durations = "Certification Course";
    } else if ($data['Duration'] == 6) {
      $durations = "Certified Skill Diploma";
    } else if ($data['Duration'] == '11/advance-diploma') {
      $durations = "Advanced Certification Skill Diploma";
    } else if ($data['Duration'] == '11/certified') {
      $durations = "Certified Skill Diploma";
    }
  } else {
    $durations = "B. VOC";
  }

  $data['duration_val'] = $durations;


  $durMonthYear = "";
  if ($data['mode'] == "Monthly") {
    $durMonthYear = " Months";
  } elseif ($data['mode'] == "Sem") {
    $durMonthYear = " Semester";
  } else {
    $durMonthYear = " Years";
  }

  if ($data['University_ID'] == 48) {
    $data['mode_type'] = "Duration";
  } else {
    $data['mode_type'] = "Semester";
  }
  if ($data['University_ID'] == 48) {
    $data['durMonthYear'] = $data['Duration'] . $durMonthYear;
  } else {
    $data['durMonthYear'] = $data['Duration'] . $typoArr[$data['Duration']];
  }

  $hours = '';
  $total_duration = '';

  if ($data['University_ID'] == 48) {
    $data['mode_type'] = "Duration";
    $total_duration = $data['Duration'];
    $data['Durations'] = $data['Duration'];
    if ($total_duration == 3) {
      $certificate = "Certification Course";
      $hours = 160;
    } elseif ($total_duration == 6) {
      $certificate = "Certified Skill Diploma";
      $hours = 320;
    } elseif ($total_duration == "11/advance-diploma"|| $total_duration == "11/certified") {
      $hours = 960;
      $data['Durations']=11;
    } elseif ($total_duration == 6 && $durMonthYear == "Semester") {
      $hours = 'NA';
    }
  } else {
    $data['mode_type'] = "Semester";
  }
  // echo $hours;
// echo $data['Durations'];die;
  if ($data['University_ID'] == 48) {
   
    $data['durMonthYear'] = $data['Durations'] . $durMonthYear . '/' . $hours . " hours";
  }
  // $data['durMonthYear'] = $data['Duration'] . $durMonthYear;

  $total_obt = 0;
  $total_max = 0;
  $data['remarks'] = "Pass";

  $data['in_word_marks'] = ucwords(strtolower(numberToWordFunc($total_obt)));
  $data['percentage'] = $percentage;
  $data['status'] = 1;

  $photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = " . $data['ID'] . " AND Type = 'Photo'");
  if ($photo->num_rows > 0) {
    $photo = $photo->fetch_assoc();
    $photo = $photo['Location'];
  }
  $data['Photo'] = $url . $photo;
  // echo "<pre>"; print_r($data);die;

  $pdf = new Fpdi();
  $pdf->AddPage();
  // $pdf->setSourceFile("../../assets/mark-sheet.pdf");

  // $tplId = $pdf->importPage(1);
  // $pdf->useTemplate($tplId); 

  if ($data['University_ID'] == 48) {
    $pdf->SetFont("times", '', 10);
 //   $pdf->SetXY(182, 22.4);
  //  $pdf->Cell(0, 0,  $data['OA_Number'] , 0, 0, 'C', 0);
    
  
    // $imagePath = $data['Photo'];
    // $pdf->Image($imagePath, 163, 50, 30, 30);
    $pdf->SetXY(15, 65);
    $pdf->Cell(0, 0, 'Statement of Marks', 0, 0, 'C', 0);
   
    $pdf->SetXY(15, 72.4);
    $pdf->Cell(0, 0,  $data['duration_val'] . ' ' . 'in' . ' ' . $data['course'], 0, 0, 'C', 0);
    $pdf->SetXY(15, 81);
    $pdf->Cell(0, 0, 'AY  2023-24' . '', 0, 0, 'C', 0);
    $pdf->SetXY(16.1, 86);
    $pdf->Cell(107, 10,  'Name : ' . ucwords(strtolower($data['First_Name'])) . ' ' . ucwords(strtolower($data['Middle_Name'])) . ' ' . ucwords(strtolower($data['Last_Name'])), 'TL', 0, 'L', 0);
    $pdf->SetXY(123, 86);
    $pdf->Cell(70, 10,  'Enrollment No : ' . $data['Enrollment_No'], 'LTR', 0, 'L', 0);
    $pdf->SetXY(16.1, 96);
    $pdf->Cell(107, 10, 'School : ' . 'School Of' . ' ' . ucwords(strtolower($data['university_name'])), 'LTB', 0, 'L', 0);
    $pdf->SetXY(123, 96);
    $pdf->Cell(70, 10, $data['mode_type'] . ' ' . ':' . ' ' . $data['durMonthYear'], 1, 0, 'L', 0);

    $pdf->SetFont('Arial', 'B',  10);
    $cellWidth = 20;
    $cellHeight = 10;
    $pdf->SetXY(16.1, 110);
    $pdf->MultiCell(25, 10,  'Subject Code', 'TLB',   'C');
    $pdf->SetXY(41, 110);
    $pdf->MultiCell(71, 10,  'Subject Name ', 'TLB',   'C');
    $pdf->SetXY(112, 110);
    $pdf->MultiCell(20, 5,  'Obtained Marks', 'TLB',   'C');
    $pdf->SetXY(132, 110);
    $pdf->MultiCell(20, 5,  'Min. Marks', 'TLB',  'C');
    $pdf->SetXY(152, 110);
    $pdf->MultiCell(19.8, 5,  'Max. Marks', 'TLB',   'C');
    $pdf->SetXY(172, 110);
    $pdf->MultiCell(22, 10,  'Remarks', 1,   'C');
    $pdf->SetXY(10, 110);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 10);
    $x_cor = 16;
    $pdf->SetX($x_cor);
    // $data['marks']=array();
    foreach ($data['marks'] as $mark) {
      $pdf->SetX($x_cor);
      if (strlen($mark['subject_name']) > 30) {
        $cellHeight = 20;
      } else {
        $cellHeight = 10;
      }
      if (strlen($mark['subject_name']) > 30) {
        $pdf->Cell(25, $cellHeight - 10, $mark['Code'], 'LB', 0, 'L');
        $nameParts = explode("\n", wordwrap($mark['subject_name'], 30));
        $pdf->MultiCell(71, 5, $nameParts[0] . chr(10) . $nameParts[1], 'LB', 0, 0, 'L');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x + 102, $y - 10);
        $pdf->Cell($cellWidth, $cellHeight - 10, $mark['obt_marks'], 'LB', 0, 'C');
        $pdf->Cell($cellWidth, $cellHeight - 10, $mark['Min_Marks'], 'LB', 0, 'C');
        $pdf->Cell($cellWidth, $cellHeight - 10, $mark['Max_Marks'], 'LB', 0, 'C');
        $pdf->Cell(22, $cellHeight - 10, $mark['remarks'], 'LBR', 0, 'C');
      } else {
        $pdf->Cell(25, $cellHeight, $mark['Code'], 'LB', 0, 'L');
        $pdf->Cell(71, $cellHeight, $mark['subject_name'], 'LB', 0, 'L');
        $pdf->Cell($cellWidth, $cellHeight, $mark['obt_marks'], 'LB', 0, 'C');
        $pdf->Cell($cellWidth, $cellHeight, $mark['Min_Marks'], 'LB', 0, 'C');
        $pdf->Cell($cellWidth, $cellHeight, $mark['Max_Marks'], 'LB', 0, 'C');
        $pdf->Cell(22, $cellHeight, $mark['remarks'], 'LBR', 0, 'C');
      }
      $pdf->Ln();
    }
    $pdf->SetXY(16, 217.4);
    $pdf->SetFont('Arial', 'B',  10);
    $pdf->Cell(0, 0, 'Aggregate Marks', 0, 0, 'C', 0);
    $pdf->SetXY(16, 221.4);
    $pdf->Cell(65, 8, 'Marks', 'TL', 1, 'C', 0);
    $pdf->SetXY(81, 221.4);
    $pdf->Cell(35, 8, 'Grand Total', 'TL', 1, 'C', 0);
    $pdf->SetXY(116, 221.4);
    $pdf->Cell(35, 8, 'Result', 'LTB', 1, 'C', 0);
    $pdf->SetXY(151, 221.4);
    $pdf->Cell(42, 8, 'Percentage', 1, 1, 'C', 0);
    $pdf->SetFont('Arial', '',  10);
    $pdf->SetXY(16, 229.4);
    $pdf->Cell(65, 8, ' Obtained Mark', 'TL', 1, 'C', 0);
    $pdf->SetXY(81, 229.4);
    $pdf->Cell(35, 8, $data['total_obt'], 'TL', 1, 'C', 0);
    $pdf->SetXY(116, 229.4);
    $pdf->Cell(35, 8, $data['remarks'], 'TLR', 1, 'C', 0);
    $pdf->SetXY(151, 229.4);
    $pdf->Cell(42, 8, number_format($data['percentage'], 2) . "%", 'TR', 1, 'C', 0);
    $pdf->SetXY(16, 237.3);
    $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
    $pdf->SetXY(81, 237.3);
    $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
    $pdf->SetXY(116, 237.3);
    $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
    $pdf->SetXY(151, 237.3);
    $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
  } else {
    $pdf->SetFont("times", '', 12);
    $pdf->SetXY(15, 55);
    $imagePath = $data['Photo'];
    $pdf->Image($imagePath, 163, 50, 30, 30);
    $pdf->SetXY(15, 60);
    $pdf->Cell(0, 0, 'Statement of Marks', 0, 0, 'C', 0);
    $pdf->SetXY(15, 67.4);
    $pdf->Cell(0, 0,  $data['duration_val'] . ' ' . 'in' . ' ' . $data['course'], 0, 0, 'C', 0);
    $pdf->SetXY(15, 75);
    $pdf->Cell(0, 0, 'Admission Session :' . ucwords(strtolower($data['Admission_Session'] )). '', 0, 0, 'C', 0);
    $pdf->SetXY(16.1, 82);
    $pdf->Cell(107, 10,  'Name : ' . ucwords(strtolower(($data['First_Name'])) . ' ' .ucwords(strtolower( $data['Middle_Name'])) . ' ' . ucwords(strtolower($data['Last_Name']))), 'TL', 0, 'L', 0);
    $pdf->SetXY(123, 82);
    $pdf->Cell(70, 10,  'Enrollment No : ' . $data['Enrollment_No'], 'LTR', 0, 'L', 0);
    $pdf->SetXY(16.1, 92);
    $pdf->Cell(107, 10, 'Father Name : ' . ucwords(strtolower($data['Father_Name'])), 'LTB', 0, 'L', 0);
    $pdf->SetXY(123, 92);
    $pdf->Cell(70, 10, $data['mode_type'] . ' ' . ':' . ' ' . $data['durMonthYear'], 1, 0, 'L', 0);
    $pdf->SetXY(16.1, 102);
    $pdf->Cell(107, 10, 'School : ' .  ' ' . $data['university_name'], 'LTB', 0, 'L', 0);
    $pdf->SetXY(123, 102);
    $pdf->Cell(70, 10,  'Exam Session : '. ' ' . ucwords(strtolower($data['Exam_Session'])), 1, 0, 'L', 0);
    $pdf->SetFont('Arial', 'B',  10);
    $cellWidth = 25;
    $cellHeight = 10;
    $pdf->SetXY(16.1, 115);
    $pdf->MultiCell(25, 10,  'Subject Code', 'TL',  'C');
    $pdf->SetXY(41, 115);
    $pdf->MultiCell(76, 10,  'Subject Name ', 'TL', 'C');
    $pdf->SetXY(117, 115);
    $pdf->MultiCell(25, 10,  'Internal', 'TL',  'C');
    $pdf->SetXY(142, 115);
    $pdf->MultiCell(25, 10,  'External', 'TL',  'C');
    $pdf->SetXY(167, 115);
    $pdf->MultiCell(26, 10,  'Total', 'TLR',  'C');
    $pdf->SetXY(16.1, 125);
    $pdf->MultiCell(25, 10,  '', 'LB',  'C');
    $pdf->SetXY(41, 125);
    $pdf->MultiCell(76, 10,  ' ', 'LB',  'C');
    $pdf->SetXY(117, 125);
    $pdf->MultiCell(12.6, 10,  'Obt', 'TBL',  'C');
    $pdf->SetXY(129.8, 125);
    $pdf->MultiCell(12, 10,  'Max', 'TBL',  'C');
    $pdf->SetXY(142, 125);
    $pdf->MultiCell(12.5, 10,  'Obt', 'TBL',  'C');
    $pdf->SetXY(154.8, 125);
    $pdf->MultiCell(12, 10,  'Max', 'TBL',  'C');
    $pdf->SetXY(167, 125);
    $pdf->MultiCell(14, 10,  'Obt', 'TBL',  'C');
    $pdf->SetXY(181, 125);
    $pdf->MultiCell(12, 10,  'Max', 'TLBR', 'C');
    $pdf->SetXY(10, 125);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 10);
    $x_cor = 16;
    $pdf->SetX($x_cor);
    foreach ($data['marks'] as $mark) {
        $pdf->SetX($x_cor);
        if (strlen($mark['subject_name']) > 30) {
            $cellHeight = 20;
        } else {
            $cellHeight = 10;
        }
        if (strlen($mark['subject_name']) > 30) {
            $pdf->Cell(25, $cellHeight - 10, $mark['Code'], 'BL', 0, 'L');
            $nameParts = explode("\n", wordwrap($mark['subject_name'], 30));
            $pdf->MultiCell(76, 5, $nameParts[0] . chr(10) . $nameParts[1], 'BL', 0, 0, 'L');
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 107, $y-10);
            $pdf->Cell(12.8, 10,  $mark['obt_marks_int'], 'LB',0,  'C');
            $pdf->Cell(12.2, 10, $mark['Min_Marks'] , 'LB',0,  'C');
            $pdf->Cell(12.8, 10,  $mark['obt_marks_ext'], 'BL',0,  'C');
            $pdf->Cell(12.2, 10,  $mark['Max_Marks'] , 'BL',0,  'C');
            $pdf->Cell(14, 10, $mark['obt_marks']  , 'BL',0,  'C');
            $pdf->Cell(12, 10,  $mark['Min_Marks']+$mark['Max_Marks'], 'BLR',0, 'C');
            // $pdf->Cell($cellWidth, $cellHeight - 10, $mark['obt_marks'], 'LB', 0, 'C');
            // $pdf->Cell($cellWidth, $cellHeight - 10, $mark['Min_Marks'], 'LB', 0, 'C');
            // $pdf->Cell($cellWidth, $cellHeight - 10, $mark['Max_Marks'], 'LB', 0, 'C');
            // $pdf->Cell(27, $cellHeight - 10, $mark['remarks'], 'LBR', 0, 'C');
        } else {
            $pdf->Cell(25, $cellHeight, $mark['Code'], 'LB', 0, 'L');
            $pdf->Cell(76, $cellHeight, $mark['subject_name'], 'LB', 0, 'L');
            // $pdf->Cell($cellWidth, $cellHeight, $mark['obt_marks'], 'LB', 0, 'C');
            // $pdf->Cell($cellWidth, $cellHeight, $mark['Min_Marks'], 'LB', 0, 'C');
            // $pdf->Cell($cellWidth, $cellHeight, $mark['Max_Marks'], 'LB', 0, 'C');
            // $pdf->Cell(27, $cellHeight, $mark['remarks'], 'LBR', 0, 'C');
            $pdf->Cell(12.8, 10,    $mark['obt_marks_int'], 'LB',0,  'C');
            $pdf->Cell(12.2, 10,$mark['Min_Marks'], 'LB',0,  'C');
            $pdf->Cell(12.8, 10,   $mark['obt_marks_ext'], 'BL',0,  'C');
            $pdf->Cell(12.2, 10, $mark['Max_Marks'] , 'BL',0,  'C');
            $pdf->Cell(14, 10,$mark['obt_marks'] , 'BL',0,  'C');
            $pdf->Cell(12, 10,    $mark['Min_Marks']+$mark['Max_Marks'], 'BLR',0, 'C');
        }
        $pdf->Ln();
    }
    $pdf->SetXY(16, 230.4);
    $pdf->SetFont('Arial', 'B',  10);
    $pdf->Cell(0, 0, 'Aggregate Marks', 0, 0, 'C', 0);
    $pdf->SetXY(16, 232.4);
    $pdf->Cell(65, 8, 'Marks', 'TL', 1, 'C', 0);
    $pdf->SetXY(81, 232.4);
    $pdf->Cell(35, 8, 'Grand Total', 'TL', 1, 'C', 0);
    $pdf->SetXY(116, 232.4);
    $pdf->Cell(35, 8, 'Result', 'LT', 1, 'C', 0);
    $pdf->SetXY(151, 232.4);
    $pdf->Cell(42, 8, 'Percentage', 'LTR', 1, 'C', 0);
    $pdf->SetFont('Arial', '',  10);
    $pdf->SetXY(16, 240.4);
    $pdf->Cell(65, 8, ' Obtained Mark', 'TL', 1, 'C', 0);
    $pdf->SetXY(81, 240.4);
    $pdf->Cell(35, 8, $data['total_obt'], 'TL', 1, 'C', 0);
    $pdf->SetXY(116, 240.4);
    $pdf->Cell(35, 8, $data['remarks'], 'TLR', 1, 'C', 0);
    $pdf->SetXY(151, 240.4);
    $pdf->Cell(42, 8, number_format($data['percentage'], 2) . "%", 'TR', 1, 'C', 0);
    $pdf->SetXY(16, 247.3);
    $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
    $pdf->SetXY(81, 247.3);
    $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
    $pdf->SetXY(116, 247.3);
    $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
    $pdf->SetXY(151, 247.3);
    $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
  }


  $pdf->SetXY(39, 260.5);
  $pdf->Cell(0, 9.1, date('d-m-y'), 0, 1, 'L', 0);
  $pdf->Output('I', 'mark-sheet.pdf');
}
