<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
<div class="page-container ">
  <!-- <div class="page-content-wrapper "> -->
  <div class="content ">
    <div class="jumbotron" data-pages="parallax">
      <div class=" container-fluid sm-p-l-0 sm-p-r-0">
      </div>
    </div>
    <div class=" container-fluid">
      <div class="card card-transparent">
        <div class="card-header">
          <?php
          ?>
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
            <table class="table table-hover nowrap" id="student_table">
              <thead>
                <tr>
                  <th>Practical Subject Name</th>
                  <th>Practical Subject Code</th>
                  <th>Practical Name</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Total Marks</th>
                  <th>Obtained Marks</th>
                  <th>Reason</th>
                  <th>Teacher Status</th>
                  <th>Student Status</th>
                  <th>Teacher Practical File</th>
                  <th>Student Practical File</th>
                  <th>Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END PAGE CONTENT -->

<script type="text/javascript">
  $(function() {
    var role = '<?= $_SESSION['Role'] ?>';
    var show = role == 'Administrator' ? true : false;
    var table = $('#student_table');

    var settings = {
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'ajax': {
        'url': '/app/practicals/student'
      },
      'columns': [{
          data: "Name"
        },
        {
          data: "Code"
        },
        {
          data: "practical_name"
        },
        {
          data: "start_date"
        },
        {
          data: "end_date"
        },
        {
          data: "marks"
        },
        {
          data: "obtained_mark"
        },
        {
          data: "remark"
        },
        {
          data: "status"
        },
        {
          data: "practical_submission_status"
        },
        {
          data: "practical_file",
          render: function(data, type, row) {
            var path = '../../uploads/practicals/';
            var file;
            if (row.File_Type && row.File_Type.toLowerCase() === 'pdf') {
              file = '<a href="' + path + data + '" class="btn btn-danger btn-sm" download>Download Practical File</a>';
            } else {
              file = '<a href="' + path + data + '"  class="btn btn-danger btn-sm" download>Download Practical File</a>';
            }
            return file;
          }
        },
        {
          data: "student_practical_file",
          render: function(data, type, row) {
            var path = '../../uploads/practicals/';
            var file;
            if (row.File_Type && row.File_Type.toLowerCase() === 'pdf') {
              file = '<a href="' + path + data + '" class="btn btn-warning btn-sm" download>Practical File</a>';
            } else {
              file = '<a href="' + path + data + '" class="btn btn-warning btn-sm" download>Practical File</a>';
            }
            return file;
          }
        },
        {
          data: "student_practical_file",
          render: function(data, type, row) {
            var uploadDir = '../../uploads/practicals/';
            var filePath = uploadDir + data;
            var button = '';
            if (row.status !== 'Rejected') {
              if (data && row.file_exists) {
                button += '<a href="' + filePath + '" class="btn btn-danger btn-sm" download>Download Practical File</a>';
              }
              button += '<button class="btn btn-primary btn-sm" data-toggle="modal" onclick=\'openUploadModal("' + row.id + '", "' + row.subject_id + '")\'>Upload Practical File</button>';
            } else {
              if (data && row.file_exists) {
                button += '<a href="' + filePath + '" class="btn btn-danger btn-sm" download>Download Updated Practical</a>';
              }
              button += '<button class="btn btn-warning btn-sm" data-toggle="modal" onclick=\'openUploadModal("' + row.id + '", "' + row.subject_id + '")\'>Reupload Practical File</button>';
            }

            return button;
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
      "iDisplayLength": 25,
    };
    table.dataTable(settings);
    // search box for table
    $('#e-book-search-table').keyup(function() {
      table.fnFilter($(this).val());
    });
  })
</script>
<script type="text/javascript">
  function openUploadModal(id, subject_id) {
    $.ajax({
      url: '/app/practicals/student_result_review',
      type: 'GET',
      data: {
        id,
        subject_id
      },
      success: function(data) {
        console.log(data);
        $('#md-modal-content').html(data);
        $('#mdmodal').modal('show');
      }
    });
  }
</script>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>