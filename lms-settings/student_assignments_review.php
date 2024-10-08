<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <?php
  unset($_SESSION['filterByDepartment']);
  unset($_SESSION['filterBySubCourses']);
  unset($_SESSION['filterBySubjectdata']);
  unset($_SESSION['filterByUser']);
  unset($_SESSION['filterByVerticalType']);
  unset($_SESSION['filterBysubmitted_students']);
  unset($_SESSION['filterBySemesterdata']);
  ?>
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
            <div class="row d-flex justify-content-start">
              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <select class="full-width" style="width:40px" data-init-plugin="select2" id="vartical_type" onchange="addFilter(this.value,'vertical_type')" data-placeholder="Choose Vertical Type">
                    <option value="">Vertical Type</option>
                    <option value="0">IITS LLP Paramedical</option>
                    <option value="1">Edtech</option>
                  </select>
                </div>
              </div>



              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <select class="full-width" style="width:40px" id="coursetypef" name="coursetype" onchange="addFilter(this.value,'departments')" data-init-plugin="select2">
                    <option value="">Choose Courses Types</option>
                    <?php
                    $sql = "SELECT ID, Name FROM Courses";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                        $courseName = $row["Name"];
                        $courseId = $row["ID"];
                        echo '<option value="' . $courseId . '">' . $courseName . '</option>';
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>



              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <select class="full-width" style="width:40px" data-init-plugin="select2" id="subcourse_id_filter" name="subcourse_id" onchange="addFilter(this.value,'sub_courses')" data-placeholder="Choose SubCourses Types">
                    <option value="">Choose SubCourses Types</option>
                    <?php
                    $ss = "SELECT ID,Name FROM Sub_Courses";
                    $resultt = $conn->query($ss);
                    if ($resultt->num_rows > 0) {
                      while ($roww = $resultt->fetch_assoc()) {
                        $subCourseId = $roww["ID"];
                        $subCourseName = $roww["Name"];
                        echo '<option value="' . $subCourseId . '">' . $subCourseName . '</option>';
                      }
                    }

                    ?>
                  </select>
                </div>
              </div>




              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <select class="full-width" style="width:40px" data-init-plugin="select2" id="semef" name="seme" onchange="addFilter(this.value,'semesterdata')" data-placeholder="Choose Semester">
                    <option value="">Choose Semester</option>
                    <?php
                    $sql = "SELECT ID, Name, Min_Duration FROM Sub_Courses";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                        $semesterId = $row["ID"];
                        $semesterName = $row["Name"];
                        $semesterDuration = $row["Min_Duration"];
                        echo '<option value="' . $semesterId . '">' . ' (Semester: ' . $semesterDuration . ')</option>';
                      }
                    } else {
                      echo '<option value="">No semesters found</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>




              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <!-- <label for="subject">Subject</label> -->
                  <select class="full-width" style="width:40px" id="subject_idf" name="subject" onchange="addFilter(this.value,'subjectdata')" data-init-plugin="select2" data-placeholder="Choose Subjects">
                    <option value="">Choose Subjects</option>
                  </select>
                </div>
              </div>

              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <select class="full-width" style="width:40px" data-init-plugin="select2" id="users" onchange="addFilter(this.value, 'users')" data-placeholder="Choose Center/Sub-Center">
                    <option value="">Choose Center/Sub-Center</option>
                    <?php
                    $ss = "SELECT ID, Name FROM Users WHERE Role = 'Center' OR Role = 'Sub-Center'";
                    $resultt = $conn->query($ss);
                    if ($resultt->num_rows > 0) {
                      while ($roww = $resultt->fetch_assoc()) {
                        $userId = $roww["ID"];
                        $userName = $roww["Name"];
                        echo '<option value="' . $userId . '">' . $userName . '</option>';
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>



              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <select class="full-width" style="width:40px" data-init-plugin="select2" id="users" onchange="addFilter(this.value, 'assignmentstatus')" data-placeholder="Choose Assignment Status">
                    <option value="">Choose Assignment Status</option>
                    <option value="1">SUBMITTED</option>
                    <option value="2">NOT SUBMITTED</option>
                  </select>
                </div>
              </div>
            </div>



















            <div class="pull-right">
              <div class="col-md-2 m-b-10">
                <div class="form-group">
                  <button class="btn btn-lg btn-danger" aria-label="Add bulk Download" data-toggle="tooltip" data-placement="top" title="Add bulk Download" onclick="add('zip_bulk_download','md')">Bulk Assignments</button>
                </div>
              </div>
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
              <table class="table table-hover nowrap" id="students-table">
                <thead>
                  <tr>
                    <th>Vertical Based</th>
                    <th>University Name</th>
                    <th>Student Name</th>
                    <th>Enrollment No</th>
                    <th>Unique ID</th>
                    <th>Course Name</th>
                    <th>SubCourses Name</th>
                    <th>Subject Name</th>
                    <th>Subject Code</th>
                    <th>Semester</th>
                    <th>Student DOB</th>
                    <th>Center/SubCenter Name</th>
                    <th>Center/SubCenter Code</th>
                    <th>Center/SubCenter Short Name</th>
                    <th>Obtained Mark</th>
                    <th>Total Mark</th>
                    <th>Remark</th>
                    <th>Assignment Submission Date</th>
                    <th>Student Status</th>
                    <th>Assignment Status</th>
                    <th>Evaluation Status</th>
                    <th>Uploaded Type</th>
                    <th>Download AnswerSheet</th>
                    <th>Assignment Upload</th>
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
        var table = $('#students-table');
        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': '/app/assignments/server'
          },
          'columns': [{
              data: "verticaltypes"
            },
            {
              data: "Universityname"
            },
            {
              data: "student_name"
            },
            {
              data: "enrollment_no"
            },
            {
              data: "uniqueid"
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
              data: "dateofbirth"
            },
            {
              data: "Center_SubCeter"
            },
            {
              data: "Center_code"
            },
            {
              data: "Center_Short_Name"
            },
            {
              data: "obtained_mark"
            },
            {
              data: "total_mark"
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
              data: "assignment_status"
            },
            {
              data: "eva_status"
            },
            {
              data: "uploaded_type"
            },
            {
              data: "file_name",
              render: function(data, type, row) {
                var fileLinks = "";
                var path = '../../uploads/assignments/';
                if (row.assignment_status && row.assignment_status !== 'NOT CREATED') {
                  if (row.uploaded_type === 'Manual' || row.uploaded_type === 'Online') {
                    var files = data.split(',').map(file => encodeURIComponent(file.trim())).join(',');
                    var zipLink = '/app/assignments/admin_zip_files.php?files=' + files +
                      '&student_name=' + encodeURIComponent(row.student_name) +
                      '&enrollment_no=' + encodeURIComponent(row.enrollment_no) +
                      '&subject_name=' + encodeURIComponent(row.subject_name);
                    fileLinks += '<a href="' + zipLink + '" class="btn btn-danger btn-sm" download>Download Assignments</a> ';
                  }
                }
                return fileLinks;
              }
            },
            {
              data: 'idd',
              render: function(data, type, full, meta) {
                if (full.assignment_status && full.assignment_status === 'CREATED') {
                  if (full.uploaded_type !== 'Manual' && full.uploaded_type !== 'Online') {
                    var buttonHtml = '<button class="btn btn-primary btn-block" onclick="opensolution(\'' + full.student_id + '\', \'' + full.subject_id + '\', \'' + full.assignment_id + '\')">Manual</button>';
                    return buttonHtml;
                  }
                }
                return '';
              }
            },
            // {
            //   data: 'idd',
            //   render: function(data, type, full, meta) {
            //     if (full.practical_status && full.practical_status === 'CREATED') {
            //       if (full.uploaded_type !== 'Manual' && full.uploaded_type !== 'Online') {
            //         var buttonHtml = '<button class="btn btn-success btn-block" onclick="opensolution(\'' + full.student_id + '\', \'' + full.subject_id + '\', \'' + full.practical_id + '\')">Manual Upload File</button>';
            //         return buttonHtml;
            //       }
            //     }
            //     return '';
            //   }
            // },
            {
              data: "id",
              render: function(data, type, row) {
                var buttonHtml = '<div class="button-list text-end">';
                if (row.assignment_status == 'CREATED' || row.assignment_status == 'NOT CREATED') {
                  if (row.uploaded_type == 'Manual' || row.uploaded_type == 'Online') {
                    if (
                      row.eva_status === "Rejected" ||
                      row.eva_status === "Approved" ||
                      row.eva_status === "Submitted" ||
                      row.eva_status === "Not Submitted"
                    ) {
                      var sub_id = row.subject_id;
                      buttonHtml += '<i class="btn btn-success btn-block" onclick="openEditModal(\'' + data + '\',\'' + sub_id + '\')">Edit Result</i>';
                    } else {
                      var subj = row.subject_id;
                      buttonHtml += '<i class="btn btn-warning btn-block" onclick="openModal(\'' + data + '\',\'' + subj + '\')">Set Result</i>';
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
          "iDisplayLength": 25,
        };
        table.dataTable(settings);
        $('#e-book-search-table').keyup(function() {
          table.fnFilter($(this).val());
        });
      });
    </script>
    <script type="text/javascript">
      function opensolution(id, subjectId, assignmentId) {
        $.ajax({
          url: '/app/assignments/admin-assignment-review/create',
          type: 'GET',
          data: {
            id,
            subjectId,
            assignmentId
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
      function openModal(id, subj) {
        $.ajax({
          url: '/app/assignments/admin-assignment-review/setresult',
          type: 'GET',
          data: {
            assignment_id: id,
            subj: subj
          },
          success: function(data) {
            console.log(data);
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
          }
        });
      }

      function openEditModal(id, sub_id) {
        $.ajax({
          url: '/app/assignments/assignment-existing-result',
          type: 'POST',
          data: {
            assignment_id: id,
            sub_id: sub_id

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
    <script>
      function addFilter(id, by) {
        // alert("hello");
        $.ajax({
          url: '/app/assignments/filter',
          type: 'POST',
          data: {
            id,
            by
          },
          dataType: 'json',
          success: function(data) {
            if (data.status) {
              $('.table').DataTable().ajax.reload(null, false);
              $("#sub_center").html(data.subCenterName);
              if ('<?= $_SESSION['Role'] ?>' == 'Administrator') {
                $(".sub_center").html(data.subCenterName);

              }
            }
          }
        })
      }



      // function addSubCenterFilter(id, by) {
      //   $.ajax({
      //     url: '/app/assignments/filter',
      //     type: 'POST',
      //     data: {
      //       id,
      //       by
      //     },
      //     dataType: 'json',
      //     success: function(data) {
      //       if (data.status) {
      //         $('.table').DataTable().ajax.reload(null, false);
      //       }
      //     }
      //   })
      // }
    </script>

    <script>
      // function getSpecialization(courseName) {
      //   $.ajax({
      //     type: 'POST',
      //     url: '/app/assignments/get_subcourses',
      //     data: {
      //       couseId: courseName
      //     },
      //     success: function(response) {
      //       $('#subcourse_id').html(response);
      //     },
      //     error: function(xhr, status, error) {
      //       console.error(xhr.responseText);
      //     }
      //   });
      // }

      // function getsemester(subCourseId) {
      //   $.ajax({
      //     type: 'POST',
      //     url: '/app/assignments/getsemester',
      //     data: {
      //       subCourseId: subCourseId
      //     },
      //     success: function(response) {
      //       $('#seme').html(response);
      //     },
      //     error: function(xhr, status, error) {
      //       console.error(xhr.responseText);
      //     }
      //   });
      // }

      // function getSubjects(semester) {
      //   var subCourseId = $("#subcourse_id").val();
      //   $.ajax({
      //     url: '/app/assignments/getsubject',
      //     type: 'POST',
      //     dataType: 'text',
      //     data: {
      //       'semester': semester,
      //       'sub_course_id': subCourseId
      //     },
      //     success: function(response) {
      //       console.log(response);
      //       $('#subject_id').html(response);
      //     }
      //   })
      // }
    </script>
    <script>
      $(document).ready(function() {
        $("#coursetypef").change(function() {
          var courseId = $(this).val();
          if (courseId) {
            $.ajax({
              type: "POST",
              url: "/app/assignments/get_subcourses",
              data: {
                courseId: courseId
              },
              success: function(response) {
                $("#subcourse_id_filter").html(response);
              }
            });
          } else {
            $("#subcourse_id_filter").html('<option value="">Choose SubCourses Types</option>');
          }
        });
      });
    </script>
    <script>
      $(document).ready(function() {
        $("#subcourse_id_filter").change(function() {
          var subCourseId = $(this).val();
          if (subCourseId) {
            $.ajax({
              type: 'POST',
              url: '/app/assignments/getsemester',
              datatype: 'text',
              data: {
                subCourseId: subCourseId
              },
              success: function(response) {
                $("#semef").html(response);
              }
            })
          } else {
            $("#semef").html('<option value="">Choose Semester</option>');
          }
        })
      })
    </script>
    <script>
      $(document).ready(function() {
        $("#semef").change(function() {
          var semester = $(this).val();
          var sub_course_id = $('#subcourse_id_filter').val();
          if (semester) {
            $.ajax({
              type: 'POST',
              url: '/app/assignments/getsubject',
              data: {
                'sub_course_id': sub_course_id,
                'semester': semester
              },
              datatype: 'text',
              success: function(response) {
                console.log(response);
                $("#subject_idf").html(response);
              }
            });
          } else {
            $("#subject_idf").html('<option value="">Choose Subjects</option>');
          }
        });
      });
    </script>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>