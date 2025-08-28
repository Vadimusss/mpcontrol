<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class NmReportCsvParser
{
    /**
     * Распаковывает ZIP архив и извлекает CSV файл
     */
    public function extractCsvFromZip(string $zipContent): ?string
    {
        $tempPath = $tempPath ?? sys_get_temp_dir();
        
        // Сохраняем ZIP во временный файл
        $zipFilePath = tempnam($tempPath, 'wb_report_') . '.zip';
        file_put_contents($zipFilePath, $zipContent);
        
        $zip = new ZipArchive();
        
        if ($zip->open($zipFilePath) === true) {
            // Ищем CSV файл в архиве
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                    $csvContent = $zip->getFromIndex($i);
                    $zip->close();
                    unlink($zipFilePath);
                    return $csvContent;
                }
            }
            $zip->close();
        }
        
        unlink($zipFilePath);
        return null;
    }

    /**
     * Парсит CSV контент и преобразует в коллекцию данных
     */
    public function parseCsvContent(string $csvContent): Collection
    {
        $lines = explode("\n", trim($csvContent));
        $headers = str_getcsv(array_shift($lines));
        
        $data = collect($lines)->map(function ($line) use ($headers) {
            $values = str_getcsv($line);
            
            if (count($values) !== count($headers)) {
                return null;
            }
            
            return array_combine($headers, $values);
        })->filter()->values();
        
        return $data;
    }

    /**
     * Преобразует CSV данные в структуру для модели WbNmReportDetailHistory
     */
    public function transformToModelData(Collection $csvData): Collection
    {
        return $csvData->map(function ($row) {
            return [
                'nm_id' => $row['nmID'] ?? null,
                'dt' => $row['dt'] ?? null,
                'open_card_count' => $row['openCardCount'] ?? 0,
                'add_to_cart_count' => $row['addToCartCount'] ?? 0,
                'orders_count' => $row['ordersCount'] ?? 0,
                'orders_sum_rub' => $row['ordersSumRub'] ?? 0,
                'buyouts_count' => $row['buyoutsCount'] ?? 0,
                'buyouts_sum_rub' => $row['buyoutsSumRub'] ?? 0,
                'cancel_count' => $row['cancelCount'] ?? 0,
                'cancel_sum_rub' => $row['cancelSumRub'] ?? 0,
                'add_to_cart_conversion' => $row['addToCartConversion'] ?? 0,
                'cart_to_order_conversion' => $row['cartToOrderConversion'] ?? 0,
                'buyout_percent' => $row['buyoutPercent'] ?? 0,
            ];
        })->filter(function ($item) {
            // Фильтруем некорректные данные
            return !empty($item['nm_id']) && !empty($item['dt']);
        });
    }

    /**
     * Полностью обрабатывает ZIP архив: распаковывает, парсит и преобразует данные
     */
    public function processZipReport(string $zipContent): Collection
    {
        $csvContent = $this->extractCsvFromZip($zipContent);
        
        if (!$csvContent) {
            Log::error('Failed to extract CSV from ZIP archive');
            return collect();
        }
        
        $csvData = $this->parseCsvContent($csvContent);
        
        if ($csvData->isEmpty()) {
            Log::warning('Empty CSV data after parsing');
            return collect();
        }
        
        return $this->transformToModelData($csvData);
    }
}
