<?php

require_once __DIR__ . '/../vendor/autoload.php';
header('Access-Control-Allow-Origin: *');

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;

class WordTemplateProcessor
{
    private string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function processTemplate(array $placeholders): ?string
    {
        try {
            $templateProcessor = new TemplateProcessor($this->templatePath);

            $distanceId = isset($placeholders['distance']) ? (int)$placeholders['distance'] : null;
            $agency_name = $placeholders['agency_name'] ?? '';

            if ($agency_name === 'QUIETALIS PARIS NORD' || $agency_name === 'QUIETALIS PARIS EST' || $agency_name === 'QUIETALIS PARIS OUEST') {
                // If agency is IDF, set only the generic 'forfaitDeplacement'
                $templateProcessor->setValue('forfaitDeplacement', $placeholders['forfaitDeplacement'] ?? '');

                // Ensure specific 'forfaitDeplacementX' placeholders are set to blank
                for ($i = 0; $i <= 5; $i++) {
                    $templateProcessor->setValue("forfaitDeplacement$i", '');
                }
            } else {
                // Set the generic 'forfaitDeplacement' to blank
                $templateProcessor->setValue('forfaitDeplacement', '');

                // Set value or blank for specific 'forfaitDeplacementX'
                for ($i = 0; $i <= 5; $i++) {
                    if ($i === $distanceId) {
                        $templateProcessor->setValue("forfaitDeplacement$i", $placeholders['forfaitDeplacement'] ?? '');
                    } else {
                        $templateProcessor->setValue("forfaitDeplacement$i", '');
                    }
                }
            }

            // Process other placeholders, excluding 'distance' and handled 'forfaitDeplacement' variants
            foreach ($placeholders as $placeholder => $value) {
                if (!str_starts_with($placeholder, 'forfaitDeplacement') && $placeholder !== 'distance') {
                    $templateProcessor->setValue($placeholder, $value);
                }
            }

            $tempFileName = tempnam(sys_get_temp_dir(), 'PHPWord');
            $templateProcessor->saveAs($tempFileName);
            return $tempFileName;
        } catch (Exception $e) {
            error_log("Error processing Word template: " . $e->getMessage());
            return null;
        }
    }
}

// API endpoint
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Check if the contract parameter is provided
    if (empty($_GET['contract'])) {
        echo "Erreur: Vous devez specifier le type du contrat.";
        exit;
    }

    $contract = $_GET['contract'];

    // Define the mapping of contract types to template files
    $templates = [
        'contract_Ivoire' => 'ivoire.docx',
        'contract_Silver' => 'silver.docx',
        'contract_Gold' => 'gold.docx',
        'contract_GoldPlus' => 'goldp.docx',
        'contract_Platinium' => 'platinium.docx',
    ];

    // Check if the provided contract type is valid
    if (!array_key_exists($contract, $templates)) {
        echo "Error: Invalid contract type.";
        exit;
    }

    $templatePath = "../docxFiles/" . $templates[$contract];

    // Fetch all query parameters and use them as placeholders
    $placeholders = $_GET;
    unset($placeholders['contract']); // Exclude 'contract' from placeholders

    $wordProcessor = new WordTemplateProcessor($templatePath);
    $tempFileName = $wordProcessor->processTemplate($placeholders);

    if ($tempFileName) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $contract . '_document.docx"');
        readfile($tempFileName);
        unlink($tempFileName);
    } else {
        echo "Failed to process template.";
    }
} else {
    echo "Invalid request method.";
}

