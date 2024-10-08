<?php

if (isset($_GET['id']) && !empty($_GET['id'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $assignment_id = $_GET['id'];
    $sql = "SELECT * FROM student_assignment WHERE Assignment_id = $assignment_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $assignment = $result->fetch_assoc();
        $course_type = $assignment['course_id'];
        $sub_course_type = $assignment['sub_course_id'];
        $semester = $assignment['semester'];
        $subject = $assignment['subject_id'];
        $assignment_name = $assignment['assignment_name'];
        $marks = $assignment['marks'];
        $file_path = $assignment['file_path'];
        $start_date = $assignment['start_date'];
        $end_date = $assignment['end_date'];
    } else {
        echo "Assignment not found";
        exit;
    }
}
?>
<div class="modal-body">
    <div class="modal-header">
        <h5 class="mb-0">Edit Assignment</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <form id="editassignmentform" method="post" action="/app/assignments/update_assignment.php" enctype="multipart/form-data">
        <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="coursetype">Course Type</label>
                    <select class="form-control" id="coursetype" name="coursetype" onchange="getSpecialization(this.value);">
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
                    <select class="form-control" id="subcourse_id" name="subcourse_id" onchange="getsemester(this.value);">
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
                    <select class="form-control" id="seme" name="seme" onchange="getSubjects(this.value);">
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
                    <label for="subject_id">Subject</label>
                    <select class="form-control" id="subject_id" value="<?php echo $subject; ?>" name="subject_id">
                        <option value="">Select Subject</option>
                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="assignmentname">Assignment Name</label>
                    <input type="text" class="form-control" value="<?php echo $assignment_name; ?>" id="assignmentname" placeholder="Assignment Name" name="assignmentname">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="marks">Marks</label>
                    <input type="number" class="form-control" value="<?php echo $marks; ?>" id="marks" placeholder="Marks" name="marks">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="files">Assignment File</label>
                    <input type="file" class="form-control" id="files" name="filesin" accept=".pdf, .jpeg, .jpg">
                    <?php if (!empty($file_path)) : ?>
                        <p>
                            <a href="../../uploads/<?= htmlspecialchars($file_path) ?>" target="_blank">View Assignment File</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="startdate">Start Date</label>
                    <input type="date" class="form-control" value="<?php echo $start_date; ?>" id="startdate" name="startdate">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="enddate">End Date</label>
                    <input type="date" class="form-control" value="<?php echo $end_date; ?>" id="enddate" name="enddate">
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close Assignment</button>
                    <button type="submit" class="btn btn-primary">Update Assignment</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    function getSpecialization(courseName) {
        //if (courseName !== '') {
        $.ajax({
            type: 'POST',
            url: '/app/assignments/get_subcourses',
            data: {
                courseId: courseName
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
                $('#seme').val('<?= $semester ?>')
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
                $('#subject_id').val('<?= $subject ?>')
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
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
    // $(function() {
    //     $('#editassignmentform').validate({
    //         rules: {
    //             coursetype: {
    //                 required: true
    //             },
    //             subcourse_id: {
    //                 required: true
    //             },
    //             seme: {
    //                 required: true
    //             },
    //             subject: {
    //                 required: true
    //             },
    //             assignmentname: {
    //                 required: true
    //             },
    //             marks: {
    //                 required: true
    //             },
    //             files: {
    //                 required: true
    //             },
    //             startdate: {
    //                 required: true
    //             },
    //             enddate: {
    //                 required: true
    //             },
    //         },
    //         highlight: function(element) {
    //             $(element).addClass('error');
    //             $(element).closest('.form-control').addClass('has-error');
    //         },
    //         unhighlight: function(element) {
    //             $(element).removeClass('error');
    //             $(element).closest('.form-control').removeClass('has-error');
    //         }
    //     });
    // });
    $("#editassignmentform").on("submit", function(e) {
        if ($('#editassignmentform').valid()) {
            $(':input[type="submit"]').prop('disabled', true);
            var formData = new FormData(this);
            formData.append('assignment_id', '<?= $assignment_id; ?>');
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
                },
                error: function() {
                    $(':input[type="submit"]').prop('disabled', false);
                    notification('success', 'file Updated Successfully.');
                    window.location.href = "assignments";
                }
            });
            e.preventDefault();
        }
    });

    $(function() {
        getsemester('<?= $sub_course_type ?>');
    })
    $(function() {
        getSubjects('<?= $semester ?>');
    })
</script>