<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../includes/db-config.php';
require '../../extras/vendor/autoload.php';

use setasign\Fpdi\Fpdi;

session_start();

$sqlQuery ='';
if (isset($_POST['course_type_id'])  && !empty($_POST['course_type_id'])) {
    $course_id = isset($_POST['course_type_id']) ? $_POST['course_type_id'] : '';
    $sqlQuery .= "AND Students.Course_ID = '$course_id'";
}

if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
    $sub_course_id = isset($_POST['course_id']) ? $_POST['course_id'] : '';
    $sqlQuery .= " AND Students.Sub_Course_ID = '$sub_course_id'";
}


if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
    $student_id_array = [];
    $student_id_array = explode(",", $_POST['student_id']);
    foreach ($student_id_array as &$en_no) {
        $en_no = "'" . $en_no . "'";
    }
    unset($en_no);
    $student_id = implode(",", $student_id_array);
    $sqlQuery .= " AND Students.Enrollment_No IN ($student_id)";
}

if (isset($_POST['category']) && !empty($_POST['category'])) {
    $sub_course_id = isset($_POST['category']) ? $_POST['category'] : '';
    $sqlQuery .= " AND Students.Duration = '$sub_course_id'";
}

$pdf_dir = '../../uploads/certificates/';

$students_sql = $conn->query("SELECT Students.*, Sub_Courses.Min_Duration as total_duration, Modes.Name as mode, Sub_Courses.Name as course, Courses.Name as program_Type FROM Students LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Modes ON Students.University_ID = Modes.University_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID  WHERE Students.Enrollment_No IS NOT NULL $sqlQuery ");

