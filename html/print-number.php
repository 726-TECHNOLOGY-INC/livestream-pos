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
    // $labelSize = "3x1";
    $labelSize = "225x125";
}

$offset = filter_input(INPUT_GET, "offset", FILTER_SANITIZE_STRING);
$offsetVal = intval($offset);

$day = filter_input(INPUT_GET, "day", FILTER_SANITIZE_STRING);
if ((!isset($day)) || ($day == "")) {
    // $dayName = "MONDAY";
    $dayName = "AUCTION LOT";
} else {
    $dayName = $day;
}

for ($i = 1 + $offsetVal; $i <= 99 + $offsetVal; $i++) {
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
        case "225x125":            
            PDF_begin_page_ext($p2, 162, 90, "");
            PDF_set_parameter($p2, "textformat", "utf8");

            $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

            PDF_setfont($p2, $fontHelveticaBold, 30.0);
            PDF_show_xy_center($p2, $fontHelveticaBold, str_pad($i, 3, "0", STR_PAD_LEFT), 162, 40);  

            PDF_setfont($p2, $fontHelveticaBold, 11.0);
            PDF_show_xy_center($p2, $fontHelveticaBold, strtoupper($dayName), 162, 22);  

            PDF_setfont($p2, $fontHelveticaBold, 6.0);
            PDF_show_xy($p2, $livestream, $xMargin, 6); 

            break;
        case "3x1": default:
            PDF_begin_page_ext($p2, 216, 72, "");
            PDF_set_parameter($p2, "textformat", "utf8");

            $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

            PDF_setfont($p2, $fontHelveticaBold, 30.0);
            PDF_show_xy_center($p2, $fontHelveticaBold, str_pad($i, 3, "0", STR_PAD_LEFT), 216, 30);  

            PDF_setfont($p2, $fontHelveticaBold, 11.0);
            PDF_show_xy_center($p2, $fontHelveticaBold, strtoupper($dayName), 216, 12);  

            PDF_setfont($p2, $fontHelveticaBold, 6.0);
            PDF_show_xy($p2, $livestream, $xMargin, 6); 

            break;
    }
        
    PDF_end_page_ext($p2, "");   
    PDF_end_document($p2, "");

    exec("./print-label.sh $instance");

    sleep(1);
}

echo("Printing Complete");
