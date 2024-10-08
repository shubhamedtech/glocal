<?php
// print_r(($_GET['id']));
ini_set('display_errors', 1);
session_start();
if (isset($_GET['id']) || !empty($_GET['id'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $practical_id = $_GET['id'];
    $sql = "SELECT * FROM Student_Practical WHERE id = $practical_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $practical = $result->fetch_assoc();
        $course_type = $practical['course_id'];
        $sub_course_type = $practical['sub_course_id'];
        $semester = $practical['semester'];
        $practical_idd = $practical['subject_id'];
        $practical_name = $practical['practical_name'];
        $marks = $practical['marks'];
        $file_path = $practical['practical_file'];
        $start_date = $practical['start_date'];
        $end_date = $practical['end_date'];
    } else {
        echo "Practical not found";
        exit;
    }
}
?>
<!-- Modal -->
<div class="modal-body">
    <div class="modal-header">
        <h5 class="mb-0">Edit Practical</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <form id="practicalForm" method="post" action="/app/practicals/update_practical" enctype="multipart/form-data">
        <input type="hidden" name="practical_id" value="<?php echo $practical_id; ?>">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="coursetype">Course Type</label>
                    <select class="form-control" id="coursetype" name="coursetype" onchange="getSpecialization(this.value);" required>
                        <option value="">Select Course Type</option>
                        <?php
                        $sql = "SELECT ID, Name FROM Courses";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $option_value = $row["ID"];
                                $option_label = $row["Name"];
                                $selected = ($option_value == $course_type) ? 'selected' : '';
                                echo '<option value="' . $option_value . '" ' . $selected . '>' . $option_label . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="subcourse_id">Sub Course Type</label>
                    <select class="form-control" id="subcourse_id" name="subcourse_id" onchange="getSemester(this.value);" required>
                        <option value="">Select Sub Course Type</option>
                        <?php
                        $sql = "SELECT ID, Name FROM Sub_Courses";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $subCourseId = $row["ID"];
                                $subCourseName = $row["Name"];
                                $selected = ($subCourseId == $sub_course_type) ? 'selected' : '';
                                echo '<option value="' . $subCourseId . '" ' . $selected . '>' . $subCourseName . '</option>';
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
                        $sql = "SELECT ID, Min_Duration FROM Sub_Courses";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $semesterId = $row["ID"];
                                $semesterDuration = $row["Min_Duration"];
                                $selected = ($semesterId == $sub_course_type) ? 'selected' : '';
                                echo '<option value="' . $semesterId . '" ' . $selected . '>';
                                echo  ' (Semester: ' . $semesterDuration . ')';
                                echo '</option>';
                            }
                        } else {
                            echo '<option value="">No semesters found</option>';
                        }

                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="subject_id">Practical Subject</label>
                    <select class="form-control" id="Practical" value="<?php echo $practical_idd ?>" name="subject_id">
                        <option value="">Select Practical</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="practicalname">Practical Name</label>
                    <input type="text" class="form-control" id="practicalname" value="<?php echo $practical_name; ?>" placeholder="Practical Name" required name="practicalname">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="marks">Total Practical Marks</label>
                    <input type="number" class="form-control" id="marks" value="<?php echo $marks; ?>" placeholder="Marks" required name="marks">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="files">Practical File</label>
                    <input type="file" class="form-control" id="files" name="filesin" accept=".pdf, .jpeg, .jpg">
                    <?php if (!empty($file_path)) : ?>
                        <p>
                            <a href="../../uploads/<?= htmlspecialchars($file_path) ?>" target="_blank">View Practical File</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="startdate">Start Date</label>
                    <input type="date" class="form-control" id="startdate" value="<?php echo $start_date; ?>" required name="startdate">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="enddate">End Date</label>
                    <input type="date" class="form-control" id="enddate" value="<?php echo $end_date; ?>" required name="enddate">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close Practical</button>
            <button type="submit" name="submit" class="btn btn-primary">Update Practical</button>
        </div>
    </form>
</div>
<script>
    function getSpecialization(courseName) {
        $.ajax({
            type: 'POST',
            url: '/app/practicals/get_subcourses',
            data: {
                couseId: courseName
            },
            success: function(response) {
                $('#subcourse_id').html(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function getSemester(subCourseId) {
        $.ajax({
            type: 'POST',
            url: '/app/practicals/getsemester',
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
            url: '/app/practicals/getsubject',
            type: 'POST',
            dataType: 'text',
            data: {
                'semester': semester,
                'sub_course_id': subCourseId
            },
            success: function(response) {
                $('#Practical').html(response);
            }
        });
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

    $(function() {
        $('#practicalForm').validate({
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
                subject_id: {
                    required: true
                },
                practicalname: {
                    required: true
                },
                marks: {
                    required: true
                },
                filesin: {
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
    });

    // $("#practicalForm").on("submit", function(e) {
    //   if ($('#practicalForm').valid()) {
    //     $(':input[type="submit"]').prop('disabled', true);
    //     var formData = new FormData(this);
    //     $.ajax({
    //       url: this.action,
    //       type: 'post',
    //       data: formData,
    //       cache: false,
    //       contentType: false,
    //       processData: false,
    //       dataType: "json",
    //       success: function(data) {
    //         if (data.status == 200) {
    //           $('.modal').modal('hide');
    //           notification('success', data.message);
    //           $('#admin_table').DataTable().ajax.reload(null, false);
    //         } else {
    //           $(':input[type="submit"]').prop('disabled', false);
    //           notification('danger', data.message);
    //         }
    //       }
    //     });
    //     e.preventDefault();
    //   }
    // });
</script>