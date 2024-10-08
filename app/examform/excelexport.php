<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
if (isset($_SESSION['current_session'])) {
    if ($_SESSION['current_session'] == 'All') {
        $session_query = '';
    } else {
        $session_query = "AND Admission_Sessions.Name like '%" . $_SESSION['current_session'] . "%'";
    }
} else {
    $get_current_session = $conn->query("SELECT Name FROM Admission_Sessions WHERE Current_Status = 1 AND University_ID = '" . $_SESSION['university_id'] . "'");
    if ($get_current_session->num_rows > 0) {
        $gsc = mysqli_fetch_assoc($get_current_session);
        $session_query = "AND Admission_Sessions.Name like '%" . $gsc['Name'] . "%'";
    } else {
        $session_query = '';
    }
}
// Admin Query

$searchValue = "";
## Search 
$searchQuery = "";
if ($searchValue != '') {
    $searchQuery = " AND (Students.Enrollment_No like '%" . $searchValue . "%' OR Students.First_Name like '%" . $searchValue . "%' OR Syllabi.Name like '%" . $searchValue . "%' OR Sub_Courses.Name like '%" . $searchValue . "%' OR Sub_Courses.Short_Name like '%" . $searchValue . "%')";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(Students.id) as allcount FROM Students 
  LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Syllabi ON  Sub_Courses.ID = Syllabi.Sub_Course_ID
    LEFT JOIN Users ON Students.Added_For = Users.ID
   LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID
        LEFT JOIN Courses ON Students.Course_ID = Courses.ID
            LEFT JOIN Universities ON Students.University_ID = Universities.ID
                LEFT JOIN Examination_Confirmation ON Examination_Confirmation.Student_ID = Students.ID WHERE Examination_Confirmation.Confirmation_Status=1 
    $searchQuery  $session_query ");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];


## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Students.id) as filtered FROM Students 
     LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Syllabi ON  Sub_Courses.ID = Syllabi.Sub_Course_ID
    LEFT JOIN Users ON Students.Added_For = Users.ID
         LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID
        LEFT JOIN Courses ON Students.Course_ID = Courses.ID
            LEFT JOIN Universities ON Students.University_ID = Universities.ID
                LEFT JOIN Examination_Confirmation ON Examination_Confirmation.Student_ID = Students.ID WHERE Examination_Confirmation.Confirmation_Status=1 
     $searchQuery $session_query ");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];



$result_record = "SELECT CONCAT_WS(' ', Students.First_Name, Students.Middle_Name, Students.Last_Name) AS student_name,
    Students.Enrollment_No,
    Sub_Courses.Name as sub_course_name,
    Syllabi.Semester AS semester,
    Users.Name AS Center_SubCeter,
    Students.Unique_ID as uniqueid,
    Admission_Sessions.Name as adm,
    Universities.Name as universityname,
    Courses.Name as coursename
FROM Students
    LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Syllabi ON Sub_Courses.ID = Syllabi.Sub_Course_ID 
    LEFT JOIN Users ON Students.Added_For = Users.ID
    LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID
    LEFT JOIN Courses ON Students.Course_ID = Courses.ID
    LEFT JOIN Universities ON Students.University_ID = Universities.ID 
    LEFT JOIN Examination_Confirmation ON Examination_Confirmation.Student_ID = Students.ID 
WHERE Examination_Confirmation.Confirmation_Status = 1 
   $session_query $searchQuery 
GROUP BY Students.ID";
$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
    $data[] = array(
        "student_name" => $row["student_name"],
        "enrollment_no" => $row["Enrollment_No"],
        "sub_course_name" => $row["sub_course_name"],
        "semester" => $row["semester"],
        "coursename" => $row["coursename"],
        "Center_SubCeter" => $row["Center_SubCeter"],
        "adm" => $row["adm"],
        "uniqueid" => $row["uniqueid"],
        "universityname" => $row["universityname"],
    );
}
if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'csv') {
    if (count($data) > 0) {
        $filename = "examform" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        $f = fopen('php://output', 'w');
        $headers = array('Student Name', 'Enrollment No', 'Sub Course Name', 'Semester', 'Course Name', 'Center Sub Center', 'Admission Session', 'Unique ID', 'University Name');
        fputcsv($f, $headers);
        foreach ($data as $row) {
            fputcsv($f, $row);
        }
        fclose($f);
        exit();
    } else {
        echo "No data available to download.";
        header('Location: /../settings/examformsubmitted');
        exit();
    }
}
