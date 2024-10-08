<?php
require '../../includes/db-config.php';
session_start();
//if($_SESSION['university_id'] == 48){}
?>

<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Create<span class="semi-bold"> Certificate</span></h5>
</div>

<form role="form" id="form-add-e-book" action="/app/certificates/download-bulk-certificate" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Program Type</label>
          <select required class="full-width" style="border: transparent;" id="course_type_id" name="course_type_id" onchange="getSubCourse(this.value);">
            <option value="">Select</option>
            <?php
            $programs = $conn->query("SELECT ID,Name,Short_Name FROM Courses WHERE University_ID = 48");
            while ($program = $programs->fetch_assoc()) { ?>
              <option value="<?= $program['ID'] ?>">
                <?= $program['Name'] . ' (' . $program['Short_Name'] . ')' ?>
              </option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default ">
          <label>Specialization/Course</label>
          <select class="full-width"  style="border: transparent;" id="sub_course_id" name="course_id" onchange="getSubjects(this.value);">
            <option value="">Select</option>
          </select>
        </div>
      </div>
      <div class="col-md-12">
        <div class="form-group form-group-default ">
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
    <div class="row">
      <div class="col-md-12">
        <div class="form-group form-group-default ">
          <label>Student</label>
          <input type="text" class="full-width" style="border: transparent;" id="student_id" name="student_id">
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer clearfix justify-content-center">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <input type="submit" class="btn btn-primary btn-cons btn-animated from-left" value="Save">
      </button>
    </div>
  </div>
</form>


<script>
  function view() {

    var student_id = $('#student_id').val();
    var course_id = $('#course_type_id').val();
    var sub_course_id = $('#sub_course_id').val();


    if (student_id == undefined) {
      notification('danger', 'Please select student to proceed!');
      return false;
    } 
    
    // else {

    //   var request = $.ajax({
    //     url: "/app/certificates/certificate-view",
    //     type: "POST",
    //     data: {
    //       student_id: student_id,course_id:course_id,sub_course_id:sub_course_id
    //     },
    //     dataType: "json",
    //     success: function(data) {
    //       if (data.status == 200) {
    //         notification('success', data.message);
    //         $('.modal').modal('hide');
    //       } else {
    //         notification('danger', data.message);
    //       }

    //       $('#e_books-table').DataTable().ajax.reload(null, false);

    //     },
    //     error: function(data) {
    //       notification('danger', 'Server is not responding. Please try again later');
    //     }
    //   });

    //   // window.location.assign("/app/certificates/certificate-view?student_id="+student_id);
    // }

  }

  function getSubCourse(course_id) {

    const durations = $('#min_duration').val();
    const university_id = $('#university_id').val();
    const mode = $('#mode').val();
    $.ajax({
      url: '/app/certificates/get-subcourse?course_id=' + course_id,
      type: 'GET',
      success: function(data) {
        $('#sub_course_id').html(data);
        // $("#sub_course_id").select2({
        //   placeholder: 'Choose Specialization'
        // })
      }
    });
  }

  // $("#sub_course_id").select2({
  //         placeholder: 'Choose Specialization'
  //       })

</script>