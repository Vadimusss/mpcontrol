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
        Schema::table('wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->renameColumn('imtName', 'imt_name');
            $table->renameColumn('openCardCount', 'open_card_count');
            $table->renameColumn('addToCartCount', 'add_to_cart_count');
            $table->renameColumn('ordersCount', 'orders_count');
            $table->renameColumn('ordersSumRub', 'orders_sum_rub');
            $table->renameColumn('buyoutsCount', 'buyouts_count');
            $table->renameColumn('buyoutsSumRub', 'buyouts_sum_rub');
            $table->renameColumn('buyoutPercent', 'buyout_percent');
            $table->renameColumn('addToCartConversion', 'add_to_cart_conversion');
            $table->renameColumn('cartToOrderConversion', 'cart_to_order_conversion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->renameColumn('imt_name', 'imtName');
            $table->renameColumn('open_card_count', 'openCardCount');
            $table->renameColumn('add_to_cart_count', 'addToCartCount');
            $table->renameColumn('orders_count', 'ordersCount');
            $table->renameColumn('orders_sum_rub', 'ordersSumRub');
            $table->renameColumn('buyouts_count', 'buyoutsCount');
            $table->renameColumn('buyouts_sum_rub', 'buyoutsSumRub');
            $table->renameColumn('buyout_percent', 'buyoutPercent');
            $table->renameColumn('add_to_cart_conversion', 'addToCartConversion');
            $table->renameColumn('cart_to_order_conversion', 'cartToOrderConversion');
        });
    }
};
