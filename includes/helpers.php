<?php
// String Encryption
ini_set('display_errors', 1);

function stringToSecret(string $string = NULL)
{
  if (!$string) {
    return NULL;
  }
  $length = strlen($string);
  $visibleCount = (int) round($length / 6);
  $hiddenCount = $length - ($visibleCount * 2);
  return substr($string, 0, $visibleCount) . str_repeat('*', $hiddenCount) . substr($string, ($visibleCount * -1), $visibleCount);
}

function uuidGenerator($table, $conn)
{
  $all_key = array();
  $get_key = $conn->query("SELECT Api_Key FROM $table");
  while ($gk = $get_key->fetch_assoc()) {
    $all_key[] = $gk['Api_Key'];
  }

  $data = $data ?? random_bytes(16);
  assert(strlen($data) == 16);
  // Set version to 0100
  $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
  // Set bits 6-7 to 10
  $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
  // Output the 36 character UUID.
  $generated_key = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  if (in_array($generated_key, $all_key)) {
    uuidGenerator($table, $conn);
  } else {
    return $generated_key;
  }
}

function generateStudentLedger($conn, $student_id)
{
  $check = $conn->query("SELECT ID FROM Student_Ledgers WHERE Student_ID = $student_id AND Type = 2");
  if ($check->num_rows>0) {
    return true;
  }
   

  $check = $conn->query("SELECT ID FROM Student_Ledgers WHERE Student_ID = $student_id");
  if ($check->num_rows > 0) {
    $conn->query("DELETE FROM Student_Ledgers WHERE Student_ID = $student_id AND Type = 1");
  }

  $student = $conn->query("SELECT Admission_Sessions.Name as Session, Students.Admission_Session_ID, Students.Adm_Duration, Students.University_ID, Students.Duration, Students.Course_ID, Sub_Course_ID, Sub_Courses.Min_Duration, Students.Created_At, Users.ID as Added_For, Users.Role, Users.Code FROM Students LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Users ON Students.Added_For = Users.ID WHERE Students.ID = $student_id");
  $student = mysqli_fetch_assoc($student);

  if (empty($student['Added_For'])) {
    return true;
  }

  $durationQuery = $student['University_ID'] == 48 ? " AND Duration = '" . $student['Duration'] . "'" : "";

  $centerFee = 0;
  if ($student['Role'] == 'Sub-Center') {
    $reporting = $conn->query("SELECT Center FROM Center_SubCenter WHERE Center_SubCenter.Sub_Center = " . $student['Added_For']);
    if ($reporting->num_rows > 0) {
      $reporting = mysqli_fetch_assoc($reporting);
      $center = $reporting['Center'];

      // Fee for Co-ordinator
      $feeForCenter = $conn->query("SELECT Fee FROM Center_Sub_Courses WHERE University_ID = " . $student['University_ID'] . " AND User_ID = $center AND Course_ID = " . $student['Course_ID'] . " AND Sub_Course_ID = " . $student['Sub_Course_ID'] . " $durationQuery");
      if ($feeForCenter->num_rows > 0) {
        $feeForCenter = $feeForCenter->fetch_assoc();
        $centerFee = $feeForCenter['Fee'];
      }
    }
  }
  
  $table = $student['Role'] == 'Sub-Center' ? 'Sub_Center_Sub_Courses' : 'Center_Sub_Courses';

  $studentFee = $conn->query("SELECT Fee FROM $table WHERE User_ID = " . $student['Added_For'] . " AND Course_ID = " . $student['Course_ID'] . " AND Sub_Course_ID = " . $student['Sub_Course_ID'] . " AND University_ID = " . $student['University_ID'] . " $durationQuery");
  if ($studentFee->num_rows > 0) {
    $studentFee = $studentFee->fetch_assoc();
    $studentFee = $studentFee['Fee'];

    $date = date('Y-m-d', strtotime($student['Created_At']));
    if ($student['University_ID'] == 47) {
      
      if ($student['Admission_Session_ID'] <= 76) {
        if (!empty($centerFee)) {
          $centerFee = 4750;
        }
        
        if($student['Role']=='Center'){
         $hasDownline = $conn->query("SELECT ID FROM Users WHERE CanCreateSubCenter = 1 AND ID = ".$student['Added_For']);
         if($hasDownline->num_rows>0){
             $studentFee = 4750;
         }else{
             $studentFee =  5250;
         }
        }else{
            $studentFee =  5250;
        }
      }
      
      if ($student['Admission_Session_ID'] == 95) {
        if (!empty($centerFee)) {
          $centerFee = 28000;
        }
        $studentFee =  28000;
        
         // NSIDC User
        $userIds = array();
        $subCenters = $conn->query("SELECT Sub_Center FROM Center_SubCenter WHERE Center = 2071");
        while($subCenter = $subCenters->fetch_assoc()){
            $userIds[] = $subCenter['Sub_Center'];
        }
        if($student['Role']=='Sub-Center' && in_array($student['Added_For'], $userIds)){
            $studentFee = 38000;
            $centerFee = 38000;
        }
      }
      
      $maxDuration = json_decode($student['Min_Duration'], true);

      $admissionDate = $student['Created_At'];
      $startDuration = !empty($student['Adm_Duration']) ? $student['Adm_Duration'] : $student['Duration'];
      $durations = range($startDuration, $maxDuration);
      $session = $student['Session'];
      $sessionMonth = date("m", strtotime($session));

      $ledgerDates = array();
      $newDate = date("Y-$sessionMonth-01 H:i:s", strtotime($admissionDate));
      foreach ($durations as $duration) {
        if ($duration == $startDuration) {
          $ledgerDates[$duration] = $admissionDate;
          $studentLedgerDate = $admissionDate;
        } else {
          $newDate = $duration == $startDuration + 1 ? $newDate : $ledgerDates[$duration - 1];
          $ledgerDates[$duration] = date("Y-m-01 H:i:s", strtotime("+6 months " . $newDate));
          $studentLedgerDate = date("Y-m-01 H:i:s", strtotime("+6 months " . $newDate));
        }

        $add = $conn->query("INSERT INTO Student_Ledgers (Date, Student_ID, Duration, University_ID, Type, Fee, Fee_Without_Sharing, Status) VALUES ('$studentLedgerDate', $student_id, '$duration'," . $student['University_ID'] . ", 1, '$studentFee', '$studentFee', 1)");
        if ($add && $student['Role'] == 'Sub-Center') {
          // Settlement Amount
          $ledgerId = $conn->insert_id;
          $settlementAmount = $studentFee - $centerFee;
          $update = $conn->query("UPDATE Student_Ledgers SET Settlement_Amount = $settlementAmount, Center_Fee = $centerFee WHERE ID = $ledgerId");
        }
      }
    } else {
      $add = $conn->query("INSERT INTO Student_Ledgers (Date, Student_ID, Duration, University_ID, Type, Fee, Fee_Without_Sharing, Status) VALUES ('$date', $student_id, '" . $student['Duration'] . "', " . $student['University_ID'] . ", 1, '$studentFee', '$studentFee', 1)");
      if ($add && $student['Role'] == 'Sub-Center') {
        // Settlement Amount
        $ledgerId = $conn->insert_id;
        $settlementAmount = $studentFee - $centerFee;
        $update = $conn->query("UPDATE Student_Ledgers SET Settlement_Amount = $settlementAmount, Center_Fee = $centerFee WHERE ID = $ledgerId");
      }
    }

	// Other Fee
	$startDate = date("Y-m-d", strtotime($student['Created_At']));
	$lateFees = $conn->query("SELECT End_Date, Fee, Exception, Admission_Session, Name FROM Late_Fees WHERE University_ID = {$student['University_ID']} AND Start_Date <= '$startDate' AND Status = 1 AND For_Students = 'Fresh' AND IsLateFee <> 1 ORDER BY ID DESC");
	while ($lateFee = $lateFees->fetch_assoc()) {
	  if (!empty($lateFee['End_Date']) && $lateFee['End_Date'] < $startDate) {
		continue;
	  }

	  $exceptions = !empty($lateFee['Exception']) ? json_decode($lateFee['Exception'], true) : array();
	  if (!empty($exceptions) && in_array($student['Code'], $exceptions)) {
		continue;
	  }

	  $admissionSessions = json_decode($lateFee['Admission_Session'], true);
	  if (!in_array($student['Admission_Session_ID'], $admissionSessions)) {
		continue;
	  }

	  $add = $conn->query("INSERT INTO Student_Ledgers (Date, Student_ID, Duration, University_ID, Type, Fee, Fee_Without_Sharing, Source, Status) VALUES ('$date', $student_id, '" . $student['Duration'] . "', " . $student['University_ID'] . ", 1, '" . $lateFee['Fee'] . "', '" . $lateFee['Fee'] . "', '{$lateFee['Name']}', 1)");
	  break;
	}
  }
}


