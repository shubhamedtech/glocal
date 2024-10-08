<?php
  ini_set('display_errors', 1);
  error_reporting(1);
  session_start();
  if($_SESSION['Role']=='Administrator'){
    include '../includes/db-config.php';
    include '../includes/helpers.php';

    $students = $conn->query("SELECT ID FROM `Students` WHERE `Added_For` = 1869");
    while($student = $students->fetch_assoc()){
      echo $student['ID']."<br>";
      generateStudentLedger($conn, $student['ID']);
    }
  }
