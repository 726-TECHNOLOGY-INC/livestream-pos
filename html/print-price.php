<?php 
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2025            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Receipt - PDF - Multi Size and Barcode Support - v7.5.26
//
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    if (array_key_exists("Access-Control-Request-Private-Network", $_SERVER)) {
        header("Access-Control-Allow-Private-Network: true");
    }

    http_response_code(200);

    return true;
}


function PDF_show_xy_center(&$xypdf, &$xyfont, $xytext, $docwidth, $ybottom) {
    if ($xytext == "") { return false; }

    $xyfontsize = pdf_get_value($xypdf, "fontsize", 0);
    $width = pdf_stringwidth($xypdf, $xytext, $xyfont, $xyfontsize);

    PDF_show_xy($xypdf, $xytext, ($docwidth / 2) - ($width / 2), $ybottom);
}

$labelSize = filter_input(INPUT_GET, "size", FILTER_SANITIZE_STRING);
if ($labelSize == "") {
    $labelSize = "3x1";
}

$price = filter_input(INPUT_GET, "price", FILTER_SANITIZE_STRING);
$priceVal = $price;

$day = filter_input(INPUT_GET, "day", FILTER_SANITIZE_STRING);
if ((!isset($day)) || ($day == "")) {
    $dayName = "MONDAY";
} else {
    $dayName = $day;
}

$rando = rand(pow(10, 2), pow(10, 3) - 1);
$instance = "LABEL-" . $i . "-" . date("Y-m-d-H-m-s") . "-$rando.pdf";
$p2 = PDF_new();

if (PDF_begin_document($p2, "./tags/$instance", "") == 0) {
    die("Error: " . PDF_get_errmsg($p2));
}

PDF_set_info($p2, "Creator", "print-receipt-pdf.php");
PDF_set_info($p2, "Author", "726 TECHNOLOGY INC");
PDF_set_info($p2, "Title", "PRINT NUMBER PDF");

$xMargin = 8;

switch ($labelSize) {
    case "3x2":            
        break;
    case "3x1": default:
        PDF_begin_page_ext($p2, 216, 72, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

        PDF_setfont($p2, $fontHelveticaBold, 40.0);
        PDF_show_xy_center($p2, $fontHelveticaBold, "$" . $priceVal, 216, 10);  

        PDF_setfont($p2, $fontHelveticaBold, 6.0);
        PDF_show_xy($p2, $livestream, $xMargin, 6); 

        break;
}
    
PDF_end_page_ext($p2, "");   
PDF_end_document($p2, "");

exec("./print-label.sh $instance");


echo("Printing Complete");
