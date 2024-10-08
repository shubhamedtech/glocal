<?php
if (isset($_POST['value'], $_POST['university_id'])) {
  require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
  $value = mysqli_real_escape_string($conn, $_POST['value']);
  $universityId = intval($_POST['university_id']);

  if ($value == 'Fresh') {
    $admission_sessions = $conn->query("SELECT ID, Admission_Sessions.Name FROM Admission_Sessions WHERE Admission_Sessions.Status = 1 AND University_ID = $universityId");
    while ($admission_session = $admission_sessions->fetch_assoc()) { ?>
      <option value="<?= $admission_session['ID'] ?>"><?= $admission_session['Name'] ?></option>
    <?php }
  } else {
    $examSessions = $conn->query("SELECT ID, Name FROM Exam_Sessions WHERE RR_Status = 1");
    while ($examSession = $examSessions->fetch_assoc()) { ?>
      <option value="<?= $examSession['ID'] ?>"><?= $examSession['Name'] ?></option>
<?php }
  }
}
