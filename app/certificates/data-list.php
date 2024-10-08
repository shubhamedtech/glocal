<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
if(isset($_POST['order'])){
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}
$searchValue = mysqli_real_escape_string($conn,$_POST['search']['value']); // Search value

if(isset($columnSortOrder)){
  $orderby = "ORDER BY $columnName $columnSortOrder";
}else{
  $orderby = "ORDER BY certificates.id ASC";
}




// Admin Query
$query = "";
//$query = " AND Sub_Courses.University_ID = ".$_SESSION['university_id'];

## Search 
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " AND (Students.First_Name like '%".$searchValue."%' OR Students.Middle_Name like '%".$searchValue."%' OR Students.Last_Name like '%".$searchValue."%' OR Students.Enrollment_No like '%".$searchValue."%' OR Sub_Courses.Name like '%".$searchValue."%' OR Sub_Courses.Short_Name like '%".$searchValue."%')";
}

## Total number of records without filtering
$all_count=$conn->query("SELECT COUNT(id) as allcount FROM certificates WHERE certificates.status!=2 $query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering  //Students
$filter_count = $conn->query("SELECT COUNT(certificates.id) as filtered FROM certificates LEFT JOIN Students ON Students.ID = certificates.student_id LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID  WHERE certificates.status !=2 $searchQuery $query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records
$result_record = "SELECT certificates.`id`, certificates.file_path, certificates.`file_type`,Students.First_Name,Students.Middle_Name,Students.Last_Name,Students.Enrollment_No, Sub_Courses.`Name` as course_name, Sub_Courses.`Short_Name` as course_short_name, certificates.`status`,certificates.created_at as issue_date FROM certificates LEFT JOIN Students ON Students.ID = certificates.student_id LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID  WHERE certificates.status !=2  $searchQuery $query $orderby LIMIT ".$row.",".$rowperpage;


$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {

    $data[] = array( 
        "student_name" => $row["First_Name"]." ".$row["Middle_Name"]." ".$row["Last_Name"],
        "enrollment_no" => $row["Enrollment_No"],
        "course_name" => $row["course_name"],
        "issue_date" => date("d-m-Y",strtotime($row["issue_date"])),
        //"file_type" => $row["file_type"],
        "file_path"=>$row["file_path"],
        "status" => $row["status"],
        "ID" => $row["id"],
        
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
