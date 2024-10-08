<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php');

  $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/";
  ?>
  <div class="page-content-wrapper ">
    <div class="content ">
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
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

              </div>
            </ol>
          </div>
        </div>
      </div>

      <div class=" container-fluid">
        <div class="card card-transparent">
          <div class="card-header">

            <?php
            $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
            for ($i = 1; $i <= count($breadcrumbs); $i++) {
              if (count($breadcrumbs) == $i) : $active = "active";
                $crumb = explode("?", $breadcrumbs[$i]);
                echo $crumb[0];
              endif;
            }
            ?>

            <div class="pull-right">
              <div class="row">
                <div class="col-xs-7" style="margin-right: 10px;">
                  <input type="text" id="e-book-search-table" class="form-control pull-right p-2 fw-bold" placeholder="Search">
                </div>
                <div class="col-xs-5" style="margin-right: 10px;">

                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <?php 
          if ($_SESSION['university_id'] == 48) {
            $redirection = "/app/marksheets/download-bulk-marksheets";
          } else {
            $redirection = "/app/marksheets/download-bulk-marksheets";
          } 
          ?>
          <div class="card-body">
            <form role="form" id="form-add-e-book" action="<?= $redirection ?>" method="POST" enctype="multipart/form-data">
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group form-group-default required">
                      <label>Program Type</label>
                      <select required class="full-width" style="border: transparent;" id="course_type_id" name="course_type_id" onchange="getSubCourse(this.value);">
                        <option value="">Select</option>
                        <?php
                        $programs = $conn->query("SELECT ID,Name,Short_Name FROM Courses WHERE Status = 1 AND University_ID='".$_SESSION['university_id']."' ");
                        while ($program = $programs->fetch_assoc()) { ?>
                          <option value="<?= $program['ID'] ?>">
                            <?= $program['Name'] . ' (' . $program['Short_Name'] . ')' ?>
                          </option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group form-group-default ">
                      <label>Specialization/Course</label>
                      <select class="full-width" style="border: transparent;" id="sub_course_id" name="course_id" onchange="getSemester(this.value);">
                        <option value="">Select</option>
                      </select>
                    </div>
                  </div>
                  <?php if ($_SESSION['university_id'] == 48) { ?>
                    <div class="col-md-4">
                      <div class="form-group form-group-default ">
                        <label>Category</label>
                        <select class="full-width" style="border: transparent;" id="category" name="category" onchange="getCategory(this.value) ">
                          <option value="">Choose Category</option>
                          <option value="3">3 Months</option>
                          <option value="6">6 Months</option>
                          <option value="11/certified">11 Months Certified</option>
                          <option value="11/advance-diploma">11 Months Advance Diploma</option>
                        </select>
                      </div>
                    </div>
                  <?php } else { ?>
                    <div class="col-md-4">
                            <div class="form-group form-group-default required">
                                <label>Semester</label>
                                <select class="full-width" name="semester" style="border: transparent;" id="semester">
                                    <option value="">Choose</option>
                                </select>
                            </div>
                        </div>
                  <?php  } ?>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group form-group-default ">
                      <label>Student</label>
                      <input type="text" class="full-width" placeholder="Enter Enrollment No. Ex : E3241, E3245 " style="border: transparent;" id="student_id" name="student_id">
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer clearfix justify-content-center">
                <div class="col-md-4 m-t-10 sm-m-t-10">
                  <input type="submit" class="btn btn-primary btn-cons btn-animated from-left" value="Save">
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>

    <script type="text/javascript">
      function add_bulk(url, modal) {
        $.ajax({
          url: '/app/' + url + '/create-bulk',
          type: 'GET',
          success: function(data) {
            $('#' + modal + '-modal-content').html(data);
            $('#' + modal + 'modal').modal('show');
          }
        })
      }
    </script>
    <script>
      function view() {

        var student_id = $('#student_id').val();
        var course_id = $('#course_type_id').val();
        var sub_course_id = $('#sub_course_id').val();


        if (student_id == undefined) {
          notification('danger', 'Please select student to proceed!');
          return false;
        }


      }

      function getSubCourse(course_id) {

        const durations = $('#min_duration').val();
        const university_id = $('#university_id').val();
        const mode = $('#mode').val();
        $.ajax({
          url: '/app/certificates/get-subcourse?course_id=' + course_id,
          type: 'GET',
          success: function(data) {
            $('#sub_course_id').html(data);
            $("#sub_course_id").select2({
              placeholder: 'Choose Specialization'
            })
          }
        });
      }

      $("#sub_course_id").select2({
        placeholder: 'Choose Specialization'
      })
      $("#course_type_id").select2({
        placeholder: 'Choose Specialization'
      })
      $("#category").select2({
        placeholder: 'Choose Specialization'
      })

      function getSemester(id,val=null) {
            
            $.ajax({
                url: '/app/subjects/semester?id=' + id+"&onload="+val,
                type: 'GET',
                success: function(data) {
                    $("#semester").html(data);
                }
            })
        }

    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
