<?php
session_start();
include '../../includes/db-config.php';
if (!isset($_SESSION['ID']) || !isset($_SESSION['Sub_Course_ID']) || !isset($_SESSION['Duration'])) {
    // die("Session variables not set.");
}
$mysyllabi = array();
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$orderby = "ORDER BY student_assignment.Assignment_id ASC";



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



$all_count = $conn->query("SELECT COUNT(student_assignment.Assignment_id) as allcount FROM student_assignment 
LEFT JOIN Syllabi ON Syllabi.ID = student_assignment.subject_id 
LEFT JOIN submitted_assignment ON student_assignment.Assignment_id = submitted_assignment.assignment_id AND submitted_assignment.student_id = '$student_id' 
LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id
where Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester = $current_sem AND student_assignment.subject_id");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];





$filter_count = $conn->query("SELECT COUNT(student_assignment.Assignment_id) as filtered FROM student_assignment 
LEFT JOIN Syllabi ON Syllabi.ID = student_assignment.subject_id 
LEFT JOIN submitted_assignment ON student_assignment.Assignment_id = submitted_assignment.assignment_id AND submitted_assignment.student_id = '$student_id' 
LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id 
WHERE Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester = $current_sem AND student_assignment.subject_id $searchQuery $query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];



// Fetch records
$result_record = "SELECT student_assignment.Assignment_id,
Syllabi.ID AS subject_id,
    Syllabi.id, 
    Syllabi.Code,
    Syllabi.Name,
    Syllabi.Sub_Course_ID,
    Syllabi.Course_ID,
    CASE WHEN submitted_assignment.id IS NULL THEN 'NOT SUBMITTED'
    WHEN submitted_assignment.id is not null and student_assignment_result.status='Rejected' AND submitted_assignment.reuploaded=1 then 'RESUBMITTED'
WHEN submitted_assignment.id IS NOT NULL AND student_assignment_result.status = 'Rejected' THEN 'NOT RESUBMITTED'
when submitted_assignment.id is not null then 'SUBMITTED'
ELSE 'RESUBMITTED' END AS assignment_submission_status,
    submitted_assignment.file_name AS file_name,
    student_assignment.assignment_name,
    student_assignment.file_path,
    student_assignment.start_date,
    student_assignment.end_date,
    student_assignment.marks,
    student_assignment.course_id AS acourse_id,
    student_assignment.sub_course_id AS a_sub_course_id, 
    student_assignment_result.obtained_mark,
    student_assignment_result.remark, 
    -- student_assignment_result.status
    IF(submitted_assignment.reuploaded=1, 'Pending', student_assignment_result.status) as status
FROM student_assignment 
LEFT JOIN Syllabi ON Syllabi.ID = student_assignment.subject_id 
LEFT JOIN submitted_assignment ON student_assignment.Assignment_id = submitted_assignment.assignment_id AND submitted_assignment.student_id = '$student_id'
LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id  
WHERE Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester = $current_sem AND student_assignment.subject_id
$searchQuery $query $orderby 
LIMIT $row, $rowperpage";

// print_r($result_record);
// die();



$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
    $today = date("Y-m-d");
    $uploadingIsActive = $row['start_date'] <= $today && $today <= $row['end_date'] ? 1 : 0;
    $data[] = array(
        "id" => $row["Assignment_id"],
        "Name" => $row["Name"],
        "Code" => $row["Code"],
        "assignment_name" => $row["assignment_name"],
        "start_date" => $row["start_date"],
        "end_date" => $row["end_date"],
        "marks" => $row["marks"],
        "obtained_mark" => $row["obtained_mark"],
        "remark" => $row["remark"],
        "status" => $row["status"],
        "assignment_submission_status" => $row["assignment_submission_status"],
        "file_path" => $row["file_path"],
        "file_name" => $row["file_name"],
        "subject_id" => $row["subject_id"],
        "uploadingIsActive" => $uploadingIsActive,
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
