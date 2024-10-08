<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'includes/db-config.php';
require 'includes/helpers.php';

// $students = $conn->query("SELECT ID FROM `Students` WHERE `University_ID` = 47 AND Admission_Session_ID = 82");
// while($student = $students->fetch_assoc()){
//     generateStudentLedger($conn, $student['ID']);
// }


//generateStudentLedger($conn, 1369);