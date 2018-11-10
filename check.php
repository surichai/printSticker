<?php 
require __DIR__ . '/vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
$connector = new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector("POS58");
$printer = new Printer($connector);
$id ='123456789012345';
$type = Printer::BARCODE_CODE39;
$position = Printer::BARCODE_TEXT_NONE;

$printer->setJustification(Printer::JUSTIFY_CENTER);

//$printer->setBarcodeHeight(60);
//$printer->setBarcodeWidth(4);
// $printer->setPrintWidth(20);
$printer->setBarcodeTextPosition($position);
$printer->barcode($id, Printer::BARCODE_CODE93);
$printer->text("imei: ".$id);
$printer->feed();
$printer->feed(1);
$printer->feed();

$printer->close();