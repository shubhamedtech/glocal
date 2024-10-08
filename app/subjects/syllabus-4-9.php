<?php
require '../../includes/db-config.php';
session_start();

if ((isset($_GET['course_id']) && isset($_GET['semester']) && empty($_GET['lms'])) || (empty($_GET['lms']) && isset($_GET['duration']) && isset($_GET['course_id']) && $_GET['duration'] != 'undefined')) {
  $sub_course_id = intval($_GET['course_id']);
  $duration = isset($_GET['duration']) ? $_GET['duration'] : '';
  $course_category = isset($_GET['course_category']) ? $_GET['course_category'] : '';

  if ($duration == 'advance_diploma' && ($course_category == 'advance_diploma' || $course_category == '11/advanced')) {
    $duration = "11/advanced";
  } else if ($duration == 'certified' && ($course_category == 'certified' || $course_category == '6/certified')) {
    $duration = "6/certified";
  } else if ($duration == 'certification' && ($course_category == 'certification' || $course_category == '3')) {
    $duration = "3";
  }
  // echo $duration; die;
  if ($duration != null) {

    $syllabus = $conn->query("SELECT * FROM Syllabi WHERE Semester ='" . $duration . "' AND Sub_Course_ID = $sub_course_id");
  } else {
    $semester = explode("|", $_GET['semester']);
    $scheme = $semester[0];
    $semester = $semester[1];
    $syllabus = $conn->query("SELECT * FROM Syllabi WHERE Sub_Course_ID = $sub_course_id AND Scheme_ID = $scheme AND Semester = $semester");
  }
  // print_r($syllabus);die;
?>
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Credit</th>
            <th>Paper Type</th>
            <th>Min/Max Marks</th>
            <th>Syllabus</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($syllabus->num_rows > 0) {
            while ($row = $syllabus->fetch_assoc()) { ?>
              <tr>
                <td>
                  <?= $row['Code'] ?>
                </td>
                <td>
                  <?= $row['Name'] ?>
                </td>
                <td>
                  <?= $row['Credit'] ?>
                </td>
                <td>
                  <?= $row['Paper_Type'] ?>
                </td>
                <td>
                  <?= $row['Min_Marks'] ?>/
                  <?= $row['Max_Marks'] ?>
                </td>
                <td>
                  <?php if (!is_null($row['Syllabus']) && !empty($row['Syllabus'])) {
                    $files = explode("|", $row['Syllabus']);
                    foreach ($files as $file) { ?>
                      <a href="<?= $file ?>" target="_blank" download="<?= $row['Code'] ?>">Download</a>
                  <?php }
                  }
                  if (is_null($row['Syllabus']) && $_SESSION['Role'] == "Student") {
                    echo "NA";
                  }
                  ?>
                  <?php if (in_array($_SESSION['Role'], ['Administrator', 'University Head', 'Academic'])) { ?>
                    <div class="d-flex">
                      Upload (
                      <span class="text-primary cursor-pointer" onclick="uploadFile('Syllabi', 'Syllabus', <?= $row['ID'] ?>)">PDF</span> /
                      <span class="text-primary cursor-pointer" onclick="uploadFile('Syllabi', 'Syllabus', <?= $row['ID'] ?>)">Video</span>
                      )
                    </div>
                  <?php } ?>
                </td>
              </tr>
            <?php }
          } else { ?>
            <tr>
              <td colspan='6' style="text-align:center">No data available in table</td>
            </tr>
          <?php } ?>

        </tbody>
      </table>
    </div>
  </div>
  <?php }
   else if (isset($_GET['course_id']) && isset($_GET['semester']) && $_GET['lms'] == "lms") {
  $sub_course_ids = intval($_GET['course_id']);
  $semesterArr = explode("|", $_GET['semester']);
  $schemes = $semesterArr[0];
  $semesters = $semesterArr[1];
 // echo "SELECT Name,ID FROM Syllabi WHERE Sub_Course_ID = $sub_course_ids AND Scheme_ID = $schemes AND Semester = $semesters"; die;
  $syllabus_query = $conn->query("SELECT Name,ID FROM Syllabi WHERE Sub_Course_ID = $sub_course_ids AND Scheme_ID = $schemes AND Semester = $semesters");
  $bg_colors = array("0" => "bg-yellow-gradient", "1" => "bg-purple-gradient", "2" => "bg-green-gradient", "3" => "bg-aqua-gradient", "4" => "bg-red-gradient", "5" => "bg-aqua-gradient", "6" => "bg-maroon-gradient", "7" => "bg-teal-gradient", "8" => "bg-blue-gradient");
  $colorIndex = 0;
  
  while ($rows = $syllabus_query->fetch_assoc()) {
    $clr = $bg_colors[$colorIndex % count($bg_colors)];
    $colorIndex++;

  ?>
    <div class="col-md-3">
      <div class="card info-box p-0">
        <a href="/student/lms/e-books">
          <div class="card-img-top <?= $clr ?>">
            <p class="subject-name"><?= $rows['Name']; ?> </p>
          </div>
        </a>
        <div class="card-footer">
          <div class="row justify-content-between align-items-center">
            <?php 
                  $ebook_query = $conn->query("SELECT ID FROM e_books WHERE subject_id  = '" . $rows['ID'] . "'AND Course_ID='" . $_SESSION['Sub_Course_ID'] . "'");
                  $video_query = $conn->query("SELECT ID FROM video_lectures WHERE subject_id  = '" . $rows['ID'] . "'AND Course_ID='" . $_SESSION['Sub_Course_ID'] . "' and Status = 1");
                  $video_count = ($video_query->num_rows > 0) ? $video_query->num_rows : 0;
                  $ebook_count = ($ebook_query->num_rows > 0) ? $ebook_query->num_rows : 0;
            ?>
            <div class="col-md-4 text-center">
              <a href="/student/lms/e-books"><i class="ti-book mr-2"></i><span><?=  $ebook_count ?></span></a>
            </div>
            <div class="col-md-4 text-center">
              <a href="/student/lms/videos"><i class="ti- ti-video-clapper mr-2"></i><span><?= $video_count ?></span></a>
            </div>
            <div class="col-md-4 text-center">
              <a href="/student/lms/assignments"><i class=" ti-clipboard mr-2"></i><span>0</span></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php }  ?>

  <?php } else {
    $course_sql='';
    if(isset($_GET['id'])){
      $course_sql = ' AND Sub_Course_ID ='.$_GET['id'];
    }
     $syllabuss = $conn->query("SELECT * FROM Syllabi WHERE University_ID = " . $_SESSION['university_id'] . "  $course_sql " );
   ?>
    <div class="col-md-12">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Credit</th>
              <th>Paper Type</th>
              <th>Min/Max Marks</th>
              <th>Syllabus</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $syllabuss->fetch_assoc()) {

            ?>
              <tr>
                <td>
                  <?= $row['Code'] ?>
                </td>
                <td>
                  <?= $row['Name'] ?>
                </td>
                <td>
                  <?= $row['Credit'] ?>
                </td>
                <td>
                  <?= $row['Paper_Type'] ?>
                </td>
                <td>
                  <?= $row['Min_Marks'] ?>/
                  <?= $row['Max_Marks'] ?>
                </td>
                <td>
                  <?php if (!is_null($row['Syllabus']) && !empty($row['Syllabus'])) {
                    $files = explode("|", $row['Syllabus']);
                    foreach ($files as $file) { ?>
                      <a href="<?= $file ?>" target="_blank" download="<?= $row['Code'] ?>">Download</a>
                  <?php }
                  } ?>
                  <?php if (in_array($_SESSION['Role'], ['Administrator', 'University Head', 'Academic'])) { ?><span class="text-primary cursor-pointer" onclick="uploadFile('Syllabi', 'Syllabus', <?= $row['ID'] ?>)">Upload</span>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
<?php } ?>