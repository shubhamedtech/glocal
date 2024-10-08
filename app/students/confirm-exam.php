<?php
error_reporting(1);

if (isset($_POST['confirm']) && isset($_POST['student_id'])) {
    require '../../includes/db-config.php';
    $checked = $_POST['confirm']=='on'?true:false;
    $student = $_POST['student_id'];
    // print_r($checked);
    // exit();
    $add = $conn->query("INSERT INTO `Examination_Confirmation`(`Student_Id`, `Confirmation_Status`) VALUES ('$student', '$checked')");
    if ($add) {
        echo json_encode(['status' => 200, 'message' => 'Thank You for Confirming!']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
} else {

    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    exit();
}
