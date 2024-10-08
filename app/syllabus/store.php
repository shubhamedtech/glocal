<?php

if (isset($_POST['university_id']) && isset($_POST['course']) && isset($_POST['duration']) && isset($_POST['subject']) || isset($_POST['chapter_code']) && isset($_POST['unit_code'])) {
  require '../../includes/db-config.php';
  session_start();

  $university_id = intval($_POST['university_id']);
  $sub_course = intval($_POST['course']);
  $subject_id = intval($_POST['subject']);
  $duration = mysqli_real_escape_string($conn, $_POST['duration']);
  $chapter_name_arr = is_array($_POST['chapter_name']) ? array_filter($_POST['chapter_name']) : [];
  $chapter_code_arr = is_array($_POST['chapter_code']) ? array_filter($_POST['chapter_code']) : [];
  $unit_name_arr = is_array($_POST['unit_name']) ? array_filter($_POST['unit_name']) : [];
  $unit_code_arr = is_array($_POST['unit_code']) ? array_filter($_POST['unit_code']) : [];
  $topic_name_arr = is_array($_POST['topic_name']) ? array_filter($_POST['topic_name']) : [];

  foreach ($chapter_name_arr as $chapter_key => $chapter_name) {
    $chapter_code = $chapter_code_arr[$chapter_key];
    $chapter_keys = $chapter_key + 1;
    $addchapter = $conn->query("INSERT INTO Chapter (Name,Code,University_ID,Sub_Course_ID,Semester,Subject_ID) VALUES('" . $chapter_name . "','" . $chapter_code . "',$university_id,$sub_course,'" . $duration . "',$subject_id)");
    $chapter_id = $conn->insert_id;
    if (isset($unit_name_arr[$chapter_keys])) {
      foreach ($unit_name_arr[$chapter_keys] as $unit_key => $unit_name) {
        $unit_keys = $unit_key + 1;
        $unit_code = $unit_code_arr[$chapter_keys][$unit_key];
        $chapterUnit = $conn->query("INSERT INTO Chapter_Units (Name,Code,Chapter_ID)  VALUES('" . $unit_name . "','" . $unit_code . "', $chapter_id)");
        $unit_id = $conn->insert_id;
        if (isset($topic_name_arr[$chapter_keys][$unit_keys])) {
          foreach ($topic_name_arr[$chapter_keys][$unit_keys] as $topic_key => $topic_name) {
            $addUnitTopic = $conn->query("INSERT INTO Chapter_Units_Topics (Name,Unit_ID,Chapter_ID) VALUES('" . $topic_name . "', $unit_id, $chapter_id)");
          }
        }
      }
    }
  }

  if ($addchapter || $chapterUnit || $addUnitTopic) {
    echo json_encode(['status' => 200, 'message' => 'Syllabus added successlly!']);
  } else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
  }
} else {
  echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
}


