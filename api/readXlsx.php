<?php

require_once __DIR__ . '/../vendor/autoload.php';
header('Access-Control-Allow-Origin: *');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetReaderException;
use MongoDB\Client;
use MongoDB\Exception\Exception as MongoDBException;

class SpreadsheetProcessor
{
    private string $allowedFileType = 'xlsx';
    private string $uploadPath = '/app/uploads/';
    private Client $mongoClient;
    private string $mongoCollection = 'chiffrages';
    private ?string $contractId = null;

    public function __construct()
    {
        $mongoUri = $_ENV['IS_DEVELOPING'] === 'true' ? $_ENV['MONGODB_URI'] : $_ENV['MONGODB_URI'];
        $this->mongoClient = new Client($mongoUri);
        $this->contractId = $_GET['contract_ID'] ?? null;
    }

    public function processUploadedFile($file): bool|string
    {
        if (!$this->isFileUploaded($file)) {
            return "File hasn't been uploaded";
        }

        if (!$this->isValidFileType($file['name'])) {
            return "Please upload a xlsx file";
        }

        $filePath = $this->saveFile($file);
        if (!$filePath) {
            return "Error saving file";
        }

        try {
            $contractPrices = $this->readSpreadsheet($filePath);
            if ($this->contractId) {
                $this->updateDatabase($contractPrices);
            } else {
                throw new \Exception("Contract ID is missing.");
            }
        } catch (SpreadsheetReaderException|MongoDBException|\Exception $e) {
            $this->cleanUpFile($filePath);
            return "Error processing file: " . $e->getMessage();
        }

        $this->cleanUpFile($filePath);

        return json_encode($contractPrices);
    }

    private function isFileUploaded($file): bool
    {
        return isset($file['name']);
    }

    private function isValidFileType($filename): bool
    {
        return pathinfo($filename, PATHINFO_EXTENSION) === $this->allowedFileType;
    }

    private function saveFile($file): bool|string
    {
        $filename = $file['name'];
        $tmpPath = $file['tmp_name'];
        $newPath = $this->uploadPath . basename($filename);

        if (!move_uploaded_file($tmpPath, $newPath)) {
            error_log("Failed to move uploaded file to $newPath");
            return false;
        }
        return $newPath;
    }

    private function readSpreadsheet($filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(2);

        return [
            "contract_Ivoire" => $this->formatCell($sheet->getCell('D238')),
            "contract_Silver" => $this->formatCell($sheet->getCell('D239')),
            "contract_Gold" => $this->formatCell($sheet->getCell('D241')),
            "contract_GoldPlus" => $this->formatCell($sheet->getCell('D243')),
            "contract_Platinium" => $this->formatCell($sheet->getCell('D245'))
        ];
    }

    private function formatCell($cell): string
    {
        return number_format($cell->getCalculatedValue(), 2, '.', '');
    }

    private function updateDatabase(array $contractPrices): void
    {
        $collection = $this->mongoClient->selectCollection('sites', $this->mongoCollection);
        $updateResult = $collection->updateOne(
            ['contract_ID' => $this->contractId],
            ['$set' => ['contractPricesAtTime' => $contractPrices]]
        );

        if ($updateResult->getModifiedCount() == 0) {
            error_log("No document was updated for contract ID: {$this->contractId}");
        }
    }

    private function cleanUpFile($filePath): void
    {
        unlink($filePath);
    }
}

// Main script logic
if (isset($_FILES['file'])) {
    $processor = new SpreadsheetProcessor();
    $response = $processor->processUploadedFile($_FILES['file']);
    echo $response;
} else {
    echo "No file uploaded";
}
