<?php
  include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php');
  require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');
  
  if($_SESSION['university_id']=='48'){ 
    $header[] = array('Scheme','Course', 'Specialization', 'Category', 'Duration', 'Subject Code', 'Subject Name', 'Type (Theory/Practical)', 'Credit','Minimum Marks', 'Maximum Marks');
  }else{
    $header[] = array('Scheme', 'Course', 'Sub-Course', 'Semester', 'Subject Code', 'Subject Name', 'Type (Theory/Practical)', 'Credit','Minimum Marks', 'Maximum Marks');
  }

  $xlsx = SimpleXLSXGen::fromArray( $header )->downloadAs('Subjects Sample.xlsx');
