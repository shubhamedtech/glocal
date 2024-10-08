<?php
  if(isset($_POST['id']) && $_POST['student_stage']){
    require '../../includes/db-config.php';
    session_start();

    $id = intval($_POST['id']);
    $student_stage = mysqli_real_escape_string($conn, $_POST['student_stage']);

    if(empty($student_stage)){
      echo json_encode(['status'=>400, 'message'=>'Student Stage is required.']);
      exit();
    }

    $update = $conn->query("UPDATE Students SET Student_Stage = '$student_stage' WHERE ID = $id");
    if($update){
      echo json_encode(['status'=>200, 'message'=>'Student Stage updated successfully!']);
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
    }
  }
