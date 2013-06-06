
<?php
require('lib/fpdf.php');

class PDF extends FPDF
{
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
    $this->Cell(30,10,'Purchase Receipt',0,0,'C');
    // Line break
    $this->Ln(20);
}
}

try {
    $link = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}


$rec = 'VF04X3EP1K';


gen_new_receipt($rec, $link);

function gen_new_receipt($rec,$link)
{
    $header = array('Stock Name', 'Cash Buy Price', 'Exchange Buy Price', 'Points');
    //$filename = 'lib/stock_qr/4SFWGAS2SP5MQBJYNCAU8ISYI.png';
    
    $pdf = new PDF();
    
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);
    $w = array(40, 35, 40, 45);
    for($i=0;$i<count($header);$i++)
        $pdf->Cell($w[$i],7,$header[$i],1,0,'C',true); 
    $getQR = 'receipt_qr_images/'. $rec . '.png';
    $pdf->Image($getQR,6,60,30);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    
    
    $query = $link->query("SELECT * FROM _gg_stock WHERE _rec_ref = '$rec'");
    $data = array();
    while ($nrow = mysqli_fetch_array($query)) {
            $points = $nrow["_sp"] * 100;
            $data[] = array($nrow["_stock_name"],
                $nrow["_cbp"], $nrow["_ebp"], "$points");
        }
    $fill = false;
    foreach ($data as $row)
    {
        $pdf->Cell($w[0],10,$row[0],'LR',0,'L',$fill);
        $pdf->Cell($w[1],10,$row[1],'LR',0,'L',$fill);
        $pdf->Cell($w[2],10,$row[2],'LR',0,'L',$fill);
        $pdf->Cell($w[3],10,$row[3],'LR',0,'L',$fill);
        $pdf->Ln();
        $fill = !$fill;
        
    }
    $pdf->Cell(array_sum($w),0,'','T');
    
    $pdf->Output('cust_docs/purchase_receipts/' . $rec . '.pdf','F');
}


?>