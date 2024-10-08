<?php

	error_reporting(0);

	function encrypt($plainText,$key)
	{
		$key = hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
		$encryptedText = bin2hex($openMode);
		return $encryptedText;
	}

	function decrypt($encryptedText,$key)
	{
		$key = hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText = hextobin($encryptedText);
		$decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
		return $decryptedText;
	}
	//*********** Padding Function *********************

	 function pkcs5_pad ($plainText, $blockSize)
	{
	    $pad = $blockSize - (strlen($plainText) % $blockSize);
	    return $plainText . str_repeat(chr($pad), $pad);
	}

	//********** Hexadecimal to Binary function for php 4.0 version ********

	function hextobin($hexString) 
   	 { 
        	$length = strlen($hexString); 
        	$binString="";   
        	$count=0; 
        	while($count<$length) 
        	{       
        	    $subString =substr($hexString,$count,2);           
        	    $packedString = pack("H*",$subString); 
        	    if ($count==0)
		    {
				$binString=$packedString;
		    } 
        	    
		    else 
		    {
				$binString.=$packedString;
		    } 
        	    
		    $count+=2; 
        	} 
  	        return $binString; 
    	  } 

		  function sendForOnboardingStudent($data)
      	{
			
			$working_key='ZG5SRTQ974KHDHSBJ377W7F4DTC452HS';
			$access_code='AVS1T4S0FKF756HSJB';
			$postData1 = 'cid='.$data['cid'].'&cCategory='.$data['cCategory'].'&fname='.$data['fname'].'&mname='.$data['mname'].'&lname='.$data['lname'].'&fthname='.$data['fthname'].'&mthname='.$data['mthname'].'&email='.$data['email'].'&contact='.$data['contact'].'&dob='.$data['dob'].'&category='.$data['category'].'&aadhar='.$data['aadhar'].'&gender='.$data['gender'].'&nationality='.$data['nationality'].'&present_address='.$data['present_address'].'&present_pincode='.$data['present_pincode'].'&present_city='.$data['present_city'].'&present_district='.$data['present_district'].'&present_state='.$data['present_state'].'&ABC_ID='.$data['ABC_ID'];
			
			$encrypted_data=encrypt($postData1,$working_key);
			$postData['encRequest'] = $encrypted_data;
			$postData['access_code'] = $access_code;
            $url = "https://erp.glocaluniversity.edu.in/onboard/securecheck.php?api=chck_details";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
      	}
?>

