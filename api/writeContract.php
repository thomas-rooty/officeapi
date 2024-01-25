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

            // Save the processed template to a temporary file
            $tempFileName = tempnam(sys_get_temp_dir(), 'PHPWord');
            $templateProcessor->saveAs($tempFileName);

            // Read the file content
            $content = file_get_contents($tempFileName);

            // Clean up the temporary file
            unlink($tempFileName);

            return $content;
        } catch (Exception $e) {
            error_log("Error processing Word template: " . $e->getMessage());
            return null;
        }
    }
}

// Example usage of the class
$templatePath = '../docxFiles/gold.docx';
$placeholders = [
    'nomclient' => 'John Doe',
    // Add more placeholders and their values as needed
];

$wordProcessor = new WordTemplateProcessor($templatePath);
$documentContent = $wordProcessor->processTemplate($placeholders);

if ($documentContent) {
    // Send headers to force download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="downloaded_document.docx"');
    header('Content-Length: ' . strlen($documentContent));
    echo $documentContent;
} else {
    echo "Failed to process template.";
}
