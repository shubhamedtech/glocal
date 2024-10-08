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
                if (count($breadcrumbs) == $i) : $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>

                <button class="btn btn-primary p-2 " data-toggle="tooltip" data-original-title="Add" onclick="add('results','lg')"> <i class="uil uil-plus-circle"></i>Add</button>
                <button class="btn btn-primary p-2 " data-toggle="tooltip" data-original-title="Bulk import" onclick="upload('results', 'lg')"> <i class="uil uil-upload"></i></button>
              </div>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class=" container-fluid">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group form-group-default required">
              <label>Program Type</label>
              <select class="full-width" style="border: transparent;" id="course_type_ids" name="course_type_id" onchange="getCourse(this.value);">
                <option value="">Select</option>
                <?php
                $programs = $conn->query("SELECT ID,Name,Short_Name FROM Courses WHERE Status=1 ORDER BY Name ASC");
                while ($program = $programs->fetch_assoc()) { ?>
                  <option value="<?= $program['ID'] ?>">
                    <?= $program['Name'] . ' (' . $program['Short_Name'] . ')' ?>
                  </option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group form-group-default required">
              <label>Specialization/Course</label>
              <select class="full-width" style="border: transparent;" id="sub_course_ids" name="sub_course_id" onchange="getStudent(this.value);">
                <option value="">Select</option>
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group form-group-default required">
              <label>Student</label>
              <select class="full-width" style="border: transparent;" id="student_ids" name="student_id" onchange="getResults(this.value)">
                <option value="">Select</option>
              </select>
            </div>
          </div>
        </div>
        <div class="card card-transparent">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover nowrap" id="notes-table">
                <thead>
                  <tr>
                    <th>Enrollment No.</th>
                    <th>Subject</th>
                    <th>Maximum Marks ext.</th>
                    <?php if($_SESSION['university_id']==47){ ?>
                      <th>Maximum Marks int.</th>
                      <th>Total</th>
                      <?php } ?>
                   
                  
                    <th>Status</th>
                    <th>Programs</th>
                  </tr>
                </thead>
                <tbody class="reslult_data">

                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <script type="text/javascript">
      $(function() {
        $("#course_type_ids").select2({
          placeholder: 'Choose Course'
        })


        getResults();
      })

      function getCourse(id) {
        $.ajax({
          url: '/app/results/cources',
          type: 'POST',
          dataType: 'text',
          data: {
            'program_id': id
          },
          success: function(result) {
            $("#sub_course_ids").select2({
              placeholder: 'Choose Center'
            })
            $('#sub_course_ids').html(result);
            getResults(stu_id = null, course_id = null, id);
          }
        })
      }

      function getStudent(id) {
        var course_id = $("#course_type_ids").val();
        $.ajax({
          url: '/app/results/students',
          type: 'POST',
          dataType: 'text',
          data: {
            'sub_course_id': id,
            course_id: course_id
          },
          success: function(result) {
            $("#student_ids").select2({
              placeholder: 'Choose Center'
            })
            $('#student_ids').html(result);
          }
        })
      }


      function getResults(stu_id = null, course_id = null, sub_course_id = null) {
        var course_id = $("#course_type_ids").val();
        var sub_course_id = $("#sub_course_ids").val();
        $.ajax({
          url: '/app/results/server',
          type: 'POST',
          dataType: 'text',
          data: {
            course_id: course_id,
            sub_course_id: sub_course_id,
            stu_id: stu_id
          },
          success: function(result) {
            $('.reslult_data').html(result);
          }
        })
      }
    </script>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>