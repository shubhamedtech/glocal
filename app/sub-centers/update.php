<?php
  if(isset($_POST['name']) && isset($_POST['id'])){
    require '../../includes/db-config.php';
    require '../../includes/helpers.php';

    session_start();

    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = intval($_POST['mobile']);

    if(empty($name) || empty($id) || empty($email) || empty($mobile)){
      echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
    }
    $center_id  = getCenterIdFunc($conn, $id);
    $center_code = $conn->query("SELECT Code,Vertical_type FROM Users WHERE ID = $center_id");
    $center_code_arr = mysqli_fetch_array($center_code);
    $vertical_type = $center_code_arr['Vertical_type'];

    
    if(isset($_FILES["image"]["name"]) && $_FILES["image"]["name"]!=''){
      $temp = explode(".", $_FILES["image"]["name"]);
      $filename = round(microtime(true)) . '.' . end($temp);
      $tempname = $_FILES["image"]["tmp_name"];
      $folder = "../../assets/img/sub-centers/".$filename; 
      if(move_uploaded_file($tempname, $folder)){ 
        $filename = "/assets/img/sub-centers/".$filename;
      }else{
        echo json_encode(['status'=>400, 'message'=>'Unable to save image!']);
        exit();
      }
    }else{
      $filename = "/assets/img/default-user.png";
    }

    $add = $conn->query("UPDATE `Users` SET `Name` = '$name', `Email` = '$email', `Mobile` = '$mobile', `Photo` = '$filename',`Vertical_type`= $vertical_type WHERE ID = $id");
    if($add){
      echo json_encode(['status'=>200, 'message'=>'Sub-Center updated successlly!']);
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
    }
  }
