<?php 
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2026            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Label - PDF - Silver Chain Labels - v8.02.04
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

$labelSize = filter_input(INPUT_GET, "size", FILTER_SANITIZE_STRING);
if ($labelSize == "") {
    $labelSize = "3x1";
} else {
    $labelSize = "225x125";
}

$itemNameLine1 = filter_input(INPUT_GET, "itemName", FILTER_SANITIZE_STRING);
if ($itemNameLine1 == "") {
    $itemName = "The Item";
} else {
    $itemName = str_replace("&#34;", "\"", $itemNameLine1);
}

$itemNameLine2 = filter_input(INPUT_GET, "itemName2", FILTER_SANITIZE_STRING);
$itemName2 = str_replace("&#34;", "\"", $itemNameLine2);

$qty = filter_input(INPUT_GET, "qty", FILTER_SANITIZE_STRING);
if ($qty == "") {
    $qty = "1";
}

$price = filter_input(INPUT_GET, "price", FILTER_SANITIZE_STRING);
if ($price == "") {
    $price = "10";
}

// $qrText = $itemName . ((strlen($itemName2) > 0) ? " $itemName2" : "");
$qrText = $itemName . " x" . $qty;
$qrTextEncoded = urlencode($qrText);

$count = intval(filter_input(INPUT_GET, "count", FILTER_SANITIZE_STRING));
if ($count == 0) { $count++; }

// $printedDate = date("F jS Y h:i:s A");
$printedDate = date("d M Y");

$instance = "LABEL-" . date("Y-m-d-H-m-s") . strval(rand(100000, 999999));
$p2 = PDF_new();

if (PDF_begin_document($p2, "./tags/$instance.pdf", "") == 0) {
    die("Error: " . PDF_get_errmsg($p2));
}

PDF_set_info($p2, "Creator", "print-sticker.php");
PDF_set_info($p2, "Author", "726 TECHNOLOGY INC");
PDF_set_info($p2, "Title", "GENERIC RETAIL LABEL");

switch ($labelSize) {
    case "3x1": default:         
        PDF_begin_page_ext($p2, 216, 72, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

        // headline - item name
        PDF_setfont($p2, $fontHelveticaBold, 16.0);
        PDF_show_xy($p2, $itemName, 12, 48);
        
        // lower left side - price
        PDF_setfont($p2, $fontHelveticaBold, 20.0);
        PDF_show_xy($p2, "$" . $price, 14, 8);  
                        
        // qr image
        $qrInstance = "QR-$instance.png";
        
        error_log($qrInstance);

        $ch = curl_init("https://726.technology/print/qr/qrcode.php?s=qr&sf=9&d=" . $qrTextEncoded);
        $fp = fopen("tags/" . $qrInstance, "wb");

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);

        fclose($fp);

        $qrImage = PDF_load_image($p2, "png", "tags/" . $qrInstance, "");

        PDF_fit_image($p2, $qrImage, 167, 22, "boxsize {46 46} fitmethod entire"); 
        
        break;
    case "225x125":    
        PDF_begin_page_ext($p2, 162, 90, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

        // headline - item name
        PDF_setfont($p2, $fontHelveticaBold, 12.0);
        PDF_show_xy($p2, $itemName, 10, 68);

        // sub heading - item name 2
        PDF_setfont($p2, $fontHelveticaBold, 12.0);
        // PDF_show_xy($p2, $qty . "x " . $itemName2, 10, 54);
        PDF_show_xy($p2, $qty . " " . $itemName2, 10, 54);
        
        // qty / price or just price
        if ($qty != "x") {
            PDF_setfont($p2, $fontHelveticaBold, 28.0);
            // PDF_show_xy_right($p2, $fontHelveticaBold, $qty . "  /  $" . $price, 153, 12);  

            PDF_show_xy_right($p2, $fontHelveticaBold, "$" . $price, 153, 14);  

            $priceWidth = pdf_stringwidth($p2, "$" . $price, $fontHelveticaBold, 24);

            PDF_setfont($p2, $fontHelveticaBold, 40.0);
            PDF_show_xy_right($p2, $fontHelveticaBold, "/ ", 153 - $priceWidth, 9);  

            PDF_setfont($p2, $fontHelveticaBold, 28.0);
            PDF_show_xy_right($p2, $fontHelveticaBold, $qty, 153 - $priceWidth - 26, 14);  
        } else {
            PDF_setfont($p2, $fontHelveticaBold, 28.0);
            PDF_show_xy_right($p2, $fontHelveticaBold, "$" . $price, 153, 14);  
        }
                                
        // lower left side - qr image
        $qrInstance = "QR-$instance.png";
        
        // error_log($qrInstance);

        $ch = curl_init("https://726.technology/print/qr/qrcode.php?s=qr&sf=9&d=" . $qrTextEncoded);
        $fp = fopen("tags/" . $qrInstance, "wb");

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);

        fclose($fp);

        $qrImage = PDF_load_image($p2, "png", "tags/" . $qrInstance, "");

        PDF_fit_image($p2, $qrImage, 7, 6, "boxsize {36 36} fitmethod entire"); 
}
    
PDF_end_page_ext($p2, "");   
PDF_end_document($p2, "");

for ($ct = 1; $ct <= $count; $ct++) {
    exec("./print-label.sh $instance.pdf");  
}
