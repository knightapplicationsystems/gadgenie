<?php

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
try {
    $link = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('');
$pdf->SetTitle('Gad Genie Purchase Reeceipt');
$pdf->SetSubject('');
$pdf->SetKeywords('');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' Purchase Receipt', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------
// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));
$rec = 'ZQ9ZQXQVQE';
$image = file_get_contents('http://chart.apis.google.com/chart?chs=500x500&cht=qr&chld=M&chl=' . urlencode($rec));
$pdf->Image('@' . $image, 10, 45, 45, 45);
// Set some content to print
$html = <<<EOD
<br>
<br>    
<br>
<br>
<br>
<br>  
<br>
<br>  
<br>
<br>
<br>
<p>Ref: $rec</p>  
<br> 
<h1>Thank you for selling your items to Gad Genie</h1>
<p>Please see below the list of items you have sold to us</p>
<br>
<table style="border:2px solid black;border-collapse:collapse;">        
<tr>        
        <th style="border:1px solid black;border-collapse:collapse;">Stock Name</th>
        <th style="border:1px solid black;border-collapse:collapse;">Cash Price</th>
        <th style="border:1px solid black;border-collapse:collapse;">Exchange Price</th>
        <th style="border:1px solid black;border-collapse:collapse;">Points Value</th>
</tr>    
EOD;
$data = array();
$query = $link->query("SELECT * FROM _gg_stock WHERE _rec_ref = '$rec'");
echo "SELECT * FROM _gg_stock WHERE _rec_ref = '$rec'";
while ($nrow = mysqli_fetch_array($query)) {
    $points = $nrow["_sp"] * 100;
    $data[] = array($nrow["_stock_name"],
        $nrow["_cbp"], $nrow["_ebp"], "$points", $nrow["_code_id"]);
}


foreach ($data as $row) {
    $html .='<tr>';
    $html .= '<td style="font-size:14px;">' . $row[0] . '</td>';
    $html .= '<td style="font-size:14px;">£' . $row[1] . '</td>';
    $html .= '<td style="font-size:14px;">£' . $row[2] . '</td>';
    $html .= '<td style="font-size:14px;">' . $row[3] . '</td>';
    $html .='<br>';
    $html .='</tr>';
}

$html .='</table>';
$html .='<br>';
$html .='<br>';

foreach ($data as $row) {

    $html .= '<img src="images/' . $row[4] . '.png" width="65" height="65"></img>';
    $html .='<br>';
}


// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------
// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
