<?php

use function PHPSTORM_META\sql_injection_subst;

if (isset($_POST['id']) && isset($_POST['by'])) {
    session_start();
    require '../../includes/db-config.php';

    $id = intval($_POST['id']);
    $by = $_POST['by'];
    $type = isset($_POST['type']) ? (mysqli_real_escape_string($conn, $_POST['type'])) : '';

    $sub_center_name = "";
    if ($by == 'sub_courses') {
        $_SESSION['filterBySubCourses'] = " AND Students.Sub_Course_ID = $id";
    } elseif ($by == 'users' && $type!='vartical_type') {

        $user = $conn->query("SELECT Role FROM Users WHERE ID = $id");
        $role_query = '';
        if ($user && $user->num_rows > 0) {
            $user = $user->fetch_assoc();

            $role = $user['Role'];
            $role_query = " AND Students.Added_For = $id";

            if ($role === 'Center') {
                $center_list[] = $id;
                $get_sub_center_list = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = $id AND University_ID = " . $_SESSION['university_id']);
                if ($get_sub_center_list->num_rows > 0) {
                    while ($gscl = $get_sub_center_list->fetch_assoc()) {
                        $sub_center_list[] = $gscl['User_ID'];
                    }
                    $all_list = array_merge($center_list, $sub_center_list);
                    $all_lists = "(" . implode(",", ($all_list)) . ")";
                    $role_query = " AND Students.Added_For IN $all_lists";
                }
                $subCenter = array();
                $subCenter = $conn->query("SELECT * FROM Center_SubCenter WHERE Center=$id");
                if ($subCenter->num_rows > 0) {

                    $subCenterArrId = array();
                    $subCenterArrIdzz = array();
                    while ($subCenterArr = $subCenter->fetch_assoc()) {
                        $subCenterArrId[] = $subCenterArr['Sub_Center'];
                        $subCenterArrIdzz[] = $subCenterArr['Sub_Center'];
                    }

                    $subCenterArrIdzz[] = $id;
                    $centerSubCenterIds = "(" . implode(",", $subCenterArrIdzz) . ")";

                    $role_query = " AND Students.Added_For IN $centerSubCenterIds AND Students.University_ID = " . $_SESSION['university_id'];

                    $subCenter_list = "(" . implode(",", $subCenterArrId) . ")";
                    $sub_centers = $conn->query("SELECT `ID`, `Code`, `Name`, `Role` FROM Users  WHERE ID IN $subCenter_list");
                    $sub_center_name .= "<option value=''>Select Sub Center</option>";
                    while ($subCenterListArr = $sub_centers->fetch_assoc()) {
                        $sub_center_name .= "<option value='" . $subCenterListArr['ID'] . "'>" . ucwords(strtolower($subCenterListArr['Name'])) . "</option>";
                    }
                } else {
                    $sub_center_name = "<option value=''>No Record found!</option>";
                }
            } elseif ($role == 'Sub-Center') {
                $role_query = " AND Students.Added_For = $id AND Students.University_ID = " . $_SESSION['university_id'];
            }
        }
        $_SESSION['filterByUser'] = $role_query;
    } elseif ($type === 'vartical_type') {

        $vartical_type_sql = $conn->query("SELECT ID FROM Users WHERE  Vertical_type='$id' AND Status=1");
        while ($row = $vartical_type_sql->fetch_array()) {
            $center_id_arr[] = $row['ID'];
        }
        $center_ids = implode(',', $center_id_arr);
        $_SESSION['filterByVerticalType'] = " AND Students.Added_For IN ($center_ids)";
    }
    echo json_encode(['status' => true, 'subCenterName' => $sub_center_name]);
}