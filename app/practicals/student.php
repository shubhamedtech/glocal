<?php
ini_set('display_errors', 1);
session_start();
include '../../includes/db-config.php';
if (!isset($_SESSION['ID']) || !isset($_SESSION['Sub_Course_ID']) || !isset($_SESSION['Duration'])) {
    // die("Session variables not set.");
}
$mysyllabi = array();
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$orderby = "ORDER BY Student_Practical.id ASC";

if (isset($_POST['order'])) {
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $orderby = "ORDER BY $columnName $columnSortOrder";
}
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
$searchQuery = "";
if ($searchValue != '') {
    $searchQuery = " AND (Syllabi.Name LIKE '%$searchValue%' OR Syllabi.Code LIKE '%$searchValue%')";
}
$course_id = $_SESSION['Sub_Course_ID'];
$student_id = $_SESSION['ID'];
$current_sem = $_SESSION['Duration'];
$query = "";
$all_count = $conn->query("SELECT COUNT(Student_Practical.id) as allcount FROM Student_Practical 
LEFT JOIN Syllabi ON Syllabi.ID = Student_Practical.subject_id 
LEFT JOIN Submitted_Practical ON Student_Practical.id = Submitted_Practical.practical_id AND Submitted_Practical.student_id = $student_id
LEFT JOIN Student_Practical_Result ON Submitted_Practical.id = Student_Practical_Result.practical_id where Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester = $current_sem $searchQuery $query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

$filter_count = $conn->query("SELECT COUNT(Student_Practical.id) as filtered FROM Student_Practical
LEFT JOIN Syllabi ON Syllabi.ID = Student_Practical.subject_id 
LEFT JOIN Submitted_Practical ON Student_Practical.id = Submitted_Practical.practical_id AND Submitted_Practical.student_id = $student_id
LEFT JOIN Student_Practical_Result ON Submitted_Practical.id = Student_Practical_Result.practical_id WHERE Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester = $current_sem $searchQuery $query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

// Fetch records
$result_record = "SELECT Student_Practical.id AS practical_id,
    Syllabi.ID AS subject_id,
    Syllabi.id, 
    Syllabi.Code,
    Syllabi.Name,
    Syllabi.Sub_Course_ID,
    Syllabi.Course_ID,
    CASE 
    WHEN Submitted_Practical.id IS NULL THEN 'NOT SUBMITTED'
    ELSE 'SUBMITTED'
    END AS practical_submission_status,
    Submitted_Practical.student_practical_file AS student_practical_file,
    Student_Practical.practical_name,
    Student_Practical.practical_file,
    Student_Practical.start_date,
    Student_Practical.end_date,
    Student_Practical.marks,
    Student_Practical.course_id AS acourse_id,
    Student_Practical.sub_course_id AS a_sub_course_id, 
    Student_Practical_Result.obtained_mark,
    Student_Practical_Result.remark, 
    Student_Practical_Result.status
FROM Student_Practical 
LEFT JOIN Syllabi ON Syllabi.ID = Student_Practical.subject_id 
LEFT JOIN Submitted_Practical ON Student_Practical.id = Submitted_Practical.practical_id AND Submitted_Practical.student_id = $student_id
LEFT JOIN Student_Practical_Result ON Submitted_Practical.id = Student_Practical_Result.practical_id WHERE Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester = $current_sem AND Student_Practical.subject_id
$searchQuery 
$query 
$orderby 
LIMIT 
    $row, $rowperpage";
$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
    $data[] = array(
        "id" => $row["practical_id"],
        "Name" => $row["Name"],
        "Code" => $row["Code"],
        "practical_name" => $row["practical_name"],
        "start_date" => $row["start_date"],
        "end_date" => $row["end_date"],
        "marks" => $row["marks"],
        "obtained_mark" => $row["obtained_mark"],
        "remark" => $row["remark"],
        "status" => $row["status"],
        "practical_submission_status" => $row["practical_submission_status"],
        "practical_file" => $row["practical_file"],
        "student_practical_file" => $row["student_practical_file"],
        "subject_id" => $row["subject_id"],
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
