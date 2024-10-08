<?php
require '../../includes/db-config.php';
session_start();

$role_query = str_replace('{{ table }}', 'Users', $_SESSION['RoleQuery']);
$role_query = str_replace('{{ column }}', 'ID', $role_query);

$centers = $conn->query("SELECT ID, CONCAT(UPPER(Name), ' (', Code, ')') as Name FROM Users WHERE Role = 'Center' $role_query ORDER BY Code ASC");
if($centers->num_rows == 0){
  $centers = $conn->query("SELECT Users.Created_By, CONCAT(UPPER(Name), ' (', Code, ')') as Name FROM Users WHERE ID = ".$_SESSION['ID']." AND Role = 'Sub-Center' $role_query ORDER BY Code ASC");
}
$options = '<option value="">Select</option>';
while ($center = $centers->fetch_assoc()) {
  if(isset($center['Created_By'])){
    $center['ID'] = $center['Created_By'];
  }
  $options .= '<option value="' . $center['ID'] . '">' . $center['Name'] . '</option>';
}

echo $options;
