<?php

require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = intval($_POST['assignment_id']);
    $subject_id = $_POST['subj'];
    $marks = $_POST['marks'];
    $reason = $_POST['reason'];
    $uploaded_type = $_POST['uploaded_type'];
    $status = $_POST['status'];

    $get_marks_sql = "SELECT sa.marks FROM student_assignment AS sa WHERE sa.subject_id = ?";
    if ($stmt = $conn->prepare($get_marks_sql)) {
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $stmt->bind_result($allotted_marks);
        $stmt->fetch();
        $stmt->close();
        // Check if obtained marks are within the allowed range
        if ($marks > $allotted_marks) {
            echo json_encode(['status' => 400, 'message' => 'Marks should be less than or equal to allotted marks']);
            header('Location: /lms-settings/student_assignments_review');
            exit();
        }

        $sql = "INSERT INTO student_assignment_result (assignment_id,obtained_mark,remark,uploaded_type,status) VALUES (?, ?, ?, ?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("issss", $assignment_id, $marks, $reason, $uploaded_type, $status);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                header('Location: /lms-settings/student_assignments_review');
                echo json_encode(['status' => 200, 'message' => 'Result Proper Uploaded Successfully!']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Something went wrong while inserting the file path into the database.']);
            }
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
