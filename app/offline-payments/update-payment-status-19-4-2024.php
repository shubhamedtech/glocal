<?php
if (isset($_POST['id']) && isset($_POST['value'])) {
  require '../../includes/db-config.php';
  session_start();

  $id = intval($_POST['id']);
  $value = intval($_POST['value']);

  $payment = $conn->query("SELECT * FROM Payments WHERE Type = 1 AND ID  = $id");
  $payment = $payment->fetch_assoc();
  $student_id = $payment['Added_For'];


  $update = $conn->query("UPDATE Payments SET Status = $value, Approved_By = " . $_SESSION['ID'] . ", Approved_On = now() WHERE ID = $id");
  if ($update && $value == 1) {

    if (!empty($student_id)) {
      // Add to Ledger

      $student = $conn->query("SELECT Duration, University_ID FROM Students WHERE ID = $student_id");
      $student = $student->fetch_assoc();
      // echo "INSERT INTO Student_Ledgers (Student_ID, Duration, Date, University_ID, Type, Source, Transaction_ID, Fee, Status) VALUES ($student_id, '" . $student['Duration'] . "', '" . date("Y-m-d", strtotime($payment['Transaction_Date'])) . "', " . $student['University_ID'] . ", 2, 'Offline', '" . $payment['Transaction_ID'] . "', '" . json_encode(['Paid' => $payment['Amount']]) . "', 1)"; die;
      $add = $conn->query("INSERT INTO Student_Ledgers (Student_ID, Duration, Date, University_ID, Type, Source, Transaction_ID, Fee, Status) VALUES ($student_id, '" . $student['Duration'] . "', '" . date("Y-m-d", strtotime($payment['Transaction_Date'])) . "', " . $student['University_ID'] . ", 2, 'Offline', '" . $payment['Transaction_ID'] . "', '" . json_encode(['Paid' => $payment['Amount']]) . "', 1)");
      if (!$add) {
        exit(json_encode(['status' => 400, 'message' => 'Something went wrong!']));
      }

      // Check Balance
      $balance = 0;
      $ledgers = $conn->query("SELECT * FROM Student_Ledgers WHERE Student_ID = $student_id AND Status = 1 AND Duration <= '" . $student['Duration'] . "'");
      while ($ledger = $ledgers->fetch_assoc()) {
        // echo "<pre>";
        // print_r($ledger);
        if ((int)$ledger['Fee']) {
          $fees =  $ledger['Fee'];
          $debit = $ledger['Type'] == 1 ? $fees : 0;
          $credit = $ledger['Type'] == 2 ? $fees : 0;
          $balance = ($balance + $credit) - $debit;
        } else {

          $fees = json_decode($ledger['Fee'], true);
          foreach ($fees as $key => $value) {
            $debit = $ledger['Type'] == 1 ? $value : 0;
            $credit = $ledger['Type'] == 2 ? $value : 0;
            $balance = ($balance + $credit) - $debit;
          }
        }
      }

      if ($balance >= 0) {
        $conn->query("UPDATE Students SET Payment_Received = now() WHERE ID = $student_id");
      }
    }  elseif ($payment['Source'] == 'Re-Reg') {
      $updateRRStatus = $conn->query("UPDATE Re_Registrations SET Status = 1 WHERE Payment_ID = " . $payment['ID']);
      $reRegistrations = $conn->query("SELECT Student_ID, Duration, Amount FROM Re_Registrations WHERE Payment_ID = ".$payment['ID']);
      while($reRegistration = $reRegistrations->fetch_assoc()){
        $add = $conn->query("INSERT INTO Student_Ledgers (Student_ID, Duration, Date, University_ID, Type, Source, Transaction_ID, Fee, Status) VALUES (" . $reRegistration['Student_ID'] . ", " . $reRegistration['Duration'] . ", '" . date("Y-m-d", strtotime($payment['Transaction_Date'])) . "', " . $student['University_ID'] . ", 2, 'Offline', '" . $payment['Transaction_ID'] . "', '" . json_encode(['Paid' => $reRegistration['Amount']]) . "', 1)");
      }
    }else {
     // echo "SELECT Student_ID, Duration, Amount, University_ID, Created_At FROM Invoices WHERE Invoice_No = '" . $payment['Transaction_ID'] . "'";die;
      $students = $conn->query("SELECT Student_ID, Duration, Amount, University_ID, Created_At FROM Invoices WHERE Invoice_No = '" . $payment['Transaction_ID'] . "'");
      while ($student = $students->fetch_assoc()) {
        $add = $conn->query("INSERT INTO Student_Ledgers (Student_ID, Duration, Date, University_ID, Type, Source, Transaction_ID, Fee, Status) VALUES (" . $student['Student_ID'] . ", " . $student['Duration'] . ", '" . date("Y-m-d", strtotime($payment['Transaction_Date'])) . "', " . $student['University_ID'] . ", 2, 'Offline', '" . $payment['Transaction_ID'] . "', '" . json_encode(['Paid' => (-1) * $student['Amount']]) . "', 1)");
        $update = $conn->query("UPDATE Students SET Process_By_Center = '" . $student['Created_At'] . "' WHERE ID = " . $student['Student_ID']);
        if (!$add) {
          exit(json_encode(['status' => 400, 'message' => 'Something went wrong!']));
        }
      }
    }

    echo json_encode(['status' => 200, 'message' => 'Payment status updated successfully!']);
  } else if ($update) {
    echo json_encode(['status' => 200, 'message' => 'Payment status updated successfully!']);
  } else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
  }
}
