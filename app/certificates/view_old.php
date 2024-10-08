<?php
error_reporting(1);

require '../../includes/db-config.php';

session_start();

$id=$_GET['student_id'];
// $id = intval($_POST['student_id']);

//University_ID = 48 AND
$students_temps_result = $conn->query("SELECT students.*,sub_courses.Min_Duration as total_duration,modes.Name as mode,sub_courses.Name as course,courses.Name as program_Type FROM students left join sub_courses on sub_courses.ID=students.Sub_Course_ID left join modes on students.University_ID=modes.University_ID left join courses on students.Course_ID=courses.ID  WHERE students.ID = '".$id."' ");    //AND Duration = $sem 
if($students_temps_result->num_rows > 0){
    $students_temps = $students_temps_result->fetch_assoc();
}else{
    $students_temps['duration'] = '';
}

$durMonthYear="";
if($students_temps['mode']=="Monthly"){
    $durMonthYear="Months";
}elseif($students_temps['mode']=="Sem"){
    $durMonthYear="Semesters";
}else{
    $durMonthYear="Years";
}
$total_duration=0;
if($students_temps['University_ID']==47 ){//|| $students_temps['mode']=="Sem"
    $total_duration=0; 
    if (str_contains($students_temps['total_duration'], '"')) { 
        $a=str_replace('"', '', $students_temps['total_duration']);
        $total_duration=(int)$a;
    }else{
        $total_duration=(int)$students_temps['total_duration'];
    }
}else{
    if (str_contains($students_temps['Duration'], '/')) { 
        $a=explode("/",$students_temps['Duration']);
        $total_duration=(int)$a[0];
    }else{
        $total_duration=(int)$students_temps['Duration'];
    }
}

$courseCategory="";
if (str_contains($students_temps['Course_Category'], '_')) { 
    $a=str_replace('_',' ', $students_temps['Course_Category']);
    $courseCategory=ucfirst($a);
}else{
    $courseCategory=ucfirst($students_temps['Course_Category']);
}

$hours=0;

if($total_duration==3 && $durMonthYear=="Months"){
    $hours=160;
}elseif($total_duration==6 && $durMonthYear=="Months"){
    $hours=320;
}elseif($total_duration==11 && $durMonthYear=="Months"){
    $hours=960;
}elseif ($total_duration==6 && $durMonthYear=="Semester"){
    $hours="NA";
}

$a=implode(' ', array_slice(explode(' ', $students_temps['course']), 0, 6));
$b=implode(' ', array_slice(explode(' ', $students_temps['course']), 6));

$name=$students_temps['First_Name']." ".$students_temps['Middle_Name']." ".$students_temps['Last_Name'];

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
    
ob_end_clean(); 
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

//$pdf = new FPDI();

$pdf = new \setasign\Fpdi\Fpdi();
$pdf->AddPage('L', 'A4');
$pdf->setSourceFile('../../assets/img/University_Certification.pdf'); 
//$pdf->useTemplate($tplIdx, 0, 0, 0, 0, true); 
// $pdf->SetLeftMargin(0);
$pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);
$pdf->useImportedPage($pageId, 0, 0, 297,210);

// // Instantiate and use the FPDF class  
// $pdf = new FPDF('P','mm',array(298,210)); 
// $pdf->AddPage('L'); 
// $pdf->Image('../../assets/img/certificate.jpeg', 0, 0,298,210); //$pdf->w, $pdf->h

$pdf->SetY(105);
$pdf->AddFont('GreatVibes-Regular','','GreatVibes-Regular.php');
$pdf->SetFont('GreatVibes-Regular','',24);

$pdf->MultiCell(0,18, $name ,0,'C',0);
  
// // Set the font for the text 
// $pdf->SetFont('Courier', 'BIU', 24); 
// if(strlen($name)<=5){
//     $pdf->SetXY(142, 90);   
// }elseif(strlen($name)<=10){
//     $pdf->SetXY(133, 90);    
// }else if(strlen($name)<=15){
//     $pdf->SetXY(125, 90);     
// }else if(strlen($name)<=20){
//     $pdf->SetXY(127, 90);       
// }else if(strlen($name)<=25){
//     $pdf->SetXY(111, 90);      
// }else if(strlen($name)<=30){
//     $pdf->SetXY(104, 90);     
// }else{
//     $pdf->SetXY(95, 90);      
// }

