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
        Schema::create('temp_wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->integer('nm_id');
            $table->date('dt');
            $table->integer('open_card_count')->default(0);
            $table->integer('add_to_cart_count')->default(0);
            $table->integer('orders_count')->default(0);
            $table->integer('orders_sum_rub')->default(0);
            $table->integer('buyouts_count')->default(0);
            $table->integer('buyouts_sum_rub')->default(0);
            $table->integer('cancel_count')->default(0);
            $table->integer('cancel_sum_rub')->default(0);
            $table->integer('buyout_percent')->default(0);
            $table->float('add_to_cart_conversion', 16)->default(0);
            $table->integer('cart_to_order_conversion')->default(0);
            $table->timestamps();

            $table->index('nm_id');
            $table->index('dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_wb_nm_report_detail_histories');
    }
};
