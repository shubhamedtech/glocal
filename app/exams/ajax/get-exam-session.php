<?php
  if(isset($_GET['id'])){
    require '../../../includes/db-config.php';
    session_start();
    
    $id = intval($_GET['id']);
    $sub_course = $conn->query("SELECT * FROM Exam_Sessions WHERE University_ID = ".$_SESSION['university_id']." ");
    echo '<option value="">Choose</option>';
    while ($row = $sub_course->fetch_assoc()) {
        $ad_types = json_decode($row['Admission_Type'], true);
        foreach($ad_types as $index=>$ad_type){
            if($index ==  $id){
                echo '<option value="'.$row['ID'].'">'.$row['Name'].'</option>';
            }
        } 
    }
  }