// $x = $pdf->GetX();
// $y = $pdf->GetY();

// $pdf->SetXY(105, 111);
// $pdf->SetFont('Arial', 'B', 16); 
//$pdf->Write(1,$name,'C');

// $pdf->SetXY(98, 105);
 $pdf->SetFont('Arial', 'B', 14); 
// $pdf->Write(1, "has successfully completed a ");

//$pdf->MultiCell(0,10, "has successfully completed a ".$courseCategory." in" ,1,'C',0);
$pdf->SetX(156);
$pdf->MultiCell(0,10, $courseCategory ,0,0,0);


//$pdf->SetFont('Arial', 'B', 14); 
//$pdf->SetXY(98, 105);
//$pdf->Write(1, $courseCategory." ");

//$pdf->MultiCell(0,10, $courseCategory." " ,0,'C',0);

//$pdf->SetFont('Arial', '', 16); 
//$pdf->Write(1, " in");

// $x = $pdf->GetX();
// $y = $pdf->GetY();

//$pdf->SetXY(105, 111);
$pdf->SetFont('Arial', 'B', 16); 
$pdf->MultiCell(0, 6, strtoupper($students_temps['course']),0,'C',0);
//$pdf->SetXY($x + 155, $y);



// $pdf->SetXY(105, 115);
// $pdf->SetFont('Arial', 'B', 16); 
// $pdf->Write(1, strtoupper($students_temps['course']));

// if(strlen($b)>0){
//     $pdf->SetXY(92, 132);
// }else{
//     $pdf->SetXY(92, 123);
// }

$pdf->SetFont('Arial', '', 13); 
//$pdf->Write(1, " offered by the Center for skill education Glocal University.");

//$pdf->MultiCell(0, 10, " offered by the Center for skill education Glocal University.",0,'C',0);

$pdf->MultiCell(0, 10, "",0,0,0);

// if(strlen($b)>0){
//     $pdf->SetXY(86, 142);
// }else{
//     $pdf->SetXY(86, 132);
// }
//$pdf->Write(1, " During the");
//$pdf->SetFont('Arial', 'B', 16); 
//$pdf->Write(1, " AY 2023-24");

$pdf->SetFont('Arial', 'B', 15); 
//$pdf->Write(1, " with a duration of ");
//$pdf->SetFont('Arial', 'B', 16); 
//$pdf->Write(1, $hours." hours/".$total_duration." ".$durMonthYear);
//$pdf->MultiCell(0, 5, " During the AY 2023-24  with a duration of ".$hours." hours/".$total_duration." ".$durMonthYear,0,'C',0);

$pdf->SetX(96);
$pdf->MultiCell(0, 5, "AY 2023-24",0,0,0);
$pdf->SetX(172);
$pdf->MultiCell(0, -6, $hours." hours/".$total_duration." ".$durMonthYear,0,0,0);



// $filename=$students_temps['Unique_ID']."_".time().".pdf";
// // return the generated output 
// $pdf->Output('../../uploads/certificates/'.$filename,"F");
// $student_id=$id;
// $enrollment_no=$students_temps['Enrollment_No'];
// $file_path="uploads/certificates/".$filename;
// $file_type="pdf";
// $status=1;
// $created_by=$_SESSION['ID'];
// $created_at=date("Y-m-d:H:i:s");
// $add = $conn->query("INSERT INTO `certificates`(`student_id`, `enrollment_no`, `file_path`, `file_type`, `status`,`created_by`, `created_at`) VALUES ('".$student_id."', '".$enrollment_no."', '".$file_path."', '".$file_type."','".$status."', '".$created_by."', '".$created_at."' ) ");

// if($add){
//     echo json_encode(['status'=>200, 'message'=> "Certificate generated succefully!!"]);
//   }else{
//     echo json_encode(['status'=>400, 'message'=>'Something went to wrong!!']);
//   }
$pdf->Output(); 







  
