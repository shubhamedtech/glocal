<?php
session_start();
require '../../../includes/db-config.php';

if (isset($_SESSION['university_id'])) {
  $messages = array();
  $lateFees = $conn->query("SELECT Fee, Start_Date, End_Date, For_Students, Admission_Session FROM Late_Fees WHERE Status = 1 AND Show_Popup = 1 AND University_ID = " . $_SESSION['university_id']);
  while ($lateFee = $lateFees->fetch_assoc()) {
    if (!empty($lateFee['End_Date']) && $lateFee['End_Date'] < date('Y-m-d')) {
      continue;
    }

    $sessionNames = array();
    $admissionSessionIds = json_decode($lateFee['Admission_Session'], true);
    if ($lateFee['For_Students'] == 'Fresh') {
      $admissionSessions = $conn->query("SELECT ID, Name FROM Admission_Sessions WHERE Status = 1 AND ID IN (" . implode(',', $admissionSessionIds) . ") AND University_ID = " . $_SESSION['university_id']);
      while ($admissionSession = $admissionSessions->fetch_assoc()) {
        $sessionNames[] = $admissionSession['Name'];
      }
    } elseif ($lateFee['For_Students'] == 'Re-Reg') {
      $examSessions = $conn->query("SELECT ID, Name FROM Exam_Sessions WHERE ID IN (" . implode(',', $admissionSessionIds) . ") AND University_ID = " . $_SESSION['university_id']);
      while ($examSession = $examSessions->fetch_assoc()) {
        $sessionNames[] = $examSession['Name'];
      }
    }

    $sessionNames = join(' and ', array_filter(array_merge(array(join(', ', array_slice($sessionNames, 0, -1))), array_slice($sessionNames, -1)), 'strlen'));
    $endDate = !empty($lateFee['End_Date']) ? " to " . date("d-m-Y", strtotime($lateFee['End_Date'])) : "";
    $messages[] = "<b>" . $lateFee['Fee'] . "</b> late fee is applicable on " . $lateFee['For_Students'] . " Students of Session: " . $sessionNames . " from " . date("d-m-Y", strtotime($lateFee['Start_Date'])) . $endDate;
  }

  if (!empty($messages)) {
    $lateFeeList = "";
    foreach ($messages as $message) {
      $lateFeeList .= '<li style="font-size: 15px">' . $message . '</li>';
    }

    $body = '<div class="modal-header clearfix text-left">
      <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
      </button>
      <h6><span class="semi-bold">Notice</span></h6>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <h4>Late Fee</h4>
          <ul>
            ' . $lateFeeList . '
          </ul>
        </div>
      </div>
    </div>';
    $_SESSION['lateFeeNotice'] = 1;
    echo json_encode(['status' => true, 'body' => $body]);
  } else {
    echo json_encode(['status' => false]);
  }
}

$conn->close();
