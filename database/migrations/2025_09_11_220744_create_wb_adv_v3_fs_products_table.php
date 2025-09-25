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
        Schema::create('wb_adv_v3_fs_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_adv_v3_fs_app_id')
                ->constrained('wb_adv_v3_fs_apps', 'id')
                ->cascadeOnDelete();
            $table->foreignId('good_id')->nullable();
            $table->date('date');
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
            $table->string('name');
            $table->integer('nm_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_adv_v3_fs_products');
    }
};
