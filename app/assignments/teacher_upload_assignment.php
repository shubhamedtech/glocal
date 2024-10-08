<?php
print_r($_FILES);
if (isset($_POST["student_id"]) && isset($_FILES["teacher_upload_assignment"])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $file = $_FILES["teacher_upload_assignment"];
    $uploaded_type = $_POST['Manual'];
    $assignment_id = intval($_POST['assignment_id']);
    $subject_id = intval($_POST['subject_id']);
    $student_id = intval($_POST['student_id']);
    $fileName = basename($file["name"]);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = array("pdf", "doc", "docx", "txt", "jpeg", "jpg", "png", "gif");
    $fileNameNew = uniqid() . '.' . $fileExt;
    if (empty($subject_id) || empty($student_id)) {
        $conn->close();
        exit(json_encode(['status' => 400, 'message' => 'Required fields are missing!']));
    }
    if (in_array($fileExt, $allowedExtensions)) {
        if ($file["error"] === 0) {
            $uploadDir = '../../uploads/assignments/';
            $uploadPath = $uploadDir . $fileNameNew;
            if (move_uploaded_file($file["tmp_name"], $uploadPath)) {
                $sql = "INSERT INTO submitted_assignment (assignment_id, subject_id, student_id, uploaded_type, file_name) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sssss", $assignment_id, $subject_id, $student_id, $uploaded_type, $uploadPath);
                    if ($stmt->execute()) {
                        echo json_encode(['status' => 200, 'message' => 'File Uploaded Successfully!']);
                    } else {
                        echo json_encode(['status' => 400, 'message' => 'Something went wrong while inserting the file path into the database.']);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Error preparing SQL statement.']);
                }
            } else {
                echo json_encode(['status' => 400, 'message' => 'Error moving uploaded file to destination directory.']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Error uploading file.']);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'Invalid file type. Allowed extensions: pdf, doc, docx, txt, jpeg, jpg, png, gif']);
    }
    $conn->close();
} else {
    echo json_encode(['status' => 400, 'message' => 'Please select a file to upload.']);
}
