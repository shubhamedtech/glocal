<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
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
                if (count($breadcrumbs) == $i):
                  $active = "active";
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
        <?php $getData = array();
        $disable = '';
        $getQuery = $conn->query("SELECT * FROM Agreements WHERE Role = 1");
        if ($getQuery->num_rows > 0) {
          $getData = $getQuery->fetch_assoc();
          // $disable  = "disabled";
        }

        ?>
        <!-- BEGIN PlACE PAGE CONTENT HERE -->
        <form role="form" id="agreement-form" action="/app/agreement/store" method="post" enctype="multipart/form-data">
          <div class="row">
            <div class="col d-flex justify-content-center">
              <!-- <div class="col-md-5"> -->
                <div class="card card-default">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-4">
                        <label>Start Date*</label>
                        <input type="date" name="date" class="form-control" value="<?= $getData['start_date'] ?>" placeholder="Enter Date" required>
                      </div>
                      <div class="col-md-3">
                        <label>Upload Agreement*</label><br>
                        <input type="file" <?= htmlspecialchars($disable) ?> name="photo"
                          accept="image/png, image/jpg, image/jpeg, image/svg, application/pdf">
                      </div>
                      <div class="col-md-2">
                        <?php if (!empty($getData['Agreement_File'])): ?>
                          <a href="/../<?=$getData['Agreement_File'] ?>" class="btn btn-link"
                            title="Download Agreement" download>
                            <i class="uil uil-down-arrow"></i>
                          </a>
                          <i class="uil uil-trash mr-1 cursor-pointer" title="Delete Agreement Form"
                            onclick="destroy('agreement', '<?= htmlspecialchars($getData['ID']) ?>')"></i>
                            <input type="hidden" name="updated_file" value="<?= htmlspecialchars($getData['Agreement_File']) ?>">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($getData['ID']) ?>">
                        <?php endif; ?>
                      </div>
                    </div>
                    <?php
                    $role = ($_SESSION['Role'] == "Administrator") ? 1 : 2;
                    ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

                    <div class="modal-footer clearfix text-end m-t-20">
                      <div class="col-md-4 m-t-10 sm-m-t-10">
                        <button type="submit" class="btn btn-primary btn-cons btn-animated from-left">
                          <span>Save</span>
                          <span class="hidden-block">
                            <i class="pg-icon">tick</i>
                          </span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              <!-- </div> -->
            </div>
          </div>
        </form>
        <!-- END PLACE PAGE CONTENT HERE -->
      </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>

    <script>
      $(function () {
        $('#agreement-form').validate({
          rules: {
            <?php print (!empty($id) && empty($photo)) ? "photo: {required:true}," : ""; ?>
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
      });

      $("#agreement-form").on("submit", function(e){
                if($('#agreement-form').valid()){
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
                        success: function(data) {
                            if(data.status == 200){
                                notification('success', data.message);
                                location.reload();
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

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>