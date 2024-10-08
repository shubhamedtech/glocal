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
          <div class="col-md-6">
            <div class="form-group form-group-default required">
              <label>Admission Session</label>
              <select class="full-width" style="border: transparent;" id="adm_sessions" onchange="getExamSession(this.value); removeTable()">
                <option value="">Choose</option>
                <?php
                $condition = "";
                $sub_courses = $conn->query("SELECT * FROM Admission_Sessions WHERE University_ID = ".$_SESSION['university_id']." ");
                while ($sub_course = $sub_courses->fetch_assoc()) {
                  echo '<option value="' . $sub_course['ID'] . '">' . $sub_course['Name'] . '</option>';
                }
                ?>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group form-group-default required">
              <label>Exam Session</label>
              <select class="full-width" style="border: transparent;" id="exam_session" onchange="getTable()">
                <option value="">Choose</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12" id="exam_session_students">
            
          </div>
        </div>
        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <script type="text/javascript">
      function getTable() {
        var adm_sessions = $('#adm_sessions').val();
        var exam_session = $('#exam_session').val();    
        if (exam_session.length > 0 || adm_sessions.length > 0) {
          $.ajax({
            url: '../app/exams/ajax/get-all-students-by-session?adm_sessions=' + adm_sessions + '&exam_session=' + exam_session,
            type: 'GET',
            success: function(data) {
              $('#exam_session_students').html(data);
            }
          })
        } else {
            $.ajax({
            url: '../app/exams/ajax/get-all-students-by-session',
            type: 'GET',
            success: function(data) {
              $('#exam_session_students').html(data);
            }
          })
        }
      }

      getTable()
      function removeTable() {
        $('#reg_results').html('');
      }
    </script>

<script type="text/javascript">
      function getExamSession(id) {
        $.ajax({
          url: '/app/exams/ajax/get-exam-session?id=' + id,
          type: 'GET',
          success: function(data) {
            getTable();
            $("#exam_session").html(data);
          }
        })
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

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
