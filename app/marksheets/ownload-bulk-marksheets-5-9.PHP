<?php
require '../../includes/db-config.php';
require '../../includes/helpers.php';
session_start();

$url = "https://erpglocal.iitseducation.org";
$passFail = "PASS";

use setasign\Fpdi\PdfReader;
use setasign\Fpdi\Fpdi;

ob_end_clean();
require_once('../../extras/TCPDF/tcpdf.php');
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');
require '../../extras/vendor/autoload.php';


$sqlQuery = '';
if (isset($_POST['course_type_id']) && !empty($_POST['course_type_id'])) {
    $course_id = $_POST['course_type_id'];
    $sqlQuery .= "AND Students.Course_ID = '$course_id'";
}

if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
    $sub_course_id = $_POST['course_id'];
    $sqlQuery .= " AND Students.Sub_Course_ID = '$sub_course_id'";
}

if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
    $student_id_array = explode(",", $_POST['student_id']);
    foreach ($student_id_array as &$en_no) {
        $en_no = "'" . $en_no . "'";
    }
    unset($en_no);
    $student_id = implode(",", $student_id_array);
    $sqlQuery .= " AND Students.Enrollment_No IN ($student_id)";
}

if (isset($_POST['category']) && !empty($_POST['category'])) {
    $sub_course_id = $_POST['category'];
    $sqlQuery .= " AND Students.Duration = '$sub_course_id'";
}



$pdf_dir = '../../uploads/marksheet/';

$student = $conn->query("SELECT Students.*, Sub_Courses.Min_Duration as total_duration,Sub_Courses.Name as course, Courses.Name as program_Type FROM Students LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID  WHERE Students.Enrollment_No IS NOT NULL $sqlQuery");

