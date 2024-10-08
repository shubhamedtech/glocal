<?php
if (isset($_GET['id']) && isset($_GET['university_id']) && isset($_GET['duration'])) {
  require '../../includes/db-config.php';
  $id = intval($_GET['id']);
  $university_id = intval($_GET['university_id']);
  $query = '';
  $duration = $_GET['duration'];
  ?>
  <option value="">Choose</option>
  <?php
  $subjectSql = $conn->query("SELECT s.Name AS subject_name,s.ID FROM Syllabi AS s WHERE Sub_Course_ID = $id AND University_ID =$university_id  AND Semester = '" . $duration . "'   $query");
  if ($subjectSql->num_rows > 0) {
    while ($row = $subjectSql->fetch_assoc()) { ?>
      <option value="<?php echo $row['ID'] ?>"><?php echo ucfirst(strtolower($row['subject_name'])) ?></option>
      <?php
    }
  } else {
    echo '<option value="">No Subject Found!</option>';
  }
}
