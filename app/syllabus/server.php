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
  $orderby = "ORDER BY Sub_Courses.ID ASC";
}
$query ='';
// Admin Query
// $query = " AND Sub_Courses.University_ID = ".$_SESSION['university_id'];

## Search 
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " AND (Sub_Courses.Name like '%".$searchValue."%' OR Sub_Courses.Short_Name like '%".$searchValue."%' OR Universities.Short_Name LIKE '%".$searchValue."%' OR Universities.Vertical  like '%".$searchValue."%' OR Modes.Name  like '%".$searchValue."%' OR Schemes.Name  like '%".$searchValue."%' OR Courses.Short_Name  like '%".$searchValue."%' OR Courses.Name  like '%".$searchValue."%' OR CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') like '%".$searchValue."%')";
}

## Total number of records without filtering
$all_count=$conn->query("SELECT COUNT(Chapter.ID) as allcount FROM Chapter LEFT JOIN Sub_Courses ON Chapter.Sub_Course_ID = Sub_Courses.ID  WHERE 1=1 $query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Chapter.ID) as filtered FROM Chapter LEFT JOIN Syllabi ON Chapter.Subject_ID = Syllabi.ID LEFT JOIN Universities ON Chapter.University_ID = Universities.ID LEFT JOIN Sub_Courses ON Chapter.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Sub_Courses.ID IS NOT NULL $query $searchQuery ");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records

 $result_record = "SELECT Chapter.Sub_Course_ID,Chapter.University_ID,Chapter.Subject_ID, Syllabi.Semester,Syllabi.Code, Sub_Courses.`Name`, Courses.Short_Name ,Chapter.Semester as Semester , Syllabi.Name as subject_name, Universities.Name as uni_name, Chapter.Status FROM Chapter LEFT JOIN Syllabi ON Chapter.Subject_ID = Syllabi.ID LEFT JOIN Universities ON Chapter.University_ID = Universities.ID LEFT JOIN Sub_Courses ON Chapter.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE 1= 1 $query $searchQuery  GROUP BY Chapter.Subject_ID $orderby LIMIT ".$row.",".$rowperpage;
$results = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($results)) {

    $data[] = array( 
      // "Name" => ucwords(strtolower($row["Name"].'('.$row['Short_Name'].')')),
      "Name" => ucwords(strtolower($row["Name"])) . ' (' . $row['Short_Name'] . ')',
      "Duration" => $row["Semester"],
      "Subject_Name" => $row["subject_name"].'('.$row['Code'].')',
      "uni_name" => $row["uni_name"],
      "Status"  => $row["Status"],
      "Subject_ID" => $row["Subject_ID"],
      "Sub_Course_ID" => $row["Sub_Course_ID"],
      "uni_id" => $row["University_ID"],


          
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
