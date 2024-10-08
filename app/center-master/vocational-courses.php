<?php
  ini_set('display_errors', 1); 

if (isset($_GET['ids']) && isset($_GET['user_id']) && isset($_GET['university_id'])) {
  require '../../includes/db-config.php';

  $user_id = intval($_GET['user_id']);
  $university_id = intval($_GET['university_id']);
  $type_ids = mysqli_real_escape_string($conn, $_GET['ids']);
  if (empty($type_ids)) {
    exit;
  }
  $fees = [];
  if ($university_id == 48) {
    $sub_courses = $conn->query("SELECT Sub_Courses.ID, CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') AS Sub_Course, Sub_Courses.Min_Duration as durections FROM Sub_Courses LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Courses.Course_Type_ID IN ($type_ids) AND Sub_Courses.Status = 1 "); ?>
     <div class="row">
    <div class="col-md-3">
     <P>Subject Name</P>
    </div>
    <div class="col-md-2">
      3<sup>rd</sup> Month fee 
    </div>
    <div class="col-md-2">
      6<sup>th</sup> Month fee
    </div>
    <div class="col-md-2">
      11<sup>th</sup> Month Certified fee
    </div>
    <div class="col-md-2">
      11<sup>th</sup> Month Advance fee
    </div>
    </div>
<?php
   while ($sub_course = $sub_courses->fetch_assoc()) { 
    // Reset $fees array for each subcourse
    $fees = [];

    ?>
    <div class="row pb-2">
        <div class="col-md-3">
            <dt class="pt-1"><?= $sub_course['Sub_Course'] ?></dt>
        </div>
        <?php 
        $alloted_fees = $conn->query("SELECT Fee, Sub_Course_ID, Duration FROM Center_Sub_Courses WHERE `User_ID` = $user_id AND `Sub_Course_ID` = ".$sub_course['ID']." AND `University_ID` = $university_id");
        while ($alloted_fee = $alloted_fees->fetch_assoc()) {
          $fees[$alloted_fee['Sub_Course_ID']][$alloted_fee['Duration']] = $alloted_fee['Fee'];
        }

        if(count(json_decode($sub_course['durections'])) > 0) { 
            foreach(json_decode($sub_course['durections']) as $indx => $drf) {
                ?>
                <div class="col-md-2">
                    <?php
                    $subCourseID = $sub_course['ID'];
                   // $subCourseID= $alloted_fee['Sub_Course_ID'];
                 //print_r($fees[$subCourseID]);die;
                    // Fetch the corresponding fee for the current subcourse and duration
                    $defaultValue = isset($fees[$subCourseID]) && isset($fees[$subCourseID][$drf]) ? $fees[$subCourseID][$drf] : '';
                    ?>
                    <input type="number" min="0" step="500" placeholder="Fee" name="fee[<?=$subCourseID?>][<?=$drf?>]" value="<?=$defaultValue?>" class="form-control" />
                </div>
                <?php 
            } 
        } else {?>
            <input type="number" min="0" step="500" placeholder="Fee" name="fee[<?= $sub_course['ID'] ?>]" value="<?php echo array_key_exists($sub_course['ID'], $fees) ? $fees[$sub_course['ID']] : '' ?>" class="form-control" />
        <?php } ?>
    </div>
<?php
   }
  } else {
    $alloted_fees = $conn->query("SELECT Fee, Sub_Course_ID FROM Center_Sub_Courses WHERE `User_ID` = $user_id AND `University_ID` = $university_id");
    while ($alloted_fee = $alloted_fees->fetch_assoc()) {
      $fees[$alloted_fee['Sub_Course_ID']] = $alloted_fee['Fee'];
    }

    $sub_courses = $conn->query("SELECT Sub_Courses.ID, CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') AS Sub_Course FROM Sub_Courses LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Courses.Course_Type_ID IN ($type_ids)");
    while ($sub_course = $sub_courses->fetch_assoc()) { ?>
      <div class="row pb-2">
        <div class="col-md-9">
          <dt class="pt-1"><?=  $sub_course['Sub_Course']; ?></dt>
        </div>
        <div class="col-md-3">
          <input type="number" min="0" step="500" placeholder="Fee" name="fee[<?= $sub_course['ID'] ?>]" value="<?php echo array_key_exists($sub_course['ID'], $fees) ? $fees[$sub_course['ID']] : '' ?>" class="form-control" />
        </div>
      </div>
<?php }
  }
}
