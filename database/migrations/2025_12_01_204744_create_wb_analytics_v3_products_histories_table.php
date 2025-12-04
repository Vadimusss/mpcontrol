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
        Schema::create('wb_analytics_v3_products_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('good_id');
            $table->integer('nm_id');
            $table->string('title');
            $table->string('vendor_code');
            $table->string('brand_name');
            $table->unsignedBigInteger('subject_id')->default(0);
            $table->string('subject_name');
            $table->date('date');
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('cart_count')->default(0);
            $table->unsignedInteger('order_count')->default(0);
            $table->unsignedInteger('order_sum')->default(0);
            $table->unsignedInteger('buyout_count')->default(0);
            $table->unsignedInteger('buyout_sum')->default(0);
            $table->unsignedInteger('cancel_count')->default(0);
            $table->unsignedInteger('cancel_sum_rub')->default(0);
            $table->unsignedInteger('buyout_percent')->default(0);
            $table->unsignedInteger('add_to_cart_conversion')->default(0);
            $table->unsignedInteger('cart_to_order_conversion')->default(0);
            $table->unsignedInteger('add_to_wishlist_count')->default(0);
            $table->timestamps();

            $table->index('good_id');
            $table->index('nm_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_analytics_v3_products_histories');
    }
};
