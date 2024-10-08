<?php
if(isset($_POST['otp'])){
    require $_SERVER['DOCUMENT_ROOT'].'/includes/db-config.php';
    session_start();
    
    $otp = intval($_POST['otp']);
    
    $check = $conn->query("SELECT ID FROM OTP_Verifications WHERE User_ID = ".$_SESSION['ID']." AND OTP = '$otp' AND Created_At > now() - interval 5 minute");
    if($check->num_rows>0){
        $_SESSION['verify'] = 1;
        $url = $_SESSION['Role']=='Center' ? '/dashboards/center-dashborad' : '/admissions/applications';
        echo json_encode(['status'=>true, 'message'=>'OTP Verified!', 'url'=>$url]);
    }else{
        echo json_encode(['status'=>false, 'message'=>'Invalid OTP!']);
    }
    
    $conn->close();
}