<?php
ini_set('display_errors', 1);
require '../../includes/db-config.php';
if (isset($_POST['submit'])) {
    session_start();
    $student_id = $conn->real_escape_string($_SESSION['ID']);
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $practical_id = $conn->real_escape_string($_POST['practical_id']);
    $uploaded_type = $conn->real_escape_string($_POST['uploaded_type']);

    $targetDir = '../../uploads/practicals/';
    if ($_FILES["practical_file"]["error"] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES["practical_file"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = array('pdf', 'jpeg', 'jpg', 'png', 'gif');
        if (!in_array($fileType, $allowedTypes)) {
            echo "Only PDF, JPEG, or JPG files are allowed.";
            exit;
        }
        $fileNameNew = uniqid() . '.' . $fileType;
        $targetFilePath = $targetDir . $fileNameNew;
        if (move_uploaded_file($_FILES["practical_file"]["tmp_name"], $targetFilePath)) {
            $created_date = date('Y-m-d H:i:s');

            $existingData = $conn->query("SELECT * FROM Submitted_Practical WHERE practical_id='$practical_id' AND student_id='$student_id' AND subject_id='$subject_id'");

            if ($existingData->num_rows > 0) {
                $updated_query = $conn->query("UPDATE Submitted_Practical SET student_practical_file	='$targetFilePath', created_date='$created_date' WHERE practical_id='$practical_id' AND subject_id='$subject_id' AND student_id='$student_id'");
                if ($updated_query) {
                    echo json_encode(['status' => 200, 'message' => 'Practical Updated And Uploaded Successfully!']);
                    header("Location:../../student/lms/practicals");
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
                }
            } else {
                $insert_query = $conn->query("INSERT INTO Submitted_Practical (practical_id, subject_id, student_id,uploaded_type, student_practical_file, created_date) VALUES ('$practical_id', '$subject_id', '$student_id', '$uploaded_type', '$targetFilePath', '$created_date')");
                if ($insert_query) {
                    echo json_encode(['status' => 200, 'message' => 'Practical Uploaded Successfully!']);
                    header("Location:../../student/lms/practicals");
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
                }
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "File upload error.";
    }
}
