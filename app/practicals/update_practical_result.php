<?php
ini_set('display_errors', 1);
if (isset($_POST['id'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $id = $_POST['id'];
    $uploaded_type = $_POST['uploaded_type'];
    $status = $_POST['status'];
    $marks = $_POST['marks'];
    $reason = $_POST['reason'];
    $sql = "UPDATE Student_Practical_Result SET uploaded_type=?, status=?, obtained_mark=?, remark=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisi", $uploaded_type, $status, $marks, $reason, $id);
    if ($stmt->execute()) {
        echo 'Result Updated Successfully!';
        // echo json_encode(['status' => 200, 'message' => 'Result Updated Successfully!']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong while updated the file path into the database.']);
    }
    $stmt->close();
}
