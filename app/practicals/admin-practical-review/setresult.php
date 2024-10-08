<div class="modal-header" id="message">
    <h5 class="modal-title" id="myModalLabel">Student Practical Result</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <form id="resultForm" action="/app/practicals/update_result" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="uploaded_type" id="uploadedtype" value="Manual">
        <div class="form-group">
            <label for="status">Evaluation Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="Not Submitted">Not Submitted</option>
                <option value="Submitted">Submitted</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
        <div class="form-group">
            <label for="marks">Enter Marks</label>
            <input type="number" class="form-control" id="marks" name="marks" placeholder="Enter Practical Marks" required>
        </div>
        <div class="form-group">
            <label for="reason">Enter Reason (Comment)</label>
            <input type="text" class="form-control" id="reason" name="reason" placeholder="Enter Reason/Remark" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </form>
</div>
<script>
    $(document).ready(function() {
        $('#resultForm').validate({
            rules: {
                status: {
                    required: true,
                },
                marks: {
                    required: true,
                },
                reason: {
                    required: true,
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
        $('#resultForm').submit(function(e) {
            e.preventDefault();
            if (!$(this).valid()) {
                return;
            }
            var formData = new FormData(this);
            formData.append('practical_id', <?= json_encode($_GET['practical_id']) ?>);
            $.ajax({
                url: '/app/practicals/update_result',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#message')
                        .text(response.message)
                        .css('color', 'green')
                        .addClass('highlight')
                        .attr('data-custom', 'value');
                    window.location.href = "practical_review_result";
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    $('#message')
                        .text('An error occurred while updating the result. Please try again.')
                        .css('color', 'red');
                }
            });
        });
    });
</script>