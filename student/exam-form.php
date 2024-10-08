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
                                //  $student = $_SESSION['ID'];
                        
                                    $student = $conn->query("SELECT * FROM Examination_Confirmation WHERE Student_Id = '". $_SESSION['ID']."'");
                                   
                                    if ($student->num_rows > 0) { ?>
                                        <div class="text-center">
                                            <h1>Thank you for providing the requested information.<h1>
                                                    <h3>No further action required.</h3>
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
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Father's Name</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Father_Name'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Mother's Name</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Mother_Name'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Date of Birth</label>
                                                    <input type="text" disabled class="form-control" value="<?= date('d-m-Y', strtotime($_SESSION['DOB'])) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Aadhar Number</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Aadhar_Number'] ?>">
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
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Gender</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Gender'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Category</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Category'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default required">
                                                    <label>Religion</label>
                                                    <input type="text" disabled class="form-control" value="<?= $_SESSION['Religion'] ?>">
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
                                            <div class="col-md-4">
                                                <div class="form-group form-group-default required">
                                                    <label>City</label>
                                                    <input type="text" disabled class="form-control" value="<?= $address['present_city'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group form-group-default required">
                                                    <label>State</label>
                                                    <input type="text" disabled class="form-control" value="<?= $address['present_state'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group form-group-default required">
                                                    <label>Pin Code</label>
                                                    <input type="text" disabled class="form-control" value="<?= $address['present_pincode'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <form action="/app/students/confirm-exam" id="confirm-exam" method="POST" role="form">
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
        </script>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>