if ($student->num_rows > 0) {
    while ($row = $student->fetch_assoc()) {
        $students_result = $conn->query("SELECT Students.*, Sub_Courses.Min_Duration as total_duration, Modes.Name as mode, Sub_Courses.Name as course, Courses.Name as program_Type, Admission_Sessions.Name as Admission_Session,Admission_Sessions.Exam_Session, Admission_Types.Name as Admission_Type FROM Students LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Modes ON Students.University_ID = Modes.University_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID  LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.Enrollment_No = '" . trim($row['Enrollment_No']) . "'");
        $data = [];
        $data['remarks'] = "Pass";
        $data = $students_result->fetch_assoc();
        $typoArr = ["th", "st", "nd", "rd", "th", "th", "th", "th", "th"];
        $total_obt = 0;
        $total_max = 0;
        $durations_query = "";
        $min_val = 0;
        $temp_subjects = "";
        $sem_sql ='';
        $scheme_id =NULL;
        $semester = NULL;
        if (isset($_POST['semester']) && !empty($_POST['semester'])) {
            list($scheme_id, $semester) = explode('|',$_POST['semester']);
            $data['Duration'] = $semester;
            $sem_sql  = " AND Syllabi.Semester = '$semester' AND Syllabi.Scheme_ID = $scheme_id";
            $durations_query = " AND Syllabi.Semester = " . $semester;
        }else if ($data['University_ID'] == 47) {
           
            $sem_sql = " AND Semester = " . $data['Duration'];
            $durations_query = " AND Syllabi.Semester = " . $data['Duration'];
        }
       // echo "SELECT marksheets.obt_marks_ext, marksheets.obt_marks_int,marksheets.obt_marks,marksheets.status,marksheets.remarks,marksheets.Created_At,Syllabi.Code,Syllabi.Name as subject_name,Syllabi.Min_Marks, Syllabi.Max_Marks FROM marksheets LEFT JOIN Syllabi ON marksheets.subject_id = Syllabi.ID WHERE enrollment_no = '" . $data['Enrollment_No'] . "'  $sem_sql ORDER BY Syllabi.Code ASC";die;
        $temp_subjects = $conn->query("SELECT marksheets.obt_marks_ext, marksheets.obt_marks_int,marksheets.obt_marks,marksheets.status,marksheets.remarks,marksheets.Created_At,Syllabi.Code,Syllabi.Name as subject_name,Syllabi.Min_Marks, Syllabi.Max_Marks FROM marksheets LEFT JOIN Syllabi ON marksheets.subject_id = Syllabi.ID WHERE enrollment_no = '" . $data['Enrollment_No'] . "'  $sem_sql ORDER BY Syllabi.Code ASC");
        $data['marks'] = array();
        $temp_subject = [];
        $total_obt = 0;
        $total_max = 0;
        $resultPublishDay = "";
        $o_int_marks =[];
        if ($temp_subjects->num_rows > 0) {
            while ($temp_subject = $temp_subjects->fetch_assoc()) {

                if ($temp_subject != null) {
                    $resultPublishDay = date("d/m/Y", strtotime($temp_subject['Created_At']));
                    //$temp_subject['remarks'] = isset($data['remarks']) ? $data['remarks'] : '';

                    $obt_marks_ext = isset($temp_subject['obt_marks_ext']) ? $temp_subject['obt_marks_ext'] : 0;
                    $obt_marks_int = isset($temp_subject['obt_marks_int']) ? $temp_subject['obt_marks_int'] : 0;
                    $o_int_marks[] =    $obt_marks_int +  $obt_marks_ext;
                    


                    $total_obt = $total_obt + intval($obt_marks_ext) + intval($obt_marks_int);

                    $min_val = ($temp_subject['Min_Marks'] + $temp_subject['Max_Marks']) * 40 / 100;
                    $passFail = "Pass";
                    ///if ($total_obt < $min_val) {
                    //    $passFail = "FAIL";
                    //}
                    if ($temp_subject['remarks'] != "Pass") {
                            $passFail = "FAIL";
                        }

                    if ($data['University_ID'] == 47) {
                        $total_max = $total_max + $temp_subject['Min_Marks'] + $temp_subject['Max_Marks'];
                    } else {
                        $total_max = $total_max + $temp_subject['Max_Marks'];
                    }
                    $temp_subject['remarks'] = $passFail;
                    $data['marks'][] = $temp_subject;
                    $data['remarks'] = $passFail;
                }
            }
// echo "<pre>"; print_r($o_int_marks);
            $data['total_max'] = $total_max;
            $data['total_obt'] = $total_obt;
            $percentage = 0;
            if ($total_max !== 0) {
                $percentage = ($total_obt / $total_max) * 100;
            } else {
                $percentage = 0;
            }

            $marksWords = ucwords(strtolower(numberToWordFunc($total_obt)));
            $count = $temp_subjects->num_rows;
            $hours = '';
            $total_duration = '';

            if ($data['University_ID'] == 48) {
                $data['mode_type'] = "Duration";
                $data['university_name'] = "Skill Education Development";
                $total_duration = $data['Duration'];
                $data['Durations'] = $data['Duration'];
                if ($total_duration == 3) {
                    $durations = "Certification Course";
                    $hours = 160;
                } elseif ($total_duration == 6) {
                    $durations = "Certified Skill Diploma";
                    $hours = 320;
                } else if ($data['Duration'] == '11/advance-diploma') {
                    $hours = 960;
                    $durations = "Advanced Certification Skill Diploma";
                    $data['Durations'] = 11;
                } else if ($data['Duration'] == '11/certified') {
                    $durations = "Certified Skill Diploma";
                    $data['Durations'] = 11;
                    $hours = 960;
                } elseif ($total_duration == 6 && $durMonthYear == "Semester") {
                    $hours = 'NA';
                }
            } else {
                $data['university_name'] = "Glocal School Of Vocational Studies";
                $data['mode_type'] = "Semester";
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
                $data['durMonthYear'] = $data['Durations'] . $durMonthYear . '/ ' . $hours . "Hours";
            } else {
                $data['durMonthYear'] = $data['Duration'] . $typoArr[$data['Duration']];
            }

            $student_doc_query = "SELECT Location FROM Student_Documents WHERE Student_ID = '" . $data['ID'] . "' AND Type = 'Photo'";
            $student_doc = $conn->query($student_doc_query);
            $student_doc = $student_doc->fetch_assoc();
            $photo = $student_doc['Location'];
            $data['Photo'] = $url . $photo;

            $pdf = new Fpdi();
            $pdf->addPage();
            if ($data['University_ID'] == 48) {
                $pdf->SetFont("times", '', 10);
                $pdf->SetXY(15, 65);
                $pdf->Cell(0, 0, 'Statement of Marks', 0, 0, 'C', 0);
                $pdf->SetXY(15, 72.4);
                $pdf->Cell(0, 0, $data['duration_val'] . ' ' . 'in' . ' ' .  ucwords(strtolower($data['course'])), 0, 0, 'C', 0);
                $pdf->SetXY(15, 81);
                $pdf->Cell(0, 0, 'AY  2023-24' . '', 0, 0, 'C', 0);
                $pdf->SetXY(16.1, 86);
                $pdf->Cell(107, 10, strtoupper('Name : ') . strtoupper($data['First_Name']) . ' ' . strtoupper($data['Middle_Name']) . ' ' . strtoupper($data['Last_Name']), 'TL', 0, 'L', 0);
                $pdf->SetXY(123, 86);
                $pdf->Cell(70, 10, strtoupper('Enrollment No : ') . $data['Enrollment_No'], 'LTR', 0, 'L', 0);
                $pdf->SetXY(16.1, 96);
                $pdf->Cell(107, 10, strtoupper('School : ') . strtoupper('School Of' . ' ' . $data['university_name']), 'LTB', 0, 'L', 0);
                $pdf->SetXY(123, 96);
                $pdf->Cell(70, 10, strtoupper($data['mode_type'] . ' ' . ':' . ' ' . $data['durMonthYear']), 1, 0, 'L', 0);

                // skill
                $pdf->SetFont('Arial', 'B', 10);
                $cellWidth = 20;
                $cellHeight = 10;
                $pdf->SetXY(16.1, 110);
                $pdf->MultiCell(25, 10, 'Subject Code', 'TLB', 'C');
                $pdf->SetXY(41, 110);
                $pdf->MultiCell(71, 10, 'Subject Name ', 'TLB', 'C');
                $pdf->SetXY(112, 110);
                $pdf->MultiCell(20, 5, 'Obtained Marks', 'TLB', 'C');
                $pdf->SetXY(132, 110);
                $pdf->MultiCell(20, 5, 'Min. Marks', 'TLB', 'C');
                $pdf->SetXY(152, 110);
                $pdf->MultiCell(19.8, 5, 'Max. Marks', 'TLB', 'C');
                $pdf->SetXY(172, 110);
                $pdf->MultiCell(22, 10, 'Remarks', 1, 'C');
                $pdf->SetXY(10, 110);
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
                    $mark['subject_name'] = str_replace("\xC2\xA0", ' ', $mark['subject_name']);
                    $mark['subject_name'] = preg_replace('/\s+/', ' ', $mark['subject_name']);
                    $mark['subject_name'] = utf8_encode($mark['subject_name']);
                    if (strlen($mark['subject_name']) > 30) {
                        $pdf->Cell(25, $cellHeight - 10, $mark['Code'], 'LB', 0, 'L');
                        $nameParts = explode("\n", wordwrap($mark['subject_name'], 30));
                        $pdf->MultiCell(71, 5, $nameParts[0] . chr(10) . $nameParts[1], 'LB', 0, 0, 'L');
                        $x = $pdf->GetX();
                        $y = $pdf->GetY();
                        $pdf->SetXY($x + 102, $y - 10);
                        $pdf->Cell($cellWidth, $cellHeight - 10, $mark['obt_marks_int']+$mark['obt_marks_ext'], 'LB', 0, 'C');
                        $pdf->Cell($cellWidth, $cellHeight - 10, $mark['Min_Marks'], 'LB', 0, 'C');
                        $pdf->Cell($cellWidth, $cellHeight - 10, $mark['Max_Marks'], 'LB', 0, 'C');
                        $pdf->Cell(22, $cellHeight - 10, $mark['remarks'], 'LBR', 0, 'C');
                    } else {
                        $pdf->Cell(25, $cellHeight, $mark['Code'], 'LB', 0, 'L');
                        $pdf->Cell(71, $cellHeight, $mark['subject_name'], 'LB', 0, 'L');
                        $pdf->Cell($cellWidth, $cellHeight, $mark['obt_marks_int']+$mark['obt_marks_ext'], 'LB', 0, 'C');
                        $pdf->Cell($cellWidth, $cellHeight, $mark['Min_Marks'], 'LB', 0, 'C');
                        $pdf->Cell($cellWidth, $cellHeight, $mark['Max_Marks'], 'LB', 0, 'C');
                        $pdf->Cell(22, $cellHeight, $mark['remarks'], 'LBR', 0, 'C');
                    }
                    $pdf->Ln();
                }

                if ($count >= 9) {
                    //more thwn 9 subject
                    $pdf->SetXY(16, 234.4);
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(0, 0, 'Aggregate Marks', 0, 0, 'C', 0);
                    $pdf->SetXY(16, 238.4);
                    $pdf->Cell(65, 8, 'Marks', 'TL', 1, 'C', 0);
                    $pdf->SetXY(81, 238.4);
                    $pdf->Cell(35, 8, 'Grand Total', 'TL', 1, 'C', 0);
                    $pdf->SetXY(116, 238.4);
                    $pdf->Cell(35, 8, 'Result', 'LTB', 1, 'C', 0);
                    $pdf->SetXY(151, 238.4);
                    $pdf->Cell(42, 8, 'Percentage', 1, 1, 'C', 0);
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->SetXY(16, 246.4);
                    $pdf->Cell(65, 8, ' Obtained Mark', 'TL', 1, 'C', 0);
                    $pdf->SetXY(81, 246.4);
                    $pdf->Cell(35, 8, $data['total_obt'], 'TL', 1, 'C', 0);
                    $pdf->SetXY(116, 246.4);
                    $pdf->Cell(35, 8, $data['remarks'], 'TLR', 1, 'C', 0);
                    $pdf->SetXY(151, 246.4);
                    $pdf->Cell(42, 8, number_format($percentage, 2) . "%", 'TR', 1, 'C', 0);
                    $pdf->SetXY(16, 254.3);
                    $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
                    $pdf->SetXY(81, 254.3);
                    $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
                    $pdf->SetXY(116, 254.3);
                    $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
                    $pdf->SetXY(151, 254.3);
                    $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
                } else {
                    //less then 9 subject
                    $pdf->SetXY(16, 217.4);
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(0, 0, 'Aggregate Marks', 0, 0, 'C', 0);
                    $pdf->SetXY(16, 221.4);
                    $pdf->Cell(65, 8, 'Marks', 'TL', 1, 'C', 0);
                    $pdf->SetXY(81, 221.4);
                    $pdf->Cell(35, 8, 'Grand Total', 'TL', 1, 'C', 0);
                    $pdf->SetXY(116, 221.4);
                    $pdf->Cell(35, 8, 'Result', 'LTB', 1, 'C', 0);
                    $pdf->SetXY(151, 221.4);
                    $pdf->Cell(42, 8, 'Percentage', 1, 1, 'C', 0);
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->SetXY(16, 229.4);
                    $pdf->Cell(65, 8, ' Obtained Mark', 'TL', 1, 'C', 0);
                    $pdf->SetXY(81, 229.4);
                    $pdf->Cell(35, 8, $data['total_obt'], 'TL', 1, 'C', 0);
                    $pdf->SetXY(116, 229.4);
                    $pdf->Cell(35, 8, $data['remarks'], 'TLR', 1, 'C', 0);
                    $pdf->SetXY(151, 229.4);
                    $pdf->Cell(42, 8, number_format($percentage, 2) . "%", 'TR', 1, 'C', 0);
                    $pdf->SetXY(16, 237.3);
                    $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
                    $pdf->SetXY(81, 237.3);
                    $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
                    $pdf->SetXY(116, 237.3);
                    $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
                    $pdf->SetXY(151, 237.3);
                    $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
                }
            } else {
                $pdf->SetFont("times", '', 12);
                $pdf->SetXY(15, 55);
                // $imagePath = $data['Photo'];
                // $pdf->Image($imagePath, 163, 50, 30, 30);
                $pdf->SetXY(15, 60);
                $pdf->Cell(0, 0, 'Statement of Marks', 0, 0, 'C', 0);
                $pdf->SetXY(15, 67.4);
                $pdf->Cell(0, 0,  $data['duration_val'] . ' ' . 'in' . ' ' .  ucwords(strtolower($data['course'])), 0, 0, 'C', 0);
                $pdf->SetXY(15, 75);
                $pdf->Cell(0, 0, 'Admission Session :' . ucwords(strtolower($data['Admission_Session'])) . '', 0, 0, 'C', 0);
                $pdf->SetXY(16.1, 82);
                $pdf->Cell(107, 10,  'Name : ' . ucwords(strtolower(($data['First_Name'])) . ' ' . ucwords(strtolower($data['Middle_Name'])) . ' ' . ucwords(strtolower($data['Last_Name']))), 'TL', 0, 'L', 0);
                $pdf->SetXY(123, 82);
                $pdf->Cell(70, 10,  'Enrollment No : ' . $data['Enrollment_No'], 'LTR', 0, 'L', 0);
                $pdf->SetXY(16.1, 92);
                $pdf->Cell(107, 10, 'Father Name : ' . ucwords(strtolower($data['Father_Name'])), 'LTB', 0, 'L', 0);
                $pdf->SetXY(123, 92);
                $pdf->Cell(70, 10, $data['mode_type'] . ' ' . ':' . ' ' . $data['durMonthYear'], 1, 0, 'L', 0);
                $pdf->SetXY(16.1, 102);
                $pdf->Cell(107, 10, 'School : ' .  ' ' . $data['university_name'], 'LTB', 0, 'L', 0);
                $pdf->SetXY(123, 102);
                $pdf->Cell(70, 10,  'Exam Session : ' . ' ' . ucwords(strtolower($data['Exam_Session'])), 1, 0, 'L', 0);
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
                    $mark['subject_name'] = str_replace("\xC2\xA0", ' ', $mark['subject_name']);
                    $mark['subject_name'] = preg_replace('/\s+/', ' ', $mark['subject_name']);
                    $mark['subject_name'] = utf8_encode($mark['subject_name']);
                    if (strlen($mark['subject_name']) > 30) {
                        $pdf->Cell(25, $cellHeight - 10, $mark['Code'], 'BL', 0, 'L');
                        $nameParts = explode("\n", wordwrap($mark['subject_name'], 30));
                        $pdf->MultiCell(76, 5, $nameParts[0] . chr(10) . $nameParts[1], 'BL', 0, 0, 'L');
                        $x = $pdf->GetX();
                        $y = $pdf->GetY();
                        $pdf->SetXY($x + 107, $y - 10);
                        $pdf->Cell(12.8, 10,  $mark['obt_marks_int'], 'LB', 0,  'C');
                        $pdf->Cell(12.2, 10, $mark['Min_Marks'], 'LB', 0,  'C');
                        $pdf->Cell(12.8, 10,  $mark['obt_marks_ext'], 'BL', 0,  'C');
                        $pdf->Cell(12.2, 10,  $mark['Max_Marks'], 'BL', 0,  'C');
                        $pdf->Cell(14, 10, $mark['obt_marks_int']+$mark['obt_marks_ext'], 'BL', 0,  'C');
                        $pdf->Cell(12, 10,  $mark['Min_Marks'] + $mark['Max_Marks'], 'BLR', 0, 'C');
                    } else {
                        $pdf->Cell(25, $cellHeight, $mark['Code'], 'LB', 0, 'L');
                        $pdf->Cell(76, $cellHeight, $mark['subject_name'], 'LB', 0, 'L');
                        $pdf->Cell(12.8, 10,    $mark['obt_marks_int'], 'LB', 0,  'C');
                        $pdf->Cell(12.2, 10, $mark['Min_Marks'], 'LB', 0,  'C');
                        $pdf->Cell(12.8, 10,   $mark['obt_marks_ext'], 'BL', 0,  'C');
                        $pdf->Cell(12.2, 10, $mark['Max_Marks'], 'BL', 0,  'C');
                        $pdf->Cell(14, 10, $mark['obt_marks_int']+$mark['obt_marks_ext'], 'BL', 0,  'C');
                        $pdf->Cell(12, 10,    $mark['Min_Marks'] + $mark['Max_Marks'], 'BLR', 0, 'C');
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
                $pdf->Cell(42, 8, number_format($percentage, 2) . "%", 'TR', 1, 'C', 0);
                $pdf->SetXY(16, 247.3);
                $pdf->Cell(65, 8, 'Maximum Mark', 'TLB', 1, 'C', 0);
                $pdf->SetXY(81, 247.3);
                $pdf->Cell(35, 8, $data['total_max'], 'LBT', 1, 'C', 0);
                $pdf->SetXY(116, 247.3);
                $pdf->Cell(35, 8, '', 'LRB', 'LB', 'C', 0);
                $pdf->SetXY(151, 247.3);
                $pdf->Cell(42, 8, '', 'RB', 'RB', 'C', 0);
            }
            $pdf->SetXY(39, 266);
            $pdf->Cell(0, 9.1, date('d-m-Y'), 0, 1, 'L', 0);
            $filename = $data['Enrollment_No'] . "_" . time() . ".pdf";
            $pdf->Output($pdf_dir . $filename, "F");
        }
    }

    $zip = new ZipArchive();
    $zip_file = $pdf_dir . 'Marksheets_' . time() . '.zip';
    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = glob($pdf_dir . '*.pdf');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . basename($zip_file));
        header('Content-Length: ' . filesize($zip_file));
        readfile($zip_file);

        foreach ($files as $file) {
            unlink($file);
        }
        unlink($zip_file);
    } else {
        echo 'Failed to create zip file.';
    }
} else {
    echo "No record found!";
}
