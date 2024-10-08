<?php
date_default_timezone_set('Asia/Kolkata');
foreach ($ids as $id) {
  $student = $conn->query("SELECT Admission_Session_ID, Duration, Added_For, Sub_Course_ID, Course_ID, University_ID FROM Students WHERE ID = $id AND University_ID = " . $_SESSION['university_id']);
  if ($student->num_rows > 0) {
    $student = $student->fetch_assoc();
    $addedFor = $student['Added_For'];
    $courseId = $student['Course_ID'];
    $subCourseId = $student['Sub_Course_ID'];
    $universityId = $student['University_ID'];
    $duration = $student['Duration'] + 1;

    $check = $conn->query("SELECT ID FROM Re_Registrations WHERE Student_ID = $id AND Exam_Session_ID = " . $_SESSION['active_rr_session_id'] . " AND Duration = $duration AND University_ID = " . $_SESSION['university_id']);
    if ($check->num_rows > 0) {
      continue;
    }

    $fee = $conn->query("SELECT Fee, Center_Fee FROM Student_Ledgers WHERE Student_ID = $id AND Duration = $duration AND Type = 1 AND Source IS NULL");
    if ($fee->num_rows == 0) {
      continue;
    }

    $fee = $fee->fetch_assoc();
    $totalFee[$id] = $_SESSION['Role'] == 'Sub-Center' ? $fee['Fee'] : (!empty($fee['Center_Fee']) ? $fee['Center_Fee'] : $fee['Fee']);

    // Late Fee
    $startDate = date("Y-m-d");
    $lateFees = $conn->query("SELECT ID, End_Date, Fee, Exception, Admission_Session FROM Late_Fees WHERE University_ID = " . $student['University_ID'] . " AND Start_Date <= '$startDate' AND Status = 1 AND For_Students = 'Re-Reg' AND IsLateFee = 1 ORDER BY ID DESC");
    while ($lateFee = $lateFees->fetch_assoc()) {
      if (!empty($lateFee['End_Date']) && $lateFee['End_Date'] < $startDate) {
        continue;
      }

      $exceptions = !empty($lateFee['Exception']) ? json_decode($lateFee['Exception'], true) : array();
      if (!empty($exceptions) && in_array($_SESSION['Code'], $exceptions)) {
        continue;
      }

      $admissionSessions = json_decode($lateFee['Admission_Session'], true);
      if (!in_array($_SESSION['active_rr_session_id'], $admissionSessions)) {
        continue;
      }

      $totalFee[$id] = $totalFee[$id] + $lateFee['Fee'];
    }
  }
}
