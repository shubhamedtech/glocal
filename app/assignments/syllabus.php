<?php
if (isset($_GET['course_id']) && isset($_GET['semester'])) {
  require '../../includes/db-config.php';
  session_start();
  $sub_course_id = intval($_GET['course_id']);
  $semester = explode("|", $_GET['semester']);
  $scheme = $semester[0];
  $semester = $semester[1];
  $syllabus = $conn->query("SELECT * FROM Syllabi WHERE Sub_Course_ID = $sub_course_id AND Scheme_ID = $scheme AND Semester = $semester AND Paper_Type = 'Theory'");
  // print_r($syllabus);
?>
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Start Date</th>
            <th>End date</th>
            <th>Total Marks</th>
            <th>Obtained Marks</th>
            <th>Assignment</th>
            <th>Upload</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($ro = $syllabus->fetch_assoc()) { ?>
            <tr>
              <td><?= $ro['Code'] ?></td>
              <td><?= $ro['Name'] ?></td>
              <?php
              $subject_id = "";
              $assignmnet = "";
              $sql = "SELECT start_date, end_date ,marks FROM student_assignment where subject_id='' and assignment_id=''";
              $result = $conn->query($sql);
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $startDate = $row["start_date"];
                  $endDate = $row["end_date"];
                  $total_marks = $row['marks'];
                  echo "<td>" . $startDate . "<br></td>";
                  echo "<td>" . $endDate . "<br></td>";
                  echo "<td>" . $total_marks . "</br></td>";
                }
              }
              ?>
              <?php
              $obt = "SELECT obtained_mark FROM submitted_assignment";
              $resu = $conn->query($obt);
              if ($resu->num_rows > 0) {
                while ($re = $resu->fetch_assoc()) {
                  $obt_marks = $re["obtained_mark"];
                  echo "<td>" . $obt_marks . "</td><br>"; // Corrected HTML structure
                }
              }
              ?>
              <td>
                <?php if (!is_null($ro['Assignment']) && !empty($ro['Assignment'])) {
                  $files = explode("|", $ro['Assignment']);
                  foreach ($files as $file) { ?>
                    <button class="btn btn-success"><a href="<?= $file ?>" target="_blank" download="<?= $ro['Code'] ?>">Download Assignment</a></button>
                <?php }
                } ?>
              </td>
              <td>
                <form action="../../app/assignments/upload.php" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="assignment_id" value="<?= $ro['ID'] ?>">
                  <label for="fileInput">Upload File (PDF or Image):</label>
                  <input type="file" name="fileInput" id="fileInput" required accept=".pdf, .jpeg, .jpg">
                  <button type="submit" class="btn btn-primary btn-sm" name="submit">Upload</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
<?php
}


?>