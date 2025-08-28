<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Models\TempWbNmReportDetailHistory;
use App\Services\WbApiService;
use App\Services\NmReportCsvParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;
use Illuminate\Support\Sleep;

class ProcessNmReportDownload implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public array $period
    ) {
        $this->shop = $shop;
        $this->period = $period;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $api = new WbApiService($this->shop->apiKey->key);
        $csvParser = new NmReportCsvParser();

        $reportParams = [
            'startDate' => $this->period['begin'],
            'endDate' => $this->period['end'],
        ];

        $createResult = $api->createNmReportDownload($reportParams);
        $downloadId = $createResult['id'];

        if (!$downloadId) {
            throw new \Exception('Failed to get download ID from report creation response');
        }

        $maxAttempts = 60;
        $attempt = 0;
        $reportReady = false;

        while ($attempt < $maxAttempts && !$reportReady) {
            Sleep::for(10)->seconds();
            $attempt++;

            $reports = $api->getNmReportDownloads();
            $currentReport = $reports->firstWhere('id', $downloadId);

            if (!$currentReport) {
                Log::warning("Report {$downloadId} not found in downloads list, attempt {$attempt}");
                continue;
            }

            $status = $currentReport['status'] ?? null;

            if ($status === 'SUCCESS') {
                $reportReady = true;
                Log::info("Report {$downloadId} generated successfully");
                break;
            } elseif ($status === 'ERROR') {
                throw new \Exception("Report generation failed for download ID: {$downloadId}");
            } elseif (in_array($status, ['PROCESSING', 'NEW'])) {
                Log::info("Report {$downloadId} status: {$status}, attempt {$attempt}/{$maxAttempts}");
            } else {
                Log::warning("Unknown report status: {$status} for download ID: {$downloadId}");
            }
        }

        if (!$reportReady) {
            throw new \Exception("Report generation timeout for download ID: {$downloadId} after {$maxAttempts} attempts");
        }

        $zipContent = $api->getNmReportFile($downloadId);

        if (empty($zipContent)) {
            throw new \Exception("Empty report file content for download ID: {$downloadId}");
        }

        $processedData = $csvParser->processZipReport($zipContent);

        if ($processedData->isEmpty()) {
            Log::warning("No valid data found in report for shop {$this->shop->id}");
            return;
        }

        $this->updateDatabase($processedData);

        $duration = microtime(true) - $startTime;

        $message = "История Воронки продаж {$this->shop->name} за {$this->period['begin']} - {$this->period['end']} обновлена! Добавлено {$processedData->count()} записей.";
        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('ProcessNmReportDownload', $duration, $message);
    }

    protected function updateDatabase($processedData): void
    {
        $this->shop->TempWbNmReportDetailHistory()->delete();

        $dataWithShopId = $processedData->map(function ($item) {
            return array_merge($item, ['shop_id' => $this->shop->id]);
        });

        $dataWithShopId->chunk(1000)->each(function ($chunk) {
            DB::table('temp_wb_nm_report_detail_histories')->insert($chunk->toArray());
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('ProcessNmReportDownload', $exception);

        // self::dispatch($this->shop, $this->period)->delay(now()->addMinutes(30));
    }
}
