<?php

require 'functions.php';

// Check if $_POST('contrat_Type') is set
if (isset($_POST['customerAddress_ID'], $_POST['contrat_Type'])) {
    // Set the variables
    $customerAddress_ID = $_POST['customerAddress_ID'];
    $contrat_Type = $_POST['contrat_Type'];

    // Call getContractPrices() function to get the contract prices
    $contractPrice = getContractPrice($customerAddress_ID);

    // Affect to response as json
    $response = $contractPrice[$contrat_Type];
} else {
    // Set the response
    $response = 'Error, set the POST variables';
}

echo $response;
exit;
