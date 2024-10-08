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

  $addchapter = false;
  $chapterUnit = false;
  $addUnitTopic = false;
  $updateChapter = false;
  $updateUnit = false;
  $updateUnitTopic = false;

  if (isset($chapter_name_arr) && is_array($chapter_name_arr)) {
    if (array_key_exists('cedit', $chapter_name_arr)) {
      foreach ($chapter_name_arr['cedit'] as $chapter_key => $chapter_name) {// edit chapter
        $chapter_id = array_keys($chapter_name)[0];
        $chapter_name = array_column($chapter_name, 0)[0];
        $chapter_code = $chapter_code_arr['cedit'][$chapter_key][$chapter_id][0];
        $checkChapter = $conn->query("SELECT ID FROM Chapter WHERE  ID = $chapter_id");
        if ($checkChapter->num_rows > 0) {
          $updateChapter = $conn->query("UPDATE Chapter SET Name = '$chapter_name', Code = '$chapter_code' WHERE ID = $chapter_id");

          if (isset($unit_name_arr) && is_array($unit_name_arr)) { // edit unit
            if (isset($unit_name_arr['uedit'][$chapter_key]) && is_array($unit_name_arr['uedit'][$chapter_key])) {// edit unit
              foreach ($unit_name_arr['uedit'][$chapter_key] as $unit_key => $unit_name) {// edit unit
                $unit_id = array_keys($unit_name)[0];
                $unit_name = array_column($unit_name, 0)[0];
                $unit_code = $unit_code_arr['uedit'][$chapter_key][$unit_key][$unit_id][0];
                $updateUnit = $conn->query("UPDATE Chapter_Units SET Name = '$unit_name', Code = '$unit_code' WHERE ID = $unit_id AND Chapter_ID = $chapter_id");

                if (array_key_exists('tedit', $topic_name_arr) && isset($topic_name_arr) && is_array($topic_name_arr)) {// add topic
                  if (isset($topic_name_arr['tedit'][$chapter_key]) && is_array($topic_name_arr['tedit'][$chapter_key])) {
                    foreach ($topic_name_arr['tedit'][$chapter_key] as $topic_key => $topic_name) {
                      // print_r($unit_id);echo "/n";
                      $topic_id = array_keys($topic_name)[0];
                      $topic_name = array_column($topic_name, 0)[0];
                      $updateUnitTopic = $conn->query("UPDATE Chapter_Units_Topics SET Name = '$topic_name', Unit_ID =$unit_id , Chapter_ID=$chapter_id WHERE ID = $topic_id");
                    }
                  }
                }
                if (array_key_exists('tadd', $topic_name_arr) && isset($topic_name_arr) && is_array($topic_name_arr)) {
                  // add topic
                  if (isset($topic_name_arr['tadd'][$chapter_key][$unit_key]) && is_array($topic_name_arr['tedit'][$chapter_key])) {
                    foreach ($topic_name_arr['tadd'][$chapter_key][$unit_key] as $topic_key => $topic_name) {
                      
                      $addUnitTopic = $conn->query("INSERT INTO Chapter_Units_Topics (Name,Unit_ID,Chapter_ID) VALUES('" . $topic_name . "', $unit_id, $chapter_id)");
                    }
                  }
                }
              }
            }

            if (array_key_exists('uadd', $unit_name_arr) && isset($unit_name_arr['uadd'][$chapter_key]) && is_array($unit_name_arr['uadd'][$chapter_key])) { // add unit
              foreach ($unit_name_arr['uadd'][$chapter_key] as $unit_key => $unit_name) {// edit unit
                $unit_code = $unit_code_arr['uadd'][$chapter_key][$unit_key][0];
                $unit_name = $unit_name[0];
                $insertUnit = $conn->query("INSERT INTO Chapter_Units (Name, Code, Chapter_ID) VALUES('$unit_name', '$unit_code', $chapter_id)");
                $unit_last_id = $conn->insert_id;
                if (!empty($unit_last_id) && array_key_exists('tadd', $topic_name_arr) && isset($topic_name_arr) && is_array($topic_name_arr)) {// add topic
                  if (isset($topic_name_arr['tadd'][$chapter_key]) && is_array($topic_name_arr['tadd'][$chapter_key])) {
                    foreach ($topic_name_arr['tadd'][$chapter_key][$unit_key] as $topic_key => $topic_name) {
                      $addUnitTopic = $conn->query("INSERT INTO Chapter_Units_Topics (Name,Unit_ID,Chapter_ID) VALUES('" . $topic_name . "', $unit_last_id, $chapter_id)");
                    }
                  }
                }
              }

            }

          }
        }
      }
    }
    if (array_key_exists('cadd', $chapter_name_arr)) {
      unset($chapter_name_arr['cedit']);
      unset($chapter_code_arr['cedit']);

      foreach ($chapter_name_arr['cadd'] as $chapter_key => $chapter_name) {// add chapter
        $chapter_id = array_keys($chapter_name)[0];
        $chapter_name = $chapter_name[0];
        $chapter_code = $chapter_code_arr['cadd'][$chapter_key][$chapter_id][0];
        $addchapter = $conn->query("INSERT INTO Chapter (Name,Code,University_ID,Sub_Course_ID,Semester,Subject_ID) VALUES('" . $chapter_name . "','" . $chapter_code . "',$university_id,$sub_course,'" . $duration . "',$subject_id)");
        $chapter_last_id = $conn->insert_id;

        if ($chapter_last_id && array_key_exists('uadd', $unit_name_arr) && isset($unit_name_arr) && is_array($unit_name_arr)) {// add unit
          unset($unit_name_arr['uedit']);
          unset($unit_code_arr['uedit']);
          if (isset($unit_name_arr['uadd'][$chapter_key]) && is_array($unit_name_arr['uadd'][$chapter_key])) {
            foreach ($unit_name_arr['uadd'][$chapter_key] as $unit_key => $unit_name) {
              $unit_name = $unit_name[0];
              $unit_code = $unit_code_arr['uadd'][$chapter_key][$unit_key][0];
              $insertUnit = $conn->query("INSERT INTO Chapter_Units (Name, Code, Chapter_ID) VALUES('$unit_name', '$unit_code', $chapter_last_id)");
              $unit_last_id = $conn->insert_id;

              if ($unit_last_id && array_key_exists('tadd', $topic_name_arr) && isset($topic_name_arr) && is_array($topic_name_arr)) {// add topic
                unset($topic_name_arr['tedit']);
                if (isset($topic_name_arr['tadd'][$chapter_key]) && is_array($topic_name_arr['tadd'][$chapter_key])) {
                  foreach ($topic_name_arr['tadd'][$chapter_key][$unit_key] as $topic_key => $topic_name) {
                    $addUnitTopic = $conn->query("INSERT INTO Chapter_Units_Topics (Name,Unit_ID,Chapter_ID) VALUES('" . $topic_name . "', $unit_last_id, $chapter_last_id)");
                  }
                }
              }
            }
          }
        }
      }
    }
  }




  if ($addchapter || $chapterUnit || $addUnitTopic ||   $updateChapter || $updateUnit ||  $updateUnitTopic ) {
    echo json_encode(['status' => 200, 'message' => 'Syllabus added successfully!']);
  } else if ($updateChapter || $updateUnit || $updateUnitTopic) {
    echo json_encode(['status' => 200, 'message' => 'Syllabus Updated successfully!']);

  } else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
  }
} else {
  echo json_encode(['status' => 400, 'message' => 'Missing required fields!']);
}

