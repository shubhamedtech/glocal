<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <div class="page-content-wrapper ">
    <div class="content">
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
        </div>
      </div>
      <div class=" container-fluid">
        <div class="card card-transparent">
          <div class="row" id="assignments"></div>
          <div class="card-header">
            <div class="pull-right">
              <div class="row">
                <div class="col-xs-7" style="margin-right: 10px;">
                  <input type="text" id="e-book-search-table" class="form-control pull-right p-2 fw-bold" placeholder="Search">
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover nowrap" id="Students-table">
                <thead>
                  <tr>
                    <th>Student Name</th>
                    <th>Enrollment No</th>
                    <th>Course Name</th>
                    <th>Sub Courses</th>
                    <th>Practical Subject Name</th>
                    <th>Practical Subject Code</th>
                    <th>Semester</th>
                    <th>Obtained Mark</th>
                    <th>Remark</th>
                    <th>Practical Submission Date</th>
                    <th>Student Status</th>
                    <th>Practical Status</th>
                    <th>Evaluation Status</th>
                    <th>Uploaded Type</th>
                    <th>Download Practical File</th>
                    <th data-orderable="false">Practical File Upload</th>
                    <th>Feedback</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <script type="text/javascript">
      $(function() {
        var role = '<?= $_SESSION['Role'] ?>';
        var show = role == 'Administrator' ? true : false;
        var table = $('#Students-table');
        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': '/app/practicals/server'
          },
          'columns': [{
              data: "student_name"
            },
            {
              data: "enrollment_no"
            },
            {
              data: "universityname"
            },
            {
              data: "sub_course_name"
            },
            {
              data: "subject_name"
            },
            {
              data: "subject_code"
            },
            {
              data: "semester"
            },
            {
              data: "obtained_mark"
            },
            {
              data: "remark"
            },
            {
              data: "created_date"
            },
            {
              data: "student_status"
            },
            {
              data: "practical_status"
            },
            {
              data: "eva_status"
            },
            {
              data: "uploaded_type"
            },
            {
              data: "student_practical_file",
              render: function(data, type, row) {
                var path = '../../uploads/practicals/';
                var file = '';
                if (row.practical_status && row.practical_status !== 'NOT CREATED') {
                  if (row.uploaded_type == 'Manual' || row.uploaded_type == 'Online') {
                    if (row.File_Type && row.File_Type.toLowerCase() === 'pdf') {
                      file = '<a href="' + path + data + '" class="btn btn-primary btn-sm" download>Download Practical File</a>';
                    } else {
                      file = '<a href="' + path + data + '" class="btn btn-primary btn-sm" download>Download Practical File</a>';
                    }
                  }
                }
                return file;
              }
            },
            {
              data: 'idd',
              render: function(data, type, full, meta) {
                if (full.practical_status && full.practical_status === 'CREATED') {
                  if (full.uploaded_type !== 'Manual' && full.uploaded_type !== 'Online') {
                    var buttonHtml = '<button class="btn btn-success btn-block" onclick="opensolution(\'' + full.student_id + '\', \'' + full.subject_id + '\', \'' + full.practical_id + '\')">Manual Upload File</button>';
                    return buttonHtml;
                  }
                }
                return '';
              }
            },
            {
              data: "id",
              render: function(data, type, row) {
                var buttonHtml = '<div class="button-list text-end">';
                if (row.practical_status == 'CREATED' || row.practical_status == 'NOT CREATED') {
                  if (row.uploaded_type == 'Manual' || row.uploaded_type == 'Online') {
                    if (
                      row.eva_status === "Rejected" ||
                      row.eva_status === "Approved" ||
                      row.eva_status === "Submitted" ||
                      row.eva_status === "Not Submitted"
                    ) {
                      buttonHtml += '<i class="btn btn-danger btn-block" onclick="openEditModal(\'' + data + '\')">Edit Result</i>';
                    } else {
                      buttonHtml += '<i class="btn btn-primary btn-block" onclick="openModal(\'' + data + '\')">Set Result</i>';
                    }
                  }
                }
                buttonHtml += '</div>';
                return buttonHtml;
              }
            },
          ],
          "sDom": "<t><'row'<p i>>",
          "destroy": true,
          "scrollCollapse": true,
          "oLanguage": {
            "sLengthMenu": "_MENU_ ",
            "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
          },
          "aaSorting": [],
          "iDisplayLength": 100,
        };
        table.dataTable(settings);
        $('#e-book-search-table').keyup(function() {
          table.fnFilter($(this).val());
        });
      });
    </script>
    <script type="text/javascript">
      function opensolution(id, subjectId, practicalId) {
        // alert('hi');
        $.ajax({
          url: '/app/practicals/admin-practical-review/create',
          type: 'GET',
          data: {
            id,
            subjectId,
            practicalId
          },
          success: function(data) {
            console.log(data);
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
          }
        });
      }
    </script>
    <script type="text/javascript">
      function openModal(id) {
        $.ajax({
          url: '/app/practicals/admin-practical-review/setresult',
          type: 'GET',
          data: {
            practical_id: id
          },
          success: function(data) {
            console.log(data);
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
          }
        });
      }

      function openEditModal(id) {
        $.ajax({
          url: '/app/practicals/practical-existing-result',
          type: 'POST',
          data: {
            practical_id: id
          },
          success: function(response) {
            console.log(response);
            $('#md-modal-content').html(response);
            $('#mdmodal').modal('show');
          },
          error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
          }
        });
      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>