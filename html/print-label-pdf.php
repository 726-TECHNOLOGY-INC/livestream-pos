<?php 
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2026            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Label - PDF - Multi Size Support - v8.10.15
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

function PDF_show_xy_right(&$xypdf, &$xyfont, $xytext, $xright, $ybottom) {
    $xyfontsize = pdf_get_value($xypdf, "fontsize", 0);
    $width = pdf_stringwidth($xypdf, $xytext, $xyfont, $xyfontsize);

    PDF_show_xy($xypdf, $xytext, $xright - $width, $ybottom);
}

$labelSize = isset($_GET["size"]) ? htmlspecialchars($_GET["size"], ENT_QUOTES, 'UTF-8') : "3x1";
if (empty($labelSize)) {
    $labelSize = "3x1";
}

$fname = isset($_GET["fname"]) ? trim($_GET["fname"]) : "";
$clientFNameTemp = ($fname === "") ? "Client" : htmlspecialchars($fname, ENT_QUOTES, 'UTF-8');
$clientFName = str_replace("&#39;", "'", str_replace("&#34;", "\"", $clientFNameTemp));

$lname = isset($_GET["lname"]) ? trim($_GET["lname"]) : "";
$clientLNameTemp = ($lname === "") ? "Name" : htmlspecialchars($lname, ENT_QUOTES, 'UTF-8');
$clientLName = str_replace("&#39;", "'", str_replace("&#34;", "\"", $clientLNameTemp));

$instance = "LABEL-" . date("Y-m-d-H-i-s") . ".pdf";
$p2 = PDF_new();

if (PDF_begin_document($p2, "./tags/" . $instance, "") == 0) {
    die("Error: " . PDF_get_errmsg($p2));
}

PDF_set_info($p2, "Creator", "print-label-pdf.php");
PDF_set_info($p2, "Author", "726 TECHNOLOGY INC");
PDF_set_info($p2, "Title", "CLIENT NAME LABEL");

$fontHelveticaBold = PDF_load_font($p2, "Helvetica-Bold", "winansi", "");

switch ($labelSize) {
    case "3x2":
        PDF_begin_page_ext($p2, 216, 144, "");
        PDF_set_parameter($p2, "textformat", "utf8");


        PDF_setfont($p2, $fontHelveticaBold, 20.0);
        PDF_show_xy($p2, $clientFName, 18, 85);
        PDF_show_xy($p2, $clientLName, 18, 45);  
                
        break;
    case "3x1": default:         
        PDF_begin_page_ext($p2, 216, 72, "");
        PDF_set_parameter($p2, "textformat", "utf8");

        PDF_setfont($p2, $fontHelveticaBold, 17.0);
        PDF_show_xy($p2, $clientFName, 18, 42);
        PDF_show_xy($p2, $clientLName, 18, 17);  

        PDF_setfont($p2, $fontHelveticaBold, 10.0);
        PDF_show_xy_right($p2, $fontHelveticaBold, date('l'), 207, 55);

        break;
}
    
PDF_end_page_ext($p2, "");   
PDF_end_document($p2, "");

exec("./print-label.sh " . escapeshellarg($instance));
