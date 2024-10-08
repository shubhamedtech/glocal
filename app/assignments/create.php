<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
?>
<!-- Modal -->
<div class="modal-body">
    <div class="modal-header">
        <h5 class="mb-0">Create Assignment</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <form id="assignmentForm" method="post" action="/app/assignments/create_assignment.php" enctype="multipart/form-data">
        <input type="hidden" name="created" value="<?php echo $_SESSION['Role']; ?>">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="coursetype">Course Type</label>
                    <select class="form-control" id="coursetype" name="coursetype" onchange="getSpecialization(this.value);" required>
                        <option value="">Select Course Type</option>
                        <?php
                        $sql = "SELECT ID,Name FROM Courses";
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
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="subcourse_id">Sub Course Type</label>
                    <select class="form-control" id="subcourse_id" name="subcourse_id" onchange="getsemester(this.value);" required>
                        <option value="">Select Sub Course Type</option>
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
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select class="form-control" id="seme" name="seme" onchange="getSubjects(this.value);" required>
                        <option value="">Select Semester Type</option>
                        <?php
                        $sql = "SELECT ID, Name, Min_Duration FROM Sub_Courses";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $semesterId = $row["ID"];
                                $semesterName = $row["Name"];
                                $semesterDuration = $row["Min_Duration"];
                                echo '<option value="' . $semesterId . '">' . $semesterName . ' (Semester: ' . $semesterDuration . ')</option>';
                            }
                        } else {
                            echo '<option value="">No semesters found</option>';
                        }


                        ?>
                    </select>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <select class="form-control" id="subject_id" name="subject" required>
                        <option value="">Select Subject</option>
                    </select>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="form-group">
                    <label for="assignmentname">Assignment Name</label>
                    <input type="text" class="form-control" id="assignmentname" placeholder="Assignment Name" required name="assignmentname">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="marks">Total Assignment Marks</label>
                    <input type="number" class="form-control" id="marks" placeholder="Marks" required name="marks">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="files">Question Assignment File</label>
                    <input type="file" class="form-control" id="files" required name="files" accept=".pdf, .jpeg, .jpg">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="startdate">Start Date</label>
                    <input type="date" class="form-control" id="startdate" required name="startdate">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="enddate">End Date</label>
                    <input type="date" class="form-control" id="enddate" required name="enddate">
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">Close Assignment</button>
            <button type="submit" name="submit" class="btn btn-success">Save Assignment</button>
        </div>
    </form>
</div>
<script>
    function getSpecialization(courseId) {
        $.ajax({
            type: 'POST',
            url: '/app/assignments/get_subcourses',
            data: {
                courseId: courseId
            },
            success: function(response) {
                $('#subcourse_id').html(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function getsemester(subCourseId) {
        $.ajax({
            type: 'POST',
            url: '/app/assignments/getsemester',
            data: {
                subCourseId: subCourseId
            },
            success: function(response) {
                $('#seme').html(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function getSubjects(semester) {
        var subCourseId = $("#subcourse_id").val();
        $.ajax({
            url: '/app/assignments/getsubject',
            type: 'POST',
            dataType: 'text',
            data: {
                'semester': semester,
                'sub_course_id': subCourseId
            },
            success: function(response) {
                console.log(response);
                $('#subject_id').html(response);
            }
        })
    }

    function setMinDate() {
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('startdate').min = today;
        document.getElementById('enddate').min = today;
        document.getElementById('enddate').addEventListener('change', function() {
            var endDate = document.getElementById('enddate').value;
            document.getElementById('startdate').max = endDate;
        });
    }
    document.addEventListener('DOMContentLoaded', setMinDate);
    $(document).ready(function() {
        $("#startdate").on("change", function() {
            $("#enddate").attr("min", $(this).val());
        });
    });
</script>
<script>
    $(function() {
        $('#assignmentForm').validate({
            rules: {
                coursetype: {
                    required: true
                },
                subcourse_id: {
                    required: true
                },
                seme: {
                    required: true
                },
                subject: {
                    required: true
                },
                assignmentname: {
                    required: true
                },
                marks: {
                    required: true
                },
                files: {
                    required: true
                },
                startdate: {
                    required: true
                },
                enddate: {
                    required: true
                },
            },
            highlight: function(element) {
                $(element).addClass('error');
                $(element).closest('.form-control').addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).removeClass('error');
                $(element).closest('.form-control').removeClass('has-error');
            }
        });
    })
    $("#assignmentForm").on("submit", function(e) {
        if ($('#assignmentForm').valid()) {
            $(':input[type="submit"]').prop('disabled', true);
            var formData = new FormData(this);
            $.ajax({
                url: this.action,
                type: 'post',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(data) {
                    if (data.status == 200) {
                        $('.modal').modal('hide');
                        notification('success', data.message);
                        $('#admin_table').DataTable().ajax.reload(null, false);
                    } else {
                        $(':input[type="submit"]').prop('disabled', false);
                        notification('danger', data.message);
                    }
                }
            });
            e.preventDefault();
        }
    });
</script>