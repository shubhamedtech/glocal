<?php
  ini_set('display_errors', 1); 
  if(isset($_POST['id']) && isset($_POST['exam_session']) && isset($_POST['name']) && isset($_POST['exam_start_date']) && isset($_POST['exam_end_time'])){
    require '../../../includes/db-config.php';
    session_start();
    date_default_timezone_set("Asia/Kolkata");

    $start_date = mysqli_real_escape_string($conn, $_POST['exam_start_date']);
    $start_date = date('Y-m-d', strtotime($start_date));
    $start_time = mysqli_real_escape_string($conn, $_POST['exam_start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['exam_end_time']);
    $id = intval($_POST['id']);

    $add = $conn->query("UPDATE Date_Sheets SET Exam_Date = '".$start_date."', Start_Time = '".$start_time."', End_Time = '".$end_time."' WHERE ID = $id");
    if($add){
      echo json_encode(['status'=>200, 'message'=>'Exam added successfully!']);
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
    }
  }