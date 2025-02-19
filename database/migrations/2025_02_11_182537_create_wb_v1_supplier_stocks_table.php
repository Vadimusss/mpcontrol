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
        Schema::create('wb_v1_supplier_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->dateTime('last_change_date');
            $table->string('warehouse_name', length: 50);
            $table->string('supplier_article', length: 75);
            $table->integer('nm_id');
            $table->string('barcode', length: 30);
            $table->integer('quantity')->default(0);
            $table->integer('in_way_to_client')->default(0);
            $table->integer('in_way_from_client')->default(0);
            $table->integer('quantity_full')->default(0);
            $table->string('category', length: 50);
            $table->string('subject', length: 50);
            $table->string('brand', length: 50);
            $table->string('tech_size', length: 30);
            $table->float('price', precision: 16);
            $table->float('discount', precision: 16);
            $table->boolean('is_supply');
            $table->boolean('is_realization');
            $table->string('s_c_code', length: 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_v1_supplier_stocks');
    }
};
