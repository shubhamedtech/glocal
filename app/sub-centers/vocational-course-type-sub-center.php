<?php
ini_set('display_errors', 1);

if (isset($_GET['id']) && isset($_GET['university_id'])) {
  require '../../includes/db-config.php';

  $university_id = intval($_GET['university_id']);
  $id = intval($_GET['id']);


  $subcenterQuery = $conn->query("SELECT Code, ID,Role FROM Users WHERE ID=$id AND Role='Sub-Center'");
  $subcenterArr = $subcenterQuery->fetch_assoc();
  $subcentercode = explode('.', $subcenterArr["Code"]);
  $centerCode = $subcentercode[0];
  $centerQuery = $conn->query("SELECT ID, Code, Role FROM Users WHERE Code='$centerCode' AND Role='Center'");
  $centerArr = $centerQuery->fetch_assoc();
  if ($university_id == 48) {
    $course_id = $conn->query("SELECT Course_ID, Sub_Course_ID FROM Center_Sub_Courses WHERE `User_ID` = " . $centerArr['ID'] . " AND University_ID = $university_id GROUP BY Sub_Course_ID");

  } else {
    $course_id = $conn->query("SELECT Course_ID, Sub_Course_ID FROM Center_Sub_Courses WHERE `User_ID` = " . $centerArr['ID'] . " AND University_ID = $university_id");
  }
  while ($courseIdArr = $course_id->fetch_assoc()) {

    $subCourseId = $courseIdArr['Sub_Course_ID'];
    $courseId = $courseIdArr['Course_ID'];
    $subCourseQuery = $conn->query("SELECT ID, Name, Course_ID, University_ID, Min_Duration as durections FROM Sub_Courses WHERE `ID` = $subCourseId AND `Course_ID` = $courseId AND University_ID = $university_id");
    $subCourseArr = $subCourseQuery->fetch_assoc();
    $subCourseData[] = $subCourseArr;
  }

  $courseTypeQuery = $conn->query("SELECT Course_Type_ID FROM Courses WHERE ID=$courseId AND Status =1");
  $courseTypeId = $courseTypeQuery->fetch_assoc();

  $user_id = $centerArr['ID'];

  $type_ids = $courseTypeId['Course_Type_ID'];
  if (empty($type_ids)) {
    exit;
  }
  $fees = [];
  if ($university_id == 48) {
    //$sub_courses = $conn->query("SELECT Sub_Courses.ID, CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') AS Sub_Course, Sub_Courses.Min_Duration as durections FROM Sub_Courses LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Courses.Course_Type_ID IN ($type_ids) AND Sub_Courses.Status = 1 ");   ?>

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
    // while ($sub_course = $sub_courses->fetch_assoc()) {
    foreach ($subCourseData as $sub_course) {
      // Reset $fees array for each subcourse
      $fees = [];

      ?>
      <div class="row pb-2">
        <div class="col-md-3">
          <dt class="pt-1">
            <?= $sub_course['Name'] ?>
          </dt>
        </div>
        <?php
        $alloted_fees = $conn->query("SELECT Fee, Sub_Course_ID, Duration FROM Sub_Center_Sub_Courses WHERE `User_ID` = $id AND `University_ID` = $university_id");
        while ($alloted_fee = $alloted_fees->fetch_assoc()) {
          $fees[$alloted_fee['Sub_Course_ID']][$alloted_fee['Duration']] = $alloted_fee['Fee'];
        }
        

        if (count(json_decode($sub_course['durections'], true)) > 0) {
          foreach (json_decode($sub_course['durections'], true) as $indx => $drf) {
            ?>
            <div class="col-md-2">
              <?php
              $subCourseID = $sub_course['ID'];
              $defaultValue = isset($fees[$subCourseID]) && isset($fees[$subCourseID][$drf]) ? $fees[$subCourseID][$drf] : '';
              ?>
              <input type="number" min="0" step="500" placeholder="Fee" name="fee[<?= $subCourseID ?>][<?= $drf ?>]"
                value="<?= $defaultValue ?>" class="form-control" />
              <input type="hidden" id="course_type" name="course_type[]" value="<?= $type_ids ?>">
            </div>
            <?php
          }
        } else { ?>
          <input type="number" min="0" step="500" placeholder="Fee" name="fee[<?= $sub_course['ID'] ?>]"
            value="<?php echo array_key_exists($sub_course['ID'], $fees) ? $fees[$sub_course['ID']] : '' ?>"
            class="form-control" />
        <?php } ?>
      </div>
      <?php
    }
  } else {

    $alloted_fees = $conn->query("SELECT Fee, Sub_Course_ID FROM Sub_Center_Sub_Courses WHERE `User_ID` = $id AND `University_ID` = $university_id");


    while ($alloted_fee = $alloted_fees->fetch_assoc()) {
      $fees[$alloted_fee['Sub_Course_ID']] = $alloted_fee['Fee'];
    }
    // $sub_courses = $conn->query("SELECT Sub_Courses.ID, CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') AS Sub_Course FROM Sub_Courses LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Courses.Course_Type_ID IN ($type_ids)");


    //  print_r($sub_courses);
    //  while ($sub_course = $sub_courses->fetch_assoc()){
    foreach ($subCourseData as $sub_course) {
      ?>
      <div class="row pb-2">
        <div class="col-md-9">
          <dt class="pt-1">
            <?= $sub_course['Name']; ?>
          </dt>
        </div>
        <div class="col-md-3">
          <input type="hidden" id="course_type" name="course_type[]" value="<?= $type_ids ?>">
          <input type="number" min="0" step="500" placeholder="Fee" name="fee[<?= $sub_course['ID'] ?>]"
            value="<?php echo array_key_exists($sub_course['ID'], $fees) ? $fees[$sub_course['ID']] : '' ?>"
            class="form-control" />
        </div>
      </div>
    <?php }
  }
}
