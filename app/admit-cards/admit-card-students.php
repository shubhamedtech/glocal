<?php
  if(isset($_GET['course_id']) && isset($_GET['semester'])){
    require '../../includes/db-config.php';
    session_start();

    $sub_course_id = intval($_GET['course_id']);
    $semester = explode("|", $_GET['semester']);
    $scheme = $semester[0];
    $semester = $semester[1];
    // echo "SELECT Exam_Students.*, Courses.Name as course_name, Sub_Courses.Name as sub_course_name, Date_Sheets.Exam_date as exam_date, Date_Sheets.Start_time as start_time, Date_Sheets.End_time as end_time FROM Exam_Students LEFT JOIN Courses ON Exam_Students.Course = Courses.ID LEFT JOIN Sub_Courses ON Exam_Students.Sub_Course = Sub_Courses.ID LEFT JOIN Syllabi ON Exam_Students.Sub_Course = Syllabi.Sub_Course_ID LEFT JOIN Date_Sheets ON Syllabi.ID = Date_Sheets.Syllabus_ID LEFT JOIN Admission_Sessions ON Exam_Students.Admission_Session = Admission_Sessions.ID WHERE Exam_Students.Sub_Course = $sub_course_id AND Exam_Students.Duration = " . $semester . " AND Syllabi.Semester = " . $semester . " GROUP BY Exam_Students.Name"; die;
    $student = $conn->query("SELECT Exam_Students.*, Courses.Name as course_name, Sub_Courses.Name as sub_course_name, Date_Sheets.Exam_date as exam_date, Date_Sheets.Start_time as start_time, Date_Sheets.End_time as end_time FROM Exam_Students LEFT JOIN Courses ON Exam_Students.Course = Courses.ID LEFT JOIN Sub_Courses ON Exam_Students.Sub_Course = Sub_Courses.ID LEFT JOIN Syllabi ON Exam_Students.Sub_Course = Syllabi.Sub_Course_ID LEFT JOIN Date_Sheets ON Syllabi.ID = Date_Sheets.Syllabus_ID LEFT JOIN Admission_Sessions ON Exam_Students.Admission_Session = Admission_Sessions.ID WHERE Exam_Students.Sub_Course = $sub_course_id AND Exam_Students.Duration = " . $semester . " AND Syllabi.Semester = " . $semester . " GROUP BY Exam_Students.Name");
?>
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Studen Name</th>
            <th>Paper Name</th>
            <th>Paper Sem</th>
            <th>Paper Time</th>
            <th>Status</th>
            <th>Download Admit-Card</th>
          </tr>
        </thead>
        
          <?php 
          if($student->num_rows > 0){
            while($row = $student->fetch_assoc()) { 
            $id = base64_encode($row['ID'] . 'W1Ebt1IhGN3ZOLplom9I');
            ?>
            <tbody>
            <tr>
              <td><?=$row['Name']?></td>
              <td><?=$row['course_name']?> (<?=$row['sub_course_name']?>)</td>
              <td><?=$semester?></td>
              <td><?=$row['exam_date']?></td>
              <td><?=$row['start_time']?>/<?=$row['start_time']?></td>
              <td>
                <span class="text-primary cursor-pointer" onclick="window.open('/app/admit-cards/47/index?student_ids='+<?=$row['ID']?>);">Download</span>
              </td>
            </tr>
            </tbody>
            <?php } 
            }else{ ?>
            <tbody>
              <tr>
               <td>NO data Found</td>
              </tr>
            </tbody>

            <?php } ?>
      </table>
    </div>
  </div>
<?php
  }