function activityLogs($conn, $message, $user_id)
{
}

function generateLeadHistory($conn, $lead_id, $user_id, $old, $new)
{
  $result = array_diff($old, $new);
  if (!empty($result)) {
    $update = $conn->query("INSERT INTO Lead_Histories (Lead_ID, `User_ID`, Data, Created_By) VALUES ($lead_id, $user_id, '" . json_encode($result) . "', " . $_SESSION['ID'] . ")");
  }
}


function generateStudentID($conn, $suffix, $length, $university_id)
{
  $student_ids = array();
  $ids = $conn->query("SELECT Unique_ID FROM Students WHERE University_ID = " . $university_id . " AND Unique_ID IS NOT NULL");
  while ($id = $ids->fetch_assoc()) {
    $student_ids[] = $id['Unique_ID'];
  }

  $ids = $conn->query("SELECT Unique_ID FROM Lead_Status WHERE University_ID = " . $university_id . " AND Unique_ID IS NOT NULL");
  while ($id = $ids->fetch_assoc()) {
    $student_ids[] = $id['Unique_ID'];
  }

  $student_ids = array_filter($student_ids);

  $result = '';
  for ($i = 0; $i < $length; $i++) {
    $result .= mt_rand(0, 9);
  }

  $new_id = $suffix . $result;
  if (in_array($new_id, $student_ids)) {
    return generateStudentID($conn, $suffix, $length, $university_id);
  } else {
    return $new_id;
  }
}

