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
        Schema::create('stocks_and_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->string('barcode', length: 30);
            $table->string('supplier_article', length: 75);
            $table->integer('nm_id');
            $table->string('warehouse_name', length: 50);
            $table->date('date');
            $table->integer('quantity')->default(0);
            $table->integer('orders_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks_and_orders');
    }
};