if ($students_sql->num_rows > 0) {
    while ($row = $students_sql->fetch_assoc()) {

        $students_result = $conn->query("SELECT Students.*, Sub_Courses.Min_Duration as total_duration, Modes.Name as mode, Sub_Courses.Name as course, Courses.Name as program_Type FROM Students LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID LEFT JOIN Modes ON Students.University_ID = Modes.University_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID  WHERE Students.Enrollment_No = '" . trim($row['Enrollment_No']) . "'");

        if ($students_result && $students_result->num_rows > 0) {
            $students_temps = $students_result->fetch_assoc();

            $durMonthYear = "";
            if ($students_temps['mode'] == "Monthly") {
                $durMonthYear = "Months";
            } elseif ($students_temps['mode'] == "Sem") {
                $durMonthYear = "Semesters";
            } else {
                $durMonthYear = "Years";
            }

            // Calculate total duration
            $total_duration = 0;
            if ($students_temps['University_ID'] == 47) {
                if (str_contains($students_temps['total_duration'], '"')) {
                    $a = str_replace('"', '', $students_temps['total_duration']);
                    $total_duration = (int)$a;
                } else {
                    $total_duration = (int)$students_temps['total_duration'];
                }
            } else {
                if (str_contains($students_temps['Duration'], '/')) {
                    $a = explode("/", $students_temps['Duration']);
                    $total_duration = (int)$a[0];
                } else {
                    $total_duration = (int)$students_temps['Duration'];
                }
            }

            // Determine course category
            $courseCategory = "";
            if (str_contains($students_temps['Course_Category'], '_')) {
                $a = str_replace('_', ' ', $students_temps['Course_Category']);
                $courseCategory = ucfirst($a);
            } else {
                $courseCategory = ucfirst($students_temps['Course_Category']);
            }

            // Determine certificate type and hours
            $certificate = (str_contains($students_temps['Course_Category'], 'advance_diploma')) ? "Adv. Certification Skill Diploma" : "Certified Skill Diploma";
            $hours = 0;

            if ($total_duration == 3 && $durMonthYear == "Months") {
                $certificate = "Certification Course";
                $hours = 160;
            } elseif ($total_duration == 6 && $durMonthYear == "Months") {
                $certificate = "Certified Skill Diploma";
                $hours = 320;
            } elseif ($total_duration == 11 && $durMonthYear == "Months") {
                $hours = 960;
            } elseif ($total_duration == 6 && $durMonthYear == "Semester") {
                $hours = 'NA';
            }

            $a = implode(' ', array_slice(explode(' ', $students_temps['course']), 0, 6));
            $b = implode(' ', array_slice(explode(' ', $students_temps['course']), 6));

            $name = $students_temps['First_Name'] . " " . $students_temps['Middle_Name'] . " " . $students_temps['Last_Name'];
            $f_name = $students_temps['Father_Name'];
            $father_name = "Son/Daughter of Mr. $f_name";
            $Enrol_no = $students_temps['Enrollment_No'];


            require_once('../../extras/TCPDF/tcpdf.php');
            require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
            require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');
            $pdf = new FPDI('L', 'mm', array(299, 210));
            $pdf->AddPage();
            $pdf->AddFont('GreatVibes-Regular', '', 'GreatVibes-Regular.php');
            $pdf->AddFont('OpenSans-Regular', '', 'OpenSans-VariableFont_wdth,wght.php');

            $name = array_filter(explode(" ", $name));
            $htmlName = "";
            $counter = 1;
            foreach ($name as $n) {
                if ($counter > 1)
                    $htmlName .= ' ';
                $firstLetter = substr($n, 0, 1);
                $restLetters = substr($n, 1);

                $htmlName .= '<font style="font-size:12px">' . strtoupper($firstLetter) . '</font>' . strtolower($restLetters);
                $counter++;
            }

            $tcpdf = new TCPDF('L', 'mm', array(299, 210));
            $tcpdf->AddPage();
            $tcpdf->SetPrintHeader(false);
            $tcpdf->SetPrintFooter(false);
            $tcpdf->setFont($font_family = 'montserratb', $font_variant = '', $font_size = 12);
            $tcpdf->SetTextColor(6, 64, 101);
            $tcpdf->writeHTMLCell(0, 0, 3.8, 101.8,strtoupper( $htmlName) . " bearing Enrollment No " . $Enrol_no, 0, 0, false, true, 'C');

            $tcpdf->SetTextColor(0, 0, 0);
            $tcpdf->setFont($font_family = 'montserratb', $font_variant = '', $font_size = 10);
            $tcpdf->writeHTMLCell(250, 0, 156.5, 116, $certificate, 0, 0, false, true, 'L');
            $tcpdf->setFont($font_family = 'worksanssemib', $font_variant = '', $font_size = 10);
            $tcpdf->writeHTMLCell(0, 0, 0, 125, ucwords(strtolower($students_temps['course'])), 0, 0, false, true, 'C');
            $tcpdf->setFont($font_family = 'montserratb', $font_variant = '', $font_size = 10);
            $tcpdf->writeHTMLCell(40, 7, 102, 144, 'AY  2023 - 24', 0, 0, false, true, 'L');
            $tcpdf->writeHTMLCell(100, 7, 200, 144, "$total_duration" . " " . $durMonthYear . " /" . $hours . " hours ", 0, 0, false, true, 'L');
            $tcpdf->writeHTMLCell(100, 7, 42.2, 180, date('d-m-Y'), 0, 0, false, true, 'L');

            $temp_pdf_path = tempnam(sys_get_temp_dir(), 'tcpdf_');
            $tcpdf->Output($temp_pdf_path, 'F');
            $pdf->setSourceFile($temp_pdf_path);
            $tpl = $pdf->importPage(1);
            $pdf->useTemplate($tpl, 0, 0);

            unlink($temp_pdf_path);
            $filename = $students_temps['Enrollment_No'] . "_" . time() . ".pdf";
            $pdf->Output('../../uploads/certificates/' . $filename, "F");
        } else {
            echo "No student found for enrollment number: " . $enroll . "<br>";
        }
    }
    $zip = new ZipArchive();
    $zip_file = $pdf_dir . 'Certificates' . time() . '.zip';

    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = glob($pdf_dir . '*.pdf');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    } else {
        echo 'Failed to create zip file.';
    }

    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=Certificates_' . time() . '.zip');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);
    foreach ($files as $file) {
        unlink($file);
    }
  unlink($zip_file);
} else {
    echo "No record Founds!";
}
