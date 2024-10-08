<link href="../../assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen" />
<link href="../../assets/plugins/bootstrap-tag/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Add <span class="semi-bold">Syllabus</span></h5>
</div>
<style>
  .modal-dialog.modal-lg {
    width: 100% !important;
  }
</style>

<?php
require '../../includes/db-config.php';
session_start();
?>
<form role="form" id="form-add-sub-course" action="/app/syllabus/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <!-- University & Course -->
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>University</label>
          <select class="full-width" style="border: transparent;" id="university_id" name="university_id"
            onchange="getDetails(this.value);">
            <option value="">Choose</option>
            <?php

            $university_query = $_SESSION['Role'] != 'Administrator' ? " AND ID =" . $_SESSION['university_id'] : '';
            $universities = $conn->query("SELECT ID, CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as Name FROM Universities WHERE ID IS NOT NULL $university_query");
            while ($university = $universities->fetch_assoc()) { ?>
              <option value="<?= $university['ID'] ?>"><?= $university['Name'] ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required subCouresetab">
        
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 skillTab">
        <div class="form-group form-group-default required">
          <label>Category</label>
          <select class="full-width" style="border: transparent;" id="category" name="category"
            onchange="getCategory(this.value) ">
            <option value="">Choose Category</option>
            <option value="3">3 Months</option>
            <option value="6">6 Months</option>
            <option value="11/certified">11 Months Certified</option>
            <option value="11/advance-diploma">11 Months Advance Diploma</option>
          </select>
        </div>
      </div>
      <div class="col-md-6 semesterTab">
        <div class="form-group form-group-default required">
          
        </div>
      </div>
    </div>

  </div>
  <div class="modal-footer clearfix text-end">
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


<script type="text/javascript" src="../../assets/plugins/select2/js/select2.full.min.js"></script>

<script>
  $(function () {
    $("#eligibilities").select2();
    $("#course_category").select2();
    $(".skillTab").hide();
    $(".semesterTab").hide();
  })

  function getDetails(id) {
    if (id == 47) {
      $(".skillTab").hide();
      $(".semesterTab").show();
      $html='<label>Semester</label>\
          <select class="full-width" style="border: transparent;" id="semester" name="semester"onchange="getCategory(this.value) ">\
          </select>';
      $(".subCouresetab").html(html);
      getDuration();
    } else {
      $(".skillTab").show();
      $(".semesterTab").hide();
      $html='<label>Semester</label>\
          <select class="full-width" style="border: transparent;" id="semester" name="semester">\
          </select>';
      $(".subCouresetab").html(html);
    }
    $.ajax({
      url: '/app/syllabus/courses?id=' + id,
      type: 'GET',
      success: function (data) {
        $('#course').html(data);
      }
    });

  }

  function getDuration(id) {
    $.ajax({
      url: '/app/syllabus/semester?id=' + id,
      type: 'GET',
      success: function (data) {
        $('#semester').html(data);
      }
    });
  }




  $(function () {
    $('#form-add-sub-course').validate({
      rules: {
        name: {
          required: true
        },
        short_name: {
          required: true
        },
        university_id: {
          required: true
        },
        course: {
          required: true
        },
      },
      highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
      },
      unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
      }
    });
  })

  $("#form-add-sub-course").on("submit", function (e) {
    if ($('#form-add-sub-course').valid()) {
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
        success: function (data) {
          if (data.status == 200) {
            $('.modal').modal('hide');
            notification('success', data.message);
            $('#sub-courses-table').DataTable().ajax.reload(null, false);
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