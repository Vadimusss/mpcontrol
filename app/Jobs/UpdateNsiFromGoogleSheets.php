<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
use App\Services\GoogleSheetsService;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateNsiFromGoogleSheets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopId;

    public function __construct(int $shopId)
    {
        $this->shopId = $shopId;
    }

    public function handle(GoogleSheetsService $sheets)
    {
        $shop = Shop::find($this->shopId);

        $shop->nsis()->delete();

        if (!$shop || !isset($shop->settings['gsheet_url'])) {
            Log::error("Shop not found or gsheet_url missing", ['shop_id' => $this->shopId]);
            return;
        }

        try {
            $data = $sheets->getData($shop->settings['gsheet_url'], 'A:Q');

            foreach ($data as $row) {
                if (empty($row[12] ?? null)) continue; // Пропускаем если нет nm_id (столбец M)

                $good = Good::where('shop_id', $shop->id)
                    ->where('nm_id', $row[12])
                    ->first();

                if ($good) {
                    $cost = !empty($row[10]) && is_numeric(str_replace(',', '.', $row[10]))
                        ? str_replace(',', '.', $row[10])
                        : null;

                    $volume = !empty($row[14]) && is_numeric(str_replace(',', '.', $row[14]))
                        ? str_replace(',', '.', $row[14])
                        : null;

                    $good->nsi()->updateOrCreate([], [
                        'shop_id' => $shop->id,
                        'vendor_code' => $row[0] ?? null,
                        'name' => $row[1] ?? null,
                        'variant' => $row[2] ?? null,
                        'fg_0' => $row[3] ?? null,
                        'fg_1' => $row[4] ?? null,
                        'fg_2' => $row[5] ?? null,
                        'fg_3' => $row[6] ?? null,
                        'set' => $row[7] ?? null,
                        'series' => $row[8] ?? null,
                        'status' => $row[9] ?? null,
                        'cost_with_taxes' => $cost,
                        'barcode' => $row[11] ?? null,
                        'nm_id' => $row[12],
                        'wb_object' => $row[13] ?? null,
                        'wb_volume' => $volume,
                        'wb_1' => $row[15] ?? null,
                        'wb_2' => $row[16] ?? null
                    ]);

                    $shop->update(['last_nsi_update' => now()]);
                }
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Undefined array key') === false) {
                Log::error("Failed to update NSI from Google Sheets", [
                    'shop_id' => $this->shopId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->shopId))->dontRelease()];
    }
}
