<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<link href="/assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" type="text/css" media="screen">
<link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" media="screen">
<?php
unset($_SESSION['filterByUser']);
unset($_SESSION['filterByDate']);
?>
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
                        }?>
                        </ol>
                        <!-- END BREADCRUMB -->
                    </div>
                </div>
            </div>
            <!-- END JUMBOTRON -->
            <!-- START CONTAINER FLUID -->
            <div class=" container-fluid">
                <!-- BEGIN PlACE PAGE CONTENT HERE -->
                <div class="card card-transparent">
                    <!-- Start Card Header -->
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-12">
                                <div class="row">
                                <div class="col-md-4 m-b-10">
                                    <div class="input-daterange input-group" id="datepicker-range">
                                        <input type="text" class="input-sm form-control" placeholder="Select Date" id="startDateFilter" name="start" />
                                        <div class="input-group-addon">to</div>
                                        <input type="text" class="input-sm form-control" placeholder="Select Date" id="endDateFilter" name="end" />
                                    </div>
                                </div>
                                <div class="col-md-4 m-b-10">
                                    <select class="w-100 overflow-x-auto"  multiple="multiple" data-init-plugin="select2" id="users" data-placeholder="Choose User">
                                    </select>   
                                </div>
                                <div class="col-md-4 m-b-10">
                                    <button class="btn btn-primary btn-lg" id = "download_reports">Download Reports</button>
                                    <!-- <a class="btn btn-primary btn-lg" role="button" href="/app/wallet-payments/admission-history">Download Reports</a> -->
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 

<style>
    .select2-selection__rendered::-webkit-scrollbar{
        height: 3px;
    }
    .select2-selection__rendered::-webkit-scrollbar-track {
        box-shadow: inset 0 0 2px grey; 
        border-radius: 10px;
    }
</style>
<!-- END PAGE CONTENT -->
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
<script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script type="text/javascript">

$("#users").select2({
    placeholder: 'Choose Center'
});

$('#datepicker-range').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    endDate: '0d'
});

getCenterList('users');
$(".select2-selection__rendered").css('overflow-x','auto');


$("#download_reports").on('click',function(){
    var start_date = $("#startDateFilter").val();
    var end_date = $("#endDateFilter").val();
    var center = $("#users").val();
    if ((start_date.length > 0 && end_date.length > 0 ) || center.length > 0 ) {
        window.open("/app/wallet-payments/download-center-reports?start_date="+start_date+"&end_date="+end_date+"&center="+center);
    } else {
        alert("Enter specific date range or center name");
    }
});


</script>