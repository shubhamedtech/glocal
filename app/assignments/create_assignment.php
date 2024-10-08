<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $startDate = $_POST['startdate'];
    $endDate = $_POST['enddate'];
    $createdBy = $_POST['created'];
    $marks = $_POST['marks'];
    $course_type = $_POST['coursetype'];
    $subcourse_type = $_POST['subcourse_id'];
    $subject = $_POST['subject'];
    $semester = $_POST['seme'];
    $assignment_name = $_POST['assignmentname'];
    $targetDir = '../../uploads/assignments/';
    if (isset($_FILES["files"]) && $_FILES["files"]["error"] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES["files"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = array('pdf', 'jpeg', 'jpg', 'png', 'gif');
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['status' => 400, 'message' => 'Only PDF, JPEG, JPG, PNG, and GIF files are allowed.']);
            exit;
        }
        $fileNameNew = uniqid() . '.' . str_replace(' ', '_', $fileType);
        $targetFilePath = $targetDir . $fileNameNew;

        if (move_uploaded_file($_FILES["files"]["tmp_name"], $targetFilePath)) {
            require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';

            $sql = "INSERT INTO student_assignment (start_date, end_date, created_by, marks, file_path, course_id, sub_course_id, semester, subject_id, assignment_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                echo json_encode(['status' => 400, 'message' => 'SQL preparation error.']);
                exit;
            }

            $stmt->bind_param("ssssssssss", $startDate, $endDate, $createdBy, $marks, $targetFilePath, $course_type, $subcourse_type, $semester, $subject, $assignment_name);

            if ($stmt->execute()) {
                echo json_encode(['status' => 200, 'message' => 'Assignment Created Successfully!']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
            }

            $stmt->close();
            $conn->close();
        } else {
            echo json_encode(['status' => 400, 'message' => 'Error uploading file.']);
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'File upload error: ' . $_FILES["files"]["error"]]);
    }
}
