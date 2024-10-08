<?php
  ini_set('display_errors', 1); 

if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['id'])) {
  require '../../includes/db-config.php';
  session_start();

  $id = mysqli_real_escape_string($conn, $_GET['id']);
  if ($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Administrator') {
    
     $Alloted_Center_To_Counsellor = $conn->query("SELECT ID FROM Alloted_Center_To_Counsellor WHERE Code = $id");
    if($Alloted_Center_To_Counsellor->num_rows > 0){
      	echo json_encode(['status' => 400, 'message' => 'Counsellor Alloted To This Center!']);
    	exit();
    }
  	$check = $conn->query("SELECT ID FROM Users WHERE ID = $id");
    if ($check->num_rows > 0) {
      $delete_from_subcenter = $conn->query("DELETE FROM Center_SubCenter WHERE `Sub_Center` = $id");
      $delete_from_university_user = $conn->query("DELETE FROM University_User WHERE `User_ID` = $id AND University_ID = " . $_SESSION['university_id']);
      $delete_from_users = $conn->query("DELETE FROM Users WHERE ID = $id");
      if ($delete_from_subcenter && $delete_from_university_user && $delete_from_users) {
        echo json_encode(['status' => 200, 'message' => 'User deleted successfully!']);
      } else {
        echo json_encode(['status' => 302, 'message' => 'Something went wrong!']);
      }
    } else {
      echo json_encode(['status' => 302, 'message' => 'User not exists!']);
    }
  }else {
    echo json_encode(['status' => 400, 'message' => 'You are not allowed to delete this!']);
    exit();
  }
}
