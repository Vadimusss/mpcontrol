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
        Schema::create('wb_v1_supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('good_id');
            $table->dateTime('date');
            $table->dateTime('last_change_date');
            $table->string('warehouse_name', length: 50);
            $table->string('warehouse_type');
            $table->string('country_name', length: 200);
            $table->string('oblast_okrug_name', length: 200);
            $table->string('region_name', length: 200);
            $table->string('supplier_article', length: 75);
            $table->integer('nm_id');
            $table->string('barcode', length: 30);
            $table->string('category', length: 50);
            $table->string('subject', length: 50);
            $table->string('brand', length: 50);
            $table->string('tech_size', length: 30);
            $table->integer('income_id');
            $table->boolean('is_supply');
            $table->boolean('is_realization');
            $table->float('total_price', precision: 16);
            $table->integer('discount_percent');
            $table->float('spp', precision: 16);
            $table->float('finished_price', precision: 16);
            $table->float('price_with_disc', precision: 16);
            $table->boolean('is_cancel');
            $table->dateTime('cancel_date');
            $table->string('order_type');
            $table->string('sticker');
            $table->string('g_number', length: 50);
            $table->string('srid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_v1_supplier_orders');
    }
};
