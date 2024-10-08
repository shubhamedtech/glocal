<?php

//   ini_set('display_errors', 1);
// 	if(isset($_POST['photo'])){
//     require '../../includes/db-config.php';
//     session_start();
      
// 	$img = $_POST['photo'];
      
//     $folderPath = "../../uploads/webphoto/";
      
//     $image_parts = explode(";base64,", $img);
//     $image_type_aux = explode("image/", $image_parts[0]);
//     $image_type = $image_type_aux[1];
//     $image_base64 = base64_decode($image_parts[1]);
//     $fileName = uniqid() .'_'.$_SESSION['Name'].'.png';
//     $file = $folderPath . $fileName;
//     file_put_contents($file, $image_base64);
//   	move_uploaded_file($file, $fileName);
      
//     $add = $conn->query("INSERT INTO Webcam_Student_Pic (Student_ID, Photo, Syllabus_ID, Created_at, Updated_at) VALUES (".$_SESSION['ID'].", '".$fileName."', 1, now(), now())");
//     if($add){
// 
// echo '<script>startExam()</script>'; -->
//     <?php }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
//     }
//   }
?>