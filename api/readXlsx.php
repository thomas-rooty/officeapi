<?php

require_once __DIR__ . '/../vendor/autoload.php';

header('Access-Control-Allow-Origin: *');

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check if a file has been uploaded
if (!isset($_FILES['file']['name'])) {
  // File not uploaded
  $response = "File hasn't been uploaded";
  echo $response;
  exit;
}

// Retrieve the file
$tmp = $_FILES['file']['tmp_name'];
$filename = $_FILES['file']['name'];
$location = 'uploads/' . $filename;

// Check if file is a xlsx file
$fileType = pathinfo($location, PATHINFO_EXTENSION);
$response = 0;

if ($fileType !== 'xlsx') {
  // File type is not valid
  $response = "Please upload a xlsx file";
  echo $response;
  exit;
}

// Load the spreadsheet and get the second sheet
$spreadsheet = IOFactory::load($tmp);
$sheet = $spreadsheet->getSheet(2);

// Create the `contractPrices` array and get cells from `cellsToRead` array on sheet 2
$contractPrices = [
  "contract_Ivoire" => number_format($sheet->getCell('D238')->getCalculatedValue(), 1, '.', ''),
  "contract_Silver" => number_format($sheet->getCell('D239')->getCalculatedValue(), 1, '.', ''),
  "contract_Gold" => number_format($sheet->getCell('D241')->getCalculatedValue(), 1, '.', ''),
  "contract_GoldPlus" => number_format($sheet->getCell('D243')->getCalculatedValue(), 1, '.', ''),
  "contract_Platinium" => number_format($sheet->getCell('D245')->getCalculatedValue(), 1, '.', '')
];

// Get the `customerAddress_ID`
$siteID = $_POST['customerAddress_ID'];

// Return response with all the prices in JSON format
try {
  $response = json_encode($contractPrices, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
  $response = "Error: " . $e->getMessage();
}

// Remove the uploaded file
unlink($tmp);

echo $response;
exit;
