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
        Schema::create('sales_funnels', function (Blueprint $table) {
            $table->id();
            $table->integer('good_id');
            $table->string('vendor_code');
            $table->integer('nm_id');
            $table->string('imt_name');
            $table->date('date');
            $table->integer('open_card_count')->default(0);
            $table->integer('add_to_cart_count')->default(0);
            $table->integer('orders_count')->default(0);
            $table->integer('orders_sum_rub')->default(0);
            $table->integer('advertising_costs')->default(0);            
            $table->float('price_with_disc', precision: 16)->default(0);
            $table->float('finished_price', precision: 16)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_funnels');
    }
};
