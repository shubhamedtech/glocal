<div class="modal-header">
  <h5 class="modal-title" id="uploadModalLabel">Upload Assignment AnswerSheet</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<form id="uploadForm" action="/app/assignments/upload_assignment" method="post" enctype="multipart/form-data">
  <input type="hidden" name="uploaded_type" value="Online">
  <input type="hidden" name="subject_id" value="<?= $_GET['subject_id']; ?>" id="subject_id">
  <input type="hidden" name="assignment_id" value="<?= $_GET['id']; ?>" id="id">
  <div class="modal-body">
    <div class="form-group">
      <label for="assignmentFile">Select File(s) (PDF, JPEG, JPG, PNG, GIF):</label>
      <input type="file" class="form-control-file" id="assignmentFile" name="assignment_file[]" accept=".pdf, .jpeg, .jpg, .png, .gif" multiple required>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <button type="submit" name="submit" class="btn btn-primary">Upload</button>
  </div>
</form>


<script>
  $(document).ready(function() {
    $('#uploadForm').validate({
      rules: {
        'assignment_file[]': {
          required: true,
          accept: "application/pdf,image/jpeg,image/jpg,image/png"
        }
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

    $("#uploadForm").on("submit", function(e) {
      e.preventDefault();
      if ($('#uploadForm').valid()) {
        $(':input[type="submit"]').prop('disabled', true);
        var formData = new FormData(this);
        formData.append('uploaded_type', 'Online');
        formData.append('subject_id', $('#subject_id').val());
        formData.append('assignment_id', $('#id').val());
        $.ajax({
          url: $(this).attr('action'),
          type: 'post',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(data) {
            console.log(data);
            if (data.status === 200) {
              $('.modal').modal('hide');
              notification('success', data.message);
              $('#students_table').DataTable().ajax.reload(null, false);
            } else {
              $(':input[type="submit"]').prop('disabled', false);
              notification('danger', data.message);
            }
          },
          error: function() {
            $(':input[type="submit"]').prop('disabled', false);
            notification('danger', 'Error occurred during file upload.');
          }
        });
      }
    });
  });
</script>

<!-- <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.3/jquery.validate.min.js"></script> -->