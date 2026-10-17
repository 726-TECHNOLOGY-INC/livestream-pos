<?php 
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2026            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Item Tag - PDF - Multi Size Support - v8.10.15
//
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    if (array_key_exists("Access-Control-Request-Private-Network", $_SERVER)) {
        header("Access-Control-Allow-Private-Network: true");
    }

    http_response_code(200);

    exit;
}

$labelSize = isset($_GET["size"]) ? htmlspecialchars($_GET["size"], ENT_QUOTES, 'UTF-8') : "3x1";
if (empty($labelSize)) {
    $labelSize = "3x1";
}

$name = isset($_GET["name"]) ? trim($_GET["name"]) : "";
$clientNameTemp = ($name === "") ? "Client Name" : htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$clientName = htmlspecialchars_decode(str_replace("&#34;", "\"", $clientNameTemp));

$itemName = isset($_GET["itemName"]) ? trim($_GET["itemName"]) : "";
$itemNameTemp = ($itemName === "") ? "Item Name" : htmlspecialchars($itemName, ENT_QUOTES, 'UTF-8');
$itemName = htmlspecialchars_decode(str_replace("&#34;", "\"", $itemNameTemp));

$price = isset($_GET["price"]) ? trim($_GET["price"]) : "";
$priceValueTemp = ($price === "") ? "00.00" : htmlspecialchars($price, ENT_QUOTES, 'UTF-8');
$priceValue = is_numeric($priceValueTemp) ? (float)$priceValueTemp : 0.00;

$id = isset($_GET["id"]) ? trim($_GET["id"]) : "";
$idValue = ($id === "") ? rand(1000000001, 9999999999) : str_pad(htmlspecialchars($id, ENT_QUOTES, 'UTF-8'), 10, "0", STR_PAD_LEFT);

$livestream = isset($_GET["livestream"]) ? trim($_GET["livestream"]) : "";
$livestreamTemp = ($livestream === "") ? date("F jS Y h:i:s A") : htmlspecialchars($livestream, ENT_QUOTES, 'UTF-8');
$livestreamValue = htmlspecialchars_decode(str_replace("&#34;", "\"", $livestreamTemp));

$rando = rand(pow(10, 2), pow(10, 3) - 1);
$instance = "LABEL-" . date("Y-m-d-H-i-s") . "-$rando.pdf";
$p2 = PDF_new();

if (PDF_begin_document($p2, "./tags/$instance", "") == 0) {
    die("Error: " . PDF_get_errmsg($p2));
}

PDF_set_info($p2, "Creator", "print-receipt-pdf.php");
PDF_set_info($p2, "Author", "726 TECHNOLOGY INC");
PDF_set_info($p2, "Title", "CLIENT ITEM TAG PDF");

$xMargin = 8;
$fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");
$formattedPrice = "$" . number_format($priceValue, 2, ".", ",");

switch ($labelSize) {
    case "3x2":
        PDF_begin_page_ext($p2, 216, 144, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        PDF_setfont($p2, $fontHelveticaBold, 12.0);
        PDF_show_xy($p2, $clientName, $xMargin, 112);

        PDF_setfont($p2, $fontHelveticaBold, 10.0);
        PDF_show_xy($p2, $itemName, $xMargin, 88);  

        PDF_setfont($p2, $fontHelveticaBold, 18.0);
        PDF_show_xy($p2, $formattedPrice, $xMargin, 50);  

        PDF_setfont($p2, $fontHelveticaBold, 9.0);
        PDF_show_xy($p2, $livestreamValue, $xMargin, 12); 
        
        break;
    case "3x1": default:
        PDF_begin_page_ext($p2, 216, 72, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        PDF_setfont($p2, $fontHelveticaBold, 11.0);
        PDF_show_xy($p2, $clientName, $xMargin, 55);

        PDF_setfont($p2, $fontHelveticaBold, 9.0);
        PDF_show_xy($p2, $itemName, $xMargin, 42);  

        PDF_setfont($p2, $fontHelveticaBold, 20.0);
        PDF_show_xy($p2, $formattedPrice, $xMargin, 17);  

        PDF_setfont($p2, $fontHelveticaBold, 6.0);
        PDF_show_xy($p2, $livestreamValue, $xMargin, 6); 

        PDF_setfont($p2, $fontHelveticaBold, 9.0);
        PDF_show_xy($p2, implode(" ", str_split($idValue)), $xMargin + 87, 17);  

        // Generate Barcode Dynamically
        $barcodeImgPath = "tags/$idValue.png";
        exec("./render-barcode.sh " . escapeshellarg($idValue));
        usleep(100000);

        if (file_exists($barcodeImgPath)) {
            $qrImage = PDF_load_image($p2, "png", $barcodeImgPath, "");
            PDF_fit_image($p2, $qrImage, 170, -2, "scale 0.49"); 
        }
        
        break;
}
    
PDF_end_page_ext($p2, "");   
PDF_end_document($p2, "");

exec("./print-label.sh " . escapeshellarg($instance));

if (isset($barcodeImgPath) && file_exists($barcodeImgPath)) {
    usleep(100000);
    unlink($barcodeImgPath);
}

echo("Printing Complete");
