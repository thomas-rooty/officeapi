<?php

require_once __DIR__ . '/../vendor/autoload.php';

header('Access-Control-Allow-Origin: *');

use PhpOffice\PhpSpreadsheet\IOFactory;

echo json_encode($_FILES['file']['name']);

if (isset($_FILES['file']['name'])) {
    // Check if file has been uploaded
    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
        // file name
        $filename = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];

        // Location
        $location = 'uploads/' . $filename;

        // Check if file is a xlsx file
        //$fileType = pathinfo($location, PATHINFO_EXTENSION);
        $fileType = 'xlsx';
        $response = 0;

        if ($fileType === 'xlsx') {
            // Get this file
            $spreadsheet = IOFactory::load($tmp);
            $sheet = $spreadsheet->getSheet(2);

            // Create contractPrices array and get cells from cellsToRead array on sheet 2
            $contractPrices = array(
                "contract_Ivoire" => number_format($sheet->getCell('D238')->getCalculatedValue(), 1, '.', ''),  //[0]
                "contract_Silver" => number_format($sheet->getCell('D239')->getCalculatedValue(), 1, '.', ''),  //[1]
                "contract_Gold" => number_format($sheet->getCell('D241')->getCalculatedValue(), 1, '.', ''),    //[2]
                "contract_GoldPlus" => number_format($sheet->getCell('D243')->getCalculatedValue(), 1, '.', ''),//[3]
                "contract_Platinium" => number_format($sheet->getCell('D245')->getCalculatedValue(), 1, '.', '')//[4]
            );
            // customerAddress_ID
            $siteID = $_POST['customerAddress_ID'];

            // Return response with all the prix in json format
            try {
                $response = json_encode($contractPrices, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $response = "Error: " . $e->getMessage();
            }
            unlink($tmp);
        } else {
            $response = "File hasn't been uploaded";
        }
    } else {
        // Not a xlsx file
        $response = 'Please upload a xlsx file';
    }

    echo $response;
    exit;
}
