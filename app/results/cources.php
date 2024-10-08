<?php
// get courses
  if(isset($_POST['program_id'])) {

    require '../../includes/db-config.php';
    $id = intval($_POST['program_id']);
  ?>
  <option value="">Choose</option>
  <?php
  $courses = $conn->query("SELECT ID, CONCAT(Name, ' (',Short_Name, ')') as Name,Scheme_ID, University_ID FROM Sub_Courses WHERE Course_ID=$id AND Status=1 ORDER BY Name ASC");
    while($course = $courses->fetch_assoc()){ 
  ?>
      <option value='<?php echo $course['ID'].'|'.$course['Scheme_ID'].'|'.$course['University_ID'] ?>'><?php echo $course['Name'] ?></option>
  <?php
    }
  }
 
