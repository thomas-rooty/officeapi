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

            foreach ($placeholders as $placeholder => $value) {
                $templateProcessor->setValue($placeholder, $value);
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
        'gold' => 'gold.docx',
        'ivoire' => 'ivoire.docx',
        'platinium' => 'platinium.docx',
        'silver' => 'silver.docx',
        'goldplus' => 'goldp.docx'
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

