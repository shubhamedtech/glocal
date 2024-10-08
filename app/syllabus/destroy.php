<?php
if (isset($_GET['id'])) {
  require '../../includes/db-config.php';
  session_start();
  $type = $_REQUEST['type'];
  if ($type == 'chapter') {
    $table = "Chapter";
  } elseif ($type == 'topic') {
    $table = 'Chapter_Units_Topics';
  } elseif ($type == 'unit') {
    $table = 'Chapter_Units';
  }
  $id = intval($_GET['id']);
  $query = "DELETE FROM " . $table . " WHERE ID=" . $id;
  $delete = $conn->query($query);
  if ($delete) {
    echo json_encode(['status' => 200, 'message' => 'Data Deleted successfully!']);
  } else {
    echo json_encode(['status' => 400, 'message' => 'Something Went Wrong!']);
  }
}
