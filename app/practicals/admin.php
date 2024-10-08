<?php
ini_set('display_errors', 1);
// Database configuration
include '../../includes/db-config.php';

// Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];

$columnIndex = isset($_POST['order']) ? $_POST['order'][0]['column'] : null;
$columnName = $columnIndex !== null ? $_POST['columns'][$columnIndex]['data'] : null;
$columnSortOrder = isset($_POST['order']) ? $_POST['order'][0]['dir'] : null;

$searchValue = $_POST['search']['value'];
$searchValueEscaped = mysqli_real_escape_string($conn, $searchValue);

$orderby = isset($columnSortOrder) ? "ORDER BY $columnName $columnSortOrder" : "ORDER BY Student_Practical.id ASC";

// Search query
$searchQuery = "";
if ($searchValue != '') {
    $searchQuery = " AND (Syllabi.Name LIKE '%$searchValueEscaped%' OR Sub_Courses.Name LIKE '%$searchValueEscaped%' OR Courses.Name LIKE '%$searchValueEscaped%')";
}

// Total number of records without filtering
$totalRecordsQuery = "SELECT COUNT(Student_Practical.id) as allcount 
    FROM Student_Practical
    LEFT JOIN Courses ON Student_Practical.course_id = Courses.ID
    LEFT JOIN Sub_Courses ON Student_Practical.sub_course_id = Sub_Courses.ID
    LEFT JOIN Syllabi ON Student_Practical.subject_id = Syllabi.ID 
    WHERE Student_Practical.id != 2
";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult ? mysqli_fetch_assoc($totalRecordsResult)['allcount'] : 0;

// Total number of records with filtering
$filterRecordsQuery = "SELECT COUNT(Student_Practical.id) as filtered 
    FROM Student_Practical 
    LEFT JOIN Courses ON Student_Practical.course_id = Courses.ID
    LEFT JOIN Sub_Courses ON Student_Practical.sub_course_id = Sub_Courses.ID
    LEFT JOIN Syllabi ON Student_Practical.subject_id = Syllabi.ID 
    WHERE Student_Practical.id != 2 $searchQuery
";
$filterRecordsResult = $conn->query($filterRecordsQuery);
$totalRecordwithFilter = $filterRecordsResult ? mysqli_fetch_assoc($filterRecordsResult)['filtered'] : 0;

// Fetch records
$fetchRecordsQuery = "SELECT 
        Courses.Name AS course_name, 
        Sub_Courses.Name AS sub_course_name, 
        Syllabi.Name AS subject_name,
        Student_Practical.semester,
        Student_Practical.practical_name,
        Student_Practical.start_date,
        Student_Practical.end_date,
        Student_Practical.created_by,
        Student_Practical.marks,
        Student_Practical.updated_date,
        Student_Practical.created_date,
        Student_Practical.practical_file,
        Student_Practical.id 
    FROM 
        Student_Practical
    LEFT JOIN Courses ON Student_Practical.course_id = Courses.ID
    LEFT JOIN Sub_Courses ON Student_Practical.sub_course_id = Sub_Courses.ID
    LEFT JOIN Syllabi ON Student_Practical.subject_id = Syllabi.ID 
    WHERE Student_Practical.id != 2 $searchQuery $orderby 
    LIMIT ?, ?";
$stmt = $conn->prepare($fetchRecordsQuery);
$stmt->bind_param("ii", $row, $rowperpage);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = array(
        "course_name" => $row["course_name"],
        "sub_course_name" => $row["sub_course_name"],
        "subject_name" => $row["subject_name"],
        "semester" => $row["semester"],
        "practical_name" => $row["practical_name"],
        "start_date" => $row["start_date"],
        "end_date" => $row["end_date"],
        "created_by" => $row["created_by"],
        "marks" => $row["marks"],
        "updated_date" => $row["updated_date"],
        "created_date" => $row["created_date"],
        "practical_file" => $row["practical_file"],
        "id" => $row["id"],
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
