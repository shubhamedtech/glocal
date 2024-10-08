<?php
  header("Access-Control-Allow-Origin: *");
  header('Access-Control-Allow-Methods: GET');
  header('Content-Type: application/json; charset=utf-8');

    if($_SERVER['REQUEST_METHOD']=='GET')
    {
      	function sendForOnboardingStudent($data)
      	{
            print_r($data);
            die;
            $url = "";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
      	}
        require '../../includes/db-config.php';
        session_start();

    if($_GET['id']!='')
    {
        $student = $conn->query("SELECT * FROM `Students` where id='".$_GET['id']."'");
        if($student->num_rows==0){
          exit(json_encode(['status'=>400, 'message'=>'Student id is not valid!']));
        } 
        	$row = $student->fetch_assoc();
            if($row['ABC_ID']!='')
            {  
              	$course_name = $conn->query("SELECT `Name` FROM `Sub_Courses` where University_ID= '".$row['University_ID']."' and id='" . $row['Sub_Course_ID'] . "'");
                $cName = $course_name->fetch_assoc();
                $postData['cid'] = "B.Voc_in_".str_replace(' ','_',$cName['Name']);
                $address = json_decode($row['Address'],true);
                // $postData['cid'] = $row['Sub_Course_ID'];
                // $postData['cCategory'] = $row['Sub_Course_ID'];
                $cCat = 2;
                if($row['University_ID']==47)
                {
                    $cCat = 3;
                }
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
                // && $row['Document_Verified']!='' && $row['Payment_Received']!=''
                // second condition after onbording success  (&& $row['Onboarded']!='success')
                $result = sendForOnboardingStudent(json_encode($postData));
              	die;
                $result = json_decode($result,true);
                if($result['status']==200)
                {
                    // update student for onboarding update for success;
                    //   $updateStudent = 'UPDATE `Students` set `Onboarded`="success", `Onboarded_Id`="'.$result["id"].'" where id="'.$row["ID"].'" ';
                    echo json_encode(['status'=>200,'message'=>'Student successfull onboard']);
                }
                else
                {
                    // update student for onboarding update for failed;
                    //   $updateStudent = 'UPDATE `Students` set `Onboarded`="failed",`Onboarded_Id`="" where id="'.$row["ID"].'" ';
                    echo json_encode(['status'=>400,'message'=>'Onboarding failed']);
                }
            }
            else
            {
                if($row['ABC_ID']=='')
                {
                    exit(json_encode(['status'=>400,'message'=>'ABC ID Not available for this student']));   
                }
                elseif($row['Document_Verified']=='')
                {
                    exit(json_encode(['status'=>400,'message'=>'Document Verified Not available for this student']));   
                }
                elseif($row['Payment_Received']=='')
                {
                    exit(json_encode(['status'=>400,'message'=>'Payment Not available for this student']));   
                }
                else
                {
                    exit(json_encode(['status'=>400,'message'=>'Not available for this student']));
                }
            }
    	}
    	else
    	{
       		exit(json_encode(['status'=>400,'message'=>'Student id is required']));
   	 	}  
    }
    else
    {
        echo json_encode(['status'=>400,'message'=>'Only GET method is allowed.']);
    }