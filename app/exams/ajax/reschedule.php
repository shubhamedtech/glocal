<?php
ini_set('display_errors', 1);
if (isset($_GET['id'])) {
  require '../../../includes/db-config.php';
  session_start();

  $date_sheets = array();
  $date_sheets_data = $conn->query("SELECT Date_Sheets.*, Exam_Sessions.Name as Exam_Session, Syllabi.Name as Syllabi_Name FROM Date_Sheets LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID WHERE Date_Sheets.ID = '".$_GET['id']."' ");
  if($date_sheets_data->num_rows > 0){
    $date_sheets = $date_sheets_data->fetch_assoc();
  }
  $exam_date = $date_sheets['Exam_Date'];
?>
<link href="../../../assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" type="text/css" media="screen">
<!-- Modal -->
<div class="modal-header clearfix text-left">
  <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
  </button>
  <h5>Reschedule Exam</h5>
</div>
<form role="form" id="form-exams" foemtarget="_blank" action="/app/exams/ajax/update-date" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Exam Session</label>
          <input type="text" name="exam_session" class="form-control" value="<?=$date_sheets['Exam_Session']?>" readonly>
          <input type="hidden" name="id" class="form-control" value="<?=$date_sheets['ID']?>">
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Exam Paper Name</label>
          <input type="text" name="name" class="form-control" value="<?=$date_sheets['Syllabi_Name']?>" readonly>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="form-group form-group-default required">
          <label>Exam Date</label>
          <input type="tel" name="exam_start_date" id="exam_start_date" class="form-control" value="<?=date("m-d-YY", strtotime($exam_date)) ?>">
        </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Start Time</label>
          <input type="time" name="exam_start_time" id="exam_start_time" class="form-control" value="<?=$date_sheets['Start_Time']?>" onchange="setEndTime(this.value);">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>End Time</label>
          <input type="time" name="exam_end_time" id="exam_end_time" class="form-control" value="<?=$date_sheets['End_Time']?>">
        </div>
      </div>
    </div>


  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <button aria-label="" type="submit" id="submit-button" class="btn btn-primary btn-cons btn-animated from-left">
        <span>Reschedule</span>
        <span class="hidden-block">
          <i class="pg-icon">tick</i>
        </span>
      </button>
    </div>
  </div>
</form>
<?php 
}
?>
<script src="../../assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="../../assets/plugins/bootstrap-form-wizard/js/jquery.bootstrap.wizard.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../../assets/plugins/jquery-inputmask/jquery.inputmask.min.js"></script>

<script type="text/javascript">
  $("#exam_start_date").mask("99-99-9999");
  $("#exam_end_date").mask("99-99-9999");

  $('#exam_start_date').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    startDate: '+0d'
  });

  function setEndDate(value){
    $('#exam_end_date').val(value);
    $('#exam_end_date').datepicker('remove');
    $('#exam_end_date').datepicker({
      format: 'dd-mm-yyyy',
      autoclose: true,
      startDate: new Date(value.split("-").reverse().join("-"))
    });
  }

  function setEndTime(value){
    $("#exam_end_time").attr('min', value);
  }
</script>

<script type="text/javascript">
  $(function(){
    $('#form-exams').validate();
  })

  $("#form-exams").on("submit", function(e){
    if($('#form-exams').valid()){
      $(':input[type="submit"]').prop('disabled', true);
      var formData = new FormData(this);
      $.ajax({
        url: this.action,
        type: 'post',
        data: formData,
        cache:false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(data) {
          if(data.status==200){
            $('.modal').modal('hide');
            notification('success', data.message);
            // window.location.reload();
            $('#reschedule-exam').DataTable().ajax.reload(null, false);
          }else{
            $(':input[type="submit"]').prop('disabled', false);
            notification('danger', data.message);
          }
        }
      });
      e.preventDefault();
    }
  });
</script>