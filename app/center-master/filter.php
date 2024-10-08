<?php
if (isset($_POST['id']) && isset($_POST['by'])) {
    session_start();
    require '../../includes/db-config.php';

    $by = $_POST['by'];
    $id = intval($_POST['id']);
    $sub_center_name = "";
    if ($by == 'university') {
        $userIds = [];
        if ($id == 1) {
            $userQuery = $conn->query("SELECT Code FROM Alloted_Center_To_Counsellor ");
            if ($userQuery->num_rows > 0) {
                while ($row = $userQuery->fetch_assoc()) {
                    $userIds[] = $row['Code'];
                }
                $ids = implode(',', $userIds);
                $_SESSION['filterByUniversity'] = !empty($userIds) ? " AND ID NOT IN ($ids)" : "";
            } else {
                $_SESSION['filterByUniversity'] = "";
            }
        } else {
            $userQuery = $conn->query("SELECT Code FROM Alloted_Center_To_Counsellor WHERE  University_ID = " . $id);
            if ($userQuery->num_rows > 0) {
                while ($row = $userQuery->fetch_assoc()) {
                    $userIds[] = $row['Code'];
                }
                $ids = implode(',', $userIds);
                $_SESSION['filterByUniversity'] = !empty($userIds) ? " AND ID IN ($ids)" : "";
            } else {
                $_SESSION['filterByUniversity'] = "";
            }
        }
    } elseif ($by == 'vertical_type') {

        // $vartical_type_sql = $conn->query("SELECT ID FROM Users WHERE  Vertical_type='$id' AND Status=1");
        // while ($row = $vartical_type_sql->fetch_array()) {
        //     $center_id_arr[] = $row['ID'];
        // }
        // $center_ids = implode(',', $center_id_arr);

        // $_SESSION['filterByVerticalType'] = " AND ID IN ($center_ids)";
        $_SESSION['filterByVerticalType'] = " AND Vertical_type = $id";


    }
    echo json_encode(['status' => true]);
}