function clean($string)
{
  $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
  return preg_replace('/[^A-Za-z0-9\-.|,]/', '', $string); // Removes special chars.
}

function balanceAmount($conn, $student_id, $duration)
{
  $balance = [];
  $duration = mysqli_real_escape_string($conn, $duration);
  
  $fees = $conn->query("SELECT Fee, Center_Fee FROM Student_Ledgers LEFT JOIN Students ON Student_Ledgers.Student_ID = Students.ID WHERE Student_Ledgers.Student_ID = $student_id AND Student_Ledgers.Duration = Students.Duration AND Student_Ledgers.Type = 1");
    while($fee = $fees->fetch_assoc()) {
      
      $balance[] = $_SESSION['Role'] == 'Sub-Center' ? $fee['Fee'] : (!empty($fee['Center_Fee']) ? $fee['Center_Fee'] : $fee['Fee']);
    }

  return array_sum($balance);
}

function numberTowords($number)
{
  $decimal = round($number - ($no = floor($number)), 2) * 100;
  $hundred = null;
  $digits_length = strlen($no);
  $i = 0;
  $str = array();
  $words = array(
    0 => '',
    1 => 'one',
    2 => 'two',
    3 => 'three',
    4 => 'four',
    5 => 'five',
    6 => 'six',
    7 => 'seven',
    8 => 'eight',
    9 => 'nine',
    10 => 'ten',
    11 => 'eleven',
    12 => 'twelve',
    13 => 'thirteen',
    14 => 'fourteen',
    15 => 'fifteen',
    16 => 'sixteen',
    17 => 'seventeen',
    18 => 'eighteen',
    19 => 'nineteen',
    20 => 'twenty',
    30 => 'thirty',
    40 => 'forty',
    50 => 'fifty',
    60 => 'sixty',
    70 => 'seventy',
    80 => 'eighty',
    90 => 'ninety'
  );
  $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
  while ($i < $digits_length) {
    $divider = ($i == 2) ? 10 : 100;
    $number = floor($no % $divider);
    $no = floor($no / $divider);
    $i += $divider == 10 ? 1 : 2;
    if ($number) {
      $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
      $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
      $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
    } else
      $str[] = null;
  }
  $Rupees = implode('', array_reverse($str));
  $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
  return ($Rupees ? $Rupees . 'Rs. Only ' : '') . $paise;
}

