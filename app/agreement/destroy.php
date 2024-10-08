<?php
  if($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['id'])){
    require '../../includes/db-config.php';
    session_start();
    
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $check = $conn->query("SELECT ID FROM Agreements WHERE ID = $id");
    if($check->num_rows>0){
      $delete = $conn->query("DELETE FROM Agreements WHERE ID = $id");
      if($delete){
        echo json_encode(['status'=>200, 'message'=>'Agreements deleted successfully!']);
      }else{
        echo json_encode(['status'=>302, 'message'=>'Somthing went wrong!']);
      }
    }else{
      echo json_encode(['status'=>302, 'message'=>'Agreements not exists!']);
    }
  }
