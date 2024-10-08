<?php
require '../../includes/db-config.php';
if (isset($_POST['submit'])) {
    session_start();
    $student_id = $conn->real_escape_string($_SESSION['ID']);
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $assignment_id = $conn->real_escape_string($_POST['assignment_id']);
    $uploaded_type = $conn->real_escape_string($_POST['uploaded_type']);
    $targetDir = '../../uploads/assignments/';
    $fileCount = count($_FILES["assignment_file"]["name"]);
    $allowedTypes = array('pdf', 'jpeg', 'jpg', 'png', 'gif');
    $maxFileSize = 20 * 1024 * 1024;  // 20 MB
    $filePaths = [];
    foreach ($_FILES["assignment_file"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $fileName = basename($_FILES["assignment_file"]["name"][$key]);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['status' => 400, 'message' => 'Only PDF, JPEG, PNG, GIF, or JPG files are allowed.']);
                exit;
            }
            if ($_FILES["assignment_file"]["size"][$key] > $maxFileSize) {
                echo json_encode(['status' => 400, 'message' => 'Each file size should be less than 20 MB.']);
                exit;
            }

            $fileNameNew = uniqid() . '.' . $fileType;
            $uploadFile = $targetDir . $fileNameNew;

            if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"][$key], $uploadFile)) {
                $filePaths[] = $uploadFile;
            } else {
                echo json_encode(['status' => 400, 'message' => 'Error moving uploaded file.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'File upload error.']);
            exit;
        }
    }
    if (!empty($filePaths)) {
        $filesSerialized = implode(',', $filePaths);
        $existingData = $conn->query("SELECT * FROM submitted_assignment WHERE assignment_id='$assignment_id' AND student_id='$student_id' AND subject_id='$subject_id'");
        if ($existingData->num_rows > 0) {
            $updated_query = $conn->query("UPDATE submitted_assignment SET file_name='$filesSerialized', reuploaded=1 WHERE assignment_id='$assignment_id' AND subject_id='$subject_id' AND student_id='$student_id'");
            if ($updated_query) {
                header("location:../../student/lms/assignments");
                echo json_encode(['status' => 200, 'message' => 'Assignment Updated And Uploaded Successfully!']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
            }
        } else {
            $insert_query = $conn->query("INSERT INTO submitted_assignment (assignment_id, subject_id, student_id, uploaded_type, file_name) VALUES ('$assignment_id', '$subject_id', '$student_id', '$uploaded_type', '$filesSerialized')");
            if ($insert_query) {
                header("location:../../student/lms/assignments");
                echo json_encode(['status' => 200, 'message' => 'Assignment Uploaded Successfully!']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
            }
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'No files were uploaded.']);
    }
}
