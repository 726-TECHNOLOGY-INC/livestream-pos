<?php
/*******************************************************************************
* COPYRIGHT (C) 726 TECHNOLOGY INC, 2017 - 2026            ALL RIGHTS RESERVED *
*******************************************************************************/
//
// Print Receipt Paper - Condensed / No Barcode - v8.10.15
// Deprecated
//
const ESC = "\x1b";
const GS = "\x1d";
const NUL = "\x00";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    if (array_key_exists("Access-Control-Request-Private-Network", $_SERVER)) {
        header("Access-Control-Allow-Private-Network: true");
    }

    http_response_code(200);

    exit;
}

date_default_timezone_set("America/Phoenix");

$name = filter_input(INPUT_GET, "name", FILTER_SANITIZE_STRING);
if ($name == "") {
    $name = "Client Name";
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
    $id = rand(1000000001, 9999999999);
} else {
    $id = str_pad($id, 10, "0", STR_PAD_LEFT); 
}

$livestream = filter_input(INPUT_GET, "livestream", FILTER_SANITIZE_STRING);
if ((!isset($livestream)) || ($livestream == "")) {
    $livestream = date("l") . " the " . date("jS");    
}

$fp = fopen("/dev/usb/lp0", "wb+");

fwrite($fp, ESC."@");         // Reset to defaults
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."a".chr(1));  // Centered printing
fwrite($fp, ESC."E".chr(1));  // Bold
fwrite($fp, $livestream . "\n");   //                  LiveStream
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."!".chr(32)); // Double Width
fwrite($fp, $name . "\n");    //                       Client Name
fwrite($fp, ESC."!".chr(0));  // Not Double Width
fwrite($fp, ESC."E".chr(0));  // Not Bold
fwrite($fp, ESC."d".chr(2));  // 2 Blank lines
fwrite($fp, $itemName . "\n");    //                   Item Name
fwrite($fp, ESC."d".chr(2));  // 2 Blank lines
fwrite($fp, ESC."E".chr(1));  // Bold
fwrite($fp, ESC."!".chr(32)); // Double Width
fwrite($fp, "$ " . $price . "\n"); //                  Item Price
fwrite($fp, ESC."!".chr(0));  // Not Double Width
fwrite($fp, ESC."E".chr(0));  // Not Bold
// fwrite($fp, ESC."d".chr(2));  // 2 Blank lines
// fwrite($fp, GS."k".chr(4) . $id . NUL); //          Barcode
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, "ID " . $id . "\n"); //                    Item Id
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, date("F jS Y h:i:s A") . "\n"); //         Date / Time
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line
fwrite($fp, ESC."d".chr(1));  // Blank line

fwrite($fp, ESC."@".GS."V".chr(1));  // Cut

fclose($fp);

echo("Printing Complete");

exit;
