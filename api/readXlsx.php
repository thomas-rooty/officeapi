<?php

require 'vendor/autoload.php';
require 'functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['file']['name'])) {
    // file name
    $filename = $_FILES['file']['name'];


    // Location
    $location = '../uploads/' . $filename;

    // Check if file is a xlsx file
    $fileType = pathinfo($location, PATHINFO_EXTENSION);
    $response = 0;

    if ($fileType === 'xlsx') {
        // Upload file
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
            // Get this file
            $spreadsheet = IOFactory::load($location);
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
            $response = 'File upload failed, please try again.';
        }
    } else {
        // Not a xlsx file
        $response = 'Please upload a xlsx file';
    }

    echo $response;

    // Delete the file and exit the script
    unlink($location);
    exit;
}
