<?php

if (isset($_POST['id'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $id = $_POST['id'];
    $subject_id = $_POST['sub_id'];
    $uploaded_type = $_POST['uploaded_type'];
    $status = $_POST['status'];
    $marks = $_POST['marks'];
    $reason = $_POST['reason'];
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
        $sql = "UPDATE student_assignment_result SET uploaded_type=?, status=?, obtained_mark=?, remark=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $uploaded_type, $status, $marks, $reason, $id);
        if ($stmt->execute()) {
            if ($stmt->execute()) {
                $assignentId = $conn->query("SELECT assignment_id FROM student_assignment_result WHERE id = $id");
                $assignentId = $assignentId->fetch_assoc();
                $assignentId = $assignentId['assignment_id'];
                $update = $conn->query("UPDATE submitted_assignment SET reuploaded = 0 WHERE id = $assignentId");
                echo json_encode(['status' => 1, 'message' => 'Result Updated Successfully!']);
                header('location:/../lms-settings/student_assignments_review');
            } else {
                echo json_encode(['status' => 0, 'message' => 'Something went wrong while updating the result in the database.']);
            }
            echo 'Result Updated Successfully!';
            echo json_encode(['status' => 200, 'message' => 'Result Updated Successfully!']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Something went wrong while updated the file path into the database.']);
        }
        $stmt->close();
    }
}
