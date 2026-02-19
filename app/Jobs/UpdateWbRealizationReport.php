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

class UpdateWbRealizationReport implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public string $date,
    ) {
        $this->shop = $shop;
        $this->date = $date;
    }

    public $timeout = 500;
    public $tries = 1;

    public function handle(): void
    {
        $startTime = microtime(true);

        $externalConnection = DB::connection('ozon_api');

        $totalRecords = $externalConnection->table('wb.wb_realization_report')
            ->where('cabinet', $this->shop->id)
            ->where('date_from', $this->date)
            ->count();

        if ($totalRecords === 0) {
            $message = "Нет данных WbRealizationReport для магазина {$this->shop->name} за {$this->date}";

            $duration = microtime(true) - $startTime;
            JobSucceeded::dispatch('UpdateWbRealizationReport', $duration, $message);
            return;
        }

        DB::table('wb_realization_reports')
            ->where('cabinet', $this->shop->id)
            ->where('date_from', $this->date)
            ->delete();

        $csvData = $this->exportDataViaSelect($externalConnection);

        if (empty($csvData)) {
            throw new Throwable("Не удалось экспортировать данные в CSV");
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'wb_report_' . $this->shop->id . '_' . $this->date . '_');

        file_put_contents($tempFile, $csvData);

        $loadedCount = $this->loadDataFromCsv($tempFile);

        unlink($tempFile);

        $message = "Данные WbRealizationReport для магазина {$this->shop->name} за {$this->date} успешно загружены. Записей: {$loadedCount}";

        $duration = microtime(true) - $startTime;

        JobSucceeded::dispatch('UpdateWbRealizationReport', $duration, $message);
    }

    private function exportDataViaSelect($externalConnection): string
    {
        $data = $externalConnection->table('wb.wb_realization_report')
            ->where('cabinet', $this->shop->id)
            ->where('date_from', $this->date)
            ->orderBy('rrd_id')
            ->get();

        if ($data->isEmpty()) {
            return '';
        }

        $csv = fopen('php://temp', 'r+');

        $headers = [
            'cabinet',
            'inserted_at',
            'realizationreport_id',
            'date_from',
            'date_to',
            'create_dt',
            'currency_name',
            'suppliercontract_code',
            'rrd_id',
            'gi_id',
            'dlv_prc',
            'fix_tariff_date_from',
            'fix_tariff_date_to',
            'subject_name',
            'nm_id',
            'brand_name',
            'sa_name',
            'ts_name',
            'barcode',
            'doc_type_name',
            'quantity',
            'retail_price',
            'retail_amount',
            'sale_percent',
            'commission_percent',
            'office_name',
            'supplier_oper_name',
            'order_dt',
            'sale_dt',
            'rr_dt',
            'shk_id',
            'retail_price_withdisc_rub',
            'delivery_amount',
            'return_amount',
            'delivery_rub',
            'gi_box_type_name',
            'product_discount_for_report',
            'supplier_promo',
            'ppvz_spp_prc',
            'ppvz_kvw_prc_base',
            'ppvz_kvw_prc',
            'sup_rating_prc_up',
            'is_kgvp_v2',
            'ppvz_sales_commission',
            'ppvz_for_pay',
            'ppvz_reward',
            'acquiring_fee',
            'acquiring_percent',
            'payment_processing',
            'acquiring_bank',
            'ppvz_vw',
            'ppvz_vw_nds',
            'ppvz_office_name',
            'ppvz_office_id',
            'ppvz_supplier_id',
            'ppvz_supplier_name',
            'ppvz_inn',
            'declaration_number',
            'bonus_type_name',
            'sticker_id',
            'site_country',
            'srv_dbs',
            'penalty',
            'additional_payment',
            'rebill_logistic_cost',
            'rebill_logistic_org',
            'storage_fee',
            'deduction',
            'acceptance',
            'assembly_id',
            'kiz',
            'srid',
            'report_type',
            'is_legal_entity',
            'trbx_id',
            'installment_cofinancing_amount',
            'wibes_wb_discount_percent',
            'cashback_amount',
            'cashback_discount',
            'cashback_commission_change',
            'order_uid',
            'payment_schedule'
        ];

        fputcsv($csv, $headers);

        foreach ($data as $row) {
            $rowArray = (array) $row;

            $this->processDatesForCsv($rowArray);

            $csvRow = [];
            foreach ($headers as $header) {
                $value = $rowArray[$header] ?? '';

                if ($header === 'suppliercontract_code' && is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

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

    private function processDatesForCsv(array &$rowArray): void
    {
        $dateFields = [
            'inserted_at',
            'create_dt',
            'date_from',
            'date_to',
            'fix_tariff_date_from',
            'fix_tariff_date_to',
            'order_dt',
            'sale_dt',
            'rr_dt'
        ];

        foreach ($dateFields as $field) {
            if (isset($rowArray[$field]) && !empty($rowArray[$field])) {
                $value = $rowArray[$field];

                if ($value instanceof \DateTimeInterface) {
                    $rowArray[$field] = $value->format('Y-m-d H:i:s');
                } elseif (is_string($value)) {
                    $tzPos = strpos($value, '+');
                    if ($tzPos === false && strlen($value) > 11) {
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

                    if (in_array($field, ['date_from', 'date_to', 'create_dt', 'rr_dt', 'fix_tariff_date_from', 'fix_tariff_date_to'])) {
                        if (strlen($rowArray[$field]) > 10) {
                            $rowArray[$field] = substr($rowArray[$field], 0, 10);
                        }
                    }
                }
            }
        }
    }

    private function loadDataFromCsv(string $csvFilePath)
    {
        $escapedPath = DB::getPdo()->quote($csvFilePath);

        $loadDataSql = "
                LOAD DATA LOCAL INFILE {$escapedPath}
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

        $affectedRows = DB::affectingStatement($loadDataSql);

        return $affectedRows;
    }

    public function failed(Throwable $exception): void
    {
        JobFailed::dispatch('UpdateWbRealizationReport', $exception);
    }
}
