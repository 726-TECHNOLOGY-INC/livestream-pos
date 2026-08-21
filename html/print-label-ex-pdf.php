<?php 
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2025            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Label - PDF - Extended Attributes - v7.05.31
//                     New Tub Stickers Used on Desktop Interface
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

function PDF_show_xy_right(&$xypdf, &$xyfont, $xytext, $xright, $ybottom) {
    $xyfontsize = pdf_get_value($xypdf, "fontsize", 0);
    $width = pdf_stringwidth($xypdf, $xytext, $xyfont, $xyfontsize);

    PDF_show_xy($xypdf, $xytext, $xright - $width, $ybottom);
}

$fname = filter_input(INPUT_GET, "fname", FILTER_SANITIZE_STRING);
if ((!isset($fname)) || ($fname == "")) {
    $clientFName = "Client";
} else {
    $clientFName = str_replace("&#39;", "'", str_replace("&#34;", "\"", $fname));
}

$lname = filter_input(INPUT_GET, "lname", FILTER_SANITIZE_STRING);
if ((!isset($lname)) || ($lname == "")) {
    $clientLName = "Name";
} else {
    $clientLName = str_replace("&#39;", "'", str_replace("&#34;", "\"", $lname));
}

$totalValue = filter_input(INPUT_GET, "totalValue", FILTER_SANITIZE_STRING);
if ($totalValue == "") {
    $totalValue = "00.00";
}

$fullyPaid = filter_input(INPUT_GET, "fullyPaid", FILTER_SANITIZE_STRING);
if ($fullyPaid == "") {
    $fullyPaid = "0";
}

$itemCount = filter_input(INPUT_GET, "itemCount", FILTER_SANITIZE_STRING);
if ($itemCount == "") {
    $itemCount = "1";
}

$shippingRound = filter_input(INPUT_GET, "shippingRound", FILTER_SANITIZE_STRING);
if ($shippingRound == "") {
    $shippingRound = strtoupper(date("M d"));
}

$qrId = filter_input(INPUT_GET, "qrId", FILTER_SANITIZE_STRING);
if ($qrId == "") {
    die("Error: no QR Data for sticker");
}

$labelSize = filter_input(INPUT_GET, "labelSize", FILTER_SANITIZE_STRING);
if ($labelSize == "") {
    $labelSize = "3x1";
}

// $instance = "LABEL-" . $qrId . "-" . strval(rand(100000, 999999)) . ".pdf";
$instance = "LABEL-" . str_replace(" ", "", $clientFName) . "-" . str_replace(" ", "", $clientLName) . "-" . strval(rand(100000, 999999)) . ".pdf";
$p2 = PDF_new();

if (PDF_begin_document($p2, "./tags/" . $instance, "") == 0) {
    die("Error: " . PDF_get_errmsg($p2));
}

PDF_set_info($p2, "Creator", "print-label-ex-pdf.php");
PDF_set_info($p2, "Author", "726 TECHNOLOGY INC");
PDF_set_info($p2, "Title", "SHIPPING ROUND CLIENT LABEL");

$xMargin = 8;

if ($labelSize === "3x1") {
    PDF_begin_page_ext($p2, 216, 72, "");      
    
    $fontSize = 16.0; // 13.0;
    $topLineY = 42; // 52;
    $bottomLineY = 17; // 34;
    $statY1 = 200; // 32;
    $statY2 = 200; // 20;
    $statY3 = 200; // 6;
} else {
    PDF_begin_page_ext($p2, 216, 144, "");  
    
    $fontSize = 18.0;
    $topLineY = 116;
    $bottomLineY = 92;
    $statY1 = 55;
    $statY2 = 35;
    $statY3 = 15;
}

PDF_set_parameter($p2, "textformat", "utf8");

$fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "unicode", "");

PDF_setfont($p2, $fontHelveticaBold, $fontSize);
PDF_show_xy($p2, $clientFName, $xMargin, $topLineY);
PDF_show_xy($p2, $clientLName, $xMargin, $bottomLineY);  
    
PDF_setfont($p2, $fontHelveticaBold, ($fontSize - 2));

if (($fullyPaid) && ($labelSize !== "3x1")) {
    PDF_show_xy($p2, "PAID", 80, 55);
}

PDF_show_xy_right($p2, $fontHelveticaBold, "$" . $totalValue, 198, $statY1);  
PDF_show_xy_right($p2, $fontHelveticaBold, "** $itemCount **", 198, $statY2);  
PDF_show_xy_right($p2, $fontHelveticaBold, $shippingRound, 198, $statY3);  

$qrInstance = $qrId . "-" . strval(rand(100000, 999999)) . ".png";

$ch = curl_init("https://726.technology/print/qr/qrcode.php?s=qr&sf=9&d=" . $qrId);
$fp = fopen("tags/$qrInstance", "wb");

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
curl_close($ch);

fclose($fp);

$qrImage = PDF_load_image($p2, "png", "tags/$qrInstance", "");

if ($labelSize !== "3x1") {
    PDF_fit_image($p2, $qrImage, 11, 7, "scale 0.24"); 
} else {
    PDF_fit_image($p2, $qrImage, 148, 5, "scale 0.31");     
}

PDF_end_page_ext($p2, "");   
PDF_end_document($p2, "");

exec("./print-label.sh $instance");
