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