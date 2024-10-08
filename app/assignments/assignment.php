<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
$course_id = $_SESSION['Sub_Course_ID'];
$student_id = $_SESSION['ID'];
$current_sem = $_SESSION['Duration'];

$Syllabi = "SELECT Sub_Courses.ID,Sub_Courses.Mode_ID,Sub_Courses.Min_Duration, Modes.Name as mode ,Syllabi.Name,Syllabi.ID as subject_id from Syllabi  LEFT JOIN Sub_Courses ON Syllabi.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Modes ON Sub_Courses.Mode_ID = Modes.ID  WHERE Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester=$current_sem";
$Syllabi = mysqli_query($conn, $Syllabi);
$mysyllabi = array();
$subjectData = array();
while ($row = mysqli_fetch_assoc($Syllabi)) {
  $mysyllabi[] = $row['subject_id'];
  $subjectData[] = $row;
}

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
  $orderby = "ORDER BY e_books.id ASC";
}




// Admin Query
$query = "";
## Search 
$searchQuery = " ";
if ($searchValue != '') {
  $searchQuery = " AND (Syllabi.Name like '%" . $searchValue . "%' OR Sub_Courses.Name like '%" . $searchValue . "%' OR Sub_Courses.Short_Name like '%" . $searchValue . "%')";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(id) as allcount FROM Student_Assignment  $query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Student_Assignment.Assignment_id) as filtered FROM Student_Assignment LEFT JOIN Sub_Courses ON Sub_Courses.ID = Student_Assignment.sub_course_id LEFT JOIN Syllabi ON Syllabi.ID = Student_Assignment.subject_id  $searchQuery $query"); //WHERE e_books.status!=2
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records
$result_record = "SELECT Student_Assignment.`Assignment_id`, Student_Assignment.assignment_name,Student_Assignment.start_date,Student_Assignment.end_date,Student_Assignment.marks, Student_Assignment.`file_path`, Sub_Courses.`Name` as course_name, Sub_Courses.`Short_Name` as course_short_name, Syllabi.`Name` as subject_name, Syllabi.Code  FROM Student_Assignment LEFT JOIN Sub_Courses ON Sub_Courses.ID = Student_Assignment.sub_course_id LEFT JOIN Syllabi ON Syllabi.ID = Student_Assignment.subject_id WHERE Student_Assignment.subject_id IN ('" . implode("','", $mysyllabi) . "')   $searchQuery $query $orderby LIMIT " . $row . "," . $rowperpage; //e_books.status !=2

print_r($result_record);
die;
$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
  $data[] = array(
    "course_name" => $row["course_name"],
    "subject_code" => $row["Code"],
    "subject_name" => $row["subject_name"],
    "assignment_name" => $row["assignment_name"],
    "start_date" => $row["start_date"],
    "end_date" => $row["end_date"],
    "marks" => $row["marks"],
    "status" => 1,
    "ID" => $row["Assignment_id"],
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
