<?php

if ($_SESSION['Role'] == 'Sub-Center') {
  include('navigation/sub-center.php');
} else if ($_SESSION['Role'] == 'Center') {
  include('navigation/center.php');
} else if ($_SESSION['Role'] == 'Sub-Counsellor') {
  include('navigation/sub-counsellor.php');
} else if ($_SESSION['Role'] == 'Counsellor') {
  include('navigation/counsellor.php');
} elseif ($_SESSION['Role'] == 'University Head') {
  include('navigation/head.php');
} elseif ($_SESSION['Role'] == 'Operations') {
  include('navigation/operation.php');
} elseif ($_SESSION['Role'] == 'Accountant') {
  include('navigation/accountant.php');
} elseif ($_SESSION['Role'] == 'Student') {
  include('navigation/student.php');
} elseif ($_SESSION['Role'] == 'Administrator') {
  include('navigation/admin.php');
} elseif ($_SESSION['Role'] == 'Exam Student') {
  include('navigation/exam-student.php');
}
