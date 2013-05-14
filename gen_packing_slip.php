
<?php
require('lib/fpdf.php');

class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo
    //Local
    //$this->Image('C:\\Users\\Justin.Howard\\Dropbox\\Justin\\lib\\stock_qr\\gg.png',10,6,30);
    //Server
    $this->Image('gg.png',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,10,'Packing Slip',0,0,'C');
    // Line break
    $this->Ln(20);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

function gen_new_packing_slip($getQR,$unique_ref,$email)
{
    
    //$filename = 'lib/stock_qr/4SFWGAS2SP5MQBJYNCAU8ISYI.png';
    
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->MultiCell(0,10,'Deliver to:',1,'L',false);
    $pdf->MultiCell(0,10,'Gad Genie Ltd',1,'L',false);
    $pdf->MultiCell(0,10,'Some Address 1',1,'L',false);
    $pdf->MultiCell(0,10,'Some Address 2',1,'L',false);
    $pdf->MultiCell(0,10,'Croydon',1,'L',false);
    $pdf->Image($getQR,6,60,30);
    
    
    
    require 'emailer.php';
    
    //$email = "mylescgriffith@gmail.com";
    $mailType = "Delivery information for your item";
    $url = "http://gadgenie.com/api/cust_docs/packing_slips/" . $unique_ref . '.pdf';

    send_new_add_stock_email($email, $mailType,$url);
    //Local
    //$pdf->Output('C:\\wamp\\www\\gadgenie\\api\\lib\\stock_qr\\packing_slips\\' . $unique_ref . '.pdf','F');
    //Server
    //Volumes//web//gadgenie//api//lib//stock_qr//
    $pdf->Output('cust_docs/packing_slips/' . $unique_ref . '.pdf','F');
}


?>