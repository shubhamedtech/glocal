<?php
// get courses
  if(isset($_GET['course_id'])) {

    require '../../includes/db-config.php';
    session_start();
    $id = intval($_GET['course_id']);
  ?>
  <option value="">Choose</option>
  <?php
  $courses = $conn->query("SELECT ID, CONCAT(Name, ' (',Short_Name, ')') as Name ,Min_Duration FROM Sub_Courses WHERE Course_ID=$id AND Status=1 AND University_ID = '".$_SESSION['university_id']."' ORDER BY Name ASC");
    while($course = $courses->fetch_assoc()){ 
  ?>
      <option value='<?php echo $course['ID'] ?>'><?php echo $course['Name'] ?></option>
  <?php
    }
  }
 
