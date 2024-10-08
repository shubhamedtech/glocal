<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
if (isset($_POST['order'])) {
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
}
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
if (isset($columnSortOrder)) {
    $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Students.id ASC";
}




// Admin Query
$query = "";
## Search 
$searchQuery = "";
if ($searchValue != '') {
    $searchQuery = " AND (Students.Enrollment_No like '%" . $searchValue . "%' OR Students.First_Name like '%" . $searchValue . "%' OR Syllabi.Name like '%" . $searchValue . "%' OR Sub_Courses.Name like '%" . $searchValue . "%' OR Sub_Courses.Short_Name like '%" . $searchValue . "%')";
}





// Prepare filters from session
$filters = [
    'filterByDepartment' => isset($_SESSION['filterByDepartment']) ? $_SESSION['filterByDepartment'] : '',
    'filterBySubCourses' => isset($_SESSION['filterBySubCourses']) ? $_SESSION['filterBySubCourses'] : '',
    'filterByUser' => isset($_SESSION['filterByUser']) ? $_SESSION['filterByUser'] : '',
    'filterBySubjectdata' => isset($_SESSION['filterBySubjectdata']) ? $_SESSION['filterBySubjectdata'] : '',
    'filterByVerticalType' => isset($_SESSION['filterByVerticalType']) ? $_SESSION['filterByVerticalType'] : '',
    'filterBysubmitted_students' => isset($_SESSION['filterBysubmitted_students']) ? $_SESSION['filterBysubmitted_students'] : '',
    'filterBySemesterdata' => isset($_SESSION['filterBySemesterdata']) ? $_SESSION['filterBySemesterdata'] : ''
];
// Concatenate filters
$pro_filter = implode(' ', $filters);






## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(Students.id) as allcount FROM Students 
  LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Syllabi ON  Sub_Courses.ID = Syllabi.Sub_Course_ID
    LEFT JOIN Users ON Students.Added_For = Users.ID
    LEFT JOIN student_assignment ON Syllabi.ID = student_assignment.subject_id
    LEFT JOIN submitted_assignment ON Students.ID = submitted_assignment.student_id AND Syllabi.ID = submitted_assignment.subject_id
    LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id
    LEFT JOIN Universities ON Students.University_ID = Universities.ID
WHERE student_assignment.Assignment_id IS NOT NULL $pro_filter $query ");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];







## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Students.id) as filtered FROM Students 
     LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Syllabi ON  Sub_Courses.ID = Syllabi.Sub_Course_ID
    LEFT JOIN Users ON Students.Added_For = Users.ID
    LEFT JOIN student_assignment ON Syllabi.ID = student_assignment.subject_id
    LEFT JOIN submitted_assignment ON Students.ID = submitted_assignment.student_id AND Syllabi.ID = submitted_assignment.subject_id
    LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id
    LEFT JOIN Universities ON Students.University_ID = Universities.ID
    WHERE student_assignment.Assignment_id IS NOT NULL $pro_filter $searchQuery $query ");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];





## Fetch records
$result_record = "SELECT CASE WHEN Universities.ID = 47 THEN 'BVOC PROGRAM' WHEN Universities.ID = 48 THEN 'SKILL PROGRAM' ELSE 'Unknown University' END AS universityname,
CASE WHEN Users.Vertical_type = 1 THEN 'Edtech' WHEN Users.Vertical_type = 0 THEN 'IITS LLP Paramedical' END as verticaltypes,
Students.`ID` as student_id, CONCAT_WS(' ', Students.First_Name, Students.Middle_Name, Students.Last_Name) AS student_name,
    Students.Enrollment_No, Sub_Courses.`Name` as sub_course_name, Sub_Courses.`Short_Name` as sub_course_short_name,
    Syllabi.`Name` as subject_name, Syllabi.ID as subject_id, student_assignment.Assignment_id as assignment_id, Syllabi.`Code` as subject_code, Syllabi.Semester AS semester,submitted_assignment.created_date,submitted_assignment.file_name,COALESCE(submitted_assignment.uploaded_type,student_assignment_result.uploaded_type,'NULL') AS uploaded_type,submitted_assignment.id,student_assignment_result.obtained_mark,COALESCE(student_assignment_result.status,'NOT EVALUATED') AS eva_status,student_assignment_result.remark,
student_assignment.marks as total_mark,
Users.Name AS Center_SubCeter,
Users.Code as Center_code,
Users.Short_Name as Center_Short_Name,
Students.DOB as dateofbirth,
Students.Unique_ID as uniqueid,
Universities.Name as Universityname,
CASE WHEN student_assignment.Assignment_id IS NULL THEN 'NOT CREATED' ELSE 'CREATED' END AS assignment_status,
CASE WHEN submitted_assignment.id IS NULL THEN 'NOT SUBMITTED'
WHEN submitted_assignment.id IS NOT NULL AND student_assignment_result.status = 'Rejected' AND submitted_assignment.reuploaded = 1 THEN 'RESUBMITTED'
WHEN submitted_assignment.id IS NOT NULL AND student_assignment_result.status = 'Rejected' THEN 'NOT RESUBMITTED'
WHEN submitted_assignment.id IS NOT NULL THEN 'SUBMITTED'
ELSE 'UNKNOWN STATUS' END AS student_status,
 Students.`status` FROM Students
    LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Syllabi ON  Sub_Courses.ID = Syllabi.Sub_Course_ID 
    LEFT JOIN Users ON Students.Added_For = Users.ID
    LEFT JOIN student_assignment ON Syllabi.ID = student_assignment.subject_id
    LEFT JOIN submitted_assignment ON Students.ID = submitted_assignment.student_id AND Syllabi.ID = submitted_assignment.subject_id
    LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id
    LEFT JOIN Universities ON Students.University_ID = Universities.ID
    WHERE student_assignment.Assignment_id IS NOT NULL $pro_filter $searchQuery $query $orderby 
    LIMIT " . $row . "," . $rowperpage;
$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
    $data[] = array(
        "student_name" => $row["student_name"],
        "enrollment_no" => $row["Enrollment_No"],
        "universityname" => $row["universityname"],
        "Universityname" => $row["Universityname"],
        "sub_course_name" => $row["sub_course_name"],
        "subject_name" => $row["subject_name"],
        "subject_code" => $row["subject_code"],
        "status" => $row["status"],
        "semester" => $row["semester"],
        "obtained_mark" => $row["obtained_mark"],
        "remark" => $row["remark"],
        "created_date" => $row["created_date"],
        "student_status" => $row["student_status"],
        "assignment_status" => $row["assignment_status"],
        "uploaded_type" => $row["uploaded_type"],
        "file_name" => $row["file_name"],
        "eva_status" => $row["eva_status"],
        "total_mark" => $row["total_mark"],
        "id" => $row["id"],
        "student_id" => $row["student_id"],
        "Center_code" => $row["Center_code"],
        "dateofbirth" => $row["dateofbirth"],
        "subject_id" => $row["subject_id"],
        "Center_SubCeter" => $row["Center_SubCeter"],
        "uniqueid" => $row["uniqueid"],
        "assignment_id" => $row["assignment_id"],
        "verticaltypes" => $row["verticaltypes"],
        "Center_Short_Name" => $row["Center_Short_Name"]

    );
}
## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);
echo json_encode($response);
