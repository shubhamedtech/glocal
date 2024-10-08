<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<?php date_default_timezone_set("Asia/Kolkata"); ?>
<!-- START PAGE-CONTAINER -->
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <!-- START PAGE CONTENT WRAPPER -->
  <div class="page-content-wrapper ">
    <!-- START PAGE CONTENT -->
    <div class="content ">
      <!-- START JUMBOTRON -->
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i) : $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                <?php if (in_array($_SESSION['Role'], ['Administrator', 'University Head'])) { ?>
                  <button class="btn btn-link" aria-label="" title="" data-toggle="tooltip" data-original-title="Upload" onclick="upload('datesheets', 'md')"> <i class="uil uil-export"></i></button>
                <?php } ?>
              </div>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class=" container-fluid">
        <!-- BEGIN PlACE PAGE CONTENT HERE -->
        <div class="row">
          <table class="table table-striped">
            <thead class="text-center">
              <tr>
                <th>Student Name</th>
                <th>Student Photo</th>
                <th>Paper Name</th>
                <th>Attendance</th>
                <th>Attempted / Total Questions</th>
                <th>Correct Ans</th>
                <th>Submited In</th>
                <th>Exam Time</th>
                <th>Result</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <?php
                ini_set('display_errors', 1);
              if (isset($_GET['sub_course_id'])) {
                $sub_course_id = intval($_GET['sub_course_id']);
                $date_sheet_id = intval($_GET['date_id']);
                $syllb_id = intval($_GET['syllb_id']);
              
                $_SESSION['subii_id'] = $sub_course_id;
                $exam_results = $conn->query("SELECT Exam_Students.*, Syllabi.Name as  Syllabi_name FROM Exam_Students LEFT JOIN Syllabi ON Exam_Students.Sub_Course = Syllabi.Sub_Course_ID WHERE Sub_Course = '" . $_SESSION['subii_id'] . "'");
                $total_question = 0;
                $attempt_question = 0;
                $correct = 0;
                $img_name = "NA";
                while ($exam_result = $exam_results->fetch_assoc()) {
                  $exam_attend = $conn->query("SELECT * FROM Exam_Attempts_By_Exam_Students WHERE Student_ID = '" . $exam_result['ID'] . "' AND Date_Sheet_ID = '".$date_sheet_id."' LIMIT 1");
                  $start = $exam_attend->fetch_assoc();
                  if (empty($start)) {
                    $start['Start_Time'] = 'Not Attend';
                  }

                  $exam_submit = $conn->query("SELECT * FROM Exam_Students_Final_Submit WHERE Student_ID = '" . $exam_result['ID'] . "' LIMIT 1");
                  $end = $exam_submit->fetch_assoc();
                  if (empty($end)) {
                    $end['Submited_At'] = 'NA';
                  } else {
                    $start_at = date("H:i:s", strtotime($start['Start_Time']));
                    $time1 = new DateTime($start_at);
                    $time2 = new DateTime($end['Submited_At']);
                    $interval = $time1->diff($time2);
                    $end['Submited_At'] =  $interval->format('%H : %i : %s');
                  }

                  $total_questions =  $conn->query("SELECT * FROM Exam_Students_Answers WHERE Student_ID = '" . $exam_result['ID'] . "' AND Date_Sheet_ID = '".$date_sheet_id."' AND Syllabus_ID = '".$syllb_id."' ");
                  $attempt_question =  $conn->query("SELECT * FROM Exam_Students_Answers WHERE Student_ID = '" . $exam_result['ID'] . "' AND Date_Sheet_ID = '".$date_sheet_id."' AND Syllabus_ID = '".$syllb_id."' AND Answer IS NOT NULL");

                  if ($total_questions->num_rows > 0) {
                    while ($total_question = $total_questions->fetch_assoc()) {
                      $correct_answers = $conn->query("SELECT * FROM MCQs WHERE ID = '" . $total_question['Question_ID'] . "'");
                      while ($correct_answer = $correct_answers->fetch_assoc()) {
                        if ($correct_answer['Answer'] == $total_question['Answer']) {
                          $correct = $correct + 1;
                        }
                      }
                    }
                  } else {
                    $correct = 0;
                  }
                  $img_name = "NA";
                  $photo =  $conn->query("SELECT * FROM Webcam_Student_Pic WHERE Student_ID = '" . $exam_result['ID'] . "' ");
                  if($photo->num_rows > 0){
                    $img_name = $photo->fetch_assoc()['Photo'];
                  }
              ?>
                  <tr>
                    <td><?= $exam_result['Name'] ?></td>
                    <td><img src="../uploads/webphoto/<?= $img_name ?>" class="img-fluid" width="60"></td>
                    <td><?= $exam_result['Syllabi_name'] ?></td>
                    <td><?= $exam_attend->num_rows > 0 ? "Attend" : "Not Attend" ?> </td>
                    <td><?= ($total_questions->num_rows > 0) ? $attempt_question->num_rows . " / " . $total_questions->num_rows : "NA" ?></td>
                    <td><?= $correct ?></td>
                    <td><?= $end['Submited_At'] ?></td>
                    <td><?= $start['Start_Time'] ?></td>
                    <td><button onclick="getStudentResults(<?= $exam_result['ID'] ?>, '<?=$exam_result['Name']?>')" class="btn btn-primary">View Result</button></td>
                  </tr>
              <?php }
              } ?>
            </tbody>
          </table>
        </div>
        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
    <script>
      function getStudentResults(id, name) {
        var exam_id = id;
        window.location.replace('one-student-result?id=' + exam_id +'&&name='+name);
      }
    </script>