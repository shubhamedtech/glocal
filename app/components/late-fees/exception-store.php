<?php
if (isset($_POST['id'], $_POST['exception'])) {
  require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';

  $id = intval($_POST['id']);
  $exception = mysqli_real_escape_string($conn, $_POST['exception']);
  $exceptions = explode(",", $exception);

  $codes = array();
  foreach ($exceptions as $code) {
    $codes[] = trim($code);
  }
  $codes = array_filter($codes);
  if (empty($codes)) {
    $update = $conn->query("UPDATE Late_Fees SET Exception = NULL WHERE ID = $id");
  } else {
    $update = $conn->query("UPDATE Late_Fees SET Exception = '" . mysqli_real_escape_string($conn, json_encode($codes)) . "' WHERE ID = $id");
  }
  if ($update) {
    echo json_encode(['status' => true, 'message' => 'Updated successfully!']);
  } else {
    echo json_encode(['status' => false, 'message' => 'Something went wrong!']);
  }

  $conn->close();
}
