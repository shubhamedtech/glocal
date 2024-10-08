<?php
  if(isset($_POST['name']) && isset($_POST['reporting']) && isset($_POST['email']) && isset($_POST['mobile'])){
    require '../../includes/db-config.php';
    session_start();

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $reporting = intval($_POST['reporting']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = intval($_POST['mobile']);

    if(empty($name) || empty($reporting) || empty($email) || empty($mobile)){
      echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
    }

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

    $center_code = $conn->query("SELECT Code,Vertical_type FROM Users WHERE ID = $reporting");
    $codeArr = mysqli_fetch_array($center_code);
    $center_code = $codeArr['Code'];
    $vertical_type =  $codeArr['Vertical_type'];
     
    $all_reporting_user = $conn->query("SELECT Users.Code,Users.ID  FROM Center_SubCenter LEFT JOIN Users ON Center_SubCenter.Sub_Center = Users.ID WHERE Center = $reporting ORDER BY Center_SubCenter.Sub_Center DESC LIMIT 1");
    if($all_reporting_user->num_rows>0){
      $code = mysqli_fetch_array($all_reporting_user);
      $code = $code['Code'];
     
      $code = str_replace($center_code.'.', '', $code);
      $new_code = $code+1;
      $code = $center_code.'.'.$new_code;
    }else{
      $code = $center_code.'.1';
    }
    
    $check = $conn->query("SELECT ID FROM Users WHERE Code like '$code'");
    if($check->num_rows>0){
      echo json_encode(['status'=>400, 'message'=>'Code already exists!', 'code'=>$code]);
      exit();
    }
     
    $password = "12345";
    $add = $conn->query("INSERT INTO `Users`(`Name`, `Email`, `Mobile`, `Code`, `Password`, `Role`, `Designation`, `Photo`, `Created_By`,`Vertical_type`) VALUES ('$name', '$email',  '$mobile', '$code', AES_ENCRYPT('$password','60ZpqkOnqn0UQQ2MYTlJ'), 'Sub-Center', 'Sub-Center', '$filename', ".$_SESSION['ID'].",$vertical_type)");
    $add = $conn->query("INSERT INTO `Center_SubCenter`(`Center`, `Sub_Center`) VALUES ($reporting, $conn->insert_id)");
    
    if($add){
      echo json_encode(['status'=>200, 'message'=>'Sub-Center added successlly!']);
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
    }
  }else{
    echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
  }
?>
