<?php
  if(isset($_GET['id'])){
    require '../../includes/db-config.php';
    session_start();
    $id = intval($_GET['id']);
    if($_SESSION['university_id'] == 48){
      $sub_course = $conn->query("SELECT Scheme_ID, Min_Duration FROM Sub_Courses WHERE ID = $id");
      $sub_course = $sub_course->fetch_assoc();
      $sub_course['Min_Duration'] = $_SESSION['Role']=='Student' ? $_SESSION['Duration'] : $sub_course['Min_Duration'];
      $duractions = json_decode($sub_course['Min_Duration'], true);
      echo '<option value="">Choose</option>';
      for($i=0; $i<count($duractions); $i++){
        echo '<option value="'.$sub_course['Scheme_ID'].'|'.$duractions[$i].'">'.$duractions[$i].'</option>';
      }
    }else{
      $sub_course = $conn->query("SELECT Scheme_ID, Min_Duration FROM Sub_Courses WHERE ID = $id");
      $sub_course = $sub_course->fetch_assoc();
      $sub_course['Min_Duration'] = $_SESSION['Role']=='Student' ? $_SESSION['Duration'] : $sub_course['Min_Duration'];
  
      echo '<option value="">Choose</option>';
      for($i=1; $i<=$sub_course['Min_Duration']; $i++){
        echo '<option value="'.$sub_course['Scheme_ID'].'|'.$i.'">'.$i.'</option>';
      }
    }
  }