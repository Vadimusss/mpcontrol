<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\JobSucceeded;
use App\Events\JobFailed;
use Throwable;
use Carbon\Carbon;

class UpdateWbRealizationReportViaCsv implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $date,
    ) {
        $this->shop = $shop;
        $this->date = $date;
    }

    public $timeout = 3600;
    public $tries = 1;

    public function handle(): void
    {
        $startTime = microtime(true);
        Log::info("UpdateWbRealizationReportViaCsv: Начало обработки для магазина {$this->shop->id} ({$this->shop->name}) за дату {$this->date}");

        try {
            // Подключаемся к внешней БД PostgreSQL
            $externalConnection = DB::connection('ozon_api');
            
            // Получаем общее количество записей
            Log::info("UpdateWbRealizationReportViaCsv: Получение количества записей за date_from={$this->date}, cabinet={$this->shop->id}");
            $totalRecords = $externalConnection->table('wb_realization_report')
                ->where('cabinet', $this->shop->id)
                ->where('date_from', $this->date)
                ->count();
            
            Log::info("UpdateWbRealizationReportViaCsv: Всего записей за {$this->date}: {$totalRecords}");
            
            if ($totalRecords === 0) {
                $message = "Нет данных WbRealizationReport для магазина {$this->shop->name} за {$this->date}";
                Log::info($message);
                
                $duration = microtime(true) - $startTime;
                Log::info("UpdateWbRealizationReportViaCsv: Задание выполнено за " . round($duration, 2) . " секунд");
                JobSucceeded::dispatch('UpdateWbRealizationReportViaCsv', $duration, $message);
                return;
            }
            
            // Очищаем старые данные
            Log::info("UpdateWbRealizationReportViaCsv: Очистка старых данных за {$this->date}");
            $deleted = DB::table('wb_realization_reports')
                ->where('cabinet', $this->shop->id)
                ->where('date_from', $this->date)
                ->delete();
            Log::info("UpdateWbRealizationReportViaCsv: Удалено записей: {$deleted}");
            
            // Экспортируем данные в CSV через PostgreSQL COPY
            Log::info("UpdateWbRealizationReportViaCsv: Экспорт данных в CSV");
            $csvData = $this->exportDataToCsv($externalConnection);
            
            if (empty($csvData)) {
                throw new \Exception("Не удалось экспортировать данные в CSV");
            }
            
            // Сохраняем CSV во временный файл
            $tempFile = tempnam(sys_get_temp_dir(), 'wb_report_' . $this->shop->id . '_' . $this->date . '_');
            Log::info("UpdateWbRealizationReportViaCsv: Сохранение CSV во временный файл: {$tempFile}");
            
            file_put_contents($tempFile, $csvData);
            $fileSize = filesize($tempFile);
            Log::info("UpdateWbRealizationReportViaCsv: Размер CSV файла: " . round($fileSize / 1024 / 1024, 2) . " MB");
            
            // Загружаем данные в MySQL через LOAD DATA INFILE
            Log::info("UpdateWbRealizationReportViaCsv: Загрузка данных в MySQL через LOAD DATA INFILE");
            $loadedCount = $this->loadDataFromCsv($tempFile);
            
            // Удаляем временный файл
            unlink($tempFile);
            Log::info("UpdateWbRealizationReportViaCsv: Временный файл удален");
            
            $message = "Данные WbRealizationReport для магазина {$this->shop->name} за {$this->date} успешно загружены. Записей: {$loadedCount}";
            Log::info($message);
            
            $duration = microtime(true) - $startTime;
            $recordsPerSecond = $loadedCount > 0 ? round($loadedCount / $duration, 2) : 0;
            Log::info("UpdateWbRealizationReportViaCsv: Задание выполнено за " . round($duration, 2) . " секунд, скорость: " . $recordsPerSecond . " записей/сек");
            
            JobSucceeded::dispatch('UpdateWbRealizationReportViaCsv', $duration, $message);
            
        } catch (\Exception $e) {
            Log::error("UpdateWbRealizationReportViaCsv: Ошибка выполнения", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->failed($e);
        }
    }
    
    /**
     * Экспорт данных из PostgreSQL в CSV формат
     */
    private function exportDataToCsv($externalConnection): string
    {
        // Используем COPY TO STDOUT для получения CSV данных
        // Важно: порядок колонок должен соответствовать порядку в таблице wb_realization_report
        $copyQuery = "
            COPY (
                SELECT 
                    cabinet::text,
                    inserted_at::text,
                    realizationreport_id::text,
                    date_from::text,
                    date_to::text,
                    create_dt::text,
                    currency_name,
                    suppliercontract_code::text,
                    rrd_id::text,
                    gi_id::text,
                    dlv_prc::text,
                    fix_tariff_date_from::text,
                    fix_tariff_date_to::text,
                    subject_name,
                    nm_id::text,
                    brand_name,
                    sa_name,
                    ts_name,
                    barcode,
                    doc_type_name,
                    quantity::text,
                    retail_price::text,
                    retail_amount::text,
                    sale_percent::text,
                    commission_percent::text,
                    office_name,
                    supplier_oper_name,
                    order_dt::text,
                    sale_dt::text,
                    rr_dt::text,
                    shk_id::text,
                    retail_price_withdisc_rub::text,
                    delivery_amount::text,
                    return_amount::text,
                    delivery_rub::text,
                    gi_box_type_name,
                    product_discount_for_report::text,
                    supplier_promo::text,
                    ppvz_spp_prc::text,
                    ppvz_kvw_prc_base::text,
                    ppvz_kvw_prc::text,
                    sup_rating_prc_up::text,
                    is_kgvp_v2::text,
                    ppvz_sales_commission::text,
                    ppvz_for_pay::text,
                    ppvz_reward::text,
                    acquiring_fee::text,
                    acquiring_percent::text,
                    payment_processing,
                    acquiring_bank,
                    ppvz_vw::text,
                    ppvz_vw_nds::text,
                    ppvz_office_name,
                    ppvz_office_id::text,
                    ppvz_supplier_id::text,
                    ppvz_supplier_name,
                    ppvz_inn,
                    declaration_number,
                    bonus_type_name,
                    sticker_id::text,
                    site_country,
                    srv_dbs::text,
                    penalty::text,
                    additional_payment::text,
                    rebill_logistic_cost::text,
                    rebill_logistic_org,
                    storage_fee::text,
                    deduction::text,
                    acceptance::text,
                    assembly_id::text,
                    kiz,
                    srid,
                    report_type::text,
                    is_legal_entity::text,
                    trbx_id,
                    installment_cofinancing_amount::text,
                    wibes_wb_discount_percent::text,
                    cashback_amount::text,
                    cashback_discount::text,
                    cashback_commission_change::text,
                    order_uid,
                    payment_schedule
                FROM wb.wb_realization_report 
                WHERE cabinet = ? AND date_from = ?
                ORDER BY rrd_id
            ) TO STDOUT WITH CSV HEADER
        ";
        
        try {
            // Выполняем COPY запрос через PDO
            $pdo = $externalConnection->getPdo();
            $stmt = $pdo->prepare($copyQuery);
            $stmt->execute([$this->shop->id, $this->date]);
            
            $csvData = '';
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                // Для COPY TO STDOUT данные возвращаются в одном столбце
                $csvData .= $row['?column?'] ?? '';
            }
            
            return $csvData;
            
        } catch (\Exception $e) {
            Log::error("UpdateWbRealizationReportViaCsv: Ошибка экспорта в CSV", [
                'error' => $e->getMessage(),
                'query' => $copyQuery
            ]);
            
            // Fallback: используем обычный SELECT если COPY не работает
            return $this->exportDataViaSelect($externalConnection);
        }
    }
    
    /**
     * Альтернативный метод экспорта через SELECT (если COPY не доступен)
     */
    private function exportDataViaSelect($externalConnection): string
    {
        Log::info("UpdateWbRealizationReportViaCsv: Используем SELECT fallback для экспорта данных");
        
        $data = $externalConnection->table('wb_realization_report')
            ->where('cabinet', $this->shop->id)
            ->where('date_from', $this->date)
            ->orderBy('rrd_id')
            ->get();
        
        if ($data->isEmpty()) {
            return '';
        }
        
        // Создаем CSV вручную
        $csv = fopen('php://temp', 'r+');
        
        // Заголовки CSV (все поля кроме id, created_at, updated_at)
        $headers = [
            'cabinet', 'inserted_at', 'realizationreport_id', 'date_from', 'date_to', 'create_dt',
            'currency_name', 'suppliercontract_code', 'rrd_id', 'gi_id', 'dlv_prc', 'fix_tariff_date_from',
            'fix_tariff_date_to', 'subject_name', 'nm_id', 'brand_name', 'sa_name', 'ts_name', 'barcode',
            'doc_type_name', 'quantity', 'retail_price', 'retail_amount', 'sale_percent', 'commission_percent',
            'office_name', 'supplier_oper_name', 'order_dt', 'sale_dt', 'rr_dt', 'shk_id', 'retail_price_withdisc_rub',
            'delivery_amount', 'return_amount', 'delivery_rub', 'gi_box_type_name', 'product_discount_for_report',
            'supplier_promo', 'ppvz_spp_prc', 'ppvz_kvw_prc_base', 'ppvz_kvw_prc', 'sup_rating_prc_up', 'is_kgvp_v2',
            'ppvz_sales_commission', 'ppvz_for_pay', 'ppvz_reward', 'acquiring_fee', 'acquiring_percent',
            'payment_processing', 'acquiring_bank', 'ppvz_vw', 'ppvz_vw_nds', 'ppvz_office_name', 'ppvz_office_id',
            'ppvz_supplier_id', 'ppvz_supplier_name', 'ppvz_inn', 'declaration_number', 'bonus_type_name',
            'sticker_id', 'site_country', 'srv_dbs', 'penalty', 'additional_payment', 'rebill_logistic_cost',
            'rebill_logistic_org', 'storage_fee', 'deduction', 'acceptance', 'assembly_id', 'kiz', 'srid',
            'report_type', 'is_legal_entity', 'trbx_id', 'installment_cofinancing_amount', 'wibes_wb_discount_percent',
            'cashback_amount', 'cashback_discount', 'cashback_commission_change', 'order_uid', 'payment_schedule'
        ];
        
        fputcsv($csv, $headers);
        
        foreach ($data as $row) {
            $rowArray = (array) $row;
            
            // Обрабатываем даты для CSV
            $this->processDatesForCsv($rowArray);
            
            // Преобразуем значения в строки
            $csvRow = [];
            foreach ($headers as $header) {
                $value = $rowArray[$header] ?? '';
                
                // Для JSON полей преобразуем в строку
                if ($header === 'suppliercontract_code' && is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                
                // Экранируем специальные символы
                if (is_string($value) && (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false)) {
                    $value = '"' . str_replace('"', '""', $value) . '"';
                }
                
                $csvRow[] = $value;
            }
            
            fputcsv($csv, $csvRow);
        }
        
        rewind($csv);
        $csvData = stream_get_contents($csv);
        fclose($csv);
        
        return $csvData;
    }
    
    /**
     * Обработка дат для CSV экспорта
     */
    private function processDatesForCsv(array &$rowArray): void
    {
        $dateFields = ['inserted_at', 'create_dt', 'date_from', 'date_to', 'fix_tariff_date_from', 
                      'fix_tariff_date_to', 'order_dt', 'sale_dt', 'rr_dt'];
        
        foreach ($dateFields as $field) {
            if (isset($rowArray[$field]) && !empty($rowArray[$field])) {
                $value = $rowArray[$field];
                
                // Если это объект DateTime, преобразуем в строку
                if ($value instanceof \DateTimeInterface) {
                    $rowArray[$field] = $value->format('Y-m-d H:i:s');
                } elseif (is_string($value)) {
                    // Убираем часовой пояс и микросекунды
                    $tzPos = strpos($value, '+');
                    if ($tzPos === false) {
                        $tzPos = strpos($value, '-', 11);
                    }
                    
                    if ($tzPos !== false) {
                        $rowArray[$field] = substr($value, 0, $tzPos);
                    } else {
                        $dotPos = strpos($value, '.');
                        if ($dotPos !== false) {
                            $rowArray[$field] = substr($value, 0, $dotPos);
                        }
                    }
                    
                    // Для полей date оставляем только дату
                    if (in_array($field, ['date_from', 'date_to', 'create_dt', 'rr_dt', 'fix_tariff_date_from', 'fix_tariff_date_to'])) {
                        if (strlen($rowArray[$field]) > 10) {
                            $rowArray[$field] = substr($rowArray[$field], 0, 10);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Загрузка данных из CSV файла в MySQL через LOAD DATA INFILE
     */
    private function loadDataFromCsv(string $csvFilePath): int
    {
        try {
            // SQL для LOAD DATA INFILE с преобразованием типов
            $loadDataSql = "
                LOAD DATA LOCAL INFILE ?
                INTO TABLE wb_realization_reports 
                CHARACTER SET utf8mb4
                FIELDS TERMINATED BY ',' 
                ENCLOSED BY '\"' 
                ESCAPED BY '\\\\'
                LINES TERMINATED BY '\\n'
                IGNORE 1 ROWS
                (
                    cabinet,
                    @inserted_at,
                    realizationreport_id,
                    @date_from,
                    @date_to,
                    @create_dt,
                    currency_name,
                    @suppliercontract_code,
                    rrd_id,
                    gi_id,
                    @dlv_prc,
                    @fix_tariff_date_from,
                    @fix_tariff_date_to,
                    subject_name,
                    nm_id,
                    brand_name,
                    sa_name,
                    ts_name,
                    barcode,
                    doc_type_name,
                    quantity,
                    @retail_price,
                    @retail_amount,
                    sale_percent,
                    @commission_percent,
                    office_name,
                    supplier_oper_name,
                    @order_dt,
                    @sale_dt,
                    @rr_dt,
                    shk_id,
                    @retail_price_withdisc_rub,
                    delivery_amount,
                    return_amount,
                    @delivery_rub,
                    gi_box_type_name,
                    @product_discount_for_report,
                    @supplier_promo,
                    @ppvz_spp_prc,
                    @ppvz_kvw_prc_base,
                    @ppvz_kvw_prc,
                    @sup_rating_prc_up,
                    @is_kgvp_v2,
                    @ppvz_sales_commission,
                    @ppvz_for_pay,
                    @ppvz_reward,
                    @acquiring_fee,
                    @acquiring_percent,
                    payment_processing,
                    acquiring_bank,
                    @ppvz_vw,
                    @ppvz_vw_nds,
                    ppvz_office_name,
                    @ppvz_office_id,
                    @ppvz_supplier_id,
                    ppvz_supplier_name,
                    ppvz_inn,
                    declaration_number,
                    bonus_type_name,
                    @sticker_id,
                    site_country,
                    @srv_dbs,
                    @penalty,
                    @additional_payment,
                    @rebill_logistic_cost,
                    rebill_logistic_org,
                    @storage_fee,
                    @deduction,
                    @acceptance,
                    @assembly_id,
                    kiz,
                    srid,
                    @report_type,
                    @is_legal_entity,
                    trbx_id,
                    @installment_cofinancing_amount,
                    @wibes_wb_discount_percent,
                    @cashback_amount,
                    @cashback_discount,
                    @cashback_commission_change,
                    order_uid,
                    payment_schedule
                )
                SET
                    inserted_at = NULLIF(@inserted_at, ''),
                    date_from = NULLIF(@date_from, ''),
                    date_to = NULLIF(@date_to, ''),
                    create_dt = NULLIF(@create_dt, ''),
                    suppliercontract_code = NULLIF(@suppliercontract_code, ''),
                    dlv_prc = NULLIF(@dlv_prc, ''),
                    fix_tariff_date_from = NULLIF(@fix_tariff_date_from, ''),
                    fix_tariff_date_to = NULLIF(@fix_tariff_date_to, ''),
                    retail_price = NULLIF(@retail_price, ''),
                    retail_amount = NULLIF(@retail_amount, ''),
                    commission_percent = NULLIF(@commission_percent, ''),
                    order_dt = NULLIF(@order_dt, ''),
                    sale_dt = NULLIF(@sale_dt, ''),
                    rr_dt = NULLIF(@rr_dt, ''),
                    retail_price_withdisc_rub = NULLIF(@retail_price_withdisc_rub, ''),
                    delivery_rub = NULLIF(@delivery_rub, ''),
                    product_discount_for_report = NULLIF(@product_discount_for_report, ''),
                    supplier_promo = NULLIF(@supplier_promo, ''),
                    ppvz_spp_prc = NULLIF(@ppvz_spp_prc, ''),
                    ppvz_kvw_prc_base = NULLIF(@ppvz_kvw_prc_base, ''),
                    ppvz_kvw_prc = NULLIF(@ppvz_kvw_prc, ''),
                    sup_rating_prc_up = NULLIF(@sup_rating_prc_up, ''),
                    is_kgvp_v2 = NULLIF(@is_kgvp_v2, ''),
                    ppvz_sales_commission = NULLIF(@ppvz_sales_commission, ''),
                    ppvz_for_pay = NULLIF(@ppvz_for_pay, ''),
                    ppvz_reward = NULLIF(@ppvz_reward, ''),
                    acquiring_fee = NULLIF(@acquiring_fee, ''),
                    acquiring_percent = NULLIF(@acquiring_percent, ''),
                    ppvz_vw = NULLIF(@ppvz_vw, ''),
                    ppvz_vw_nds = NULLIF(@ppvz_vw_nds, ''),
                    ppvz_office_id = NULLIF(@ppvz_office_id, ''),
                    ppvz_supplier_id = NULLIF(@ppvz_supplier_id, ''),
                    sticker_id = NULLIF(@sticker_id, ''),
                    srv_dbs = CASE WHEN @srv_dbs = 't' THEN 1 WHEN @srv_dbs = 'f' THEN 0 ELSE NULL END,
                    penalty = NULLIF(@penalty, ''),
                    additional_payment = NULLIF(@additional_payment, ''),
                    rebill_logistic_cost = NULLIF(@rebill_logistic_cost, ''),
                    storage_fee = NULLIF(@storage_fee, ''),
                    deduction = NULLIF(@deduction, ''),
                    acceptance = NULLIF(@acceptance, ''),
                    assembly_id = NULLIF(@assembly_id, ''),
                    report_type = NULLIF(@report_type, ''),
                    is_legal_entity = CASE WHEN @is_legal_entity = 't' THEN 1 WHEN @is_legal_entity = 'f' THEN 0 ELSE NULL END,
                    installment_cofinancing_amount = NULLIF(@installment_cofinancing_amount, ''),
                    wibes_wb_discount_percent = NULLIF(@wibes_wb_discount_percent, ''),
                    cashback_amount = NULLIF(@cashback_amount, ''),
                    cashback_discount = NULLIF(@cashback_discount, ''),
                    cashback_commission_change = NULLIF(@cashback_commission_change, ''),
                    created_at = NOW(),
                    updated_at = NOW()
            ";
            
            // Выполняем LOAD DATA INFILE
            $affectedRows = DB::affectingStatement($loadDataSql, [$csvFilePath]);
            
            Log::info("UpdateWbRealizationReportViaCsv: Загружено записей через LOAD DATA INFILE: {$affectedRows}");
            
            return $affectedRows;
            
        } catch (\Exception $e) {
            Log::error("UpdateWbRealizationReportViaCsv: Ошибка загрузки данных через LOAD DATA INFILE", [
                'error' => $e->getMessage(),
                'sql' => $loadDataSql
            ]);
            
            // Fallback: используем обычную вставку если LOAD DATA не работает
            return $this->loadDataViaInsert($csvFilePath);
        }
    }
    
    /**
     * Альтернативный метод загрузки через INSERT (если LOAD DATA не доступен)
     */
    private function loadDataViaInsert(string $csvFilePath): int
    {
        Log::info("UpdateWbRealizationReportViaCsv: Используем INSERT fallback для загрузки данных");
        
        $csvData = file_get_contents($csvFilePath);
        $lines = explode("\n", trim($csvData));
        
        // Пропускаем заголовок
        array_shift($lines);
        
        $insertData = [];
        $batchSize = 600; // Максимальный размер батча
        $loadedCount = 0;
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            $row = str_getcsv($line);
            
            // Преобразуем CSV строку в массив для вставки
            $insertRow = $this->csvRowToInsertArray($row);
            
            if (!empty($insertRow)) {
                $insertData[] = $insertRow;
                
                // Вставляем батчами по 600 записей
                if (count($insertData) >= $batchSize) {
                    DB::table('wb_realization_reports')->insert($insertData);
                    $loadedCount += count($insertData);
                    $insertData = [];
                    
                    Log::info("UpdateWbRealizationReportViaCsv: Вставлено {$batchSize} записей через INSERT, всего: {$loadedCount}");
                }
            }
        }
        
        // Вставляем оставшиеся записи
        if (!empty($insertData)) {
            DB::table('wb_realization_reports')->insert($insertData);
            $loadedCount += count($insertData);
            Log::info("UpdateWbRealizationReportViaCsv: Вставлено оставшихся " . count($insertData) . " записей через INSERT, всего: {$loadedCount}");
        }
        
        return $loadedCount;
    }
    
    /**
     * Преобразование CSV строки в массив для INSERT
     */
    private function csvRowToInsertArray(array $csvRow): array
    {
        // Маппинг полей из CSV в структуру таблицы
        // Порядок должен соответствовать заголовкам CSV
        $mapping = [
            'cabinet', 'inserted_at', 'realizationreport_id', 'date_from', 'date_to', 'create_dt',
            'currency_name', 'suppliercontract_code', 'rrd_id', 'gi_id', 'dlv_prc', 'fix_tariff_date_from',
            'fix_tariff_date_to', 'subject_name', 'nm_id', 'brand_name', 'sa_name', 'ts_name', 'barcode',
            'doc_type_name', 'quantity', 'retail_price', 'retail_amount', 'sale_percent', 'commission_percent',
            'office_name', 'supplier_oper_name', 'order_dt', 'sale_dt', 'rr_dt', 'shk_id', 'retail_price_withdisc_rub',
            'delivery_amount', 'return_amount', 'delivery_rub', 'gi_box_type_name', 'product_discount_for_report',
            'supplier_promo', 'ppvz_spp_prc', 'ppvz_kvw_prc_base', 'ppvz_kvw_prc', 'sup_rating_prc_up', 'is_kgvp_v2',
            'ppvz_sales_commission', 'ppvz_for_pay', 'ppvz_reward', 'acquiring_fee', 'acquiring_percent',
            'payment_processing', 'acquiring_bank', 'ppvz_vw', 'ppvz_vw_nds', 'ppvz_office_name', 'ppvz_office_id',
            'ppvz_supplier_id', 'ppvz_supplier_name', 'ppvz_inn', 'declaration_number', 'bonus_type_name',
            'sticker_id', 'site_country', 'srv_dbs', 'penalty', 'additional_payment', 'rebill_logistic_cost',
            'rebill_logistic_org', 'storage_fee', 'deduction', 'acceptance', 'assembly_id', 'kiz', 'srid',
            'report_type', 'is_legal_entity', 'trbx_id', 'installment_cofinancing_amount', 'wibes_wb_discount_percent',
            'cashback_amount', 'cashback_discount', 'cashback_commission_change', 'order_uid', 'payment_schedule'
        ];
        
        $result = [];
        
        foreach ($mapping as $index => $field) {
            if (isset($csvRow[$index])) {
                $value = $csvRow[$index];
                
                // Обработка пустых значений
                if ($value === '') {
                    $result[$field] = null;
                    continue;
                }
                
                // Обработка boolean значений из PostgreSQL
                if ($field === 'srv_dbs' || $field === 'is_legal_entity') {
                    $result[$field] = ($value === 't' || $value === 'true' || $value === '1') ? 1 : 0;
                    continue;
                }
                
                // Обработка JSON полей
                if ($field === 'suppliercontract_code') {
                    $result[$field] = json_decode($value, true) ?? null;
                    continue;
                }
                
                // Обработка числовых полей
                $numericFields = ['quantity', 'sale_percent', 'delivery_amount', 'return_amount', 
                                 'ppvz_office_id', 'ppvz_supplier_id', 'report_type'];
                if (in_array($field, $numericFields)) {
                    $result[$field] = (int) $value;
                    continue;
                }
                
                // Обработка decimal полей
                $decimalFields = ['dlv_prc', 'retail_price', 'retail_amount', 'commission_percent', 
                                 'retail_price_withdisc_rub', 'delivery_rub', 'product_discount_for_report',
                                 'supplier_promo', 'ppvz_spp_prc', 'ppvz_kvw_prc_base', 'ppvz_kvw_prc',
                                 'sup_rating_prc_up', 'is_kgvp_v2', 'ppvz_sales_commission', 'ppvz_for_pay',
                                 'ppvz_reward', 'acquiring_fee', 'acquiring_percent', 'ppvz_vw', 'ppvz_vw_nds',
                                 'penalty', 'additional_payment', 'rebill_logistic_cost', 'storage_fee',
                                 'deduction', 'acceptance', 'installment_cofinancing_amount', 
                                 'wibes_wb_discount_percent', 'cashback_amount', 'cashback_discount',
                                 'cashback_commission_change'];
                if (in_array($field, $decimalFields)) {
                    $result[$field] = (float) $value;
                    continue;
                }
                
                // Обработка bigint полей
                $bigintFields = ['realizationreport_id', 'rrd_id', 'gi_id', 'nm_id', 'shk_id', 
                                'sticker_id', 'assembly_id'];
                if (in_array($field, $bigintFields)) {
                    $result[$field] = (string) $value; // Сохраняем как строку для больших чисел
                    continue;
                }
                
                // Для остальных полей оставляем как есть
                $result[$field] = $value;
            } else {
                $result[$field] = null;
            }
        }
        
        // Добавляем timestamps
        $result['created_at'] = now()->format('Y-m-d H:i:s');
        $result['updated_at'] = now()->format('Y-m-d H:i:s');
        
        return $result;
    }

    public function failed(?Throwable $exception): void
    {
        Log::error("UpdateWbRealizationReportViaCsv: Задание завершилось с ошибкой", ['error' => $exception->getMessage()]);
        JobFailed::dispatch('UpdateWbRealizationReportViaCsv', $exception);
    }
}
