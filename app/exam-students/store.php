<?php
  ini_set('display_errors', 1); 

  if(isset($_FILES['file'])){
    require '../../includes/db-config.php';
    require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
    require('../../extras/vendor/nuovo/spreadsheet-reader/SpreadsheetReader.php');

    session_start();

    $export_data = array();

    // Header
    $header = array('Student Name', 'Email', 'Phone', 'DOB', 'Duraction(Sem/Year)', 'Course', 'Sub-Course', 'Admission Session', 'Admission Type', 'Enrolment Number', "Remark");
    $export_data[] = $header;

    $mimes = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    if(in_array($_FILES["file"]["type"],$mimes)){
      
      // Upload File
      $uploadFilePath = basename($_FILES['file']['name']);
      // print_r($_FILES['file']['tmp_name'], $uploadFilePath);
      // exit();
      move_uploaded_file($_FILES['file']['tmp_name'], $uploadFilePath);

      // Read File
      $reader = new SpreadsheetReader($uploadFilePath);

      // Sheet Count
      $totalSheet = count($reader->sheets());

      /* For Loop for all sheets */
      for($i=0; $i<$totalSheet; $i++){
        $reader->ChangeSheet($i);
   
        foreach ($reader as $row)
        {

          $remark = [];
          $student_name = mysqli_real_escape_string($conn, $row[0]);
          $email = mysqli_real_escape_string($conn, $row[1]);
          $phone_number = mysqli_real_escape_string($conn, $row[2]);
          $dob = mysqli_real_escape_string($conn, $row[3]);
          $dob = date('Y-m-d', strtotime($dob));
          $duraction = mysqli_real_escape_string($conn, $row[4]);
          $course = mysqli_real_escape_string($conn, $row[5]);
          $sub_course = mysqli_real_escape_string($conn, $row[6]);
          $admission_session_name = mysqli_real_escape_string($conn, $row[7]);
          $admission_type_name = mysqli_real_escape_string($conn, $row[8]);
          $enrolment = mysqli_real_escape_string($conn, $row[9]);
          if($student_name =='Student Name'){
            continue;
          }

          $admission_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = ".$_SESSION['university_id']." AND (Name LIKE '$admission_session_name') ");

          if($admission_session->num_rows==0){
            $export_data[] = array_merge($row, ['Admission Session not found!']);
            continue;
          }

          $admission_session_id = '';
          while($admission = $admission_session->fetch_assoc()){
            $admission_session_id = $admission['ID'];
          }

          $admission_type = $conn->query("SELECT ID FROM Admission_Types WHERE University_ID = ".$_SESSION['university_id']." AND (Name LIKE '$admission_type_name') ");

          if($admission_type->num_rows==0){
            $export_data[] = array_merge($row, ['Admission Type not found!']);
            continue;
          }

          $admission_type_id = '';
          while($admission = $admission_type->fetch_assoc()){
            $admission_type_id = $admission['ID'];
          }

          $course = $conn->query("SELECT ID FROM Courses WHERE University_ID = ".$_SESSION['university_id']." AND (Name LIKE '$course' OR Short_Name LIKE '$course')");
          if($course->num_rows==0){
            $export_data[] = array_merge($row, ['Course not found!']);
            continue;
          }

          $course_ids = array();
          while($course_id = $course->fetch_assoc()){
            $course_ids[] = $course_id['ID'];
          }

          $sub_course = $conn->query("SELECT ID, Course_ID FROM Sub_Courses WHERE University_ID = ".$_SESSION['university_id']." AND (Name LIKE '$sub_course' OR Short_Name LIKE '$sub_course') AND Course_ID IN (".implode(',', $course_ids).")");
          if($sub_course->num_rows==0){
            $export_data[] = array_merge($row, ['Sub-Course not found!']);
            continue;
          }

          $sub_course = $sub_course->fetch_assoc();
          $course_id = $sub_course['Course_ID'];
          $sub_course_id = $sub_course['ID'];

          $student_check = $conn->query("SELECT ID FROM Exam_Students WHERE Phone_Number = '$phone_number' AND Email = '$email' AND DOB = '$dob' AND University_ID = " . $_SESSION['university_id'] . " AND Course = $course_id");
          if ($student_check->num_rows > 0) {
            $export_data[] = array_merge($row, ['Student with same details already exists!']);
            continue;
          }
          $gender = null;
          $category = null;
          $employment_status = null;
          $marital_status = null;
          $religion = null;
          $nationality = "INDIAN";
          $aadhar = null;
          $add = $conn->query("INSERT INTO Exam_Students (University_ID, Admission_Session, Admission_Type, Course, Sub_Course, Duration, Phone_Number, Name, Email, Enrolment_Number, Gender, Category, Emploment_Status, Marital_Status, Religion, Nationality, Aadhar, DOB, Status, Created_at, Updated_at) VALUES (" . $_SESSION['university_id'] . ", $admission_session_id, $admission_type_id, $course_id, $sub_course_id, $duraction, $phone_number, '$student_name', '$email', '$enrolment', '$gender', '$category', '$employment_status', '$marital_status', '$religion','$nationality', '$aadhar', '$dob', 1, now(), now())");

          if($add){
            $export_data[] = array_merge($row, ['Student added successfully!']);
          }else{
            $export_data[] = array_merge($row, ['Something went wrong!']);
          }
        }
      }
      unlink($uploadFilePath);
      $xlsx = SimpleXLSXGen::fromArray( $export_data )->downloadAs('\Added Students Status '.date('h m s').'.xlsx');
    }
  }
?>
