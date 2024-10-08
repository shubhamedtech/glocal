<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../../includes/db-config.php';
$sub_course_id = intval($_POST['sub_course_id']);
$semester = intval($_POST['semester']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$sql = "SELECT Syllabi.ID as subject_id, Syllabi.Name 
        FROM Syllabi 
        WHERE Paper_Type='Practical' AND Syllabi.Sub_Course_ID = ? AND Semester = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("ii", $sub_course_id, $semester);
if (!$stmt->execute()) {
    die("Error executing statement: " . htmlspecialchars($stmt->error));
}
$result = $stmt->get_result();
$html = '<option value="">Select</option>';
while ($row = $result->fetch_assoc()) {
    $html .= '<option value="' . htmlspecialchars($row['subject_id']) . '">' . htmlspecialchars($row['Name']) . '</option>';
}
echo $html;
$stmt->close();
$conn->close();
exit;
