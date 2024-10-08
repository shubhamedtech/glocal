<?php
ini_set('display_errors', 1);
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

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(Students.id) as allcount FROM Students 
LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID 
LEFT JOIN Submitted_Practical ON Students.ID = Submitted_Practical.student_id 
LEFT JOIN Syllabi ON Syllabi.Sub_Course_ID = Sub_Courses.ID 
LEFT JOIN Universities ON Students.University_ID = Universities.ID
LEFT JOIN Student_Practical_Result ON Submitted_Practical.id = Student_Practical_Result.practical_id
LEFT JOIN Student_Practical ON Students.ID = Student_Practical.id
WHERE Paper_Type = 'Practical' AND Students.status != 2 $query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Students.id) as filtered FROM Students 
    LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID 
    LEFT JOIN Submitted_Practical ON Students.ID = Submitted_Practical.student_id 
    LEFT JOIN Syllabi ON Syllabi.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Universities ON Students.University_ID = Universities.ID
    LEFT JOIN Student_Practical_Result ON Submitted_Practical.id = Student_Practical_Result.practical_id
    LEFT JOIN Student_Practical ON Students.ID = Student_Practical.id
    WHERE Paper_Type = 'Practical' AND Students.status != 2 $searchQuery $query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records
$result_record = "SELECT Students.`ID` as student_id, CONCAT_WS(' ', Students.First_Name, Students.Middle_Name, Students.Last_Name) AS student_name,
    Students.Enrollment_No, Sub_Courses.`Name` as sub_course_name, Sub_Courses.`Short_Name` as sub_course_short_name, Student_Practical_Result.id as resultId,
    Syllabi.`Name` as subject_name, Syllabi.ID as subject_id, Student_Practical.id as practical_id, Syllabi.`Code` as subject_code, Syllabi.Semester AS semester,Submitted_Practical.created_date,Submitted_Practical.student_practical_file,COALESCE(Submitted_Practical.uploaded_type,Student_Practical_Result.uploaded_type,'NULL') AS uploaded_type,Submitted_Practical.id,Student_Practical_Result.obtained_mark,Student_Practical_Result.remark,
    CASE WHEN Student_Practical_Result.id IS NOT NULL THEN Student_Practical_Result.status ELSE 'NOT EVALUATED' END AS eva_status,
    CASE WHEN Universities.ID = 47 THEN 'BVOC PROGRAM' WHEN Universities.ID = 48 THEN 'SKILL PROGRAM' ELSE 'Unknown University' END AS universityname,
    CASE WHEN Student_Practical.id IS NULL THEN 'NOT CREATED' ELSE 'CREATED' END AS practical_status,
    CASE WHEN Submitted_Practical.id IS NULL THEN 'NOT SUBMITTED' ELSE 'SUBMITTED' END AS student_status, 
    Students.`status`
    FROM Students
    LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID
    LEFT JOIN Syllabi ON Syllabi.Sub_Course_ID = Sub_Courses.ID
    LEFT JOIN Student_Practical ON Syllabi.ID = Student_Practical.subject_id
    LEFT JOIN Submitted_Practical ON Students.ID = Submitted_Practical.student_id AND Syllabi.ID = Student_Practical.subject_id AND Student_Practical.id = Submitted_Practical.practical_id
    LEFT JOIN Student_Practical_Result ON Submitted_Practical.id = Student_Practical_Result.practical_id
    LEFT JOIN Universities ON Students.University_ID = Universities.ID
    WHERE Paper_Type = 'Practical' AND Students.status != 2 $searchQuery $query $orderby
    LIMIT " . $row . "," . $rowperpage;
$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
    $data[] = array(
        "student_name" => $row["student_name"],
        "enrollment_no" => $row["Enrollment_No"],
        "universityname" => $row["universityname"],
        "sub_course_name" => $row["sub_course_name"],
        "subject_name" => $row["subject_name"],
        "subject_code" => $row["subject_code"],
        "status" => $row["status"],
        "semester" => $row["semester"],
        "obtained_mark" => $row["obtained_mark"],
        "remark" => $row["remark"],
        "created_date" => $row["created_date"],
        "student_status" => $row["student_status"],
        "practical_status" => $row["practical_status"],
        "uploaded_type" => $row["uploaded_type"],
        "student_practical_file" => $row["student_practical_file"],
        "eva_status" => $row["eva_status"],
        "practical_id" => $row["practical_id"],
        "student_id" => $row["student_id"],
        "subject_id" => $row["subject_id"],
        "id" => $row["id"],
        "resultId" => $row['resultId']
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
$conn->close();
