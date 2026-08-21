<?php 
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2023            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Label - PDF - Multi Size Support - v5.6.30
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

$fname = filter_input(INPUT_GET, "fname", FILTER_SANITIZE_STRING);
if ($fname == "") {
    $fname = "Client";
}

$lname = filter_input(INPUT_GET, "lname", FILTER_SANITIZE_STRING);
if ($lname == "") {
    $lname = "Name";
}

$instance = "LABEL-" . date("Y-m-d-H-m-s") . ".pdf";
$p2 = PDF_new();

if (PDF_begin_document($p2, "./tags/" . $instance, "") == 0) {
    die("Error: " . PDF_get_errmsg($p2));
}

PDF_set_info($p2, "Creator", "print-label-pdf.php");
PDF_set_info($p2, "Author", "726 TECHNOLOGY INC");
PDF_set_info($p2, "Title", "CLIENT LABEL");

switch ($labelSize) {
    case "3x2":
        PDF_begin_page_ext($p2, 216, 144, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

        PDF_setfont($p2, $fontHelveticaBold, 20.0);
        PDF_show_xy($p2, $fname, 18, 85);
        PDF_show_xy($p2, $lname, 18, 45);  
                
        break;
    case "3x1": default:         
        PDF_begin_page_ext($p2, 216, 72, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        $fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

        PDF_setfont($p2, $fontHelveticaBold, 18.0);
        PDF_show_xy($p2, $fname, 18, 40);
        PDF_show_xy($p2, $lname, 18, 15);  

        break;
}
    
PDF_end_page_ext($p2, "");   
PDF_end_document($p2, "");

exec("./print-label.sh " . $instance);