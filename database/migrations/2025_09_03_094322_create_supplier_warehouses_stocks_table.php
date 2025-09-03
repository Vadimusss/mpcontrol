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
        Schema::create('supplier_warehouses_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->date('date')->index();
            $table->bigInteger('office_id');
            $table->string('warehouse_name');
            $table->bigInteger('warehouse_id');
            $table->string('barcode')->index();
            $table->integer('amount');
            $table->timestamps();

            $table->index(['shop_id', 'date']);
            $table->index(['shop_id', 'barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_warehouses_stocks');
    }
};
