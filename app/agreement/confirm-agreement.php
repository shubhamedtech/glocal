<?php
error_reporting(1);

if (isset($_POST['confirm']) && isset($_POST['student_id']) && (!empty($_FILES["student_signature"]['tmp_name']) || isset($_POST['uploaded_student'])) && (!empty($_FILES["parent_signature"]['tmp_name']) || isset($_POST['uploded_parent_signature']))) {
    require '../../includes/db-config.php';
    session_start();
    $signature_folder = '../../uploads/signature/';

    
    $checked = $_POST['confirm']=='on'?true:false;
    $student = $_POST['student_id'];
    $allowed_file_extensions = array("jpeg", "jpg", "png", "gif", "JPG", "PNG", "JPEG");

      // Student's Signature
  if (isset($_FILES["student_signature"]['tmp_name']) && $_FILES["student_signature"]['tmp_name'] != '') {
    $student_signature = mysqli_real_escape_string($conn, $_FILES["student_signature"]['name']);
    $tmp_name = $_FILES["student_signature"]["tmp_name"];
    $student_signature_extension = pathinfo($student_signature, PATHINFO_EXTENSION);
    $student_signature = $student . "_Student_Signature." . $student_signature_extension;
    if (in_array($student_signature_extension, $allowed_file_extensions)) {
      if (!move_uploaded_file($tmp_name, $signature_folder . $student_signature)) {
        echo json_encode(['status' => 503, 'message' => 'Unable to upload Student Signature!']);
        exit();
      } else {
        $student_signature = str_replace('../..', '', $signature_folder) . $student_signature;
        $check = $conn->query("SELECT ID FROM Student_Documents WHERE Student_ID = $student AND Type = 'Student Signature'");
        if ($check->num_rows > 0) {
          $update = $conn->query("UPDATE Student_Documents SET Location = '$student_signature' WHERE Student_ID = $student AND Type = 'Student Signature'");
        } else {
          $update = $conn->query("INSERT INTO Student_Documents (Student_ID, Type, Location) VALUES ($student, 'Student Signature', '$student_signature')");
        }
      }
    } else {
      echo json_encode(['status' => 302, 'message' => 'Student Signature should be image!']);
      exit();
    }
  }

  // Parent's Signature
  if (isset($_FILES["parent_signature"]['tmp_name']) && $_FILES["parent_signature"]['tmp_name'] != '') {
    $parent_signature = mysqli_real_escape_string($conn, $_FILES["parent_signature"]['name']);
    $tmp_name = $_FILES["parent_signature"]["tmp_name"];
    $parent_signature_extension = pathinfo($parent_signature, PATHINFO_EXTENSION);
     $parent_signature = $student . "_Parent_Signature." . $parent_signature_extension;
    if (in_array($parent_signature_extension, $allowed_file_extensions)) {
      if (!move_uploaded_file($tmp_name, $signature_folder . $parent_signature)) {
        echo json_encode(['status' => 503, 'message' => 'Unable to upload Parent Signature!']);
        exit();
      } else {
        $parent_signature = str_replace('../..', '', $signature_folder) . $parent_signature;
        $check = $conn->query("SELECT ID FROM Student_Documents WHERE Student_ID = $student AND Type = 'Parent Signature'");
        if ($check->num_rows > 0) {
          $update = $conn->query("UPDATE Student_Documents SET Location = '$parent_signature' WHERE Student_ID = $student AND Type = 'Parent Signature'");
        } else {
          $update = $conn->query("INSERT INTO Student_Documents (Student_ID, Type, Location) VALUES ($student, 'Parent Signature', '$parent_signature')");
        }
      }
    } else {
      echo json_encode(['status' => 302, 'message' => 'Parent Signature should be image!']);
      exit();
    }
  }
    $query = "INSERT INTO Agreements (`University_ID`,`Role`,`Agreement_File`,`Student_ID`) VALUES (" . $_SESSION['university_id'].",2,NULL,$student)";
    $add = $conn->query($query);

    // $add = $conn->query("INSERT INTO `Agreement`(`Student_Id`, `Confirmation_Status`) VALUES ('$student', '$checked')");
    if ($add) {
        echo json_encode(['status' => 200, 'message' => 'Thank You for Confirming!']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
} else {

    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    exit();
}
