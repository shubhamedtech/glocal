<style>
    .profile_img {
        width: 150px;
        height: 150px;
        object-fit: fill;
        margin: 10px auto;
        border: 5px solid #ccc;
        border-radius: 50%;
    }

    table,
    tr,
    th,
    th {
        border: none !important;
    }
</style>
<div class="row">
    <div class="col-md-6">
        <div class="card-header separator">
            <h5>Date Sheets</h5>
        </div>
        <div class="card">
            <?php
            $syllabus_ids = array();
            $codes = $conn->query("SELECT ID FROM Syllabi WHERE Course_ID = " . $_SESSION['Course_ID'] . " AND Sub_Course_ID = " . $_SESSION['Sub_Course_ID'] . " AND Semester = " . $_SESSION['Duration'] . "");
            if ($codes->num_rows > 0) {
                while ($row = $codes->fetch_assoc()) {
                    $syllabus_ids[] = $row['ID'];
                }

                $date_sheets = $conn->query("SELECT Date_Sheets.*, Exam_Sessions.Name as Exam_Session, Syllabi.Name, Syllabi.Code FROM Date_Sheets LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID WHERE Syllabus_ID IN (" . implode(",", $syllabus_ids) . ") ORDER BY Exam_Date ASC");
                if ($date_sheets->num_rows == 0) {
                    echo '<center><h1>Date Sheet Not Available</h1></center>';
                } else {
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Paper Code</th>
                                    <th>Paper Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <!-- <th>Exam</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($date_sheet = $date_sheets->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $date_sheet['Code'] ?></td>
                                        <td><?= $date_sheet['Name'] ?></td>
                                        <td><?= date("l, dS M, Y", strtotime($date_sheet['Exam_Date'])) ?></td>
                                        <td><?= date("h:i A", strtotime($date_sheet['Start_Time'])) . " to " . date("h:i A", strtotime($date_sheet['End_Time'])) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
            <?php }
            } else {
                // No Date Sheet Available
                echo '<center><h1>Date Sheet Not Available</h1></center>';
            }
            ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-header separator">
            <h5>Today's Exams</h5>
        </div>
        <div class="card">
            <?php
            $syllabus_ids = array();
            $codes = $conn->query("SELECT ID FROM Syllabi WHERE Course_ID = " . $_SESSION['Course_ID'] . " AND Sub_Course_ID = " . $_SESSION['Sub_Course_ID'] . " AND Semester = " . $_SESSION['Duration'] . "");
            if ($codes->num_rows > 0) {
                while ($row = $codes->fetch_assoc()) {
                    $syllabus_ids[] = $row['ID'];
                }

                $date_sheets = $conn->query("SELECT Date_Sheets.*, Exam_Sessions.Name as Exam_Session,Syllabi.ID as Syllab_id, Syllabi.Name, Syllabi.Code FROM Date_Sheets LEFT JOIN Syllabi ON Date_Sheets.Syllabus_ID = Syllabi.ID LEFT JOIN Exam_Sessions ON Date_Sheets.Exam_Session_ID = Exam_Sessions.ID WHERE Syllabus_ID IN (" . implode(",", $syllabus_ids) . ") AND Exam_Date = '" . date("Y-m-d") . "' ORDER BY Exam_Date ASC");
                if ($date_sheets->num_rows == 0) {
                    echo '<center><h1>NO Exam for today</h1></center>';
                } else {
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <!-- <th>Exam Session</th> -->
                                    <th>Paper Code</th>
                                    <th>Paper Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($date_sheet = $date_sheets->fetch_assoc()) {
                                    $status = '';
                                    $action = '';
                                    if (date('H:i') <= date("H:i", strtotime($date_sheet['End_Time'])) && date('H:i') >= date("H:i", strtotime($date_sheet['Start_Time']))) {
                                        $action = "Start";
                                        $status = 'Started';
                                    } else if (date('H:i') <= date("H:i", strtotime($date_sheet['Start_Time']))) {
                                        $action = "Starting Soon";
                                        $status = 'Not started yet';
                                    } else if (date('H:i') >= date("H:i", strtotime($date_sheet['End_Time']))) {
                                        $action = "Finished";
                                        $status = 'Completed';
                                    }
                                ?>
                                    <tr>
                                        <td><?= $date_sheet['Code'] ?></td>
                                        <td><?= $date_sheet['Name'] ?></td>
                                        <td><?= date("l, dS M, Y", strtotime($date_sheet['Exam_Date'])) ?></td>
                                        <td><?= date("h:i A", strtotime($date_sheet['Start_Time'])) . " to " . date("h:i A", strtotime($date_sheet['End_Time'])) ?></td>
                                        <td>
                                            <?php if ($action == "Start") {
                                                $_SESSION['Today_Exam_ID'] = $date_sheet['Exam_Session_ID'];
                                                $check_web_pic = $conn->query("SELECT * FROM Exam_Students_Final_Submit WHERE Student_ID = " . $_SESSION['ID'] . " AND 	Syllabus_ID = " . $date_sheet['Syllab_id'] . " AND Date_Sheet_ID = " . $date_sheet['ID'] . "");
                                                if ($check_web_pic->num_rows > 0) {
                                                    $action = "Submited";
                                                }
                                                if ($action == "Submited") {
                                            ?>
                                                    <button class="btn btn-disabled" disabled><span class="title"><?= $action ?></span></button>
                                                <?php } else { ?>
                                                    <a class="btn btn-success" href="/exam-students/exams"><span class="title"><?= $action ?></span></a>
                                                <?php }
                                            } else { ?>
                                                <button class="btn btn-disabled" disabled><span class="title"><?= $action ?></span></button>
                                            <?php } ?>
                                        </td>
                                        <td><?= $status ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
            <?php }
            } else {
                // No Date Sheet Available
                echo '<center><h1>Date Sheet Not Available</h1></center>';
            }
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    function changeNotificationStatus(id) {
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