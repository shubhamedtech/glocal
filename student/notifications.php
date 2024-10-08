<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<link rel="stylesheet" href="/assets/css/new-style.css" />
<style>
 .table > thead > tr > th{
    padding-left: 18px !important;
    color: black !important;
    font-weight: 900 !important;
}
.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, .125) !important;
}
.card .card-header {
    padding: 4px 7px 4px 20px  !important;
    min-height: 48px  !important;
}
.card-body {
    padding: 1.25rem !important;
}
</style>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>
<!-- START PAGE-CONTAINER -->
<div class="page-container ">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <!-- START PAGE CONTENT WRAPPER -->
  <div class="page-content-wrapper ">
    <!-- START PAGE CONTENT -->
    <div class="content ">
      <!-- START JUMBOTRON -->
      <div class="jumbotron" data-pages="parallax">
        <div class=" container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i) : $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . $crumb[0] . '</li>';
                endif;
              }
              ?>
              <div>

              </div>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class=" container-fluid">
        <!-- BEGIN PlACE PAGE CONTENT HERE -->
        <div class="row">
          <div class="col-md-6">
            <div class="card custom-card">
              <div class="card-header seperator d-flex justify-content-between">
                <h5 class="fw-bold mb-0">Notifications</h5>
              </div>
              <div class="card-body dash1">
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Regarding</th>
                        <th>Sent To</th>
                        <th>Sent On</th>
                        <th>Content</th>
                        <th>Attachment</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $result_record = $conn->query("SELECT * FROM Notifications_Generated WHERE Send_To = '" . 'student' . "' OR Send_To = '" . 'all' . "' ");
                      $data = array();
                      while ($row = $result_record->fetch_assoc()) { ?>
                        <tr>
                          <td><?= $row['Heading'] ?></td>
                          <td><?= $row['Send_To'] ?></td>
                          <td><?= $row['Noticefication_Created_on'] ?></td>
                          <td class="text-center"><a type="btn-link" class="text-primary" onclick="view_content('<?= $row['ID'] ?>');"><i class="fa fa-eye"></i></a></td>
                          <td>
                            <?php if (!empty($row['Attachment'])) { ?>
                              <a href="<?= $row['Attachment'] ?>" target="_blank" download="<?= $row['Heading'] ?>">Download</a>
                            <?php } else { ?>
                              <p>No Attachment</p>
                            <?php } ?>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-header seperator d-flex justify-content-between">
                <h5 class="fw-bold mb-0">View Notification</h5>
              </div>
              <div class="card-body">
                <?php
                $current_notification_id = 0;
                $latest_notification = $conn->query("SELECT * FROM Notifications_Generated WHERE Send_To = 'student'  OR Send_To = '" . 'all' . "' ORDER BY Notifications_Generated.ID DESC LIMIT 1");

                while ($row = $latest_notification->fetch_assoc()) {
                  $current_notification_id = $row['ID'];
                ?>
                  <div class="d-flex justify-content-between mb-2" id="show-notification">
                    <span><span class="fw-bold">Regarding : </span> <?= $row['Heading'] ?></span>
                    <span class="me-auto"><span class="fw-bold">Date :</span> <?= $row['Noticefication_Created_on'] ?></span>
                  </div>
                  <p><span class="fw-bold">Message: </span><?= $row['Content'] ?></p>
                  <?php if (!empty($row['Attachment'])) { ?>
                    <a href="<?= $row['Attachment'] ?>" target="_blank" download="<?= $row['Heading'] ?>">Download</a>
                  <?php } ?>
              </div>
            <?php } ?>
            </div>
          </div>
        </div>
        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
    <script type="text/javascript">
      $(document).ready(function() {
        if (<?= $current_notification_id ?> != 0) {
          $.ajax({
            url: '/app/notifications/student-read-notification?id=' + <?= $current_notification_id ?>,
            type: 'GET',
            success: function(data) {}
          })
        }
      });

      function view_content(id) {
        $.ajax({
          url: '/app/notifications/contents?id=' + id,
          type: 'GET',
          success: function(data) {
            $("#md-modal-content").html(data);
            $("#mdmodal").modal('show');
          }
        })
      }
    </script>