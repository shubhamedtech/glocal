<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<!-- START PAGE-CONTAINER -->
<div class="page-container">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <div class="page-content-wrapper">
    <div class="content">
      <div class="jumbotron" data-pages="parallax">
        <div class="container-fluid sm-p-l-0 sm-p-r-0"></div>
      </div>
      <div class="container-fluid">
        <div class="card card-transparent">
          <div class="card-header">
            <div class="pull-right">
              <div class="row">
                <div class="col-xs-7" style="margin-right: 10px;">
                  <input type="text" id="practicals" class="form-control pull-right p-2 fw-bold" placeholder="Search">
                </div>
                <div class="d-flex">
                  <button class="btn btn-sm btn-primary" aria-label="Create Practical" data-toggle="tooltip" data-placement="top" title="Create Practical" onclick="add('practicals','md')">Create Practical</button>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover nowrap" id="admin_table">
                <thead>
                  <tr>
                    <th>Course Name</th>
                    <th>Sub Course Name</th>
                    <th>Practical Subject Name</th>
                    <th>Semester</th>
                    <th>Practical Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Created By</th>
                    <th>Marks</th>
                    <th>Created Date</th>
                    <th>Updated Date</th>
                    <th>Download Practical File</th>
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
</div>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>

<script type="text/javascript">
  $(function() {
    var role = '<?= $_SESSION['Role'] ?>';
    var show = role == 'Administrator' ? true : false;
    var table = $('#admin_table');

    var settings = {
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'ajax': {
        'url': '/app/practicals/admin'
      },
      'columns': [{
          data: "course_name"
        },
        {
          data: "sub_course_name"
        },
        {
          data: "subject_name"
        },
        {
          data: "semester"
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
          data: "created_by"
        },
        {
          data: "marks"
        },
        {
          data: "created_date"
        },
        {
          data: "updated_date"
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
          data: "id",
          "render": function(data, type, row) {
            return '<div class="button-list text-end">\
                <i class="uil uil-edit icon-xs cursor-pointer" title="Edit" onclick="edit(&#39;practicals&#39;, &#39;' + data + '&#39, &#39;lg&#39;)"></i>\
                <i class="uil uil-trash icon-xs cursor-pointer" title="Delete" onclick="destroy(&#39;practicals&#39;, &#39;' + data + '&#39)"></i>\
              </div>'
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
    $('#practicals').keyup(function() {
      table.fnFilter($(this).val());
    });
  });
</script>