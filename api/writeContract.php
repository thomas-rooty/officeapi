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

            // Process other placeholders, excluding 'distance' and handled 'forfaitDeplacement' variants, and 'equipments'
            foreach ($placeholders as $placeholder => $value) {
                // Special treatment for
                if ($placeholder !== 'distance' && $placeholder !== 'equipments') {
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
        $groupedEquipments = [];

        foreach ($equipments as $equipment) {
            $location = $equipment['location'] ?? 'Non renseigné';
            $type = $equipment['equipmenttype'] ?? '';

            $key = $location . '|' . $type;  // Create a unique key for each combination of location and type

            if (isset($groupedEquipments[$key])) {
                $groupedEquipments[$key]['equipQty'] += 1;
            } else {
                $groupedEquipments[$key] = [
                    'equipLocation' => $location,
                    'equipType' => $type,
                    'equipQty' => 1,
                ];
            }
        }

        // Prepare the values for the template processor
        $values = array_values($groupedEquipments);

        // Clone and set values in the template
        $templateProcessor->cloneRowAndSetValues('equipLocation', $values);
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
        'contract_Silver' => 'silver.docx',
        'contract_Gold' => 'gold.docx',
        'contract_GoldPlus' => 'goldp.docx',
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
