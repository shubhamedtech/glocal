<?php
ini_set('max_execution_time', '300');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../../includes/db-config.php';
session_start();
$cenid = isset($_POST['center']) ? $_POST['center'] : '';
$coursetype = isset($_POST['coursetype']) ? $_POST['coursetype'] : '';
$subcourse_id = isset($_POST['subcourse_id']) ? $_POST['subcourse_id'] : '';
$semester = isset($_POST['seme']) ? $_POST['seme'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$sqlQuery = "SELECT Students.*, 
                    CONCAT_WS(' ', Students.First_Name, Students.Middle_Name, Students.Last_Name) AS student_name, 
                    Students.Unique_ID AS unique_id,
                    submitted_assignment.file_name as submitted_file,
                    Courses.Name as course_name,
                    Syllabi.Name as subject_name,
                    Users.Name as center_name,
                    Users.ID as centerid,
                    Users.Code as center_code,
                    Sub_Courses.Name as sub_courses,
                    Syllabi.Semester as duration
             FROM Students
             LEFT JOIN Users ON Students.Added_For = Users.ID
             LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID
             INNER JOIN submitted_assignment ON submitted_assignment.Student_ID = Students.ID
             INNER JOIN Courses ON Courses.ID = Students.Course_ID
             INNER JOIN Syllabi ON Syllabi.ID = submitted_assignment.subject_id
             WHERE Users.Role IN ('Center', 'Sub-Center')";

$params = [];
$types = '';

if (!empty($cenid)) {
    $sqlQuery .= " AND Users.ID = ?";
    $types .= 'i';
    $params[] = $cenid;
}

if (!empty($coursetype)) {
    $sqlQuery .= " AND Students.Course_ID = ?";
    $types .= 'i';
    $params[] = $coursetype;
}

if (!empty($subcourse_id)) {
    $sqlQuery .= " AND Students.Sub_Course_ID = ?";
    $types .= 'i';
    $params[] = $subcourse_id;
}

if (!empty($semester)) {
    $sqlQuery .= " AND Students.Duration = ?";
    $types .= 'i';
    $params[] = $semester;
}

if (!empty($subject)) {
    $subjects_id_array = explode(',', $subject);
    $placeholders = implode(',', array_fill(0, count($subjects_id_array), '?'));
    $sqlQuery .= " AND submitted_assignment.subject_id IN ($placeholders)";
    $types .= str_repeat('i', count($subjects_id_array));
    $params = array_merge($params, $subjects_id_array);
}

$stmt = $conn->prepare($sqlQuery);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
error_log("Executing query: $sqlQuery");

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

if ($result->num_rows > 0) {
    $queue = [];  // Initialize queue array
    while ($row = $result->fetch_assoc()) {
        $files = explode(',', trim($row['submitted_file']));
        $course_name = trim($row['course_name']);
        $student_name = trim($row['student_name']);
        $subject_name = trim($row['subject_name']);
        $unique_id = trim($row['unique_id']);
        $sub_course = trim($row['sub_courses']);
        $semester = trim($row['duration']);
        $center_name = trim($row['center_name']);
        $center_code = trim($row['center_code']);

        if (!empty($course_name) && !empty($subject_name) && !empty($sub_course)) {
            // Ensure the sub_courses directory is created correctly
            $dir = $center_name . '-' . $center_code . '/' . $course_name . '/' . $sub_course . '/' . $semester . '/' . $subject_name;
            foreach ($files as $file) {
                $file = trim($file);
                if (file_exists($file)) {
                    $file_path_in_zip = $dir . '/' . $unique_id . '_' . $student_name . '_' . basename($file);
                    $queue[] = ['file' => $file, 'path_in_zip' => $file_path_in_zip];
                } else {
                    error_log("File not found: $file");
                }
            }
        }
    }
    processQueue(json_encode($queue));
} else {
    http_response_code(404);
    echo '<script type="text/javascript">
            alert("No records found.");
            window.location.href = "../../lms-settings/student_assignments_review";
          </script>';
}

function processQueue($data)
{
    $zipFileName = time() . '.zip';
    $queue = json_decode($data, true);

    if (empty($queue)) {
        exit("No files to zip.\n");
    }

    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
        exit("Cannot open <$zipFileName>\n");
    }

    foreach ($queue as $entry) {
        $file = $entry['file'];
        $path_in_zip = $entry['path_in_zip'];
        $dir_in_zip = dirname($path_in_zip);

        if (!empty($dir_in_zip)) {
            if (!$zip->locateName($dir_in_zip)) {
                $zip->addEmptyDir($dir_in_zip);
            }
        }

        if (file_exists($file)) {
            $zip->addFile($file, $path_in_zip);
        } else {
            error_log("File $file does not exist.");
            $zip->addFromString($dir_in_zip . '/error.txt', "File not found: $file\n");
        }
    }
    if (!$zip->close()) {
        exit("Failed to close the zip file properly.\n");
    }
    if (file_exists($zipFileName)) {
        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipFileName));
        flush();
        readfile($zipFileName);
        unlink($zipFileName);
    } else {
        exit("Failed to create the zip file.\n");
    }
}
?>