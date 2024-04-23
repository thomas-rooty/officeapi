<?php

require_once __DIR__ . '/../vendor/autoload.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Just send OK status and exit
    http_response_code(200);
    exit;
}

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;

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

            // Checking the 'equipments' placeholder
            $equipments = $placeholders['equipments'] ?? null;
            if (isset($equipments)) {
                $this->populateEquipmentsTable($templateProcessor, $placeholders['equipments']);
            }

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

            // Process other placeholders, excluding 'distance' and handled 'forfaitDeplacement' variants, and 'equipments'
            foreach ($placeholders as $placeholder => $value) {
                // Special treatment for
                if (!str_starts_with($placeholder, 'forfaitDeplacement') && $placeholder !== 'distance' && $placeholder !== 'equipments') {
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

    private function populateEquipmentsTable(TemplateProcessor $templateProcessor, array $equipments): void
    {
        // Group equipments by equipmentkind_ID
        $groupedEquipments = [];
        foreach ($equipments as $equipment) {
            $groupedEquipments[$equipment['equipmentkind_ID']][] = $equipment['name'];
        }

        // Prepare values for cloning
        $values = [];
        foreach ($groupedEquipments as $kindID => $equipmentNames) {
            // Add the kindID as a header row
            $values[] = ['equipmentInfo' => $kindID];
            foreach ($equipmentNames as $name) {
                // Add each equipment name under the corresponding kindID
                $values[] = ['equipmentInfo' => $name];
            }
        }

        // Clone and set values in the template
        $templateProcessor->cloneRowAndSetValues('equipmentInfo', $values);
    }
}

// API endpoint
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Read the JSON payload from the request body
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    // Ensure the necessary data is present
    if (empty($data['contract'])) {
        echo "Error: You must specify the type of contract.";
        exit;
    }

    $contract = $data['contract'];
    $templates = [
        'contract_Ivoire' => 'ivoire.docx',
        'contract_Silver' => 'silver.docx',
        'contract_Gold' => 'gold.docx',
        'contract_GoldPlus' => 'goldp.docx',
        'contract_Platinium' => 'platinium.docx',
    ];

    if (!array_key_exists($contract, $templates)) {
        echo "Error: Invalid contract type.";
        exit;
    }

    $templatePath = "../docxFiles/" . $templates[$contract];
    $wordProcessor = new WordTemplateProcessor($templatePath);

    // Use all provided data as placeholders, excluding 'contract'
    unset($data['contract']);
    $tempFileName = $wordProcessor->processTemplate($data);

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
