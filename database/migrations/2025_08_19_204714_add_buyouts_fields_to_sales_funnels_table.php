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
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->integer('buyouts_count')->default(0)->after('orders_sum_rub');
            $table->integer('buyouts_sum_rub')->default(0)->after('buyouts_count');
            $table->integer('buyout_percent')->default(0)->after('buyouts_sum_rub');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->dropColumn(['buyouts_count', 'buyouts_sum_rub', 'buyout_percent']);
        });
    }
};
