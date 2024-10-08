<?php
session_start();
// echo $_SESSION['Role'];
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
?>
<!-- Modal -->
<div class="modal-body">
    <div class="modal-header">
        <h5 class="mb-0">Bulk Downloads Submitted Assignments</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <form id="assignmentForm" method="post" action="/app/zip_bulk_download/create_zip_structure.php" enctype="multipart/form-data">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="center">Center/Sub Center Name</label>
                    <select class="form-control" id="center" name="center">
                        <option value="">Select Center/Sub Center</option>
                        <?php
                        $sql = "SELECT ID, Name, Code FROM Users WHERE Role='Center' OR Role='Sub-Center' ORDER BY Name";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row["ID"] . '">' . $row["Name"] . ' (' . $row["Code"] . ')</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="coursetype">Course Type</label>
                    <select class="form-control" id="coursetype" name="coursetype" onchange="getSpecialization(this.value);">
                        <option value="">Select Course Type</option>
                        <?php
                        $sql = "SELECT ID,Name FROM Courses";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $courseName = $row["Name"];
                                $couseId = $row["ID"];
                                echo '<option value="' . $couseId . '">' . $courseName . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="subcourse_id">Sub Course Type</label>
                    <select class="form-control" id="subcourse_id_zip" name="subcourse_id" onchange="getsemester(this.value);">
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
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select class="form-control" id="semes_zip" name="seme" onchange="getSubjects(this.value);">
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
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <select class="form-control" id="subject_id_zip" name="subject">
                        <option value="">Select Subject</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">Close Assignment</button>
            <button type="submit" name="submit" class="btn btn-success">Download Assignment</button>
        </div>
    </form>

</div>
<script>
    function getSpecialization(courseName) {
        $.ajax({
            type: 'POST',
            url: '/app/zip_bulk_download/get_subcourses',
            data: {
                courseName: courseName
            },
            success: function(response) {
                $('#subcourse_id_zip').html(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function getsemester(subCourseId) {
        $.ajax({
            type: 'POST',
            url: '/app/zip_bulk_download/getsemester',
            data: {
                subCourseId: subCourseId
            },
            success: function(response) {
                $('#semes_zip').html(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function getSubjects(semester) {
        var subCourseId = $("#subcourse_id_zip").val();
        $.ajax({
            url: '/app/zip_bulk_download/getsubject',
            type: 'POST',
            dataType: 'text',
            data: {
                'semester': semester,
                'sub_course_id': subCourseId
            },
            success: function(response) {
                console.log(response);
                $('#subject_id_zip').html(response);
            }
        })
    }
</script>