<?php
// Database configuration
include '../../includes/db-config.php';
session_start();

// Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);

// Sorting
if (isset($_POST['order'])) {
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Students.id ASC";
}

// Session filtering
$session_query = '';
if (isset($_SESSION['current_session'])) {
    if ($_SESSION['current_session'] != 'All') {
        $session_query = "AND Admission_Sessions.Name LIKE '%" . $_SESSION['current_session'] . "%'";
    }
} else {
    $get_current_session = $conn->query("SELECT Name FROM Admission_Sessions WHERE Current_Status = 1 AND University_ID = '" . $_SESSION['university_id'] . "'");
    if ($get_current_session->num_rows > 0) {
        $gsc = mysqli_fetch_assoc($get_current_session);
        $session_query = "AND Admission_Sessions.Name LIKE '%" . $gsc['Name'] . "%'";
    }
}

// Search query
$searchQuery = '';
if ($searchValue != '') {
    $searchQuery = "AND (Students.Enrollment_No LIKE '%$searchValue%' 
                        OR Students.First_Name LIKE '%$searchValue%' 
                        OR Syllabi.Name LIKE '%$searchValue%' 
                        OR Sub_Courses.Name LIKE '%$searchValue%' 
                        OR Sub_Courses.Short_Name LIKE '%$searchValue%')";
}

// Total number of records without filtering
$all_count_query = "SELECT count(DISTINCT(Students.ID)) AS allcount
                 FROM Students
                 LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
                 LEFT JOIN Syllabi ON Sub_Courses.ID = Syllabi.Sub_Course_ID 
                 LEFT JOIN Users ON Students.Added_For = Users.ID
                 LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID
                 LEFT JOIN Courses ON Students.Course_ID = Courses.ID
                 LEFT JOIN Universities ON Students.University_ID = Universities.ID 
                 LEFT JOIN Examination_Confirmation ON Examination_Confirmation.Student_ID = Students.ID 
                 WHERE Examination_Confirmation.Confirmation_Status = 1 $session_query";
$all_count_result = $conn->query($all_count_query);
$all_count_row = mysqli_fetch_assoc($all_count_result);
$totalRecords = $all_count_row['allcount'];

// Total number of records with filtering
$filter_count_query = "SELECT count(DISTINCT(Students.ID)) AS filtered
                 FROM Students
                 LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
                 LEFT JOIN Syllabi ON Sub_Courses.ID = Syllabi.Sub_Course_ID 
                 LEFT JOIN Users ON Students.Added_For = Users.ID
                 LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID
                 LEFT JOIN Courses ON Students.Course_ID = Courses.ID
                 LEFT JOIN Universities ON Students.University_ID = Universities.ID 
                 LEFT JOIN Examination_Confirmation ON Examination_Confirmation.Student_ID = Students.ID 
                 WHERE Examination_Confirmation.Confirmation_Status = 1 
                       $searchQuery $session_query";
$filter_count_result = $conn->query($filter_count_query);
$filter_count_row = mysqli_fetch_assoc($filter_count_result);
$totalRecordwithFilter = $filter_count_row['filtered'];

// Fetch records
$result_query = "SELECT CONCAT_WS(' ', Students.First_Name, Students.Middle_Name, Students.Last_Name) AS student_name,
                        Students.Enrollment_No,
                        Sub_Courses.Name AS sub_course_name,
                        Syllabi.Semester AS semester,
                        Users.Name AS Center_SubCeter,
                        Students.Unique_ID AS uniqueid,
                        Admission_Sessions.Name AS adm,
                        Universities.Name AS universityname,
                        Courses.Name AS coursename
                 FROM Students
                 LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
                 LEFT JOIN Syllabi ON Sub_Courses.ID = Syllabi.Sub_Course_ID 
                 LEFT JOIN Users ON Students.Added_For = Users.ID
                 LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID
                 LEFT JOIN Courses ON Students.Course_ID = Courses.ID
                 LEFT JOIN Universities ON Students.University_ID = Universities.ID 
                 LEFT JOIN Examination_Confirmation ON Examination_Confirmation.Student_ID = Students.ID 
                 WHERE Examination_Confirmation.Confirmation_Status = 1
                 $session_query GROUP BY Students.ID $searchQuery
               $orderby
                 LIMIT $row, $rowperpage";

$result = mysqli_query($conn, $result_query);
$data = array();

while ($row = mysqli_fetch_assoc($result)) {
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

// Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);