function getLedgerSummary($conn, $student_id)
{
  // Total Fee
  $totalFee = array();
  $remittedFee = array();
  $debits = $conn->query("SELECT Fee_Without_Sharing FROM Student_Ledgers WHERE Student_ID = $student_id AND Type = 1");
  if ($debits->num_rows == 0) {
    generateStudentLedger($conn, $student_id);
    $debits = $conn->query("SELECT Fee_Without_Sharing FROM Student_Ledgers WHERE Student_ID = $student_id AND Type = 1");
  }

  if ($debits->num_rows > 0) {
    while ($debit = $debits->fetch_assoc()) {
      if (empty($debit['Fee_Without_Sharing'])) {
        generateStudentLedger($conn, $student_id);
        $debits = $conn->query("SELECT Fee_Without_Sharing FROM Student_Ledgers WHERE Student_ID = $student_id AND Type = 1");
        while ($debit = $debits->fetch_assoc()) {
          $fees = json_decode($debit['Fee_Without_Sharing'], true);
          $totalFee[] = array_sum($fees);
        }
      } else {
        $fees = json_decode($debit['Fee_Without_Sharing'], true);
        $totalFee = $fees;
      }
    }
  }

  $credits = $conn->query("SELECT Fee FROM Student_Ledgers WHERE Student_ID = $student_id AND Type = 2");
  if ($credits->num_rows > 0) {
    while ($credit = $credits->fetch_assoc()) {
      $paid = $credit['Fee'];
      $remittedFee = $paid;
    }
  }

  return json_encode(['totalFee' => $totalFee, 'totalRemitted' => $remittedFee, 'totalBalance' => $totalFee - (int) $remittedFee]);
}

function getCenterIdFunc($conn, $subcenter_id = null)
{
  $subcenterQuery = $conn->query("SELECT Code, ID,Role FROM Users WHERE ID=$subcenter_id AND Role='Sub-Center'");
  $subcenterArr = $subcenterQuery->fetch_assoc();
  $subcentercode = explode('.', $subcenterArr["Code"]);
  $centerCode = $subcentercode[0];
  $centerQuery = $conn->query("SELECT  ID, Code, Role FROM Users WHERE Code='$centerCode' AND Role='Center'");
  $centerArr = $centerQuery->fetch_assoc();
  $center_id =  $centerArr['ID'];
  return $center_id;
}

function numberToWordFunc($number) {
  $words = array('','One','Two','Three','Four','Five','Six','Seven','Eight','Nine');
  $wordsTeen = array('Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen');
  $wordsTens = array('','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety');

  if ($number == 0) return 'Zero';

  $wordsArray = array();

  if ($number >= 1000000000) {
      $wordsArray[] = numberToWordFunc(floor($number / 1000000000)) . ' Billion';
      $number %= 1000000000;
  }

  if ($number >= 1000000) {
      $wordsArray[] = numberToWordFunc(floor($number / 1000000)) . ' Million';
      $number %= 1000000;
  }

  if ($number >= 1000) {
      $wordsArray[] = numberToWordFunc(floor($number / 1000)) . ' Thousand';
      $number %= 1000;
  }

  if ($number >= 100) {
      $wordsArray[] = numberToWordFunc(floor($number / 100)) . ' Hundred';
      $number %= 100;
  }

  if ($number >= 20) {
      $wordsArray[] = $wordsTens[floor($number / 10)];
      $number %= 10;
  }

  if ($number >= 10) {
      $wordsArray[] = $wordsTeen[$number - 10];
      $number = 0;
  }

  if ($number > 0) {
      $wordsArray[] = $words[$number];
  }

  return implode(' ', $wordsArray);
}


function get_ordinal_suffix($number) {
  if ($number % 100 >= 11 && $number % 100 <= 13) {
      return 'th';
  } else {
      switch ($number % 10) {
          case 1: return 'st';
          case 2: return 'nd';
          case 3: return 'rd';
          default: return 'th';
      }
  }
}

$student_stage = [
  'Dropout' => 'Dropout',
  'Certificate Issued' => 'Certificate Issued',
  'Exam Conducted' => 'Exam Conducted',
  'Eligible for Exam' => 'Eligible for Exam',
  'Pass Out' => 'Pass Out'
];

function wrapText($text, $wordsPerLine, $indentation = '    ') {
  $words = explode(' ', $text);
  $wrappedText = '';
  $lineWordCount = 0;

  foreach ($words as $index => $word) {
      $wrappedText .= $word . ' ';
      $lineWordCount++;

      if ($lineWordCount == $wordsPerLine) {
          $wrappedText .= "\n";
          $lineWordCount = 0;
          if ($index + 1 < count($words)) {
              $wrappedText .= $indentation;
          }
      }
  }

  return $wrappedText;
}
