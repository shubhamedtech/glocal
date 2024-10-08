<?php
if (isset($_POST['id']) && isset($_POST['by'])) {
    session_start();
    require '../../includes/db-config.php';
    $by = $_POST['by'];
    // print_r($_SESSION['university_id']);
    $id = intval($_POST['id']);
    $sub_center_name = "";
    $center_list = array();
    $sub_center_list = array();
    if ($by == 'departments') {
        $courseIds = $conn->query("SELECT GROUP_CONCAT(ID) as ID FROM Courses WHERE ID = $id AND University_ID = " . $_SESSION['university_id']);
        if ($courseIds && $courseIds->num_rows > 0) {
            $courseIds = $courseIds->fetch_assoc()['ID'];
            $_SESSION['filterByDepartment'] = !empty($courseIds) ? " AND Students.Course_ID IN ($courseIds)" : " AND Students.ID IS NULL";
        } else {
            $_SESSION['filterByDepartment'] = " AND Students.ID IS NULL";
        }
    } elseif ($by == 'sub_courses') {
        $_SESSION['filterBySubCourses'] = " AND Students.Sub_Course_ID = $id";
    } elseif ($by == 'semesterdata') {
        $_SESSION['filterBySemesterdata'] = " AND Students.Duration= $id";
    } elseif ($by == 'subjectdata') {
        $_SESSION['filterBySubjectdata'] = " AND Syllabi.ID= $id";
        $_SESSION['filterSubjectsID'] = $id;
    } elseif ($by == 'users') {
        $user = $conn->query("SELECT Role FROM Users WHERE ID = $id");
        if ($user && $user->num_rows > 0) {
            $user = $user->fetch_assoc();
            $role = $user['Role'];
            $role_query = " AND Students.Added_For = $id";
            if ($role == 'Sub-Center') {
                $role_query = " AND Students.Added_For = $id AND Students.University_ID = " . $_SESSION['university_id'];
            }
            $_SESSION['filterByUser'] = $role_query;
        }
    } elseif ($by == 'vertical_type') {
        $vartical_type_sql = $conn->query("SELECT ID FROM Users WHERE Vertical_type='$id' AND Status=1");
        if ($vartical_type_sql && $vartical_type_sql->num_rows > 0) {
            $center_id_arr = array();
            while ($row = $vartical_type_sql->fetch_array()) {
                $center_id_arr[] = $row['ID'];
            }
            $center_ids = implode(',', $center_id_arr);
            $_SESSION['filterByVerticalType'] = " AND Students.Added_For IN ($center_ids)";
        }
    } elseif ($by == 'assignmentstatus') {
        if (isset($_SESSION['filterSubjectsID'])) {
            $subject_id = $_SESSION['filterSubjectsID'];
            $students_ids = [];
            $submit_sql = ($id == 1) ? " AND id IS NOT NULL" : "";
            $submitted_count_query = $conn->query("SELECT * FROM submitted_assignment WHERE subject_id = $subject_id $submit_sql");
            if ($submitted_count_query && $submitted_count_query->num_rows > 0) {
                while ($get_students = $submitted_count_query->fetch_assoc()) {
                    $students_ids[] = $get_students['student_id'];
                }
            }
            $stu_ids = implode(',', $students_ids);
            if ($id == 1) {
                $_SESSION['filterBysubmitted_students'] = ' AND submitted_assignment.student_id IN (' . $stu_ids . ')';
            } else {
                $_SESSION['filterBysubmitted_students'] = ' AND Students.ID NOT IN (' . $stu_ids . ')';
            }
        } else {
            echo json_encode(['status' => false, 'message' => 'No subject selected']);
            exit;
        }
    }
    echo json_encode(['status' => true, 'subCenterName' => $sub_center_name]);
}
