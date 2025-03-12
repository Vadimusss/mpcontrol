<?php

namespace App\Jobs;

use App\Models\Good;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class MigrateSalesFunnelData implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $timeout = 300,
    )
    {
        //
    }

    public function handle()
    {
        $page = 1;
        $perPage = 100;

        do {
            $oldData = DB::table('wb_parser_analitics_data')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
            Log::info("Starting processing page: {$page}");
            foreach ($oldData as $oldRow) {
                Log::info("Processing row with article: {$oldRow->article}");
                $good = Good::where('nm_id', $oldRow->article)->first();

                if ($good) {
                    // Создаем новую запись в таблице sales_funnels
                    $good->salesFunnel()->create([
                        'vendor_code' => $oldRow->vendor_code,
                        'nm_id' => $oldRow->article,
                        'imt_name' => $oldRow->product_name,
                        'date' => $oldRow->date,
                        'open_card_count' => $oldRow->transitions_to_product_card,
                        'add_to_cart_count' => $oldRow->put_in_basket,
                        'orders_count' => $oldRow->ordered_pcs,
                        'orders_sum_rub' => $oldRow->ordered_for_amount,
                        'advertising_costs' => $oldRow->advertising_costs,
                        'price_with_disc' => 0,
                        'finished_price' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            Log::info("Finished processing page: {$page}");
            $page++;
        } while ($oldData->count() > 0);
    }
}
