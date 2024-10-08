<?php
if (isset($_POST['ids']) && isset($_POST['center'])) {
  require '../../../includes/db-config.php';
  require '../../../includes/helpers.php';
  session_start();

  $center = intval($_POST['center']);
  $ids = is_array($_POST['ids']) ? array_filter($_POST['ids']) : [];
  $by = $_POST['by'];

  if (empty($ids)) {
    exit(json_encode(['status' => false, 'message' => 'Please select student!']));
  }

  $invoice_no = strtoupper(uniqid('IN'));

  $balance = array();
  foreach ($ids as $id) {
    $counter = 1;
    $fees = $conn->query("SELECT Fee, Center_Fee FROM Student_Ledgers LEFT JOIN Students ON Student_Ledgers.Student_ID = Students.ID WHERE Student_Ledgers.Student_ID = $id AND Student_Ledgers.Duration = Students.Duration AND Student_Ledgers.Type = 1");
    while($fee = $fees->fetch_assoc()) {
      
      $balance[$id][] = $_SESSION['Role'] == 'Sub-Center' ? $fee['Fee'] : (!empty($fee['Center_Fee']) ? $fee['Center_Fee'] : $fee['Fee']);

    if($counter==1){
      $student = $conn->query("SELECT Students.Admission_Session_ID, Students.Created_At, Students.University_ID, Students.Added_For, Users.Code, Users.Role FROM Students LEFT JOIN Users ON Students.Added_For = Users.ID WHERE Students.ID = $id");
      if ($student->num_rows > 0) {
        $student = $student->fetch_assoc();

        // Late Fee
        $startDate = date("Y-m-d");
        $lateFees = $conn->query("SELECT End_Date, Fee, Exception, Admission_Session FROM Late_Fees WHERE University_ID = " . $student['University_ID'] . " AND Start_Date <= '$startDate' AND Status = 1 AND For_Students = 'Fresh' AND IsLateFee = 1 ORDER BY ID DESC");
        while ($lateFee = $lateFees->fetch_assoc()) {
          if (!empty($lateFee['End_Date']) && $lateFee['End_Date'] < $startDate) {
            continue;
          }

          $exceptions = !empty($lateFee['Exception']) ? json_decode($lateFee['Exception'], true) : array();
          if (!empty($exceptions) && in_array($_SESSION['Code'], $exceptions)) {
            continue;
          }

          $admissionSessions = json_decode($lateFee['Admission_Session'], true);
          if (!in_array($student['Admission_Session_ID'], $admissionSessions)) {
            continue;
          }

          $balance[$id][] = $lateFee['Fee'];
        }
      }
    }
    $counter++;
    }
  }

  $totalAmount = 0;
  foreach($balance as $amount){
      $totalAmount += array_sum($amount);
  }

  $amount = $totalAmount;
  $amount = $amount < 0 ? (-1) * $amount : $amount;

  if ($by == 'wallet') {
    $walletAmounts = $conn->query("SELECT sum(Amount) as total_amt FROM Wallets WHERE Added_By = " . $_SESSION['ID'] . " AND Status = 1");
    $walletAmounts = $walletAmounts->fetch_assoc();
    $debited_amount = 0;
    $debit_amts = $conn->query("SELECT sum(Amount) as debit_amt FROM Wallet_Payments WHERE Added_By = " . $_SESSION['ID'] . " AND Type = 3");
    if ($debit_amts->num_rows > 0) {
      $debit_amt = $debit_amts->fetch_assoc();
      $debited_amount = $debit_amt['debit_amt'];
    }

    $walletAmount = $walletAmounts['total_amt'] - $debited_amount;

    if ($walletAmount < $amount) {
      $conn->close();
      exit(json_encode(['status' => false, 'message' => 'Wallet balance insufficient!']));
    }
  }

  echo json_encode(['status' => true, 'amount' => $amount, 'studentCount' => count($balance), 'ids' => array_keys($balance)]);
}
