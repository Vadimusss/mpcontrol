<?php

namespace App\Services\Integrations;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;

class GoogleSheetsService
{
    private $client;
    private $service;

    public function __construct(array $credentials)
    {
        $this->client = new Client();
        $this->client->setAuthConfig($credentials);
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->service = new Sheets($this->client);
    }

    public function getSheetIdFromUrl(string $url): string
    {
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? '';
    }

    public function getData(string $sheetUrl, string $range): array
    {
        $sheetId = $this->getSheetIdFromUrl($sheetUrl);
        return $this->service->spreadsheets_values
            ->get($sheetId, $range)
            ->getValues();
    }

    public function updateData(string $sheetUrl, string $range, array $data): void
    {
        $sheetId = $this->getSheetIdFromUrl($sheetUrl);
        $body = new ValueRange(['values' => $data]);
        $this->service->spreadsheets_values
            ->update($sheetId, $range, $body, ['valueInputOption' => 'RAW']);
    }
}
