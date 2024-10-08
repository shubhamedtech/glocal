<?php
ini_set('display_errors', 1);
require '../../includes/db-config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['practical_id'])) {
    $practical_id = intval($_POST['practical_id']);
    $course_type = $_POST['coursetype'];
    $sub_course_type = $_POST['subcourse_id'];
    $semester = $_POST['seme'];
    $subject = $_POST['subject_id'];
    $practical_name = $_POST['practicalname'];
    $marks = $_POST['marks'];
    $start_date = $_POST['startdate'];
    $end_date = $_POST['enddate'];

    $upload_dir = '../../uploads/assignments/';
    // File upload handling
    if (isset($_FILES['filesin']) && $_FILES['filesin']['error'] == UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['filesin']['name']);
        $fileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowedTypes = array('pdf', 'jpeg', 'jpg', 'png', 'gif');
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['status' => 400, 'message' => 'Only PDF, JPEG, JPG, PNG, and GIF files are allowed.']);
            exit;
        }
        $fileNameNew = uniqid() . '.' . str_replace(' ', '_', $fileType);
        $targetFilePath = $upload_dir . $fileNameNew;
        if (move_uploaded_file($_FILES['filesin']['tmp_name'], $targetFilePath)) {
            $sql = "UPDATE Student_Practical SET 
                    course_id = '$course_type', 
                    sub_course_id = '$sub_course_type', 
                    semester = '$semester', 
                    subject_id = '$subject', 
                    practical_name = '$practical_name', 
                    marks = '$marks', 
                    practical_file = '$targetFilePath',
                    start_date = '$start_date', 
                    end_date = '$end_date',
                    updated_date = NOW()
                    WHERE id = $practical_id";
            if ($conn->query($sql) === TRUE) {
                header("Location:../../lms-settings/practicals");
                echo json_encode(['status' => 200, 'message' => 'Practical Updated Successfully!']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
            }
        } else {
            echo 'Error uploading file.';
        }
    } else {
        $sql = "UPDATE Student_Practical SET 
                course_id = '$course_type', 
                sub_course_id = '$sub_course_type', 
                semester = '$semester', 
                subject_id = '$subject', 
                practical_name = '$practical_name', 
                marks = '$marks', 
                start_date = '$start_date', 
                end_date = '$end_date',
                updated_date = NOW()
                WHERE id = $practical_id";
        if ($conn->query($sql) === TRUE) {
            header("Location:../../lms-settings/practicals");
            echo json_encode(['status' => 200, 'message' => 'Practicals Without File Updated Successfully!']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
        }
    }
}
