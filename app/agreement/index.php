<?php
error_reporting(1);

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['id'])) {
    require '../../includes/db-config.php';
    require '../../includes/helpers.php';
    session_start();
    $id = $_GET['id'];
    $getDate = $conn->query("SELECT * FROM Agreements WHERE Role=1");
    $dateArr = [];
    if ($getDate->num_rows > 0) {
        $agreementArr = $getDate->fetch_assoc();
        $start_date = date('d-M-Y', strtotime($agreementArr['start_date']));
        $dateArr = explode('-', $start_date);

        $agreement_pdf = '../..' . $agreementArr['Agreement_File'];

        $getStudentQuery = $conn->query("SELECT CONCAT(TRIM(CONCAT(u.State, ', ', u.District))) as state, u.State, u.District, u.Address as u_address, u.Name as center_name, u.Contact_Name, CONCAT(TRIM(CONCAT(s.First_Name, ' ', s.Middle_Name, ' ', s.Last_Name))) as Name, s.Contact, s.Address, s.Father_Name, s.Email, c.Name AS course_name, c.Short_Name AS course_short_name, un.Name AS u_name, un.Address AS u_address FROM Students AS s LEFT JOIN Users AS u ON s.Added_By = u.ID LEFT JOIN Courses AS c ON s.Course_ID = c.ID LEFT JOIN Universities AS un ON s.University_ID = un.ID WHERE s.ID = $id");
        if ($getStudentQuery->num_rows > 0) {
            $row = $getStudentQuery->fetch_assoc();
            require_once('../../extras/qrcode/qrlib.php');
            require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
            require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

            $pdf = new Fpdi();
            $pdf->SetTitle('Agreement');
            $pageCount = $pdf->setSourceFile($agreement_pdf);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($tplId, ['adjustPageSize' => true]);

                if ($pageNo == 1) {
                    $pdf->SetFont('Arial', 'B', 11);
                    $pdf->SetXY(165, 106);
                    $pdf->Write(10, ucwords(strtolower($row['State'])));
                    $pdf->SetXY(15, 115);
                    $pdf->Write(10, ucwords(strtolower(trim($row['District']))));
                    $pdf->SetXY(102.8, 106);
                    $pdf->Write(10, $dateArr[0]);
                    $pdf->SetXY(135.3, 106.2);
                    $pdf->Write(10, $dateArr[1] . ', ' . $dateArr[2]);
                    $pdf->SetXY(100, 115);
                    $pdf->Write(10, $row['center_name']);
                    $pdf->SetXY(70, 120);
                    $pdf->Write(10, $row['u_address']);
                    $pdf->SetXY(85, 128);
                    $pdf->Write(10, ucwords(strtolower($row['Contact_Name'])));
                    $pdf->SetXY(58, 136.5);
                    $pdf->Write(10, ucwords(strtolower($row['Name'])));
                    $pdf->SetXY(15, 145);
                    $address = json_decode($row['Address'], true);
                    $pdf->Write(10, ucwords(strtolower($address['present_address'])));
                    $pdf->SetXY(119, 180);
                    $pdf->Write(10, ucwords(strtolower($row['Name'])));

                    $pdf->SetXY(18, 180);
                    $center_name = wrapText(ucwords(strtolower($row['center_name'])), 4, '        ');
                    $pdf->Write(6, $center_name);
                }

                if ($pageNo == 2) {
                    $pdf->SetFont('Arial', 'B', 11);
                    $pdf->SetXY(112, 56);
                    $pdf->Write(10, ucwords(strtolower($row['course_name'])));
                    $pdf->SetXY(22, 64.5);
                    $pdf->Write(10, ucwords(strtolower($row['u_name'])) . ' and ' . ucwords(strtolower($row['u_address'])));
                    $pdf->SetXY(98, 73.6);
                    $pdf->Write(10, ucwords(strtolower($row['course_name'])));
                    $pdf->SetXY(29, 82);
                    $pdf->Write(10, ucwords(strtolower($row['u_name'])) . ' and ' . ucwords(strtolower($row['u_address'])));
                }

                if ($pageNo == 5) {
                    $pdf->SetFont('Arial', 'B', 11);
                    $pdf->SetXY(48, 25);
                    $pdf->Write(10, ucwords(strtolower($row['state'])));
                    $pdf->SetXY(122, 57);
                    $pdf->Write(10, $row['Contact']);
                    $pdf->SetXY(122, 65);
                    $pdf->Write(10, $row['Email']);
                    $pdf->SetXY(152, 73);
                    $pdf->Write(10, ucwords(strtolower($row['Father_Name'])));

                    // Parent Signature
                    $photo_query = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Parent Signature'");
                    if ($photo_query->num_rows > 0) {
                        $photo = $photo_query->fetch_assoc();
                        $photo_path = "../.." . $photo['Location'];
                        if (file_exists($photo_path)) {
                           // $pdf->Image($photo_path, 20, 50, 30.5, 35.9);
                          $pdf->Image($photo_path, 110, 90, 30.2, 11.3);
                        }
                    }

                    // Student Signature
                    $signature_query = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Student Signature'");
                    if ($signature_query->num_rows > 0) {
                        $signature = $signature_query->fetch_assoc();
                        $signature_path = "../.." . $signature['Location'];
                        if (file_exists($signature_path)) {
                            $pdf->Image($signature_path, 110, 45, 30.2, 11.3);
                        }
                    }
                }
            }

            $pdf->Output('I', 'Agreement_Preview.pdf');
        } else {
            echo json_encode(['status' => 400, 'message' => 'Student not found!']);
            exit();
        }
    } else {
        echo json_encode(['status' => 400, 'message' => 'Agreement not uploaded!']);
        exit();
    }
} else {
    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    exit();
}
