<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;
use App\Events\JobSucceeded;
use App\Events\JobFailed;
use Throwable;
use Carbon\Carbon;

class UpdateWbRealizationReportChunk implements ShouldQueue
{
    use Batchable, Queueable;

    public Shop $shop;
    public string $date;
    public int $lastRrdId;
    public int $limit;
    public int $chunkNumber;

    public function __construct(
        Shop $shop,
        string $date,
        int $lastRrdId, // Последний обработанный rrd_id (0 для первого чанка)
        int $limit,
        int $chunkNumber,
    ) {
        $this->shop = $shop;
        $this->date = $date;
        $this->lastRrdId = $lastRrdId;
        $this->limit = $limit;
        $this->chunkNumber = $chunkNumber;
    }

    public $timeout = 600;
    public $tries = 3;

    public function handle(): void
    {
        $startTime = microtime(true);
        Log::info("UpdateWbRealizationReportChunk: Начало обработки чанка {$this->chunkNumber} для магазина {$this->shop->id} за {$this->date} (lastRrdId: {$this->lastRrdId}, limit: {$this->limit})");

        try {
            // Подключаемся к внешней БД
            $externalConnection = DB::connection('ozon_api');
            
            // Получаем данные для текущего чанка (ключевая пагинация)
            Log::info("UpdateWbRealizationReportChunk: Запрос данных с lastRrdId={$this->lastRrdId}, limit={$this->limit}");
            $data = $externalConnection->table('wb_realization_report')
                ->where('cabinet', $this->shop->id)
                ->where('date_from', $this->date)
                ->where('rrd_id', '>', $this->lastRrdId) // Берем записи после lastRrdId
                ->orderBy('rrd_id') // Сортировка по rrd_id для последовательной пагинации
                ->limit($this->limit)
                ->get();
            
            $recordCount = $data->count();
            Log::info("UpdateWbRealizationReportChunk: Получено записей: {$recordCount}");
            
            if ($recordCount > 0) {
                $insertData = [];
                $maxRrdId = 0;
                
                foreach ($data as $row) {
                    // Преобразуем объект в массив
                    $rowArray = get_object_vars($row);
                    
                    // Запоминаем максимальный rrd_id в этом чанке
                    if (isset($rowArray['rrd_id']) && $rowArray['rrd_id'] > $maxRrdId) {
                        $maxRrdId = $rowArray['rrd_id'];
                    }
                    
                    // Обрабатываем даты из PostgreSQL
                    $this->processPostgresDates($rowArray);
                    
                    // Добавляем timestamps
                    $rowArray['created_at'] = now()->format('Y-m-d H:i:s');
                    $rowArray['updated_at'] = now()->format('Y-m-d H:i:s');
                    
                    $insertData[] = $rowArray;
                }
                
                // Вставляем чанк (НЕ очищаем данные - это делает UpdateWbRealizationReportHistory)
                Log::info("UpdateWbRealizationReportChunk: Вставка {$recordCount} записей");
                DB::table('wb_realization_reports')->insert($insertData);
                
                $message = "Чанк {$this->chunkNumber} для магазина {$this->shop->name} за {$this->date} обработан. Записей: {$recordCount}, последний rrd_id: {$maxRrdId}";
                Log::info($message);
                
                // Если получили полный чанк (limit записей), значит есть еще данные
                // Создаем следующее задание в цепочке
                if ($recordCount == $this->limit) {
                    $nextChunkNumber = $this->chunkNumber + 1;
                    Log::info("UpdateWbRealizationReportChunk: Создание следующего чанка {$nextChunkNumber} с lastRrdId={$maxRrdId}");
                    
                    $nextJob = new UpdateWbRealizationReportChunk(
                        $this->shop,
                        $this->date,
                        $maxRrdId, // lastRrdId: Передаем максимальный rrd_id этого чанка
                        $this->limit,
                        $nextChunkNumber // chunkNumber
                    );
                    
                    // Запускаем следующее задание (цепочка продолжается)
                    Bus::dispatch($nextJob);
                } else {
                    Log::info("UpdateWbRealizationReportChunk: Это последний чанк (получено {$recordCount} из {$this->limit} записей)");
                }
            } else {
                $message = "Чанк {$this->chunkNumber} для магазина {$this->shop->name} за {$this->date} не содержит данных (после rrd_id={$this->lastRrdId})";
                Log::info($message);
            }
            
            $duration = microtime(true) - $startTime;
            $recordsPerSecond = $recordCount > 0 ? round($recordCount / $duration, 2) : 0;
            Log::info("UpdateWbRealizationReportChunk: Чанк {$this->chunkNumber} выполнен за " . round($duration, 2) . " секунд, скорость: " . $recordsPerSecond . " записей/сек");
            
            // Отправляем событие об успешном выполнении чанка
            JobSucceeded::dispatch('UpdateWbRealizationReportChunk', $duration, $message);
            
        } catch (\Exception $e) {
            Log::error("UpdateWbRealizationReportChunk: Ошибка выполнения чанка {$this->chunkNumber}", [
                'error' => $e->getMessage(),
                'lastRrdId' => $this->lastRrdId,
                'limit' => $this->limit,
                'trace' => $e->getTraceAsString()
            ]);
            $this->failed($e);
        }
    }
    
