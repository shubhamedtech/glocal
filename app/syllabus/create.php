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

  .card-body {
    padding: 15px;
  }

  .card {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
    border-radius: 2px !important;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
    -webkit-transition: all 0.2s ease;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    position: relative;
    margin-bottom: 20px;
    width: 100%;
    word-wrap: none;
    background: #fff;
  }

  .col-md-6.semesterTab {
    padding-left: unset !important;
  }
</style>

<?php
require '../../includes/db-config.php';
session_start();
?>
<form role="form" id="form-add-sub-course" action="/app/syllabus/store" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
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
        <div class="form-group form-group-default required">
          <label>Specialization</label>
          <select class="full-width" style="border: transparent;" id="course" name="course"
            onchange="getDuration(this.value)">
            <option value="">Choose</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 skillTab">
        <div class="form-group form-group-default required">
          <label>Duration</label>
          <select class="full-width" style="border: transparent;" id="duration" name="duration"
            onchange="getSubject(this.value)">

          </select>
        </div>
      </div>
      <div class="col-md-6 subjectTab">
        <div class="form-group form-group-default required">
          <label>Subjects</label>
          <select class="full-width" style="border: transparent;" id="subject" name="subject">
          </select>
        </div>
      </div>
    </div>
    <!-- start kp -->
    <div class="row" style="float: right;">
      <div class="btn btn-outline-primary rounded add-more mb-3"> + Add Chapter
        <!-- <i class=" uil uil-plus-circle icon-xs cursor-pointer mt-1 ms-0 pe-2"></i> -->
      </div>
    </div>
    <div class=" after-add-more">
    </div>
    <!-- end kp -->
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
<script type="text/javascript">

  $(document).ready(function () {
    var chapterCount = 1;
    var unitCount = {};
    var topicCount = {};

    // Add more chapters
    $(".add-more").click(function () {
      var html = `
          <div class="row control1 card card-body mb-3">
            <h4 class="mt-0 ">Chapter ${chapterCount}</h4>
            <div class="control-group input-group" style="margin-top:10px">
              <div class="col-md-5">
                <div class="form-group form-group-default required">
                  <label>Chapter Name</label>
                  <input type="text" name="chapter_name[]" class="form-control" placeholder="ex: Introduction of Technology" value=""
                    required>
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group form-group-default required">
                  <label>Chapter Code</label>
                  <input type="number" name="chapter_code[]" class="form-control" placeholder="ex: UNI123" value="" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group-btn">
                  <a class="remove" type="button"><i class="uil uil-minus-circle icon-xs cursor-pointer"></i></a>
                </div>
              </div>
            </div>
            <!-- Unit Section -->
            <div class="unit-section" id="unit-section-${chapterCount}">
              <div class="row">
                <div class="col-md-2">
                  <div class="btn btn-outline-primary rounded add-more-unit mb-3" data-chapter="${chapterCount}">
                    + Add Unit
                  </div>
                </div>
              </div>
            </div>
            <div class="after-add-more-unit" id="after-add-more-unit-${chapterCount}"></div>
          </div>`;
      $(".after-add-more").append(html);
      unitCount[chapterCount] = 1;
      topicCount[chapterCount] = {};
      chapterCount++;
    });

    $("body").on("click", ".remove", function () {
      $(this).parents(".control1").remove();
    });

    $("body").on("click", ".add-more-unit", function () {
      var chapter = $(this).data("chapter");
     

      var unit_html = `
          <div class="unit-section unit-control card card-body mb-3">
            <h4 class="mt-0">Unit ${unitCount[chapter]}</h4>
            <div class="row">
              <div class="col-md-5">
                <div class="form-group form-group-default required">
                  <label>Unit Name</label>
                  <input type="text" name="unit_name[${chapter}][]" class="form-control" placeholder="ex: Unit 1" value="" required>
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group form-group-default required">
                  <label>Unit Code</label>
                  <input type="number" name="unit_code[${chapter}][]" class="form-control" placeholder="ex: UNIT123" value="" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group-btn">
                  <a class="remove-unit" type="button"><i class="uil uil-minus-circle icon-xs cursor-pointer"></i></a>
                </div>
              </div>
            </div>
            <div class="unit-section" id="unit-section-${chapter}">
              <div class="row">
                <div class="col-md-2">
                  <div class="btn btn-outline-primary rounded add-more-topic mb-3" data-chapter="${chapter}" data-unit="${unitCount[chapter]}">
                    + Add Topic
                  </div>
                </div>
              </div>
            </div>
            <div class="after-add-more-topic" id="after-add-more-topic-${chapter}-${unitCount[chapter]}"></div>
          </div>`;
      $(`#after-add-more-unit-${chapter}`).append(unit_html);
      topicCount[chapter][unitCount[chapter]] = 1;
      unitCount[chapter]++;
    });

    $("body").on("click", ".remove-unit", function () {
      $(this).parents(".unit-control").remove();
    });
    // <h4 class="mt-0">Topic ${topicCount[chapter][unit]}</h4>
    // Add more topics
    $("body").on("click", ".add-more-topic", function () {
      var chapter = $(this).data("chapter");
      var unit = $(this).data("unit");
      var topic_html = `
          <div class="unit-section topic-control ">

            <div class="row">
              <div class="col-md-10">
                <div class="form-group form-group-default required">
                  <label>Topic Name</label>
                  <input type="text" name="topic_name[${chapter}][${unit}][]" class="form-control"
                    placeholder="ex: Introduction to Topic" value="" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group-btn">
                  <a class="remove-topic" type="button"><i class="uil uil-minus-circle icon-xs cursor-pointer"></i></a>
                </div>
              </div>
            </div>
          </div>`;
      $(`#after-add-more-topic-${chapter}-${unit}`).append(topic_html);
      topicCount[chapter][unit]++;
    });

    $("body").on("click", ".remove-topic", function () {
      $(this).parents(".topic-control").remove();
    });
  });

</script>
<script>
  $(function () {
    $("#eligibilities").select2();
    $("#course_category").select2();
    // $(".skillTab,.subjectTab").hide();
  })

  function getDetails(id) {
    $(".skillTab").show();
    $("#duration").empty();
    $("#subject").empty();

    $.ajax({
      url: '/app/syllabus/courses?id=' + id,
      type: 'GET',
      success: function (data) {
        $('#course').html(data);
      }
    });
  }

  function getDuration(id) {
    var university_id = $("#university_id").val();
    $.ajax({
      url: '/app/syllabus/semester?id=' + id + '&university_id=' + university_id,
      type: 'GET',
      success: function (data) {
        $('.subjectTab').show();
        $('#duration').html(data);
      }
    });
  }

  function getSubject(duration) {
    var sub_course_id = $("#course").val();
    var university_id = $("#university_id").val();
    $.ajax({
      url: '/app/syllabus/subjects?id=' + sub_course_id + '&university_id=' + university_id + '&duration=' + duration,
      type: 'GET',
      success: function (data) {
        $('#subject').html(data);
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
      // $(':input[type="submit"]').prop('disabled', true);
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
            // $('.modal').modal('hide');
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