<?php
require '../../includes/db-config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assignment_id'])) {
    $assignment_id = intval($_POST['assignment_id']);
    $course_type = $_POST['coursetype'];
    $sub_course_type = $_POST['subcourse_id'];
    $semester = $_POST['seme'];
    $subject = $_POST['subject_id'];
    $assignment_name = $_POST['assignmentname'];
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
            $sql = "UPDATE student_assignment SET 
                    course_id = '$course_type', 
                    sub_course_id = '$sub_course_type', 
                    semester = '$semester', 
                    subject_id = '$subject', 
                    assignment_name = '$assignment_name', 
                    marks = '$marks', 
                    file_path = '$targetFilePath',
                    start_date = '$start_date', 
                    end_date = '$end_date',
                    updated_date = NOW()
                    WHERE Assignment_id = $assignment_id";
            if ($conn->query($sql) === TRUE) {
                header("Location:../../lms-settings/assignments");
                echo json_encode(['status' => 200, 'message' => 'Assignment Updated Successfully!']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
            }
        } else {
            echo 'Error uploading file.';
        }
    } else {
        $sql = "UPDATE student_assignment SET 
                course_id = '$course_type', 
                sub_course_id = '$sub_course_type', 
                semester = '$semester', 
                subject_id = '$subject', 
                assignment_name = '$assignment_name', 
                marks = '$marks', 
                start_date = '$start_date', 
                end_date = '$end_date',
                updated_date = NOW()
                WHERE Assignment_id = $assignment_id";
        if ($conn->query($sql) === TRUE) {
            header("Location:../../lms-settings/assignments");
            echo json_encode(['status' => 200, 'message' => 'Assignments Without File Updated Successfully!']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
        }
    }
}
