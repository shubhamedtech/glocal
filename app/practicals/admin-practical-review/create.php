<?php
ini_set('display_errors', 1);
if (isset($_GET['id'], $_GET['subjectId'], $_GET['practicalId'])) {
    $practicalId = htmlspecialchars($_GET['practicalId']);
    $subjectId = htmlspecialchars($_GET['subjectId']);
    $studentId = htmlspecialchars($_GET['id']);
?>
    <div class="modal-header">
        <h5 class="modal-title">Upload Practical File(Solutions)</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <form id="uploadForm" enctype="multipart/form-data" action="/app/practicals/teacher_upload_practical" method="post">
            <input type="hidden" name="practical_id" value="<?= $practicalId ?>" id="practical_id">
            <input type="hidden" name="student_id" value="<?= $studentId ?>" id="student_id">
            <input type="hidden" name="subject_id" value="<?= $subjectId ?>" id="subject_id">
            <input type="hidden" name="Manual" value="Manual">
            <div class="form-group">
                <label for="teacher_upload_practical">Select File</label>
                <input type="file" class="form-control-file" id="teacher_upload_practical" name="teacher_upload_practical" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Upload</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
        </form>
    </div>
    <script>
        $(document).ready(function() {
            $('#uploadForm').validate({
                rules: {
                    teacher_upload_practical: {
                        required: true
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
                            if (data.status == 200) {
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
                            notification('success', 'file Uploaded Successfully.');
                            window.location.href = "practical_review_result";
                        }
                    });
                }
            });
        });
    </script>
<?php } ?>