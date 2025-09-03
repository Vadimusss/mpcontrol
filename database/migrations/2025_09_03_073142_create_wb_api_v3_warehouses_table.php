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
        Schema::create('wb_api_v3_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->bigInteger('office_id')->index();
            $table->bigInteger('warehouse_id');
            $table->integer('cargo_type');
            $table->integer('delivery_type');
            $table->boolean('is_deleting')->default(false);
            $table->boolean('is_processing')->default(false);
            $table->timestamps();

            $table->index(['shop_id', 'office_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_api_v3_warehouses');
    }
};
