<?php

function pdoConnect(): PDO
{
    // Init the PDO variables
    $host = 'localhost';
    $db = 'cuisineo-react';
    $user = 'root';
    $pass = 'Obumeicpendearinuck8!';

    // Connect to the database
    return new PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $pass);
}

function storeContractPrices(string $customerAddress_ID, array $contractPrices)
{
    // Connect to the database
    $pdo = pdoConnect();
    $null = null;

    // Delete the row with the customerAddress_ID if it exists
    $sql = 'DELETE FROM contractPrices WHERE customerAddress_ID = :customerAddress_ID';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':customerAddress_ID', $customerAddress_ID);
    $stmt->execute();

    // Insert the new contractPrices
    $sql = 'INSERT INTO contractPrices (contract_ID, customerAddress_ID, contract_Ivoire, contract_Silver, contract_Gold, contract_GoldPlus, contract_Platinium) VALUES (:contract_ID, :customerAddress_ID, :contract_Ivoire, :contract_Silver, :contract_Gold, :contract_GoldPlus, :contract_Platinium)';
    $stmt = $pdo->prepare($sql);

    // Bind the contract prices to the sql row
    $stmt->bindParam(':contract_ID', $null, PDO::PARAM_NULL);
    $stmt->bindParam(':customerAddress_ID', $customerAddress_ID);
    $stmt->bindParam(':contract_Ivoire', $contractPrices['contract_Ivoire']);
    $stmt->bindParam(':contract_Silver', $contractPrices['contract_Silver']);
    $stmt->bindParam(':contract_Gold', $contractPrices['contract_Gold']);
    $stmt->bindParam(':contract_GoldPlus', $contractPrices['contract_GoldPlus']);
    $stmt->bindParam(':contract_Platinium', $contractPrices['contract_Platinium']);

    // Execute the sql row
    $stmt->execute();

    // Close the connection
    $pdo = null;
}

function getContractPrice(string $customerAddress_ID): array
{
    // Connect to the database
    $pdo = pdoConnect();

    // Select the contrat_Type where the customerAddress_ID is equal to the customerAddress_ID
    $sql = 'SELECT * FROM contractPrices WHERE customerAddress_ID = :customerAddress_ID';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':customerAddress_ID', $customerAddress_ID);
    $stmt->execute();

    // Get the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Close the connection
    $pdo = null;

    // Return the result
    return $result;
}