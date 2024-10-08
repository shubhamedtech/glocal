<?php
if ((isset($_FILES['photo']) || isset($_POST['updated_file'])) && isset($_POST['role'])) {
  require '../../includes/db-config.php';
  session_start();

  $role = mysqli_real_escape_string($conn, $_POST['role']);
  $date = $_POST['date'];
  $id = isset($_POST['id']) ? intval($_POST['id']) : '';
  $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : null;
  $stuQuery = '';
  if ($role == 2) {
    $stuQuery = ' AND Student_ID = '.$student_id;
  }

  if (isset($_FILES["photo"]["name"]) && $_FILES["photo"]["name"] != '') {
    $maxFileSize = 10 * 1024 * 1024;
    if ($_FILES["photo"]["size"] > $maxFileSize) {
      echo json_encode(['status' => 400, 'message' => 'File size should not be larger than 10 MB!']);
      exit();
    }

    $temp = explode(".", $_FILES["photo"]["name"]);
    $filename = round(microtime(true)) . '.' . end($temp);
    $tempname = $_FILES["photo"]["tmp_name"];
    $folder = "../../uploads/agreement/" . $filename;

    if (move_uploaded_file($tempname, $folder)) {
      $filename = "/uploads/agreement/" . $filename;
    } else {
      echo json_encode(['status' => 400, 'message' => 'Unable to save Agreement!']);
      exit();
    }
  } else if (isset($_POST['updated_file']) && !empty($id)) {
    $filename = mysqli_real_escape_string($conn, $_POST['updated_file']);
  } else {
    echo json_encode(['status' => 400, 'message' => 'Please upload the Agreement!']);
    exit();
  }

  $check = $conn->query("SELECT ID FROM Agreements WHERE ID = '$id' ");

  if ($check->num_rows > 0) {
    // echo "UPDATE Agreements SET Agreement_File='$filename', Role=$role, start_date = $date  WHERE ID = $id  $stuQuery";die;
    $update = $conn->query("UPDATE Agreements SET Agreement_File='$filename', Role=$role, start_date = '$date'  WHERE ID = $id  $stuQuery");
    
    if ($update) {
      echo json_encode(['status' => 200, "message" => "Agreement Updated Successfully!"]);
    } else {
      echo json_encode(['status' => 400, "message" => "Something went Wrong!"]);
    }
  } else {
    if ($role == 2 && $student_id !== null) {
      $query = "INSERT INTO Agreements (`University_ID`,`Role`,`Agreement_File`,`Student_ID`) VALUES (" . $_SESSION['university_id'] . ",$role,'$filename',$student_id)";
    } else {
      $query = "INSERT INTO Agreements (`University_ID`,`Role`,`Agreement_File`,`start_date`) VALUES (" . $_SESSION['university_id'] . ",$role,'$filename','$date')";
    }
    $add = $conn->query($query);
    if ($add) {
      echo json_encode(['status' => 200, "message" => "Agreement Added Successfully!"]);
    } else {
      echo json_encode(['status' => 400, "message" => "Something Went Wrong!"]);
    }
  }

}