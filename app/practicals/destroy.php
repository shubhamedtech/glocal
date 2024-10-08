<?php
ini_set('display_errors', 1);
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $practical_id = htmlspecialchars($_GET['id']);
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $sql = "DELETE FROM Student_Practical WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $practical_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 200, 'message' => 'Practical File Deleted Successfully!']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
} else {

    echo "Invalid request. Practical ID is missing.";
}
