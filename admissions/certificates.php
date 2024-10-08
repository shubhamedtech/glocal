<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php');

  $base_url="http://".$_SERVER['HTTP_HOST']."/";
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
              
               <button class="btn btn-primary p-2 "  data-toggle="tooltip" data-original-title="Download Bulk Certificate" onclick="add_bulk('certificates','lg')"> <i class="uil uil-download-circle\"></i>Download Bulk Certificate</button>

              <button class="btn btn-primary p-2 "  data-toggle="tooltip" data-original-title="Add" onclick="add('certificates','lg')"> <i class="uil uil-plus-circle"></i>Add</button>
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
          
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover nowrap" id="e_books-table">
                <thead>

                <tr>
                  <th>Student name</th>
                  <th>Enrollment No</th>
                  <th>Course/Specialization</th>
                  <th>Issue Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
                </thead>
              </table>
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
            var table = $('#e_books-table');

            var settings = {
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': '/app/certificates/data-list'
                },
                'columns': [{
                        data: "student_name"
                    },
                    {
                        data: "enrollment_no"
                    },
                    {
                        data: "course_name"
                    },
                    {
                        data: "issue_date"
                    },
                    {
                      data: "status",
                      "render": function(data, type, row) {
                        var active = data == 1 ? 'Active' : 'Inactive';
                        var checked = data == 1 ? 'checked' : '';
                        return '<div class="form-check form-check-inline switch switch-lg success">\
                                <input onclick="changeStatus('+"'certificates'"+', '+row.ID+','+"'status'"+')" type="checkbox" ' + checked + ' id="status-switch-' + row.ID + '">\
                                <label for="status-switch-' + row.ID + '">' + active + '</label>\
                              </div>';
                      }
                    },
                    {
                      data: "ID",
                      "render": function(data, type, row) {
                        return '<div class="button-list ">\
                        <i class="uil uil-eye pt-2 cursor-pointer" onclick="viewCertificate('+"'"+row.file_path+"'"+')"></i>\
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
                "iDisplayLength": 5,
            };
            table.dataTable(settings);
            // search box for table
            $('#e-book-search-table').keyup(function() {
              table.fnFilter($(this).val());
            });
          })

        function viewCertificate(file_path){
          window.open("../"+file_path,'_blank');
        }
    </script>
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
    
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>

<!--<i class="uil uil-trash icon-xs cursor-pointer" onclick="changeStatus('+"'certificates'"+', '+data+','+"'status'"+',2)"></i> -->