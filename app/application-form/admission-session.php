<?php
  if(isset($_GET['university_id'])){
    require '../../includes/db-config.php';
    session_start();

    $university_id = intval($_GET['university_id']);
     if ($_SESSION['Role'] == 'Center') {
        $userId = $_SESSION['ID'];
      } else {
        $userId = intval($_GET['userId']);
      }
    if(!empty($_GET['form'])){
      $status_query = "";
    }else{
      $status_query = " AND Status = 1";
    }
    
    // NSIDC User
    $userIds = array(2071);
    $subCenters = $conn->query("SELECT Sub_Center FROM Center_SubCenter WHERE Center = 2071");
    while($subCenter = $subCenters->fetch_assoc()){
        $userIds[] = $subCenter['Sub_Center'];
    }
    
    if(in_array($userId, $userIds)){
        $status_query .= " AND ID = 95"; 
    }

    $sessions = $conn->query("SELECT ID, Name, Current_Status FROM Admission_Sessions WHERE University_ID = $university_id $status_query");
    if($sessions->num_rows==0){
      echo '<option value="">Please add admission session</option>';
      exit();
    }
    while($session = $sessions->fetch_assoc()){ ?>
      <option value="<?php echo $session['ID'] ?>" <?php print $session['Current_Status']==1 ? 'selected' : '' ?>><?php echo $session['Name'] ?></option>
    <?php }
  }
