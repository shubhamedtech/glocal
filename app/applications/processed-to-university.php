<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require '../../includes/db-config.php';
  require '../../app/applications/onboarding/index.php';
  session_start();
  $id = base64_decode($_REQUEST['id']);
  $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
  if ($id != '') {
 
    $student = $conn->query("SELECT * FROM `Students` LEFT JOIN `Admission_Sessions` ON `Admission_Sessions`.`ID`=`Students`.`Admission_Session_ID` WHERE `Students`.`ID`='" . $id . "'");
    if ($student->num_rows == 0) {
      exit(json_encode(['status' => 400, 'message' => 'Student id is not valid!']));
    }
    $row = $student->fetch_assoc();
    if($row['University_ID']==47)
    {
        if($row['Name']!='July-24')
        {
          exit(json_encode(['status' => 400, 'message' => 'Admission sessin not allow for enrollment']));
        }
    }
    if ($row['Payment_Received'] != null && $row['Document_Verified'] != null && $row['Payment_Received'] != '' && $row['Document_Verified'] != '') {
      $course_name = $conn->query("SELECT `Name` FROM `Sub_Courses` where University_ID= '" . $row['University_ID'] . "' and id='" . $row['Sub_Course_ID'] . "'");
      $cName = $course_name->fetch_assoc();
      $cCat = 2;
      if ($row['University_ID'] == 47) {
        $cCat = 3;
      }
      elseif($row['University_ID'] == 48)
      {
        if($row['Course_Category']=="certified")
        {
        	$cCat = 1;
        }
      }
      if($row['University_ID']==47)
      {
        $cName['Name'] = $cName['Name'];
      }
      //print_r($cName['Name']);
      $postData['cid'] = $cName['Name'];
      $address = json_decode($row['Address'], true);
      $postData['cCategory'] = $cCat;
      $postData['fname'] = $row['First_Name'];
      $postData['mname'] = $row['Middle_Name'];
      $postData['lname'] = $row['Last_Name'];
      $postData['fthname'] = $row['Father_Name'];
      $postData['mthname'] = $row['Mother_Name'];
      $postData['email'] = $row['Email'];
      $postData['contact'] = $row['Contact'];
      $postData['dob'] = $row['DOB'];
      $postData['category'] = $row['Category'];
      $postData['aadhar'] = $row['Aadhar_Number'];
      $postData['gender'] = $row['Gender'];
      $postData['nationality'] = $row['Nationality'];
      $postData['present_address'] = $address['present_address'];
      $postData['present_pincode'] = $address['present_pincode'];
      $postData['present_city'] = $address['present_city'];
      $postData['present_district'] = $address['present_district'];
      $postData['present_state'] = $address['present_state'];
      $postData['ABC_ID'] = $row['ABC_ID'];
      $result = sendForOnboardingStudent($postData);
      $result = json_decode($result, true);
      if ($result['status'] == 'Success') {
        $updateStudent = 'UPDATE `Students` set  `Enrollment_No`="' . $result["GU_NO"] . '", `Processed_To_University`=CURRENT_TIMESTAMP where id="' . $id . '" ';
        $conn->query($updateStudent);
        echo json_encode(['status' => 200, 'message' => 'Student successfull onboard']);
      } else {
        echo json_encode(['status' => 400, 'message' => $result['Message']]);
      }
    } else {
      if ($row['ABC_ID'] == '') {
        exit(json_encode(['status' => 400, 'message' => 'ABC ID Not available for this student']));
      } elseif ($row['Document_Verified'] == '') {
        exit(json_encode(['status' => 400, 'message' => 'Document Verified Not available for this student']));
      } elseif ($row['Payment_Received'] == '') {
        exit(json_encode(['status' => 400, 'message' => 'Payment Not available for this student']));
      } else {
        exit(json_encode(['status' => 400, 'message' => 'Not available for this student']));
      }
    }
  } else {
    exit(json_encode(['status' => 400, 'message' => 'Student id is required']));
  }
} else {
  echo json_encode(['status' => 400, 'message' => 'Only GET method is allowed.']);
}
