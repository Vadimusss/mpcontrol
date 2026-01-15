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
            $table->integer('assoc_orders_from_this_sum')->default(0)->after('assoc_orders_from_this');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->dropColumn('assoc_orders_from_this_sum');
        });
    }
};
