<?php
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $assignment_id = htmlspecialchars($_GET['id']);
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $sql = "DELETE FROM student_assignment WHERE Assignment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assignment_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 200, 'message' => 'Assignment Deleted Successfully!']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
} else {

    echo "Invalid request. Assignment ID is missing.";
}
