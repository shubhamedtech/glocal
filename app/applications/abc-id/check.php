<?php
  if(isset($_POST['id'])){
    require '../../../includes/db-config.php';
    session_start();
    $id = $_POST['id'];
    $id = base64_decode($id);
    $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
    $select = $conn->query("SELECT ABC_ID, University_ID FROM Students WHERE ID = $id");
    $abc_id = $select->fetch_assoc();
    if(!empty($abc_id['ABC_ID']) || $abc_id['University_ID']==48 || $abc_id['University_ID']==47){
      echo json_encode(['status'=>200]);
    }else{
      echo json_encode(['status'=>400]);
    }
  }
?>