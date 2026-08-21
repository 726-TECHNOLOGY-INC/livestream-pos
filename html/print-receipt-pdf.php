<?php 
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2024            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Receipt - PDF - Multi Size Support - v6.2.29
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

$labelSize = filter_input(INPUT_GET, "size", FILTER_SANITIZE_STRING);
if ($labelSize == "") {
    $labelSize = "3x1";
}

$name = filter_input(INPUT_GET, "name", FILTER_SANITIZE_STRING);
if ((!isset($name)) || ($name == "")) {
    $clientName = "Client Name";
} else {
    $clientName = str_replace("&#39;", "'", str_replace("&#34;", "\"", $name));
}

$itemName = filter_input(INPUT_GET, "itemName", FILTER_SANITIZE_STRING);
if ($itemName == "") {
    $itemName = "Item Name";
}

$price = filter_input(INPUT_GET, "price", FILTER_SANITIZE_STRING);
if ($price == "") {
    $price = "00.00";
}

$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING);
if ($id == "") { 
    $id = rand(1000000001,9999999999);
} else {
    $id = str_pad($id, 10, "0", STR_PAD_LEFT); 
}

$livestream = filter_input(INPUT_GET, "livestream", FILTER_SANITIZE_STRING);
if ($livestream == "") {
    $livestream = date("l") . " the " . date("jS");    
}
$livestreamId = "$livestream\n";

$rando = rand(pow(10, 2), pow(10, 3) - 1);
$instance = "LABEL-" . date("Y-m-d-H-m-s") . "-$rando.pdf";
$p2 = PDF_new();

if (PDF_begin_document($p2, "./tags/$instance", "") == 0) {
    die("Error: " . PDF_get_errmsg($p2));
}

PDF_set_info($p2, "Creator", "print-receipt-pdf.php");
PDF_set_info($p2, "Author", "726 TECHNOLOGY INC");
PDF_set_info($p2, "Title", "CLIENT RECEIPT PDF");

switch ($labelSize) {
    case "3x2":
        PDF_begin_page_ext($p2, 216, 144, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

        PDF_setfont($p2, $fontHelveticaBold, 12.0);
        PDF_show_xy($p2, $clientName, 18, 112);

        PDF_setfont($p2, $fontHelveticaBold, 10.0);
        PDF_show_xy($p2, $itemName, 18, 88);  

        PDF_setfont($p2, $fontHelveticaBold, 18.0);
        PDF_show_xy($p2, "$" . number_format($price, 2, ".", ","), 18, 50);  

        PDF_setfont($p2, $fontHelveticaBold, 9.0);
        PDF_show_xy($p2, date("F jS Y h:i:s A"), 18, 12); 
        
        break;
    case "3x1": default:
        PDF_begin_page_ext($p2, 216, 72, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

        PDF_setfont($p2, $fontHelveticaBold, 12.0);
        PDF_show_xy($p2, $clientName, 18, 53);

        PDF_setfont($p2, $fontHelveticaBold, 10.0);
        PDF_show_xy($p2, $itemName, 18, 38);  

        PDF_setfont($p2, $fontHelveticaBold, 14.0);
        PDF_show_xy($p2, "$" . number_format($price, 2, ".", ","), 18, 20);  

        PDF_setfont($p2, $fontHelveticaBold, 9.0);
        PDF_show_xy($p2, date("F jS Y h:i:s A"), 18, 6); 
        
        break;
}
    
PDF_end_page_ext($p2, "");   
PDF_end_document($p2, "");

exec("./print-label.sh $instance");

echo("Printing Complete");