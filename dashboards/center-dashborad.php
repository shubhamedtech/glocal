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
                <div class="inner d-flex flex-wrap justify-content-between">
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
                    </ol>
                    <?php 
                        $new_notification = $conn->query("SELECT * FROM Notifications_Generated WHERE Status <> 1 AND Send_To = 'center' OR Send_To = '".'all'."' ORDER BY Notifications_Generated.ID DESC LIMIT 1");
                        $records = mysqli_fetch_assoc($new_notification);
                        $record_count = array();
                        $viewed_id = array();

                        $viewed_notification = $conn->query("SELECT * FROM Notifications_Viewed_By WHERE Reader_ID =  ". $_SESSION['ID'] ." ORDER BY Notifications_Viewed_By.ID DESC LIMIT 1 ");
                        
                        if($viewed_notification->num_rows>0){
                            $viewed_records = mysqli_fetch_assoc($viewed_notification);
                            $viewed_id = json_decode($viewed_records['Notification_ID']);
                        }
                        if(in_array($records['ID'], $viewed_id))
                        {
                            $record_count = '';
                        }else{
                            $record_count = 1;
                        }
                    ?>
                    <div class="justify-content-between align-self-end" id="show-notification">
                    <?php if($record_count != '') { ?>
                        <a type="button" onclick="show_notification('<?=$records['ID'] ?>')"><iconify-icon icon="uil:bell"></iconify-icon>
                    <?php   echo "One New Notification regarding ".$records['Heading'];
                     }else{
                            echo '';
                    }?></a>
                    </div>
                    
                    <!-- END BREADCRUMB -->
                </div>
            </div>
        </div>
      <!-- END JUMBOTRON -->
      <!-- START CONTAINER FLUID -->
      <div class="container-fluid">
            <div class="row">
                <div class="col-sm-4 col-xl-3 col-lg-4">
                    <div class="card custom-card">
                        <div class="card-body dash1">
                            <div class="d-flex">
                                <p class="mb-1 tx-inverse">Total students</p>
                                <div class="ml-auto">
                                    <i class="fas fa-chart-line fs-20 text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <?php
                                    $all_count = $conn->query("SELECT COUNT(ID) as allcount FROM Students WHERE University_ID = " . $_SESSION['university_id'] . " AND Added_For =  " . $_SESSION['ID'] . " ");
                                    $records = mysqli_fetch_assoc($all_count);
                                    $totalRecords = $records['allcount'];
                                ?>
                                <h3 class="dash-25"><?=$totalRecords?><span><i class="uil uil-users-alt"></i></span></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-xl-3 col-lg-4">
                    <div class="card custom-card">
                        <div class="card-body dash1">
                            <div class="d-flex">
                                <p class="mb-1 tx-inverse">Total programs</p>
                                <div class="ml-auto">
                                    <i class="fas fa-chart-line fs-20 text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <?php
                                    $all_count = $conn->query("SELECT COUNT(Sub_Courses.ID) as allcount FROM Sub_Courses LEFT JOIN Center_Sub_Courses ON Center_Sub_Courses.Sub_Course_ID = Sub_Courses.ID WHERE Sub_Courses.University_ID = " . $_SESSION['university_id'] . " AND Center_Sub_Courses.User_ID = ".$_SESSION['ID']);
                                    $records = mysqli_fetch_assoc($all_count);
                                    $totalRecords = $records['allcount'];
                                ?>
                                <h3 class="dash-25"><?=$totalRecords?><span><i class="uil uil-book-open"></i></span></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-xl-3 col-lg-4">
                    <div class="card custom-card">
                        <div class="card-body dash1">
                            <div class="d-flex">
                                <p class="mb-1 tx-inverse">Univesity Head</p>
                                <div class="ml-auto">
                                    <i class="fas fa-chart-line fs-20 text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <?php
                                
                                    $counsellor = $conn->query("SELECT Alloted_Center_To_Counsellor.Counsellor_ID as counsellor,  University_User.Reporting as head FROM Alloted_Center_To_Counsellor LEFT JOIN University_User ON University_User.User_ID = Alloted_Center_To_Counsellor.Counsellor_ID WHERE Code =  ". $_SESSION['ID']." ");

                                    $records = mysqli_fetch_assoc($counsellor);
                                    $totalRecords = $records['head'];
                                    $Head = $conn->query("SELECT * FROM Users WHERE ID = ". $totalRecords." ");
                                    $university_head = mysqli_fetch_assoc($Head);
                                ?>
                                <span><?=$university_head['Code']?></span>
                                <h3 class="dash-25"><?=$university_head['Name']?></h3>
                                <span><?=$university_head['Role']?></span></br>
                                <span><?=$university_head['Email']?></span></br>
                                <span><?=$university_head['Mobile']?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-xl-3 col-lg-4">
                    <div class="card custom-card">
                        <div class="card-body dash1">
                            <div class="d-flex">
                                <p class="mb-1 tx-inverse">Counsellor</p>
                                <div class="ml-auto">
                                    <i class="fas fa-chart-line fs-20 text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <?php
                                    $counsellor = $conn->query("SELECT Alloted_Center_To_Counsellor.Counsellor_ID as counsellor,  University_User.Reporting as head FROM Alloted_Center_To_Counsellor LEFT JOIN University_User ON University_User.User_ID = Alloted_Center_To_Counsellor.Counsellor_ID WHERE Code =  ". $_SESSION['ID']." ");

                                    $records = mysqli_fetch_assoc($counsellor);
                                    $totalRecords = $records['counsellor'];
                                    $Counsellor = $conn->query("SELECT * FROM Users WHERE ID = ". $totalRecords." ");
                                    $university_Counsellor = mysqli_fetch_assoc($Counsellor);
                                ?>
                                  <span><?=$university_Counsellor['Code']?></span>
                                <h3 class="dash-25"><?=$university_Counsellor['Name']?></h3>
                                <span><?=$university_Counsellor['Role']?></span></br>
                                <span><?=$university_Counsellor['Email']?></span></br>
                                <span><?=$university_Counsellor['Mobile']?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card custom-card">
                        <div class="card-body dash1">
                            <div class="d-flex">
                                <p class="mb-1 tx-inverse">Notifications</p>
                                <div class="ml-auto">
                                    <i class="fas fa-chart-line fs-20 text-primary"></i>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Regarding</th>
                                        <th>Content</th>
                                        <th>Sent To</th>
                                        <th>Noticefication Sent On</th>
                                        <th>Attachment</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                            $query = "SELECT * FROM Notifications_Generated 
          WHERE (Send_To = 'center' OR Send_To = 'all') 
          ORDER BY Noticefication_Created_on ASC";
                                            $result_record = $conn->query($query);
                                            $data = array();
                                            while ($row = $result_record->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?= $row['Heading'] ?></td>
                                                    <td><a type="btn" onclick="view_content('<?= $row['ID'] ?>');"><i class="uil uil-eye"></i></a></td>
                                                    <td><?= $row['Send_To'] ?></td>
                                                    <td><?= $row['Noticefication_Created_on'] ?></td>
                                                    <td>
                                                        <a href="<?= $row['Attachment'] ?>" target="_blank" download="<?= $row['Heading'] ?>">Download</a>
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
                    <div class="card custom-card">
                        <div class="card-body">
                            <div>
                                <h6 class="card-title mb-1">Recent added Students</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Student Code</th>
                                            <th>DOB</th>
                                            <th>Created At</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $centers = $conn->query("SELECT * FROM Students WHERE University_ID = " . $_SESSION['university_id'] . " AND Added_For =  " . $_SESSION['ID'] . " ");
                                            if($centers->num_rows>0){
                                            while ($row = $centers->fetch_assoc()) {
                                        ?>
                                            <tr>
                                                <td><?= $row['First_Name']?></td>
                                                <td><?= $row['Unique_ID']?></td>
                                                <td><?= $row['DOB']?></td>
                                                <td><?= $row['Created_At']?></td>
                                                <td><?php  if( $row['Status'] == 1){  ?>  <span class="badge badge-success">Active</span> 
                                                    <?php  } else {  ?>  <span class="badge badge-danger">Inactive</span>
                                                    <?php  } ?>  
                                                </td>
                                            </tr>
                                        <?php } } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      <!-- END CONTAINER FLUID -->
    </div>
    <!-- END PAGE CONTENT -->
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
<script type="text/javascript">
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

    function show_notification(id){
        $.ajax({
        url: '/app/notifications/current-notification?id=' + id,
        type: 'GET',
        success: function(data) {
            $("#md-modal-content").html(data);
            $("#mdmodal").modal('show');
        }
      })
    }
</script>