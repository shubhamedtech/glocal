<?php
if (isset($_GET['files']) && isset($_GET['student_name']) && isset($_GET['enrollment_no']) && isset($_GET['subject_name'])) {
    $files = explode(',', $_GET['files']);
    $student_name = $_GET['student_name'];
    $enrollment_no = $_GET['enrollment_no'];
    $subject_name = $_GET['subject_name'];

    $zip = new ZipArchive();
    $zipName = sys_get_temp_dir() . '/' . $subject_name . '_' . $enrollment_no . '_' . $student_name . '_assignments.zip';
    error_log("Creating zip file: $zipName");

    if ($zip->open($zipName, ZipArchive::CREATE) !== TRUE) {
        error_log("Cannot create zip file.");
        http_response_code(500);
        exit("Cannot create zip file.");
    }

    foreach ($files as $file) {
        $file = trim($file);
        error_log("Adding file to zip: $file");
        if (file_exists($file)) {
            $zip->addFile($file, basename($file));
        } else {
            error_log("File not found: $file");
            $zip->addFromString('error.txt', "File not found: $file\n");
        }
    }

    $zip->close();

    if (file_exists($zipName)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename(addslashes($subject_name . '_' . $enrollment_no . '_' . $student_name . '_assignments.zip')) . '"');
        header('Content-Length: ' . filesize($zipName));
        readfile($zipName);
        unlink($zipName);
        exit;
    } else {
        error_log("Error creating zip file.");
        http_response_code(500);
        exit("Error creating zip file.");
    }
} else {
    http_response_code(400);
    exit("No files or subject name specified.");
}
