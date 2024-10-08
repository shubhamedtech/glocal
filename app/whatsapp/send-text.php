<?php
if (isset($_POST['number'], $_POST['message'], $_POST['session'])) {
  $chatId = "91" . $_POST['number'] . "@c.us";
  $message = $_POST['message'];
  $session = $_POST['session'];
  $message = str_replace('@@ ', '\n', $message);
  $delay = strlen($message) / 32;

  // Check Number exists
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://172.105.42.32:8092/api/contacts/check-exists?phone=' . $chatId . '&session=' . $session,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'x-api-key: 019259b0-bd42-7f97-9179-708e256d9915'
    ),
  ));
  $response = curl_exec($curl);
  curl_close($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  if ($httpCode == 200) {
    $response = json_decode($response, true);
    if (array_key_exists('numberExists', $response) && $response['numberExists']) {
      // Start Typing
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://172.105.42.32:8092/api/startTyping',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
          "chatId": "' . $chatId . '",
          "session": "' . $session . '"
        }',
        CURLOPT_HTTPHEADER => array(
          'x-api-key: 019259b0-bd42-7f97-9179-708e256d9915',
          'Content-Type: application/json'
        ),
      ));
      $response = curl_exec($curl);
      curl_close($curl);
      sleep($delay);
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://172.105.42.32:8092/api/stopTyping',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
          "chatId": "' . $chatId . '",
          "session": "' . $session . '"
        }',
        CURLOPT_HTTPHEADER => array(
          'x-api-key: 019259b0-bd42-7f97-9179-708e256d9915',
          'Content-Type: application/json'
        ),
      ));
      $response = curl_exec($curl);
      curl_close($curl);
      sleep(0.5);
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://172.105.42.32:8092/api/sendText',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
          "chatId": "' . $chatId . '",
          "session": "' . $session . '",
          "text": "' . $message . '"
        }',
        CURLOPT_HTTPHEADER => array(
          'x-api-key: 019259b0-bd42-7f97-9179-708e256d9915',
          'Content-Type: application/json'
        ),
      ));
      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      echo json_encode(['status'=>true, 'message'=>'OTP sent to '.$_POST['number']]);
    }else{
        echo json_encode(['status'=>false, 'message'=>'Number is not on WhatsApp!']);
    }
  }else{
      echo json_encode(['status'=>false, 'message'=>'Something went wrong!']);
  }
}
