<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<!-- START PAGE-CONTAINER -->
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <!-- START PAGE CONTENT WRAPPER -->
  <div class="page-content-wrapper ">
    <!-- START PAGE CONTENT -->
    <div class="content ">
      <!-- START JUMBOTRON -->
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i) :
                  $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                <button class="btn btn-link" aria-label="" title="" data-toggle="tooltip" data-original-title="Upload" onclick="add('subjects', 'lg')"> <i class="uil uil-export"></i></button>
              </div>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class=" container-fluid">
        <!-- BEGIN PlACE PAGE CONTENT HERE -->
        <div class="row">

          <div class="col-md-4">
            <div class="form-group form-group-default required">
              <label>Course</label>

              <?php if ($_SESSION['university_id'] == '48') { ?>
                <select class="full-width" style="border: transparent;" id="course" onchange="getCourseCategory();">
                <?php } else { ?>
                  <select class="full-width" style="border: transparent;" id="course" onchange="getSemester(this.value); removeTable()">
                  <?php } ?>
                  <option value="">Choose</option>
                  <?php
                  $condition = "";
                  if (in_array($_SESSION['Role'], ['Center', 'Sub-Center'])) {
                    $ids = array();
                    $sub_course_ids = $conn->query("SELECT Sub_Course_ID FROM Center_Sub_Courses WHERE `User_ID` = " . $_SESSION['ID'] . "");
                    while ($sub_course_id = $sub_course_ids->fetch_assoc()) {
                      $ids[] = $sub_course_id['Sub_Course_ID'];
                    }
                    $condition = " AND Sub_Courses.ID IN (" . implode(",", $ids) . ")";
                  }
                  $sub_courses = $conn->query("SELECT CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') AS Sub_Course, Sub_Courses.ID FROM Sub_Courses LEFT JOIN Courses ON Sub_Courses.Course_ID = Courses.ID WHERE Sub_Courses.University_ID = " . $_SESSION['university_id'] . " $condition ORDER BY Sub_Courses.Name ASC");
                  while ($sub_course = $sub_courses->fetch_assoc()) {
                    echo '<option value="' . $sub_course['ID'] . '">' . $sub_course['Sub_Course'] . '</option>';
                  }
                  ?>
                  </select>
            </div>
          </div>
          <?php if ($_SESSION['university_id'] == '47') { ?>
            <div class="col-md-4">
              <div class="form-group form-group-default required">
                <label>Semester</label>
                <select class="full-width" style="border: transparent;" id="semester" onchange="getTable()">
                  <option value="">Choose</option>
                </select>
              </div>
            </div>
          <?php } else { ?>
            <!-- Course Category -->
            <div class="col-md-4">
              <div class="form-group form-group-default required">
                <label>Course Category</label>
                <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="course_category" name="course_category" onchange="chooseDuration(this)">
                </select>
              </div>
            </div>
            <div class="col-md-4 d-none" id="set_course_category">
            </div>

          <?php } ?>
        </div>
        <div class="row">

        </div>
        <div class="row" id="subjects"></div>
        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>

    <script type="text/javascript">
      function getSemester(id) {
        $.ajax({
          url: '/app/subjects/semester?id=' + id,
          type: 'GET',
          success: function(data) {
            $("#semester").html(data);
            getTable(id);
          }
        })
      }
    </script>

    <script type="text/javascript">
      $(document).ready(function() {
        $('#course').select2({});
      })

      function getTable(id = null) {
        var course_id = $('#course').val();
        var semester = $('#semester').val();
        if (course_id.length > 0 && semester.length > 0) {
          $.ajax({
            url: '/app/subjects/syllabus?course_id=' + course_id + '&semester=' + semester,
            type: 'GET',
            success: function(data) {
              $('#subjects').html(data);
            }
          })
        } else if (id != null) {
          $.ajax({
            url: '/app/subjects/syllabus?id=' + id,
            type: 'GET',
            success: function(data) {
              $('#subjects').html(data);
            }
          })
        } else {
          $.ajax({
            url: '/app/subjects/syllabus',
            type: 'GET',
            success: function(data) {
              $('#subjects').html(data);
            }
          })
        }
      }
      getTable()
      function removeTable() {
        $('#subjects').html('');
      }
    </script>

    <script type="text/javascript">
      function uploadFile(table, column, id) {
        $.ajax({
          url: '/app/upload/create?id=' + id + '&column=' + column + '&table=' + table,
          type: 'GET',
          success: function(data) {
            $("#md-modal-content").html(data);
            $("#mdmodal").modal('show');
          }
        })
      }
    </script>
    <script type="text/javascript">
      function getCourseCategory(id) {
        const skll_subcourse_id = $('#course').val();
        $.ajax({
          url: '/app/subjects/course-category?id=' + skll_subcourse_id,
          type: 'GET',
          success: function(data) {
            $('#course_category').html(data);
            $('#course_category').val(<?php print !empty($id) ? $student['Course_Category'] : '' ?>)
          }
        });
      }

      if ('<?php echo $_SESSION['university_id']; ?>' === '48') {
        function getTableData(duration) {
          var course_id = $('#course').val();
          var course_category = $('#course_category').val();
          $.ajax({
            url: '/app/subjects/syllabus?duration=' + duration + '&course_id=' + course_id + "&course_category=" + course_category,
            type: 'GET',
            success: function(data) {
              $('#subjects').html(data);
            }
          })
        }
        getTableData()
        function removeTable() {
          $('#subjects').html('');
        }
      }

      function chooseDuration(selectElement) {
        var selectedValues = Array.from(selectElement.selectedOptions, option => option.value);
        getTableData(selectedValues);
        $('#set_course_category').removeClass('d-none');
        var conditions = [{
            values: ['advance_diploma'],
            content: '<option value="11/advanced" selected>11 Months'
          },
          {
            values: ['certified'],
            content: '<option value="6/certified" selected >6 Months</option><option value="11/certified">11 Months Certified</option>'
          },
          {
            values: ['certification'],
            content: '<option value="3" selected>3 Months</option>'
          }
        ];

        var matchedCondition = conditions.find(condition => condition.values.every(value => selectedValues.includes(value)));

        $('#set_course_category').html('<div class="form-group form-group-default form-group-default-select2 required">\
            <label style="z-index:9999">Durations</label>\
            <select class="full-width" style="border: transparent;"  data-init-plugin="select2" id="duractions" name="duractions " onchange="getTableData(this.value)">\
                ' + matchedCondition.content + '\
            </select>\
        </div>');

        $("#duractions").select2();
      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>