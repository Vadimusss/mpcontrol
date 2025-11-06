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
            $table->decimal('commission_total', 14, 2)->default(0)->after('assoc_orders');
            $table->decimal('logistics_total', 14, 2)->default(0)->after('commission_total');
            $table->decimal('storage_total', 14, 2)->default(0)->after('logistics_total');
            $table->decimal('acquiring_total', 14, 2)->default(0)->after('storage_total');
            $table->decimal('other_total', 14, 2)->default(0)->after('acquiring_total');
            $table->decimal('profit_without_ads', 14, 2)->default(0)->after('other_total');
            $table->decimal('profit_with_ads', 14, 2)->default(0)->after('profit_without_ads');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->dropColumn([
                'commission_total',
                'logistics_total',
                'storage_total',
                'acquiring_total',
                'other_total',
                'profit_without_ads',
                'profit_with_ads'
            ]);
        });
    }
};