    /**
     * Быстрая обработка дат из PostgreSQL формата в MySQL формат
     * (та же логика, что и в UpdateWbRealizationReport)
     */
    private function processPostgresDates(array &$rowArray): void
    {
        // Поля с датами, которые нужно обработать
        $dateFields = ['inserted_at', 'create_dt', 'date_from', 'date_to', 'fix_tariff_date_from', 
                      'fix_tariff_date_to', 'order_dt', 'sale_dt', 'rr_dt'];
        
        foreach ($dateFields as $field) {
            if (isset($rowArray[$field]) && !empty($rowArray[$field]) && is_string($rowArray[$field])) {
                $value = $rowArray[$field];
                
                // Проверяем, есть ли в строке признаки PostgreSQL timestamp
                // Может быть с микросекундами: '2026-02-03 08:01:55.830729+00'
                // Или без микросекунд: '2026-01-15 00:00:00+00'
                // Или с часовым поясом в начале: '2026-01-15 00:00:00+00'
                
                // Ищем знак часового пояса (+ или -)
                $tzPos = strpos($value, '+');
                if ($tzPos === false && strlen($value) > 11) {
                    $tzPos = strpos($value, '-', 11); // Ищем минус после даты (позиция 11: "2026-01-15 ")
                }
                
                if ($tzPos !== false) {
                    // Обрезаем до знака часового пояса
                    $rowArray[$field] = substr($value, 0, $tzPos);
                } else {
                    // Если нет часового пояса, проверяем есть ли микросекунды
                    $dotPos = strpos($value, '.');
                    if ($dotPos !== false) {
                        // Обрезаем после точки (микросекунды)
                        $rowArray[$field] = substr($value, 0, $dotPos);
                    }
                }
                
                // Для полей date_from, date_to и других date полей - проверяем формат даты
                if (in_array($field, ['date_from', 'date_to', 'create_dt', 'rr_dt', 'fix_tariff_date_from', 'fix_tariff_date_to'])) {
                    // Если значение выглядит как дата с временем, оставляем только дату
                    if (strlen($rowArray[$field]) > 10 && strpos($rowArray[$field], ' ') !== false) {
                        $rowArray[$field] = substr($rowArray[$field], 0, 10);
                    }
                }
                
                // Дополнительная проверка: если после обработки остался пустая строка или null, устанавливаем null
                if (empty($rowArray[$field])) {
                    $rowArray[$field] = null;
                }
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error("UpdateWbRealizationReportChunk: Чанк {$this->chunkNumber} завершился с ошибкой", ['error' => $exception->getMessage()]);
        JobFailed::dispatch('UpdateWbRealizationReportChunk', $exception);
    }
}