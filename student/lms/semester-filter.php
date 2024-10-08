<?php
    require '../../includes/db-config.php';
    $base_url="http://".$_SERVER['HTTP_HOST']."/";
    $course_id=intval($_POST['course_id']);
   
    if (isset($_POST['semester']) ) {
        $semester = intval($_POST['semester']);

        $Syllabi = "SELECT Sub_Courses.ID,Sub_Courses.Mode_Id,Sub_Courses.Min_Duration, Modes.Name as mode ,Syllabi.Name,Syllabi.ID as subject_id from Syllabi  LEFT JOIN Sub_Courses ON Syllabi.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Modes ON Sub_Courses.Mode_Id = Modes.ID  WHERE Syllabi.Sub_Course_ID = $course_id AND Syllabi.Semester=$semester";
        $Syllabi = mysqli_query($conn, $Syllabi);
        $htmlReturnData ='<option value="">Select Subject</option>';
        $subjectData=array();
        while ($row = mysqli_fetch_assoc($Syllabi)) {
        $subjectData[]=$row;
        $htmlReturnData .= '<option value="'.$row['subject_id'].'">'.$row['Name'].'</option>';
        
        }

        if(count($subjectData)>0){
            echo $htmlReturnData; die;
        }else{
            echo 0; die;
        }

    }
        
    ?>
