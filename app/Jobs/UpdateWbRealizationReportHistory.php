<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\JobSucceeded;
use App\Events\JobFailed;
use Throwable;
use Carbon\Carbon;

class UpdateWbRealizationReportHistory implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $date, // Теперь принимаем конкретную дату, а не daysBack
        public int $chunkSize = 500,
    ) {
        $this->shop = $shop;
        $this->date = $date;
    }

    public $timeout = 3600;
    public $tries = 1;

    public function handle(): void
    {
        $startTime = microtime(true);
        Log::info("UpdateWbRealizationReportHistory: Начало обработки для магазина {$this->shop->id} ({$this->shop->name}) за дату {$this->date}");

        try {
            // Подключаемся к внешней БД
            $externalConnection = DB::connection('ozon_api');
            
            // Получаем общее количество записей за указанную дату
            Log::info("UpdateWbRealizationReportHistory: Получение количества записей за date_from={$this->date}, cabinet={$this->shop->id}");
            $totalRecords = $externalConnection->table('wb_realization_report')
                ->where('cabinet', $this->shop->id)
                ->where('date_from', $this->date)
                ->count();
            
            Log::info("UpdateWbRealizationReportHistory: Всего записей за {$this->date}: {$totalRecords}");
            
            if ($totalRecords > 0) {
                // ОЧИЩАЕМ ВСЕ ДАННЫЕ ЗА ЭТОТ ДЕНЬ ПЕРЕД ЗАПУСКОМ ЧАНКОВ
                Log::info("UpdateWbRealizationReportHistory: Очистка старых данных за {$this->date}");
                $deleted = DB::table('wb_realization_reports')
                    ->where('cabinet', $this->shop->id)
                    ->where('date_from', $this->date)
                    ->delete();
                Log::info("UpdateWbRealizationReportHistory: Удалено записей: {$deleted}");
                
                // Создаем цепочку заданий с ключевой пагинацией
                // Первое задание начинается с lastRrdId = 0
                $firstJob = new UpdateWbRealizationReportChunk(
                    $this->shop,
                    $this->date,
                    0, // lastRrdId: Начинаем с начала
                    $this->chunkSize,
                    1 // chunkNumber
                );
                
                // Запускаем цепочку (первое задание)
                Bus::dispatch($firstJob);
                
                $message = "Запущено обновление данных WbRealizationReport для магазина {$this->shop->name} за {$this->date}. Записей: {$totalRecords}, размер чанка: {$this->chunkSize}";
                Log::info($message);
            } else {
                $message = "Нет данных WbRealizationReport для магазина {$this->shop->name} за {$this->date}";
                Log::info($message);
            }
            
            $duration = microtime(true) - $startTime;
            Log::info("UpdateWbRealizationReportHistory: Задание выполнено за " . round($duration, 2) . " секунд");
            JobSucceeded::dispatch('UpdateWbRealizationReportHistory', $duration, $message);
            
        } catch (\Exception $e) {
            Log::error("UpdateWbRealizationReportHistory: Ошибка выполнения", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->failed($e);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error("UpdateWbRealizationReportHistory: Задание завершилось с ошибкой", ['error' => $exception->getMessage()]);
        JobFailed::dispatch('UpdateWbRealizationReportHistory', $exception);
    }
}
