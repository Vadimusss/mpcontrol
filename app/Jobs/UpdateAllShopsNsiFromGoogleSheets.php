<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAllShopsNsiFromGoogleSheets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $shops = Shop::whereNotNull('settings->gsheet_url')->get();

        foreach ($shops as $shop) {
            UpdateNsiFromGoogleSheets::dispatch($shop->id);
        }
    }
}
