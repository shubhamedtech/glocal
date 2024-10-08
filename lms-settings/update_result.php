<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "updated_glocal";
// Create connection using mysqli
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $assignment_id = $_POST['id'];
    $marks = $_POST['marks'];
    $reason = $_POST['reason'];
    $uploaded_type = $_POST['uploaded_type'];
    $status = $_POST['status'];
    // $teacher_updated_assignment = null;
    
    // if (isset($_FILES['teacher_updated_assignment']) && $_FILES['teacher_updated_assignment']['error'] === UPLOAD_ERR_OK) {
    //     $tempFile = $_FILES['teacher_updated_assignment']['tmp_name'];
    //     $uploadedFileName = uniqid() . '_' . $_FILES['teacher_updated_assignment']['name'];
    //     $uploadDirectory = 'uploads/student_assignment/';
    //     if (!is_dir($uploadDirectory)) {
    //         mkdir($uploadDirectory, 0777, true);
    //     }
    //     $targetFile = $uploadDirectory . $uploadedFileName;
    //     if (move_uploaded_file($tempFile, $targetFile)) {
    //         $teacher_updated_assignment = $uploadedFileName;
    //     } else {
           
    //         echo "Error: Failed to move uploaded file.";
    //     }
    // }
    $sql = "INSERT INTO student_assignment_result (assignment_id,obtained_mark,remark,uploaded_type,status) VALUES (?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issss", $assignment_id, $marks, $reason, $uploaded_type, $status);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            "<script>
         alert('Assignment Result inserting successfully');
         window.location.href = 'student_assignments_review.php';</script>";
        } else {
            echo "Error: Assignment result insertion failed.";
            echo "<script>
                   alert('Error: Assignment result insertion failed.');
                   window.location.href = 'student_assignments_review.php';
               </script>";
        }
    } else {
        echo "Error: " . $conn->error;
    }
}


if (isset($_POST['id'])) 
{
    $id = $_POST['id'];
    // print_r($id);
    $uploaded_type = $_POST['uploaded_type'];
    $status = $_POST['status'];
    // print_r($status);
    $marks = $_POST['marks'];
    $reason = $_POST['reason'];

    // Prepare update query
    $sql = "UPDATE student_assignment_result SET uploaded_type=?, status=?, obtained_mark=?, remark=? WHERE id=?";
    // print_r($sql);

    // Prepare statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisi", $uploaded_type, $status, $marks, $reason, $id);

    // Execute statement
    if ($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Assignment  Proper Updated Successfully.'];
    } else {
        $response = ['status' => 0, 'message' => 'Error updating assignment: ' . $stmt->error];
    }
    $stmt->close();
} else {
    $response = ['status' => 0, 'message' => 'Invalid request.'];
}
header('Content-Type: application/json');
echo json_encode($response);