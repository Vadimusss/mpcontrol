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
        Schema::create('wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('good_id');
            $table->integer('nm_id');
            $table->string('imtName');
            $table->string('vendor_code');
            $table->date('dt');
            $table->integer('openCardCount')->default(0);
            $table->integer('addToCartCount')->default(0);
            $table->integer('ordersCount')->default(0);
            $table->integer('ordersSumRub')->default(0);
            $table->integer('buyoutsCount')->default(0);
            $table->integer('buyoutsSumRub')->default(0);
            $table->integer('buyoutPercent')->default(0);
            $table->float('addToCartConversion', precision: 16)->default(0);
            $table->integer('cartToOrderConversion')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_nm_report_detail_histories');
    }
};
