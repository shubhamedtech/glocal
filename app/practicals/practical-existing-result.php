<?php
ini_set('display_errors', 1);
if (isset($_POST['practical_id']) && !empty($_POST['practical_id'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
    $practical_id = intval($_POST['practical_id']);
    $sql = "SELECT * FROM Student_Practical_Result WHERE practical_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $response = [
            'status' => 0,
            'message' => 'Database error: unable to prepare statement'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $stmt->bind_param('i', $practical_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $response = [
            'status' => 1,
            'message' => 'Data loaded successfully',
            'data' => $data
        ];
?>
        <div id="message"></div>
        <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel">Student Assignment Update Result</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="resultForm" action="/app/practicals/update_practical_result" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="id" value="<?php echo htmlspecialchars($data['id']); ?>">
                <input type="hidden" name="uploaded_type" id="uploadedtype" value="Manual">
                <div class="form-group">
                    <label for="status">Evaluation Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="Not Submitted" <?php if ($data['status'] == 'Not Submitted') echo 'selected'; ?>>Not Submitted</option>
                        <option value="Submitted" <?php if ($data['status'] == 'Submitted') echo 'selected'; ?>>Submitted</option>
                        <option value="Approved" <?php if ($data['status'] == 'Approved') echo 'selected'; ?>>Approved</option>
                        <option value="Rejected" <?php if ($data['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="marks">Enter Marks</label>
                    <input type="number" class="form-control" id="marks" name="marks" placeholder="Enter Practical Marks" value="<?php echo htmlspecialchars($data['obtained_mark']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="reason">Enter Reason (Comment)</label>
                    <input type="text" class="form-control" id="reason" name="reason" placeholder="Enter Reason/Remark" value="<?php echo htmlspecialchars($data['remark']); ?>" required>
                </div>
                <button type="submit" id="update" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </form>
        </div>
<?php
    } else {
        $response = [
            'status' => 0,
            'message' => 'Practical not found'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $stmt->close();
} else {
    $response = [
        'status' => 0,
        'message' => 'Invalid Practical ID'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#update').click(function(event) {
            event.preventDefault();
            $.ajax({
                data: $('form').serialize(),
                url: '/app/practicals/update_practical_result',
                type: 'POST',
                success: function(strMessage) {
                    $('#message')
                        .text(strMessage)
                        .css('color', 'green')
                        .addClass('highlight')
                        .attr('data-custom', 'value');
                    window.location.href = "practical_review_result";
                }
            })
        });
    });
</script>