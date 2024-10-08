<?php
require '../../includes/db-config.php';
session_start();
//if($_SESSION['university_id'] == 48){}
?>

<!-- Modal -->
 <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen" />
  <link href="/assets/plugins/bootstrap-tag/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
  <style>
    .select2-container--default.select2-container--focus .select2-selection--multiple {
      border: unset !important;
      outline: 0;
    }
  </style>
<div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h5>Add <span class="semi-bold">Result</span></h5>
</div>
<style>
    .modal-open .select2-container {
        z-index: auto;
    }
</style>
<form role="form" id="form-add-results" action="/app/results/store" method="POST" enctype="multipart/form-data">
    <div class="modal-body">
        <!-- University & Course -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group form-group-default required">
                    <label>Program Type</label>
                    <select class="full-width" style="border: transparent;" data-init-plugin="select2" id="course_type_id" name="course_type_id" onchange="getCourse(this.value);">
                        <option value="">Select</option>
                        <?php
                        $programs = $conn->query("SELECT ID,Name,Short_Name FROM Courses WHERE Status=1 ORDER BY Name ASC");
                        while ($program = $programs->fetch_assoc()) { ?>
                            <option value="<?= $program['ID'] ?>">
                                <?= $program['Name'] . ' (' . $program['Short_Name'] . ')' ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group form-group-default required">
                    <label>Specialization/Course</label>
                    <select class="full-width" style="border: transparent;" id="sub_course_id" name="sub_course_id" onchange="getStudent(this.value);">
                        <option value="">Select</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="col-md-6 students">
                <div class="form-group form-group-default required">
                    <label>Student</label>
                    <select class="full-width student_id" style="border: transparent;" id="student_ids" name="student_id">
                        <option value="">Select</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 skillTab">
                <div class="form-group form-group-default required">
                    <label>Category</label>
                    <select class="full-width" style="border: transparent;" id="category" name="category" onchange="getCategory(this.value) ">
                        <option value="">Choose Category</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="11/certified">11 Months Certified</option>
                        <option value="11/advance-diploma">11 Months Advance Diploma</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="student_section modal-body">
    </div>


    <div class="modal-footer clearfix justify-content-center">
        <div class="col-md-4 m-t-10 sm-m-t-10">
            <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
                <span>Save</span>
                <span class="hidden-block">
                    <i class="pg-icon">tick</i>
                </span>
            </button>
        </div>
    </div>
</form>


<script>
    $(function() {
        $("#eligibilities").select2();
        $("#course_category").select2();
        // $("#course_type_id").select2({
        //   placeholder: 'Choose Center'
        // })
 
      $(".students").hide()
      $(".skillTab").hide();
      $(".bvocTab").hide();
    })


    function getCourse(id) {
        $.ajax({
            url: '/app/results/cources',
            type: 'POST',
            dataType: 'text',
            data: {
                'program_id': id
            },
            success: function(result) {
                $('#sub_course_id').html(result);
            }
        })
    }

    $(document).on('change', '.studentData', function() {
        var stu_id = $(this).val();
        var course_id = $("#course_type_id").val();
        var duration = $("#category").val();
        var sub_course_id = $("#sub_course_id").val();
        getSubjects(stu_id, course_id, duration, sub_course_id);
    });

    function getStudent(id) {
        var [sub_course_id, scheme_id, university_id] = id.split('|');
        if (university_id == 48) {
            $(".student_id").removeClass("studentData");
            $(".skillTab").show();
         } else {
            $(".student_id").addClass("studentData");
            $(".skillTab").hide();
        }
        $(".students").show()
        var course_id = $("#course_type_id").val();

        $.ajax({
            url: '/app/results/students',
            type: 'POST',
            dataType: 'text',
            data: {
                'sub_course_id': id,
                course_id: course_id
            },
            success: function(result) {
                $('.student_id').html(result);
                // $('#student_id').html(result);

            }
        })
    }

    function getCategory(duration) {
        var course_id = $("#course_type_id").val();
        var stu_id = $(".student_id").val();
        var sub_course_id = $("#sub_course_id").val();
        getSubjects(stu_id, course_id, duration, sub_course_id);
    }

    $(document).on('change', '#category', function() {
        var duration = $(this).val();
        var course_id = $("#course_type_id").val();
        var stu_id = $(".student_id").val();
        var sub_course_id = $("#sub_course_id").val();
        getSubjects(stu_id, course_id, duration, sub_course_id);
    });

    function getSubjects(stu_id = null, course_id = null, duration = null, sub_course_id = null) {
        var course_id = $("#course_type_id").val();
        var duration = $("#category").val();
        var sub_course_id = $("#sub_course_id").val();
        $.ajax({
            url: '/app/results/get-subjects',
            type: 'POST',
            dataType: 'text',
            data: {
                course_id: course_id,
                sub_course_id: sub_course_id,
                stu_id: stu_id,
                duration: duration
            },
            success: function(result) {
                $('.student_section').html(result);
                // getCategory()
            }
        })
    }





    $("#form-add-results").on("submit", function(e) {
        if ($('#form-add-results').valid()) {
            var formData = new FormData(this);
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                dataType: 'json',
                success: function(data) {

                    if (data.status == 200) {
                        notification('success', data.message);
                        $('.modal').modal('hide');

                        // $('#notes-table').DataTable().ajax.reload();
                    } else {
                        notification('danger', data.message);
                    }


                },
                error: function(data) {
                    notification('danger', 'Server is not responding. Please try again later');
                }
            });
        } else {
            //notification('danger', 'Invalid form information.');
        }
    });
</script>