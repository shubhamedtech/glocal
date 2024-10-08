<?php
ini_set('display_errors', 1);
  if(isset($_GET['university_id']) && isset($_GET['id'])){
    require '../../includes/db-config.php';
    $id = intval($_GET['id']);
    $university_id = intval($_GET['university_id']);

    $course_types_array = [];

    // $subcenterQuery = $conn->query("SELECT Code, ID,Role FROM users WHERE ID=$id AND Role='Sub-Center'");
    // $subcenterArr = $subcenterQuery->fetch_assoc();
    // $subcentercode = explode('.',$subcenterArr["Code"]);
    // $centerCode = $subcentercode[0];

    // $centerQuery = $conn->query("SELECT  ID, Code, Role FROM users WHERE Code=$centerCode AND Role='Center'");
    // $centerArr = $centerQuery->fetch_assoc();
    // $course_id = $conn->query("SELECT * FROM Center_Sub_Courses WHERE `User_ID` = ".$centerArr['ID']." AND University_ID = $university_id");
    // while($courseIdArr = $course_id->fetch_assoc()){
    //       $subCourseId = $courseIdArr['Sub_Course_ID'];
    //       $courseId = $courseIdArr['Course_ID'];

    //       $subCourseQuery = $conn->query("SELECT ID, Name, Course_ID, University_ID FROM Sub_Courses WHERE `ID` = $subCourseId AND `Course_ID` = $courseId AND University_ID = $university_id");
    //       $subCourseArr = $subCourseQuery->fetch_assoc();
    //       $subCourseName[] = $subCourseArr["Name"];

    //     }


    $alloted_course_types = $conn->query("SELECT Course_Type_ID FROM Center_Course_Types WHERE `User_ID` = $id AND University_ID = $university_id");
    while($alloted_course_type = $alloted_course_types->fetch_assoc()){
      $course_types_array[] = $alloted_course_type['Course_Type_ID'];
    }


?>
    <!-- <div class="row">
      <div class="col-md-12">
        <div class="form-group form-group-default form-group-default-select2 required">
          <label>Course Type</label>
          <select class="full-width" id="course_type" name="course_type[]" onchange="getVocationalCourse()" multiple>
            <?php
              // $course_types = $conn->query("SELECT ID, Name FROM Course_Types WHERE University_ID = $university_id ORDER BY Name ASC");
              // while ($course_type = $course_types->fetch_assoc()){ ?>
                <option value="<?php //$course_type['ID']?>"><?= $course_type['Name']?></option>
            <?php //} ?>
          </select>
        </div>
      </div>
    </div>

    <div id="vocational_course">

    </div> -->

    <script type="text/javascript">
      $('#course_type').val([<?=implode(",", $course_types_array)?>]).select2({
        placeholder: 'Course Type'
      }).change();
    </script>

    <script type="text/javascript">
      function getVocationalCourse(){
        var type_ids = $('#course_type').val();
        $.ajax({
          url: '/app/sub-centers/vocational-courses?ids='+type_ids+'&university_id=<?=$university_id?>&user_id=<?=$id?>',
          type:'GET',
          success: function(data) {
            $('#vocational_course').html(data);
          }
        })
      }
      // getVocationalCourse();
    </script>


<?php } ?>
