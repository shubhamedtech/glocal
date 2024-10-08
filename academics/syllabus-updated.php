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
        <div class=" container-fluid   sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i):
                  $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>
                <?php if (in_array($_SESSION['Role'], ['Administrator', 'University Head'])) { ?>

                  <button class="btn btn-link" aria-label="" title="" data-toggle="tooltip"
                    data-original-title="Add Syllabus" onclick="add('syllabus','lg')"> <i
                      class="uil uil-plus-circle"></i></button>
                <?php } ?>
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
        <div class="card card-transparent">
          <div class="card-header">
            <div class="pull-right">
              <div class="col-xs-12">
                <input type="text" id="sub-courses-search-table" class="form-control pull-right" placeholder="Search">
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover nowrap" id="syllabus-table">
                <thead>
                  <tr>
                    <th>Subject Name</th>
                    <th>Duration</th>
                    <th>Sub-Course(Course)</th>
                    <th>University</th>
                    <th data-orderable="false"></th>
                    <th data-orderable="false"></th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>

        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <script type="text/javascript">
      $(function () {
        var role = '<?= $_SESSION['Role'] ?>';
        var show = role == 'Administrator' ? true : false;
        var table = $('#syllabus-table');
        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': '/app/syllabus/server'
          },
          'columns': [{
            data: "Subject_Name"
          },
          {
            data: "Duration"
          },
          {
            data: "Name"
          },
          {
            data: "uni_name"
          },

          // {
          //   data: "Status",
          //   "render": function (data, type, row) {
          //     var active = data == 0 ? 'Active' : 'Inactive';
          //     var checked = data == 0 ? 'checked' : '';
          //     return '<div class="form-check form-check-inline switch switch-lg success">\
          //               <input onclick="changeStatus(&#39;Sub-Courses&#39;, &#39;' + row.ID + '&#39;)" type="checkbox" ' + checked + ' id="status-switch-' + row.ID + '">\
          //               <label for="status-switch-' + row.ID + '">' + active + '</label>\
          //             </div>';
          //   }
          // },

          // <i class="uil uil-trash icon-xs cursor-pointer" onclick="destroy(&#39;syllabus&#39;, &#39;' + data + '&#39)"></i>\
          {
            data: "Subject_ID",
            "render": function (data, type, row) {
              //  console.log(row);
              return '<div class="button-list text-end">\
                <i class="uil uil-edit icon-xs cursor-pointer" onclick="edit_syllabus(&#39;syllabus&#39;, &#39;' + data + '&#39, &#39;' + row.Duration + '&#39, &#39;' + row.Sub_Course_ID + '&#39,&#39;' + row.uni_id + '&#39,&#39;lg&#39;)"></i>\
              </div>'
            },
            visible: ['Administrator', 'University Head'].includes(role) ? true : false
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
          "iDisplayLength": 25
        };

        table.dataTable(settings);

        // search box for table
        $('#sub-courses-search-table').keyup(function () {
          table.fnFilter($(this).val());
        });

      })
    </script>

    <script type="text/javascript">
      function changeColumnStatus(id, column) {
        $.ajax({
          url: '/app/sub-courses/status',
          type: 'post',
          data: {
            id: id,
            column: column
          },
          dataType: 'json',
          success: function (data) {
            if (data.status == 200) {
              notification('success', data.message);
              $('#syllabus-table').DataTable().ajax.reload(null, false);
            } else {
              notification('danger', data.message);
              $('#syllabus-table').DataTable().ajax.reload(null, false);
            }
          }
        })
      }


      function edit_syllabus(url, subject_id,duration,sub_course_id,uni_id, modal) {
        $.ajax({
          url: '/app/' + url + '/edit?subject_id='+subject_id+'&duration='+duration+'&sub_course_id='+sub_course_id+'&uni_id='+uni_id,
          type: 'GET',
          success: function (data) {
            $('#' + modal + '-modal-content').html(data);
            $('#' + modal + 'modal').modal('show');
          }
        })
      }
    </script>


    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>