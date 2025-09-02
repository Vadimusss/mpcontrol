<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wb_v1_supplier_orders', function (Blueprint $table) {
            $table->index(['shop_id', 'date', 'warehouse_name', 'nm_id'], 'idx_wb_v1_supplier_orders_query_optimization');
        });
    }

    public function down(): void
    {
        Schema::table('wb_v1_supplier_orders', function (Blueprint $table) {
            $table->dropIndex('idx_wb_v1_supplier_orders_query_optimization');
        });
    }
};
