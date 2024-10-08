<?php
  require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');

  $header[] = array('Student Name', 'Email', 'Phone', 'DOB', 'Duraction(Sem/Year)', 'Course', 'Sub-Course', 'Admission Session', 'Admission Type');

  $xlsx = SimpleXLSXGen::fromArray( $header )->downloadAs('Student-add Sample.xlsx');