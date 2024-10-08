<?php
if (isset($_POST['id'])) {
  require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
  $id = intval($_POST['id']);

  $exception = $conn->query("SELECT Exception FROM Late_Fees WHERE ID = $id");
  if ($exception->num_rows > 0) {
    $exception = $exception->fetch_assoc();
    $exception = !empty($exception['Exception']) ? json_decode($exception['Exception'], true) : array();

?>
    <div class="modal-header clearfix text-left">
      <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
      </button>
      <h6>Add <span class="semi-bold">Late Fee Exceptions</span></h6>
    </div>
    <form role="form" id="form-add-late-fees-exception" action="/app/components/late-fees/exception-store" method="POST">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group form-group-default required">
              <label>Center</label>
              <textarea name="exception" class="form-control" rows="5" placeholder="ex: Center Codes with comma (,) separated"><?= !empty($exception) ? implode(",", $exception) : '' ?></textarea>
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

    <script>
      $("#form-add-late-fees-exception").on("submit", function(e) {
        if ($('#form-add-late-fees-exception').valid()) {
          $(':input[type="submit"]').prop('disabled', true);
          var formData = new FormData(this);
          formData.append('id', '<?= $id ?>');
          $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
              if (data.status) {
                $('.modal').modal('hide');
                notification('success', data.message);
                $('#tableLateFees').DataTable().ajax.reload(null, false);
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
<?php
  }
}
