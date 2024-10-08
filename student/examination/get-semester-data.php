<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
session_start();
ini_set('display_errors', 1);
require '../../includes/db-config.php';
require '../../includes/helpers.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');

    body {
        font-family: 'Roboto', sans-serif;
    }
</style>
<?php
$url = "https://erpglocal.iitseducation.org";
$passFail = "PASS";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year_sem = $_POST['year_sem'];
    $usernames = $_POST['user_id'];
    $passwords = $_POST['password'];
    $typoArr = ["th", "st", "nd", "rd", "th", "th", "th", "th", "th"];

    $student = $conn->query("SELECT Students.*,Sub_Courses.Min_Duration, Courses.Name as program_Type, Sub_Courses.Name as course,Modes.Name as mode, Course_Types.Name as Course_Type, Admission_Sessions.Name as Admission_Session,Admission_Sessions.Exam_Session, Admission_Types.Name as Admission_Type, CONCAT(Courses.Short_Name, ' (',Sub_Courses.Name,')') as Course_Sub_Course, TIMESTAMPDIFF(YEAR, DOB, CURDATE()) AS Age FROM Students LEFT JOIN Modes on Students.University_ID=Modes.University_ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Course_Types ON Courses.Course_Type_ID = Course_Types.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.Unique_ID LIKE '$usernames' AND Students.Unique_ID = '$passwords' AND Students.Step = 4 AND Students.Status = 1");
    //    echo " SELECT m.exam_month,m.exam_year FROM marksheets AS m LEFT JOIN Syllabi AS s ON m.subject_id = s.ID WHERE m.enrollment_no = '" . $Students_temps['Enrollment_No'] . "' AND s.Course_ID = " . $Students_temps['Course_ID'] . "  AND  s.Sub_Course_ID = " . $Students_temps['Sub_Course_ID'] . "  AND s.Semester=$year_sem  GROUP BY m.enrollment_no";die;
    $Students_temps = [];
    if ($student->num_rows > 0) {
        $Students_temps = $student->fetch_assoc();
    } else {
        echo '<div class="mt-5 mb-4 text-center" style="margin-top:220px;"><h5>Invalid credentials!</h5></div>';
        die;
    }

    $photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = " . $Students_temps['ID'] . " AND Type = 'Photo'");
    if ($photo->num_rows > 0) {
        $photo = $photo->fetch_assoc();
        $Students_temps['Photo'] = $photo['Location'];
    }

    $total_obt = 0;
    $total_max = 0;
    $percentage = 0;
    $durations_query = "";
    if ($Students_temps['University_ID'] == 47 && isset($year_sem)) {
        $durations_query = "AND s.Semester = " . $year_sem;
    }
    $getDataSQL = $conn->query("SELECT s.Name as subject_name, s.Code,s.Max_Marks, s.Min_Marks,m.obt_marks,m.remarks,m.obt_marks_ext,m.obt_marks_int From marksheets AS m LEFT JOIN Syllabi AS s ON m.subject_id = s.ID WHERE m.enrollment_no = '" . $Students_temps['Enrollment_No'] . "' AND s.Course_ID = " . $Students_temps['Course_ID'] . " AND  s.Sub_Course_ID = " . $Students_temps['Sub_Course_ID'] . " $durations_query ");

    if ($getDataSQL->num_rows == 0) {
        echo '<div class="mt-5 mb-4 text-center" style="margin-top:220px;"><h5>Result Not Published Yet.</h5></div>';
        die;
    }

    while ($getDataArr = $getDataSQL->fetch_assoc()) {

        if ($getDataArr['remarks'] != "Pass" || $getDataArr['obt_marks_ext'] == 0 || $getDataArr['obt_marks_int'] == 0) {
            $getDataArr['remarks'] = "FAIL";
            $passFail = 'FAIL';
        } else {
            $getDataArr['remarks'] = "Pass";
            $passFail = 'Pass';

        }
        $obt_marks_ext = $getDataArr['obt_marks_ext'];
        $obt_marks_int = $getDataArr['obt_marks_int'];
        $total_obt = $total_obt + $obt_marks_ext + $obt_marks_int;
        if ($Students_temps['University_ID'] == 47) {
            $total_max = $total_max + $getDataArr['Min_Marks'] + $getDataArr['Max_Marks'];
        } else {
            $total_max = $total_max + $getDataArr['Max_Marks'];
        }
        $Students_temps['marks'][] = $getDataArr;
    }

    $Students_temps['total_max'] = $total_max;
    $Students_temps['total_obt'] = $total_obt;

    if ($total_max !== 0) {
        $percentage = ($total_obt / $total_max) * 100;
    } else {
        $percentage = 0;
    }
    $Students_temps['percentage'] = $percentage;

    $durMonthYear = "";
    if ($Students_temps['mode'] == "Monthly") {
        $durMonthYear = " Months";
    } elseif ($Students_temps['mode'] == "Sem") {
        $durMonthYear = " Semester";
    } else {
        $durMonthYear = " Years";
    }

    $Students_temps['mode_type'] = "Semester";
    $Students_temps['durMonthYear'] = $year_sem . $durMonthYear;
    $Students_temps['Enrollment_No'] = isset($Students_temps['Enrollment_No']) ? $Students_temps['Enrollment_No'] : '';
    $Students_temps['university_name'] = "Glocal School Of Vocational Studies";
    $Students_temps['duration_val'] = "B. VOC";
    $Students_temps['mode_type'] = "Semester";
    $min_duration = json_decode($Students_temps['Min_Duration']);
    $sem = $year_sem;
    $typoArr = ["th", "st", "nd", "rd", "th", "th", "th", "th", "th"];
    if ($Students_temps['University_ID'] == 47) {
        $Students_temps['durMonthYear'] = $sem . $typoArr[$sem];
    } else {
        $Students_temps['durMonthYear'] = $Students_temps['Duration'] . $durMonthYear;
    }

    $getDataSQL = "";
    if ($Students_temps['University_ID'] == 48) {
        $exam_date = $conn->query("SELECT m.exam_month,m.exam_year FROM marksheets AS m LEFT JOIN Syllabi AS s ON m.subject_id = s.ID WHERE m.enrollment_no = '" . $Students_temps['Enrollment_No'] . "' AND s.Course_ID = " . $Students_temps['Course_ID'] . "  AND  s.Sub_Course_ID = " . $Students_temps['Sub_Course_ID'] . "   GROUP BY m.enrollment_no");
    } else {
        // echo " SELECT m.exam_month,m.exam_year FROM marksheets AS m LEFT JOIN Syllabi AS s ON m.subject_id = s.ID WHERE m.enrollment_no = '" . $Students_temps['Enrollment_No'] . "' AND s.Course_ID = " . $Students_temps['Course_ID'] . "  AND  s.Sub_Course_ID = " . $Students_temps['Sub_Course_ID'] . "  AND s.Semester=$year_sem  GROUP BY m.enrollment_no";die;
        $exam_date = $conn->query(" SELECT m.exam_month,m.exam_year FROM marksheets AS m LEFT JOIN Syllabi AS s ON m.subject_id = s.ID WHERE m.enrollment_no = '" . $Students_temps['Enrollment_No'] . "' AND s.Course_ID = " . $Students_temps['Course_ID'] . "  AND  s.Sub_Course_ID = " . $Students_temps['Sub_Course_ID'] . "  AND s.Semester=$year_sem  GROUP BY m.enrollment_no");
    }
    if ($exam_date->num_rows > 0) {
        $examArr = $exam_date->fetch_assoc();

        if (!empty($examArr['exam_month']) || !empty($examArr['exam_year'])) {
            $exam_session = ucwords($examArr['exam_month']) . '-' . $examArr['exam_year'];
        } else {
            $exam_session = ucwords($Students_temps['Exam_Session']);
        }
    }
    $html = '<div id="content" class="html-content" style="background: #fff;">';
    $html .= '<div class="mt-5 body" style="border:3px solid #1e1919;height: 1111px;; width: 900px; margin: 0 auto; background-position: center; background-size: contain; background-repeat: no-repeat; padding: 0px;">
            <div class="" style="display:flex; justify-content:center;"><img src="https://vocational.glocaluniversity.edu.in/assets/images/downloadfooter.webp" alt=""style="margin-top: 15px;width:27%"></div>
              <p style="margin-top:1%;text-align: center;font-weight: 700;font-size: 20px!important;color:black !important;">(A University Established by UP Act 2 of 2012)</p>
            <div class="main-result-box" style="padding: 0px; height: 0px; margin: 0 auto; position: relative; top: 0px; right: 0px;">
                    
                    <p class="text-center text-dark fw-bold">Statement of Marks</p>
                    <p class="text-center text-dark fw-bold">' . $Students_temps['duration_val'] . ' ' . 'in' . ' ' . $Students_temps['course'] . '</p>
                    <p class="text-center text-dark fw-bold">Admission Session :' . ucwords(strtolower($Students_temps['Admission_Session'])) . '</p>
                          <img src="' . $url . $Students_temps['Photo'] . '" alt="" width="100" height="100" class="img-pp" style=" position: absolute; right: 40px;top: 5px;">
                    <div class="row justify-content-center">
                      ';

    $html .= '<div class="col-lg-12 mb-4" style="width:94%;">
						<div class="table-resposive">
					   <table class="table-bordered mb-3">
						 <tbody>
						<tr>
							<td class="col" style="width:600px; height:40px;    padding-left: 10px;"><span class="fw-bold " style="color: #05519E;">Name:</span> <span class="text-dark fw-bold">' . ucwords(strtolower($Students_temps['First_Name'])) . " " . ucwords(strtolower($Students_temps['Middle_Name'])) . " " . ucwords(strtolower($Students_temps['Last_Name'])) . '</span></td>
							<td  class="col" style="width:400px; height:40px;     padding-left: 10px;"><span class="fw-bold " style="color: #05519E;"> Enrollment No:</span><span class="text-dark fw-bold"> ' . $Students_temps['Enrollment_No'] . '</span></td>
						</tr>
						<tr>
						<td class="col" style="width:600px; height:40px;     padding-left: 10px;"><span class="fw-bold " style="color: #05519E;">Father Name:</span> <span class="text-dark fw-bold">' . ucwords(strtolower($Students_temps['Father_Name'])) . '</span></td>
						<td class="col" style="width:400px; height:40px;     padding-left: 10px;"><span class="fw-bold"  style="color: #05519E;">' . $durMonthYear . ' ' . ':</span> <span class="fw-bold" style="color:black;">' . ' ' . $Students_temps['durMonthYear'] . '</span></td>
					
					</tr>
						<tr>
							<td  class="col" style="width:600px; height:40px;     padding-left: 10px;"><span class="fw-bold"  style="color: #05519E;>School:</span> ' . '<span class="text-dark"> School : ' . ' </span><span style="color:black;" class="fw-bold">' . ' ' . $Students_temps['university_name'] . '</span></td>
							<td  class="col" style="width:400px; height:40px;     padding-left: 10px;"><span class="fw-bold " style="color: #05519E;"> Exam Session :</span><span class="text-dark fw-bold"> ' . ucwords(strtolower($exam_session)) . '</span></td>
						
							</tr>
					</tbody>
				</table>
			</div>
			
				   </div>';


    $html .= '<div class="table-box" style="width:94%">
				<table width="100%" style="border-collapse: collapse;border: 1px solid #8b8b8b;width: 100%;">
				<tr class="text-center border-bottom-0">
										<th scope="col" class="col blue" style="width: 10%;border-bottom: 1px solid #fff;border-right: 1px solid #8b8b8b;">Subject Code</th>
										<th scope="col" class="col blue" style="width: 28%;border-bottom: 1px solid #fff;">Subject Name</th>
										<th scope="col" colspan="2" class="col blue" style="width: 10%; border-left: 1px solid #8b8b8b;">Internal</th>
										<th scope="col" colspan="2" class="col blue" style="width: 10%; border-left: 1px solid #8b8b8b;">External</th>
										<th scope="col" colspan="2" class="col blue" style="width: 10%; border-left: 1px solid #8b8b8b;">Total</th>
									</tr>
									<tr class="border-top-0 text-center">
										<th scope="col" style="border-right: 1px solid #8b8b8b;"></th>
										<th scope="col" style="border-right: 1px solid #8b8b8b;"></th>
										<th scope="col" class="col border-top-1 blue" style="border-left: 1px solid #8b8b8b;    border-top: 1px solid #8b8b8b;">Obt</th>
										<th scope="col" class="col border-top-1 blue" style=" border-left: 1px solid #8b8b8b;   border-top: 1px solid #8b8b8b;">Max</th>
										<th scope="col" class="col border-top-1 blue" style="border-left: 1px solid #8b8b8b;    border-top: 1px solid #8b8b8b;">Obt</th>
										<th scope="col" class="col border-top-1 blue" style="  border-left: 1px solid #8b8b8b;   border-top: 1px solid #8b8b8b;">Max</th>
										<th scope="col" class="col border-top-1 blue" style=" border-left: 1px solid #8b8b8b;    border-top: 1px solid #8b8b8b;">Obt</th>
										<th scope="col" class="col border-top-1 blue" style="  border-left: 1px solid #8b8b8b;   border-top: 1px solid #8b8b8b;">Max</th>
									</tr> ';


    foreach ($Students_temps['marks'] as $temp_subject) {

        $html .= '<tr class="text-center" style="font-weight: 700;">                    
				<td style="padding: 6px;border-left: 1px solid #8b8b8b;border-top: 1px solid #8b8b8b;border-radius: 1px solid #8b8b8b;font-size: 14px;"  class="text-dark">' . $temp_subject['Code'] . '</td>
				<td class="text-left text-dark" style="padding: 6px;border-top: 1px solid #8b8b8b;border-left: 1px solid #8b8b8b; border-radius: 1px solid #8b8b8b; font-size: 14px;text-align:start !important;">' . $temp_subject['subject_name'] . '</td>
				<td style="padding: 6px;border-left: 1px solid #8b8b8b;border-top: 1px solid #8b8b8b;border-radius: 1px solid #8b8b8b;font-size: 14px;"  class="text-dark">' . $temp_subject['obt_marks_int'] . '</td>
				<td style="padding: 6px;border-left: 1px solid #8b8b8b;border-top: 1px solid #8b8b8b;border-radius: 1px solid #8b8b8b;font-size: 14px;" class="text-dark">' . $temp_subject['Min_Marks'] . '</td>
				<td style="padding: 6px;border-left: 1px solid #8b8b8b;border-top: 1px solid #8b8b8b;border-radius: 1px solid #8b8b8b;font-size: 14px;"  class="text-dark">' . $temp_subject['obt_marks_ext'] . '</td>
				<td style="padding: 6px;border-left: 1px solid #8b8b8b;border-top: 1px solid #8b8b8b;border-radius: 1px solid #8b8b8b;font-size: 14px;" class="text-dark">' . $temp_subject['Max_Marks'] . '</td>
				<td style="padding: 6px;border-left: 1px solid #8b8b8b;border-top: 1px solid #8b8b8b;border-radius: 1px solid #8b8b8b;font-size: 14px;"  class="text-dark">' . $temp_subject['obt_marks'] . '</td>
				<td style="padding: 6px;border-left: 1px solid #8b8b8b;border-top: 1px solid #8b8b8b;border-radius: 1px solid #8b8b8b;font-size: 14px;"  class="text-dark">' . ($temp_subject['Min_Marks'] + $temp_subject['Max_Marks']) . '</td>
			</tr>';
    }


    $html .= '</table></div>';

    $html .= '<p class="text-center mt-3 mb-3" style="text-align:center;font-size: 22px; font-weight: 900; color: #05519E;"> AGGREGATE MARKS </p>
				<div class="table-box-bottom" style="width:94%">
					<table class="text-center" style="border-collapse: collapse;border: 1px solid #8b8b8b;width: 100%;">

						<tr style="color: #05519E; font-weight: 700;">
							<th style="border: 1px solid #8b8b8b;">Marks</th>
							<th style="border: 1px solid #8b8b8b;">Grand Total</th>
							<th style="border: 1px solid #8b8b8b;">Result</th>
							<th style="border: 1px solid #8b8b8b;">Percentage</th>
						</tr>

						<tr>
							<th style="border: 1px solid #8b8b8b;"  class="text-dark">  Obtained Mark</th>
							<td style="border: 1px solid #8b8b8b;"  class="text-dark">' . $Students_temps['total_obt'] . '</td>
							<td rowspan="2" style="border: 1px solid #8b8b8b;"  class="text-dark">' . $passFail . '</td>
							<td rowspan="2" style="border: 1px solid #8b8b8b;"  class="text-dark">' . round($Students_temps['percentage'], 2) . '%</td>
						</tr>

						<tr>
							<th style="border: 1px solid #8b8b8b;"  class="text-dark">Maximum Mark</th>
							<td style="border: 1px solid #8b8b8b;"  class="text-dark">' . $Students_temps['total_max'] . '</td>
						</tr>

					</table>
				</div>
			<div class="des" style="width:92%">
				<p style="position: relative;font-size: 20px; top: 14px; right: 10px;color: #05519E;font-weight: 700;display: inline-block;"><span class="top-heading-u"></span>Disclaimer :</p>
				<p style="position: relative; top: 10px;color: #05519E;font-weight: 700;display: inline-block;"><span class="top-heading-u"></span>
					The published result is provisional only. Glocal University is not responsible for any inadvertent error that may have crept in the data / results being published online.
					This is being published just for the immediate information to the examinees. The final mark sheet(s) issued by Glocal University will only be treated authentic & final in this regard.

				</p></div>';
    $html .= '</div>
                </div>
            </div>
        </div>';

    // $html .= '<div class="text-center no-print mb-4 mt-3">
    //   <button type="button" class="btn btn-primary" id="cmd" onclick="printDiv(\'content\')">Download as PDF/Print</button>
    // </div>';

    echo $html;
}
?>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

<script>
    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }

    function toRoman(type) {
        var roman = ["st", "nd", "rd", "th", "th", "th", "th", "th"];
        $('.semsyear').text(roman[type - 1]);
    }
</script>