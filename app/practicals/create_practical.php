<?php
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
ini_set('display_errors', 1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['startdate'];
    $endDate = $_POST['enddate'];
    $createdBy = $_POST['created'];
    $marks = $_POST['marks'];
    $courseType = $_POST['coursetype'];
    $subcourseType = $_POST['subcourse_id'];
    $Practical = $_POST['Practical'];
    $semester = $_POST['seme'];
    $practicalName = $_POST['practicalname'];
    $targetDir = '../../uploads/practicals/';

    if (isset($_FILES["files"]) && $_FILES["files"]["error"] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES["files"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['pdf', 'jpeg', 'jpg', 'png', 'gif'];

        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['status' => 400, 'message' => 'Only PDF, JPEG,png,gif and JPG files are allowed.']);
            exit;
        }

        $fileNameNew = uniqid() . '.' . str_replace(' ', '_', $fileType);
        $targetFilePath = $targetDir . $fileNameNew;

        if (move_uploaded_file($_FILES["files"]["tmp_name"], $targetFilePath)) {
            $creationDate = date("Y-m-d H:i:s");

            $sql = "INSERT INTO Student_Practical (start_date, end_date, created_by, marks, practical_file, created_date, course_id, sub_course_id, semester, subject_id, practical_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                echo json_encode(['status' => 400, 'message' => 'SQL preparation error.']);
                exit;
            }

            $stmt->bind_param("sssssssssss", $startDate, $endDate, $createdBy, $marks, $targetFilePath, $creationDate, $courseType, $subcourseType, $semester, $Practical, $practicalName);

            if ($stmt->execute()) {
                echo json_encode(['status' => 200, 'message' => 'Practical Created Successfully!']);
                header('location:/../lms-settings/practicals');
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
