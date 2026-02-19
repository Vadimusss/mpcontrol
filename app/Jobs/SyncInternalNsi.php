<?php

namespace App\Jobs;

use App\Events\JobSucceeded;
use App\Events\JobFailed;
use App\Models\Good;
use App\Models\InternalNsi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncInternalNsi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
            $startTime = microtime(true);
            $existingNmIds = Good::pluck('nm_id')->filter()->toArray();
            
            $externalData = DB::connection('ozon_api')
                ->table('internal.mv_products')
                ->select([
                    'cabinet',
                    'article_wb',
                    'sku_wb',
                    'article_oz',
                    'sku_oz',
                    'product_name',
                    'fg_0',
                    'fg_1',
                    'fg_2',
                    'fg_3',
                    'brand',
                    'subject',
                    'category_oz',
                    'barcode',
                    'cost_price',
                ])
                ->whereIn('sku_wb', $existingNmIds)
                ->get();

            $dataToInsert = [];
            $goodsByNmId = Good::whereIn('nm_id', $existingNmIds)
                ->pluck('id', 'nm_id')
                ->toArray();

            foreach ($externalData as $row) {
                $goodId = $goodsByNmId[$row->sku_wb] ?? null;
                if (!$goodId) {
                    continue;
                }

                $dataToInsert[] = [
                    'good_id' => $goodId,
                    'cabinet' => $row->cabinet,
                    'article_wb' => $row->article_wb,
                    'sku_wb' => $row->sku_wb,
                    'article_oz' => $row->article_oz,
                    'sku_oz' => $row->sku_oz,
                    'product_name' => $row->product_name,
                    'fg_0' => $row->fg_0,
                    'fg_1' => $row->fg_1,
                    'fg_2' => $row->fg_2,
                    'fg_3' => $row->fg_3,
                    'brand' => $row->brand,
                    'subject' => $row->subject,
                    'category_oz' => $row->category_oz,
                    'barcode' => $row->barcode,
                    'cost_price' => $row->cost_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::transaction(function () use ($dataToInsert) {
                InternalNsi::query()->delete();
                
                if (!empty($dataToInsert)) {
                    $chunks = array_chunk($dataToInsert, 500);
                    foreach ($chunks as $chunk) {
                        InternalNsi::insert($chunk);
                    }
                }
            });

            $syncCount = count($dataToInsert);
            Log::info("Internal NSI sync completed successfully", [
                'synced_records' => $syncCount,
                'total_external_records' => $externalData->count(),
            ]);

        $message = "Внутренний НСИ синхронизирован - {$syncCount} записей";
        $duration = microtime(true) - $startTime;

        JobSucceeded::dispatch('SyncInternalNsiF', $duration, $message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('SyncInternalNsi', $exception);
    }
}