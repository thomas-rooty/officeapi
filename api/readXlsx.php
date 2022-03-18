<?php

require_once __DIR__ . '/../vendor/autoload.php';
require 'functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['file']['name'])) {
    // file name
    $filename = $_FILES['file']['name'];
    $file = $_FILES['file']['tmp_name'] . '.xlsx';

    // Check if file is a xlsx file
    $fileType = pathinfo($filename, PATHINFO_EXTENSION);
    $response = 0;

    if ($fileType === 'xlsx') {
        // Get this temp file
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheet(2);

        // Create contractPrices array and get cells from cellsToRead array on sheet 2
        $contractPrices = array(
            "contract_Ivoire" => number_format($sheet->getCell('D238')->getCalculatedValue(), 1, '.', ''),  //[0]
            "contract_Silver" => number_format($sheet->getCell('D239')->getCalculatedValue(), 1, '.', ''),  //[1]
            "contract_Gold" => number_format($sheet->getCell('D241')->getCalculatedValue(), 1, '.', ''),    //[2]
            "contract_GoldPlus" => number_format($sheet->getCell('D243')->getCalculatedValue(), 1, '.', ''),    //[2]
            "contract_Platinium" => number_format($sheet->getCell('D245')->getCalculatedValue(), 1, '.', '') //[4]
        );
        // customerAddress_ID
        $siteID = $_POST['customerAddress_ID'];

        // Return response with all the prix in json format
        $response = json_encode($contractPrices, JSON_THROW_ON_ERROR);

        // Store the contractPrices into the db
        storeContractPrices($siteID, $contractPrices);
    } else {
        // Not a xlsx file
        $response = 'Please upload a xlsx file';
    }

    echo $response;

    unlink($_FILES['file']);
    exit;
}
