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
                    <div class="col-md-12">
                        <div class="card card-transparent">
                            <div class="card-body">
                                <?php
                                // echo "<pre>"; print_r($_SESSION);
                                    $id = $_SESSION['ID'];
                                    $student = $conn->query("SELECT * FROM Agreements WHERE Student_Id = '". $_SESSION['ID']."' AND Role=2");
                                   
                                    if ($student->num_rows > 0) { ?>
                                        <div class="text-center">
                                            <h1>Thank you for accepting the Agreement. Your acceptance has been successfully recorded.<h1>
                                            <h3>No further action required.</h3>
                                            <h4><a href="/app/agreement/index.php?id=<?=$id?>" >Download and View the Agreement</a></h4>
                                            <i class="uil-check-circle text-success" style="font-size: 48px;"></i>
                                        </div>
                                    <?php
                                    } else {
                                    ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Full Name</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['First_Name'] . $_SESSION['Middle_Name'] . $_SESSION['Last_Name'] ?>">
                                                </div>
                                            </div>
                                            <?php  // echo "<pre>"; print_r($_SESSION); ?>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>University Name</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['university_name'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Course Name</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Course_Sub_Course'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Mobile Number</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Contact'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Email</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Email'] ?>">
                                                </div>
                                            </div>
                                            <?php $address = json_decode($_SESSION['Address'], true); ?>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Permanent Address</label>
                                                    <input type="text" disabled class="form-control" value="<?= $address['present_address'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>District</label>
                                                    <input type="text" disabled class="form-control" value="<?= $address['present_district'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group form-group-default required">
                                                    <label>City</label>
                                                    <input type="text" disabled class="form-control" value="<?= $address['present_city'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group form-group-default required">
                                                    <label>State</label>
                                                    <input type="text" disabled class="form-control" value="<?= $address['present_state'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <form action="/app/agreement/confirm-agreement" id="confirm-exam" method="POST" role="form">
                                                    <div class="row">
                                                        <?php
                                                          if (!empty($id)) {
                                                            $parents_signature = "";
                                                            $disabled = "";
                                                            $parent_signature = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Parent Signature'");
                                                            if ($parent_signature->num_rows > 0) {
                                                              $parent_signature = mysqli_fetch_array($parent_signature);
                                                              $parents_signature = $parent_signature['Location'];
                                                              $disabled = "disabled";
                                                            }
                                                            
                                                          }
                                                        ?>
                                                        <div class="col-md-3">
                                                            <div class="form-group form-group-default required">
                                                                <label>Parent Signature</label>
                                                                <input type="file" accept="image/png, image/jpeg, image/jpg" <?=  $disabled ?> onchange="fileValidation('parent_signature');" id="parent_signature" name="parent_signature" class="form-control mt-1">
                                                                <?php if (!empty($id) && !empty($parents_signature)) { ?>

                                                                    <img src="<?php print !empty($id) ? $parents_signature : '' ?>" height="100" />
                                                                                                                                        <input type="hidden" id = "uploded_parent_signature" value ="<?= $parents_signature?>" name="uploded_parent_signature">
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                            if (!empty($id)) {
                                                                $students_signature = "";
                                                                $disabled = "";
                                                                $student_signature = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Student Signature'");
                                                                if ($student_signature->num_rows > 0) {
                                                                $student_signature = mysqli_fetch_array($student_signature);
                                                                $students_signature = $student_signature['Location'];
                                                                $disabled = "disabled";
                                                                }
                                                            }
                                                         ?>
                                                        <div class="col-md-3">
                                                            <div class="form-group form-group-default required">
                                                                <label>Student Signature</label>
                                                                <input type="file" accept="image/png, image/jpeg, image/jpg" <?=  $disabled ?> onchange="fileValidation('student_signature');" id="student_signature" name="student_signature" class="form-control mt-1" aria-invalid="false">
                                                                <?php if (!empty($id) && !empty($students_signature)) { ?>
                                                                  <input type="hidden" id = "uploaded_student" value ="<?= $students_signature?>" name="uploaded_student">
                                                                  <img src="<?php print !empty($id) ? $students_signature : '' ?>" height="100" />
                                                                <?php } ?> 
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-end mb-3">
                                                        <input type="hidden" name="student_id" id="student_id" value="<?= $_SESSION['ID'] ?>">
                                                        <input type="checkbox" class="mr-3" name="confirm" id="confirm" required>
                                                        <span>I, hereby confirm that all the information provided here is correct.</span>
                                                    </div>
                                                    <button class="btn btn-primary float-right" role="submit">Submit</button>
                                                </form>
                                            </div>
                                        </div>
                                <?php 
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END PLACE PAGE CONTENT HERE -->
            </div>
            <!-- END CONTAINER FLUID -->
        </div>
        <!-- END PAGE CONTENT -->
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
        <script>

            $('#confirm-exam').validate({
                rules: {
                <?php print (!empty($id) && empty($students_signature)) ? "student_signature: {required:true}," : "" ?>
                <?php print empty($id) ? "student_signature: {required:true}," : "" ?>
                <?php print (!empty($id) && empty($parents_signature)) ? "parent_signature: {required:true}," : "" ?>
                <?php print empty($id) ? "parent_signature: {required:true}," : "" ?>
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

            $("#confirm-exam").on("submit", function(e) {
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
                        if (data.status == 200) {
                            notification('success', data.message);
                            window.location.reload();

                        } else {
                            $(':input[type="submit"]').prop('disabled', false);
                            notification('danger', data.message);
                        }
                    }
                });
                e.preventDefault();
            });

            
      function fileValidation(id) {
        var fi = document.getElementById(id);
        if (fi.files.length > 0) {
          for (var i = 0; i <= fi.files.length - 1; i++) {
            var fsize = fi.files.item(i).size;
            var file = Math.round((fsize / 1024));
            // The size of the file.
            if (file >= 500) {
              $('#' + id).val('');
              alert("File too Big, each file should be less than or equal to 500KB");
            }
          }
        }
      }
        </script>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>