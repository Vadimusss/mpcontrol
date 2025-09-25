<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wb_adv_v3_fs_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_adv_v3_fullstats_wb_advert_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('date');
            $table->integer('views');
            $table->integer('clicks');
            $table->decimal('ctr', 8, 2);
            $table->decimal('cpc', 8, 2);
            $table->decimal('sum', 12, 2);
            $table->integer('atbs');
            $table->integer('orders');
            $table->decimal('cr', 8, 2);
            $table->integer('shks');
            $table->decimal('sum_price', 12, 2);
            $table->integer('canceled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_adv_v3_fs_days');
    }
};
