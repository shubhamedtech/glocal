<?php
require '../../../includes/db-config.php';
session_start();

if(isset($_GET['adm_sessions']) || isset($_GET['exam_session'])) {
    $adm_sessions = intval($_GET['adm_sessions']);
    $exam_session =  isset($_GET['exam_session']) ? intval($_GET['exam_session']) : '';
    $codes = $conn->query("SELECT Student_ID  FROM Students_Exam_Sessions WHERE Admission_Session_ID = " . $adm_sessions . " || Exam_Session_ID = ". $exam_session ." ");

    if ($codes->num_rows > 0) {
        $student_ids = array(); ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Admission Session</th>
                        <th>Exam Session</th>
                        <th>Paper Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $codes->fetch_assoc()) {
                        $student_ids = $row['Student_ID'];
                        $date_sheets = $conn->query("SELECT Students.First_Name as StudentName,  Admission_sessions.Name as AdmissionName, Exam_Sessions.Name ExamSessionName, Sub_Courses.Name as SubCourseName FROM Students LEFT JOIN Admission_sessions ON Admission_sessions.ID = Students.Admission_Session_ID LEFT JOIN Students_Exam_Sessions ON Students_Exam_Sessions.Student_ID = Students.ID LEFT JOIN Exam_Sessions ON Exam_Sessions.ID = Students_Exam_Sessions.Exam_Session_ID LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID WHERE Students.ID  = $student_ids");
                        while ($date_sheet = $date_sheets->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $date_sheet['StudentName'] ?></td>
                                <td><?= $date_sheet['AdmissionName'] ?></td>
                                <td><?= $date_sheet['ExamSessionName'] ?></td>
                                <td><?= $date_sheet['SubCourseName'] ?></td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    <?php } else {
        // No Date Sheet Available
        echo '<center><h1>Result Not Available</h1></center>';
    }
} else { 
    $codes = $conn->query("SELECT Admission_Session_ID  FROM Students_Exam_Sessions GROUP BY Admission_Session_ID");
?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Admission Session</th>
                    <th>Exam Session</th>
                    <th>Paper Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // $i=0;
                while ($row = $codes->fetch_assoc()) {
                    $admission = $row['Admission_Session_ID'];
                    $date_sheets = $conn->query("SELECT Students.First_Name as StudentName,  Admission_sessions.Name as AdmissionName, Exam_Sessions.Name ExamSessionName, Sub_Courses.Name as SubCourseName FROM Students LEFT JOIN Admission_sessions ON Admission_sessions.ID = Students.Admission_Session_ID LEFT JOIN Students_Exam_Sessions ON Students_Exam_Sessions.Student_ID = Students.ID LEFT JOIN Exam_Sessions ON Exam_Sessions.ID = Students_Exam_Sessions.Exam_Session_ID LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID WHERE Students.Admission_Session_ID  = $admission ");
                    while ($date_sheet = $date_sheets->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $date_sheet['StudentName'] ?></td>
                            <td><?= $date_sheet['AdmissionName'] ?></td>
                            <td><?= $date_sheet['ExamSessionName'] ?></td>
                            <td><?= $date_sheet['SubCourseName'] ?></td>
                        </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>

<?php } ?>