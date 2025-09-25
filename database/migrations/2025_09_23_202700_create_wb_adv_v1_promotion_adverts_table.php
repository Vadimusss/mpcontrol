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
        Schema::create('wb_adv_v1_promotion_adverts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->integer('advert_id');
            $table->string('name');
            $table->integer('type');
            $table->integer('status');
            $table->string('payment_type');
            $table->integer('bid_type');
            $table->integer('daily_budget')->default(0);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->dateTime('change_time')->nullable();
            $table->integer('cpm')->nullable();
            $table->integer('subject_id')->nullable();
            $table->string('subject_name')->nullable();
            $table->boolean('active_carousel')->nullable();
            $table->boolean('active_recom')->nullable();
            $table->boolean('active_booster')->nullable();
            $table->timestamps();

            $table->index(['shop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_adv_v1_promotion_adverts');
    }
};
